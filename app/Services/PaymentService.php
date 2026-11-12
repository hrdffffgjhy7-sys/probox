<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private LedgerService $ledger,
        private PurchaseService $purchases,
        private AuditService $audit
    ) {
    }

    public function create(array $data, int $userId): Payment
    {
        return DB::transaction(function () use ($data, $userId) {
            $supplier = Supplier::findOrFail($data['supplier_id']);
            $amount = Money::normalize($data['amount']);

            if (Money::cmp($amount, 0) <= 0) {
                throw ValidationException::withMessages(['amount' => __('ui.amount_positive')]);
            }

            $purchase = null;
            if (! empty($data['purchase_id'])) {
                $purchase = Purchase::where('supplier_id', $supplier->id)->findOrFail($data['purchase_id']);
                if (Money::cmp($amount, $purchase->remaining_amount) > 0) {
                    throw ValidationException::withMessages(['amount' => __('ui.payment_exceeds_invoice')]);
                }
            }

            if (! empty($data['reference_no'])) {
                $exists = Payment::where('reference_no', $data['reference_no'])->exists();
                if ($exists) {
                    throw ValidationException::withMessages(['reference_no' => __('ui.duplicate_reference')]);
                }
            }

            $payment = Payment::create([
                'supplier_id' => $supplier->id,
                'salesman_id' => $data['salesman_id'] ?? null,
                'purchase_id' => $purchase?->id,
                'payment_date' => $data['payment_date'],
                'amount' => $amount,
                'method' => $data['method'],
                'is_auto' => false,
                'reference_no' => $data['reference_no'] ?? null,
                'notes' => $data['notes'] ?? null,
                'receipt_path' => $data['receipt_path'] ?? null,
                'created_by' => $userId,
            ]);

            $this->ledger->record(
                $supplier,
                $payment->payment_date->toDateString(),
                'payment',
                __('ui.payment_made').($purchase ? ' - '.$purchase->invoice_no : ''),
                $amount,
                '0',
                'payment',
                $payment->id,
                $userId
            );

            if ($purchase) {
                $this->purchases->syncTotals($purchase);
            }

            $this->audit->log('created', 'payments', $payment, 'Payment recorded', [], $payment->toArray());

            return $payment;
        });
    }

    public function update(Payment $payment, array $data, int $userId): Payment
    {
        return DB::transaction(function () use ($payment, $data, $userId) {
            if ($payment->is_auto) {
                throw ValidationException::withMessages(['payment' => __('ui.auto_payment_edit')]);
            }

            $old = $payment->toArray();
            $oldPurchaseId = $payment->purchase_id;
            $amount = Money::normalize($data['amount']);

            if (Money::cmp($amount, 0) <= 0) {
                throw ValidationException::withMessages(['amount' => __('ui.amount_positive')]);
            }

            if (! empty($data['reference_no'])) {
                $exists = Payment::where('reference_no', $data['reference_no'])
                    ->whereKeyNot($payment->id)
                    ->exists();
                if ($exists) {
                    throw ValidationException::withMessages(['reference_no' => __('ui.duplicate_reference')]);
                }
            }

            $supplier = $payment->supplier;
            $purchase = null;
            if (! empty($data['purchase_id'])) {
                $purchase = Purchase::where('supplier_id', $supplier->id)->findOrFail($data['purchase_id']);
                $alreadyPaid = $purchase->payments()->whereKeyNot($payment->id)->where('is_auto', false)->sum('amount');
                $available = Money::sub(Money::add($purchase->remaining_amount, $payment->purchase_id === $purchase->id ? $payment->amount : 0), $alreadyPaid);
                if (Money::cmp($amount, $available) > 0) {
                    throw ValidationException::withMessages(['amount' => __('ui.payment_exceeds_invoice')]);
                }
            }

            $this->ledger->removeByReference($supplier, 'payment', $payment->id);

            $payment->update([
                'supplier_id' => $supplier->id,
                'salesman_id' => $data['salesman_id'] ?? null,
                'purchase_id' => $purchase?->id,
                'payment_date' => $data['payment_date'],
                'amount' => $amount,
                'method' => $data['method'],
                'reference_no' => $data['reference_no'] ?? null,
                'notes' => $data['notes'] ?? null,
                'receipt_path' => $data['receipt_path'] ?? $payment->receipt_path,
            ]);

            $this->ledger->record(
                $supplier,
                $payment->payment_date->toDateString(),
                'payment',
                __('ui.payment_made').($purchase ? ' - '.$purchase->invoice_no : ''),
                $amount,
                '0',
                'payment',
                $payment->id,
                $userId
            );

            if ($purchase) {
                $this->purchases->syncTotals($purchase->fresh());
            }
            if ($oldPurchaseId) {
                $oldPurchase = Purchase::find($oldPurchaseId);
                if ($oldPurchase) {
                    $this->purchases->syncTotals($oldPurchase);
                }
            }

            $this->audit->log('updated', 'payments', $payment, 'Payment updated', $old, $payment->fresh()->toArray());

            return $payment->fresh();
        });
    }

    public function delete(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            if ($payment->is_auto) {
                throw ValidationException::withMessages(['payment' => __('ui.auto_payment_delete')]);
            }

            $old = $payment->toArray();
            $this->ledger->removeByReference($payment->supplier, 'payment', $payment->id);
            $purchase = $payment->purchase;
            $payment->delete();

            if ($purchase) {
                $this->purchases->syncTotals($purchase->fresh());
            }

            $this->audit->log('deleted', 'payments', $payment, 'Payment deleted', $old, []);
        });
    }
}
