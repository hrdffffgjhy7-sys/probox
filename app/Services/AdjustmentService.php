<?php

namespace App\Services;

use App\Models\Adjustment;
use App\Models\Supplier;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdjustmentService
{
    public function __construct(
        private LedgerService $ledger,
        private AuditService $audit
    ) {
    }

    public function create(array $data, int $userId): Adjustment
    {
        return DB::transaction(function () use ($data, $userId) {
            $supplier = Supplier::findOrFail($data['supplier_id']);
            $amount = Money::normalize($data['amount']);

            if (Money::cmp($amount, 0) <= 0) {
                throw ValidationException::withMessages(['amount' => __('ui.amount_positive')]);
            }

            $adjustment = Adjustment::create([
                'supplier_id' => $supplier->id,
                'adjustment_date' => $data['adjustment_date'],
                'amount' => $amount,
                'direction' => $data['direction'],
                'category' => $data['category'] ?? 'payable',
                'reason' => $data['reason'],
                'reference_no' => $data['reference_no'] ?? null,
                'created_by' => $userId,
            ]);

            $isReceivable = ($data['category'] ?? 'payable') === 'receivable';
            $increases = $data['direction'] === 'increase';

            if ($isReceivable) {
                $debit = $increases ? $amount : '0';
                $credit = $increases ? '0' : $amount;
            } else {
                $credit = $increases ? $amount : '0';
                $debit = $increases ? '0' : $amount;
            }

            $this->ledger->record(
                $supplier,
                $adjustment->adjustment_date->toDateString(),
                'adjustment',
                __('ui.adjustment').': '.$adjustment->reason,
                $debit,
                $credit,
                'adjustment',
                $adjustment->id,
                $userId
            );

            $this->audit->log('created', 'adjustments', $adjustment, 'Adjustment created', [], $adjustment->toArray());

            return $adjustment;
        });
    }

    public function delete(Adjustment $adjustment): void
    {
        DB::transaction(function () use ($adjustment) {
            $old = $adjustment->toArray();
            $this->ledger->removeByReference($adjustment->supplier, 'adjustment', $adjustment->id);
            $adjustment->delete();
            $this->audit->log('deleted', 'adjustments', $adjustment, 'Adjustment deleted', $old, []);
        });
    }
}
