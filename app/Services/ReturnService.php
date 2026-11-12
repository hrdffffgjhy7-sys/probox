<?php

namespace App\Services;

use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnService
{
    public function __construct(
        private LedgerService $ledger,
        private AuditService $audit
    ) {
    }

    public function create(array $data, int $userId): PurchaseReturn
    {
        return DB::transaction(function () use ($data, $userId) {
            $supplier = Supplier::findOrFail($data['supplier_id']);
            $amount = Money::normalize($data['amount']);

            if (Money::cmp($amount, 0) <= 0) {
                throw ValidationException::withMessages(['amount' => __('ui.amount_positive')]);
            }

            $return = PurchaseReturn::create([
                'supplier_id' => $supplier->id,
                'purchase_id' => $data['purchase_id'] ?? null,
                'invoice_no' => $data['invoice_no'] ?? null,
                'return_date' => $data['return_date'],
                'amount' => $amount,
                'reason' => $data['reason'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'created_by' => $userId,
            ]);

            $this->ledger->record(
                $supplier,
                $return->return_date->toDateString(),
                'return',
                __('ui.purchase_return').($return->invoice_no ? ' - '.$return->invoice_no : ''),
                $amount,
                '0',
                'purchase_return',
                $return->id,
                $userId
            );

            $this->audit->log('created', 'purchase_returns', $return, 'Purchase return recorded', [], $return->toArray());

            return $return;
        });
    }

    public function delete(PurchaseReturn $return): void
    {
        DB::transaction(function () use ($return) {
            $old = $return->toArray();
            $this->ledger->removeByReference($return->supplier, 'purchase_return', $return->id);
            $return->delete();
            $this->audit->log('deleted', 'purchase_returns', $return, 'Purchase return deleted', $old, []);
        });
    }
}
