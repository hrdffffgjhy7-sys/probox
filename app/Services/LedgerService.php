<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    public function record(
        Supplier $supplier,
        string $date,
        string $type,
        string $description,
        string $debit = '0',
        string $credit = '0',
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $userId = null
    ): SupplierLedger {
        $debit = Money::normalize($debit);
        $credit = Money::normalize($credit);

        if (Money::cmp($debit, 0) < 0 || Money::cmp($credit, 0) < 0) {
            throw new \InvalidArgumentException('Ledger debit and credit must be positive amounts.');
        }

        $entry = SupplierLedger::create([
            'supplier_id' => $supplier->id,
            'entry_date' => $date,
            'type' => $type,
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'debit' => $debit,
            'credit' => $credit,
            'running_balance' => 0,
            'created_by' => $userId ?? auth()->id(),
        ]);

        $this->recalculate($supplier);

        return $entry->refresh();
    }

    public function removeByReference(Supplier $supplier, string $referenceType, int $referenceId): void
    {
        SupplierLedger::where('supplier_id', $supplier->id)
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->delete();

        $this->recalculate($supplier);
    }

    public function recalculate(Supplier $supplier): string
    {
        return DB::transaction(function () use ($supplier) {
            $running = '0.00';
            $last = null;

            $entries = SupplierLedger::where('supplier_id', $supplier->id)
                ->orderBy('entry_date')
                ->orderBy('id')
                ->get();

            foreach ($entries as $entry) {
                $running = Money::add($running, $entry->credit, '-'.$entry->debit);
                $entry->running_balance = $running;
                $entry->saveQuietly();
                $last = $entry->created_at;
            }

            $supplier->forceFill([
                'current_balance' => $running,
                'last_transaction_at' => $last,
            ])->saveQuietly();

            return $running;
        });
    }

    public function openingEntry(Supplier $supplier): void
    {
        $amount = Money::normalize($supplier->opening_balance);

        if (Money::cmp($amount, 0) <= 0) {
            return;
        }

        if ($supplier->opening_balance_type === 'receivable') {
            $this->record($supplier, $supplier->created_at?->toDateString() ?? now()->toDateString(), 'opening', __('ui.opening_balance'), $amount, '0', 'supplier', $supplier->id);
        } else {
            $this->record($supplier, $supplier->created_at?->toDateString() ?? now()->toDateString(), 'opening', __('ui.opening_balance'), '0', $amount, 'supplier', $supplier->id);
        }
    }
}
