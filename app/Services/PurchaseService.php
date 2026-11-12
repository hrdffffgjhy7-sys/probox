<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function __construct(
        private LedgerService $ledger,
        private AuditService $audit
    ) {
    }

    public function create(array $data, array $items, int $userId): Purchase
    {
        return DB::transaction(function () use ($data, $items, $userId) {
            [$rows, $subtotal, $discount, $tax, $total] = $this->build($items, $data);

            $initialPaid = Money::normalize($data['initial_paid'] ?? 0);
            $this->assertMoney($initialPaid, 'initial_paid');

            if (Money::cmp($initialPaid, $total) > 0) {
                throw ValidationException::withMessages(['initial_paid' => __('validation.lte.numeric', ['attribute' => __('ui.paid_amount'), 'value' => $total])]);
            }

            $invoiceNo = trim((string) ($data['invoice_no'] ?? '')) ?: $this->nextInvoiceNo();
            $supplier = Supplier::findOrFail($data['supplier_id']);

            if (Purchase::where('supplier_id', $supplier->id)->where('invoice_no', $invoiceNo)->exists()) {
                throw ValidationException::withMessages(['invoice_no' => __('ui.duplicate_invoice')]);
            }

            $purchase = Purchase::create([
                'supplier_id' => $supplier->id,
                'salesman_id' => $data['salesman_id'] ?? $supplier->salesman_id,
                'agency_id' => $data['agency_id'] ?? $supplier->agency_id,
                'invoice_no' => $invoiceNo,
                'purchase_date' => $data['purchase_date'],
                'due_date' => $data['due_date'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'additional_charges' => Money::normalize($data['additional_charges'] ?? 0),
                'total' => $total,
                'paid_amount' => $initialPaid,
                'remaining_amount' => Money::sub($total, $initialPaid),
                'status' => Money::cmp($initialPaid, $total) >= 0 ? 'paid' : (Money::cmp($initialPaid, 0) > 0 ? 'partial' : 'unpaid'),
                'notes' => $data['notes'] ?? null,
                'attachment_path' => $data['attachment_path'] ?? null,
                'created_by' => $userId,
            ]);

            $this->saveItems($purchase, $rows);
            $this->applyStock($rows, 1);

            $this->ledger->record($supplier, $purchase->purchase_date->toDateString(), 'purchase', __('ui.purchase_invoice').' '.$invoiceNo, '0', $total, 'purchase', $purchase->id, $userId);

            if (Money::cmp($initialPaid, 0) > 0) {
                $payment = Payment::create([
                    'supplier_id' => $supplier->id,
                    'salesman_id' => $purchase->salesman_id,
                    'purchase_id' => $purchase->id,
                    'payment_date' => $purchase->purchase_date,
                    'amount' => $initialPaid,
                    'method' => 'cash',
                    'is_auto' => true,
                    'reference_no' => 'AUTO-PUR-'.$purchase->id,
                    'notes' => __('ui.paid_with_purchase'),
                    'created_by' => $userId,
                ]);

                $this->ledger->record($supplier, $payment->payment_date->toDateString(), 'payment', __('ui.payment_against').' '.$invoiceNo, $initialPaid, '0', 'payment', $payment->id, $userId);
            }

            $this->syncTotals($purchase);
            $this->audit->log('created', 'purchases', $purchase, 'Purchase '.$invoiceNo.' created', [], $purchase->fresh()->toArray());

            return $purchase->fresh(['items']);
        });
    }

    public function update(Purchase $purchase, array $data, array $items, int $userId): Purchase
    {
        return DB::transaction(function () use ($purchase, $data, $items, $userId) {
            $old = $purchase->toArray();
            [$rows, $subtotal, $discount, $tax, $total] = $this->build($items, $data);

            $initialPaid = Money::normalize($data['initial_paid'] ?? 0);
            $this->assertMoney($initialPaid, 'initial_paid');

            $manualPaid = $purchase->payments()->where('is_auto', false)->sum('amount');
            if (Money::cmp(Money::add($initialPaid, $manualPaid), $total) > 0) {
                throw ValidationException::withMessages(['initial_paid' => __('ui.paid_exceeds_total')]);
            }

            $invoiceNo = trim((string) ($data['invoice_no'] ?? '')) ?: $purchase->invoice_no;
            $duplicate = Purchase::where('supplier_id', $purchase->supplier_id)
                ->where('invoice_no', $invoiceNo)
                ->whereKeyNot($purchase->id)
                ->exists();
            if ($duplicate) {
                throw ValidationException::withMessages(['invoice_no' => __('ui.duplicate_invoice')]);
            }

            $this->applyStock($purchase->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'quantity' => $i->quantity,
            ])->all(), -1);

            $purchase->items()->delete();
            $this->saveItems($purchase, $rows);
            $this->applyStock($rows, 1);

            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'salesman_id' => $data['salesman_id'] ?? null,
                'agency_id' => $data['agency_id'] ?? null,
                'invoice_no' => $invoiceNo,
                'purchase_date' => $data['purchase_date'],
                'due_date' => $data['due_date'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'additional_charges' => Money::normalize($data['additional_charges'] ?? 0),
                'total' => $total,
                'notes' => $data['notes'] ?? null,
                'attachment_path' => $data['attachment_path'] ?? $purchase->attachment_path,
            ]);

            $supplier = $purchase->supplier;
            $this->ledger->removeByReference($supplier, 'purchase', $purchase->id);

            $auto = Payment::where('purchase_id', $purchase->id)->where('is_auto', true)->first();
            if ($auto) {
                $this->ledger->removeByReference($supplier, 'payment', $auto->id);
            }

            $this->ledger->record($supplier, $purchase->purchase_date->toDateString(), 'purchase', __('ui.purchase_invoice').' '.$invoiceNo, '0', $total, 'purchase', $purchase->id, $userId);

            if (Money::cmp($initialPaid, 0) > 0) {
                if (! $auto) {
                    $auto = Payment::create([
                        'supplier_id' => $supplier->id,
                        'salesman_id' => $purchase->salesman_id,
                        'purchase_id' => $purchase->id,
                        'payment_date' => $purchase->purchase_date,
                        'amount' => $initialPaid,
                        'method' => 'cash',
                        'is_auto' => true,
                        'reference_no' => 'AUTO-PUR-'.$purchase->id,
                        'notes' => __('ui.paid_with_purchase'),
                        'created_by' => $userId,
                    ]);
                } else {
                    $auto->update(['amount' => $initialPaid, 'payment_date' => $purchase->purchase_date, 'salesman_id' => $purchase->salesman_id]);
                }

                $this->ledger->record($supplier, $purchase->purchase_date->toDateString(), 'payment', __('ui.payment_against').' '.$invoiceNo, $initialPaid, '0', 'payment', $auto->id, $userId);
            } elseif ($auto) {
                $auto->delete();
            }

            $this->syncTotals($purchase);
            $this->audit->log('updated', 'purchases', $purchase, 'Purchase '.$invoiceNo.' updated', $old, $purchase->fresh()->toArray());

            return $purchase->fresh(['items']);
        });
    }

    public function delete(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            if ($purchase->payments()->where('is_auto', false)->exists()) {
                throw ValidationException::withMessages(['purchase' => __('ui.delete_purchase_has_payments')]);
            }

            $old = $purchase->toArray();
            $supplier = $purchase->supplier;
            $this->applyStock($purchase->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'quantity' => $i->quantity,
            ])->all(), -1);

            $autos = Payment::where('purchase_id', $purchase->id)->where('is_auto', true)->get();
            foreach ($autos as $auto) {
                $this->ledger->removeByReference($supplier, 'payment', $auto->id);
                $auto->delete();
            }

            $this->ledger->removeByReference($supplier, 'purchase', $purchase->id);
            $purchase->delete();
            $this->audit->log('deleted', 'purchases', $purchase, 'Purchase '.$purchase->invoice_no.' deleted', $old, []);
        });
    }

    public function syncTotals(Purchase $purchase): void
    {
        $paid = (string) $purchase->payments()->sum('amount');
        $paid = Money::normalize($paid);
        $remaining = Money::sub($purchase->total, $paid);

        if (Money::cmp($remaining, 0) < 0) {
            $remaining = '0.00';
        }

        $status = Money::cmp($remaining, 0) <= 0 ? 'paid' : (Money::cmp($paid, 0) > 0 ? 'partial' : 'unpaid');

        $purchase->forceFill([
            'paid_amount' => $paid,
            'remaining_amount' => $remaining,
            'status' => $status,
        ])->saveQuietly();
    }

    private function build(array $items, array $data): array
    {
        $rows = [];
        $subtotal = '0';
        $discountTotal = Money::normalize($data['discount'] ?? 0);
        $taxTotal = Money::normalize($data['tax'] ?? 0);

        $this->assertMoney($discountTotal, 'discount');
        $this->assertMoney($taxTotal, 'tax');
        $this->assertMoney($data['additional_charges'] ?? 0, 'additional_charges');

        foreach ($items as $item) {
            $name = trim((string) ($item['product_name'] ?? ''));
            $qty = Money::normalize($item['quantity'] ?? 0);
            $rate = Money::normalize($item['rate'] ?? 0);
            $discount = Money::normalize($item['discount'] ?? 0);
            $tax = Money::normalize($item['tax'] ?? 0);

            if ($name === '' && Money::cmp($qty, 0) <= 0) {
                continue;
            }
            if ($name === '') {
                throw ValidationException::withMessages(['items' => __('ui.item_name_required')]);
            }
            if (Money::cmp($qty, 0) <= 0) {
                throw ValidationException::withMessages(['items' => __('ui.quantity_positive')]);
            }
            if (Money::cmp($rate, 0) < 0 || Money::cmp($discount, 0) < 0 || Money::cmp($tax, 0) < 0) {
                throw ValidationException::withMessages(['items' => __('ui.amounts_non_negative')]);
            }

            $lineSubtotal = Money::mul($qty, $rate);
            $lineTotal = Money::sub(Money::add($lineSubtotal, $tax), $discount);

            if (Money::cmp($lineTotal, 0) < 0) {
                throw ValidationException::withMessages(['items' => __('ui.line_total_negative')]);
            }

            $rows[] = [
                'product_id' => ! empty($item['product_id']) ? (int) $item['product_id'] : null,
                'product_name' => $name,
                'quantity' => $qty,
                'unit' => $item['unit'] ?? 'pcs',
                'rate' => $rate,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $lineTotal,
            ];

            $subtotal = Money::add($subtotal, $lineSubtotal);
            $discountTotal = Money::add($discountTotal, $discount);
            $taxTotal = Money::add($taxTotal, $tax);
        }

        if (count($rows) === 0) {
            throw ValidationException::withMessages(['items' => __('ui.at_least_one_item')]);
        }

        $additional = Money::normalize($data['additional_charges'] ?? 0);
        $total = Money::add(Money::sub(Money::add($subtotal, $taxTotal), $discountTotal), $additional);

        if (Money::cmp($total, 0) < 0) {
            throw ValidationException::withMessages(['items' => __('ui.total_negative')]);
        }

        return [$rows, $subtotal, $discountTotal, $taxTotal, $total];
    }

    private function saveItems(Purchase $purchase, array $rows): void
    {
        foreach ($rows as $row) {
            $purchase->items()->create($row);
        }
    }

    private function applyStock(array $rows, int $direction): void
    {
        foreach ($rows as $row) {
            if (empty($row['product_id'])) {
                continue;
            }
            $product = Product::find($row['product_id']);
            if (! $product) {
                continue;
            }
            $qty = Money::normalize($row['quantity']);
            $product->stock_qty = Money::add($product->stock_qty, Money::mul($qty, $direction));
            if ($direction > 0 && isset($row['rate'])) {
                $product->purchase_rate = $row['rate'];
            }
            $product->saveQuietly();
        }
    }

    private function assertMoney(string $value, string $field): void
    {
        if (Money::cmp($value, 0) < 0) {
            throw ValidationException::withMessages([$field => __('ui.amounts_non_negative')]);
        }
    }

    public function nextInvoiceNo(): string
    {
        $prefix = 'PUR-'.now()->format('Ymd').'-';
        $count = Purchase::withTrashed()->where('invoice_no', 'like', $prefix.'%')->count() + 1;

        return $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
