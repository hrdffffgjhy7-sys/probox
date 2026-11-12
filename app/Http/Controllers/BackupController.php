<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Services\AuditService;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class BackupController extends Controller
{
    public function __construct(
        private BackupService $backups,
        private AuditService $audit
    ) {
    }

    public function index()
    {
        abort_unless(auth()->user()->can('backups.manage'), 403);

        $backups = Backup::with('creator')->orderByDesc('id')->paginate(20);
        $directory = $this->backups->directory();
        $last = Backup::latest('id')->first();

        return view('backups.index', compact('backups', 'directory', 'last'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->can('backups.manage'), 403);
        $data = $request->validate([
            'type' => ['nullable', 'in:manual,json'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $backup = $this->backups->create($data['type'] ?? 'manual', $request->user()->id, $data['notes'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withErrors(['backup' => $e->getMessage()]);
        }

        return redirect()->route('backups.index')->with('success', __('Backup created successfully.').' '.$backup->name);
    }

    public function json(Request $request)
    {
        abort_unless($request->user()->can('backups.manage'), 403);

        try {
            $backup = $this->backups->create('json', $request->user()->id, 'JSON export');
        } catch (RuntimeException $e) {
            return back()->withErrors(['backup' => $e->getMessage()]);
        }

        return redirect()->route('backups.index')->with('success', __('Backup created successfully.').' '.$backup->name);
    }

    public function download(Backup $backup)
    {
        abort_unless(auth()->user()->can('backups.manage'), 403);
        $path = storage_path('app/'.$backup->path);
        abort_unless(File::exists($path), 404);

        return response()->download($path, $backup->name);
    }

    public function verify(Request $request, Backup $backup)
    {
        abort_unless($request->user()->can('backups.manage'), 403);
        $ok = $this->backups->verify($backup);
        $this->audit->log('verified', 'backups', $backup, 'Backup verification '.($ok ? 'passed' : 'failed'));

        return back()->with($ok ? 'success' : 'error', $ok ? __('Backup verified successfully.') : __('Backup verification failed.'));
    }

    public function restore(Request $request, Backup $backup)
    {
        abort_unless($request->user()->isAdmin() && $request->user()->can('backups.manage'), 403);

        try {
            $this->backups->restore($backup);
        } catch (RuntimeException $e) {
            return back()->withErrors(['restore' => __('Restore failed.').' '.$e->getMessage()]);
        }

        return redirect()->route('backups.index')->with('success', __('Backup restored successfully.'));
    }

    public function destroy(Request $request, Backup $backup)
    {
        abort_unless($request->user()->isAdmin() && $request->user()->can('backups.manage'), 403);

        try {
            $this->backups->delete($backup);
        } catch (RuntimeException $e) {
            return back()->withErrors(['delete' => $e->getMessage()]);
        }

        return back()->with('success', __('Deleted successfully.'));
    }

    public function upload(Request $request)
    {
        abort_unless($request->user()->isAdmin() && $request->user()->can('backups.manage'), 403);
        $request->validate(['file' => ['required', 'file', 'mimes:sqlite,sql,db,json', 'max:512000']]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $dir = $this->backups->directory();
        $filename = 'uploaded-'.now()->format('Ymd-His').'-'.Str::random(4).'.'.$extension;
        $file->move($dir, $filename);
        $path = $dir.DIRECTORY_SEPARATOR.$filename;

        $backup = Backup::create([
            'name' => $filename,
            'type' => 'uploaded',
            'disk' => 'local',
            'path' => 'backups/'.$filename,
            'size' => File::size($path),
            'checksum' => hash_file('sha256', $path),
            'status' => 'completed',
            'notes' => 'Uploaded backup',
            'created_by' => $request->user()->id,
        ]);

        try {
            $this->backups->restore($backup);
        } catch (RuntimeException $e) {
            return back()->withErrors(['file' => __('Restore failed.').' '.$e->getMessage()]);
        }

        return redirect()->route('backups.index')->with('success', __('Uploaded backup restored successfully.'));
    }
}
