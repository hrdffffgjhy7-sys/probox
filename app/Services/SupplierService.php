<?php

namespace App\Services;

use App\Models\Supplier;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    public function __construct(
        private LedgerService $ledger,
        private AuditService $audit
    ) {
    }

    public function create(array $data, int $userId): Supplier
    {
        return DB::transaction(function () use ($data, $userId) {
            $supplier = Supplier::create([
                'name' => $data['name'],
                'agency_id' => $data['agency_id'] ?? null,
                'salesman_id' => $data['salesman_id'] ?? null,
                'phone' => $data['phone'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'address' => $data['address'] ?? null,
                'opening_balance' => Money::normalize($data['opening_balance'] ?? 0),
                'opening_balance_type' => $data['opening_balance_type'] ?? 'payable',
                'notes' => $data['notes'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'created_by' => $userId,
            ]);

            $this->ledger->openingEntry($supplier);
            $this->audit->log('created', 'suppliers', $supplier, 'Supplier created', [], $supplier->toArray());

            return $supplier->fresh();
        });
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        return DB::transaction(function () use ($supplier, $data) {
            $old = $supplier->toArray();
            $oldOpening = Money::normalize($supplier->opening_balance);
            $oldType = $supplier->opening_balance_type;

            $supplier->update([
                'name' => $data['name'],
                'agency_id' => $data['agency_id'] ?? null,
                'salesman_id' => $data['salesman_id'] ?? null,
                'phone' => $data['phone'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'address' => $data['address'] ?? null,
                'opening_balance' => Money::normalize($data['opening_balance'] ?? 0),
                'opening_balance_type' => $data['opening_balance_type'] ?? 'payable',
                'notes' => $data['notes'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $newOpening = Money::normalize($supplier->opening_balance);
            if (Money::cmp($oldOpening, $newOpening) !== 0 || $oldType !== $supplier->opening_balance_type) {
                $this->ledger->removeByReference($supplier, 'supplier', $supplier->id);
                $this->ledger->openingEntry($supplier);
            }

            $this->audit->log('updated', 'suppliers', $supplier, 'Supplier updated', $old, $supplier->fresh()->toArray());

            return $supplier->fresh();
        });
    }

    public function archive(Supplier $supplier): void
    {
        $old = $supplier->toArray();
        $supplier->update(['is_active' => false, 'archived_at' => now()]);
        $this->audit->log('archived', 'suppliers', $supplier, 'Supplier archived', $old, $supplier->fresh()->toArray());
    }

    public function restore(Supplier $supplier): void
    {
        $old = $supplier->toArray();
        $supplier->update(['is_active' => true, 'archived_at' => null]);
        $this->audit->log('restored', 'suppliers', $supplier, 'Supplier restored', $old, $supplier->fresh()->toArray());
    }

    public function delete(Supplier $supplier): void
    {
        DB::transaction(function () use ($supplier) {
            $old = $supplier->toArray();

            if ($supplier->ledger()->exists()) {
                $supplier->update(['is_active' => false, 'archived_at' => now()]);
                $supplier->delete();
                $this->audit->log('archived_deleted', 'suppliers', $supplier, 'Supplier with financial history archived (soft deleted)', $old, []);
                return;
            }

            $supplier->forceDelete();
            $this->audit->log('deleted', 'suppliers', $supplier, 'Supplier permanently deleted (no history)', $old, []);
        });
    }

    public function forceDelete(Supplier $supplier): void
    {
        if ($supplier->ledger()->exists()) {
            throw new \RuntimeException(__('ui.permanent_delete_blocked'));
        }

        $old = $supplier->toArray();
        $supplier->forceDelete();
        $this->audit->log('force_deleted', 'suppliers', $supplier, 'Supplier permanently deleted by admin', $old, []);
    }
}
