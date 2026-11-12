<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class BackupService
{
    public function __construct(private AuditService $audit)
    {
    }

    public function directory(): string
    {
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        return $dir;
    }

    public function create(string $type = 'manual', ?int $userId = null, ?string $notes = null): Backup
    {
        $dir = $this->directory();

        $driver = config('database.default');
        $stamp = now()->format('Ymd-His');
        $base = 'kiryana-'.$stamp.'-'.substr(bin2hex(random_bytes(3)), 0, 6);

        if ($type === 'json') {
            $filename = $base.'.json';
            $path = $dir.DIRECTORY_SEPARATOR.$filename;
            File::put($path, $this->exportJson());
        } elseif ($type === 'sql' || $driver === 'mysql') {
            $filename = $base.'.sql';
            $path = $dir.DIRECTORY_SEPARATOR.$filename;
            $this->dumpMySql($path);
        } elseif ($driver === 'sqlite') {
            $filename = $base.'.sqlite';
            $path = $dir.DIRECTORY_SEPARATOR.$filename;
            $source = config('database.connections.sqlite.database');
            if (! $source || ! File::exists($source)) {
                throw new RuntimeException(__('ui.database_file_missing'));
            }
            File::copy($source, $path);
        } else {
            $filename = $base.'.json';
            $path = $dir.DIRECTORY_SEPARATOR.$filename;
            File::put($path, $this->exportJson());
        }

        $size = File::size($path);
        $checksum = hash_file('sha256', $path);

        $backup = Backup::create([
            'name' => $filename,
            'type' => $type === 'auto' ? 'auto' : $type,
            'disk' => 'local',
            'path' => 'backups/'.$filename,
            'size' => $size,
            'checksum' => $checksum,
            'status' => 'completed',
            'notes' => $notes,
            'created_by' => $userId,
        ]);

        $offsite = env('BACKUP_OFFSITE_PATH');
        if ($offsite) {
            File::ensureDirectoryExists($offsite);
            File::copy($path, rtrim($offsite, '\\/').DIRECTORY_SEPARATOR.$filename);
        }

        $this->prune();
        $this->audit->log('created', 'backups', $backup, 'Backup created ('.$type.')', [], $backup->toArray());

        return $backup;
    }

    public function verify(Backup $backup): bool
    {
        $path = storage_path('app/'.$backup->path);
        if (! File::exists($path) || File::size($path) === 0) {
            return false;
        }
        if ($backup->checksum && hash_file('sha256', $path) !== $backup->checksum) {
            return false;
        }

        if (str_ends_with($path, '.json')) {
            json_decode(File::get($path), true);
            return json_last_error() === JSON_ERROR_NONE;
        }

        if (str_ends_with($path, '.sqlite')) {
            try {
                $pdo = new \PDO('sqlite:'.$path);
                $result = $pdo->query('PRAGMA integrity_check')->fetchColumn();
                return $result === 'ok';
            } catch (\Throwable) {
                return false;
            }
        }

        return true;
    }

    public function restore(Backup $backup): void
    {
        $path = storage_path('app/'.$backup->path);
        if (! File::exists($path)) {
            throw new RuntimeException(__('ui.backup_not_found'));
        }
        if (! $this->verify($backup)) {
            throw new RuntimeException(__('ui.backup_verification_failed'));
        }

        $safety = $this->create('manual', auth()->id());
        $driver = config('database.default');

        if (str_ends_with($path, '.sqlite') && $driver === 'sqlite') {
            DB::disconnect();
            $target = config('database.connections.sqlite.database');
            File::copy($path, $target);
            DB::purge();
        } elseif (str_ends_with($path, '.json')) {
            $this->importJson(File::get($path));
        } elseif (str_ends_with($path, '.sql') && $driver === 'mysql') {
            $this->restoreMySql($path);
        } else {
            throw new RuntimeException(__('ui.restore_format_unsupported'));
        }

        $this->audit->log('restored', 'backups', $backup, 'Backup restored. Safety backup: '.$safety->name, [], []);
    }

    public function delete(Backup $backup): void
    {
        $path = storage_path('app/'.$backup->path);
        if (File::exists($path)) {
            File::delete($path);
        }
        $backup->delete();
    }

    private function exportJson(): string
    {
        $tables = $this->tables();
        $data = ['meta' => ['app' => config('app.name'), 'created_at' => now()->toIso8601String(), 'driver' => config('database.default')], 'tables' => []];
        foreach ($tables as $table) {
            $data['tables'][$table] = DB::table($table)->get()->toArray();
        }

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function importJson(string $json): void
    {
        $data = json_decode($json, true);
        if (! isset($data['tables']) || ! is_array($data['tables'])) {
            throw new RuntimeException(__('ui.invalid_backup'));
        }

        $driver = config('database.default');
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');
        }

        try {
            DB::transaction(function () use ($data, $driver) {
                foreach (array_reverse($this->tables()) as $table) {
                    DB::table($table)->delete();
                }
                foreach ($this->tables() as $table) {
                    $rows = $data['tables'][$table] ?? [];
                    foreach (array_chunk($rows, 200) as $chunk) {
                        DB::table($table)->insert(array_map(fn ($row) => (array) $row, $chunk));
                    }
                }
            });
        } finally {
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys=ON');
            }
        }
    }

    private function dumpMySql(string $path): void
    {
        $cfg = config('database.connections.mysql');
        $mysqldump = env('MYSQLDUMP_PATH', 'mysqldump');
        $command = sprintf(
            '"%s" --user=%s --password=%s --host=%s --port=%s %s > "%s" 2>&1',
            $mysqldump,
            escapeshellarg($cfg['username']),
            escapeshellarg($cfg['password']),
            escapeshellarg($cfg['host']),
            escapeshellarg((string) $cfg['port']),
            escapeshellarg($cfg['database']),
            $path
        );

        exec($command, $output, $code);
        if ($code !== 0 || ! File::exists($path) || File::size($path) === 0) {
            File::put($path, $this->exportJson());
        }
    }

    private function restoreMySql(string $path): void
    {
        $cfg = config('database.connections.mysql');
        $mysql = env('MYSQL_PATH', 'mysql');
        $command = sprintf(
            '"%s" --user=%s --password=%s --host=%s --port=%s %s < "%s" 2>&1',
            $mysql,
            escapeshellarg($cfg['username']),
            escapeshellarg($cfg['password']),
            escapeshellarg($cfg['host']),
            escapeshellarg((string) $cfg['port']),
            escapeshellarg($cfg['database']),
            $path
        );
        exec($command, $output, $code);
        if ($code !== 0) {
            throw new RuntimeException(__('ui.restore_failed').': '.implode(' ', $output));
        }
    }

    private function tables(): array
    {
        return [
            'roles', 'permissions', 'role_permission', 'users', 'agencies', 'salesmen',
            'suppliers', 'products', 'purchases', 'purchase_items', 'payments',
            'purchase_returns', 'supplier_ledger', 'adjustments', 'expenses',
            'attachments', 'audit_logs', 'notifications', 'settings',
        ];
    }

    private function prune(): void
    {
        $all = Backup::orderByDesc('id')->get();
        if ($all->count() <= 10) {
            return;
        }
        foreach ($all->slice(10) as $old) {
            if ($all->count() <= 3) {
                break;
            }
            $this->delete($old);
            $all = Backup::orderByDesc('id')->get();
        }
    }
}
