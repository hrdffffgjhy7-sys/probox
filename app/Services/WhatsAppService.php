<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Support\Money;

class WhatsAppService
{
    public function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (str_starts_with($digits, '92')) {
            return $digits;
        }
        if (str_starts_with($digits, '0')) {
            return '92'.substr($digits, 1);
        }
        if (strlen($digits) === 10) {
            return '92'.$digits;
        }

        return $digits;
    }

    public function summary(Supplier $supplier): array
    {
        return [
            'purchases' => Money::normalize($supplier->purchases()->sum('total')),
            'payments' => Money::normalize($supplier->payments()->sum('amount')),
            'returns' => Money::normalize($supplier->returns()->sum('amount')),
            'balance' => Money::normalize($supplier->current_balance),
        ];
    }

    public function message(string $type, Supplier $supplier, string $lang = 'en', ?Payment $payment = null, ?Purchase $purchase = null, ?string $custom = null): string
    {
        $s = $this->summary($supplier);
        $store = \App\Models\Setting::get('store_name', config('app.name'));
        $balance = Money::format($s['balance']);
        $purchases = Money::format($s['purchases']);
        $payments = Money::format($s['payments']);

        if ($lang === 'ur') {
            $greeting = "السلام علیکم،\n\n{$supplier->name} صاحب،";
            $footer = "\n\nشکریہ،\n{$store}";
            $statement = "آپ کے اکاؤنٹ کا گوشوارہ:\n\nکل خریداری: {$purchases}\nکل ادائیگی: {$payments}\nبقایا بیلنس: {$balance}\n\nبراہ کرم اپنا اکاؤنٹ چیک کریں۔";
        } else {
            $greeting = "Assalam-o-Alaikum,\n\nDear {$supplier->name},";
            $footer = "\n\nThank you,\n{$store}";
            $statement = "Your account statement:\n\nTotal Purchases: {$purchases}\nTotal Payments: {$payments}\nOutstanding Balance: {$balance}\n\nPlease verify your account.";
        }

        $body = match ($type) {
            'payment_confirmation' => $lang === 'ur'
                ? "آپ کی ادائیگی {$payment?->amount} روپے موصول ہو گئی ہے۔\nتاریخ: ".($payment?->payment_date?->format('d M Y') ?? '-')."\nحوالہ: ".($payment?->reference_no ?? '-')
                : "We have received your payment of ".Money::format($payment?->amount ?? 0).".\nDate: ".($payment?->payment_date?->format('d M Y') ?? '-')."\nReference: ".($payment?->reference_no ?? '-'),
            'purchase_summary' => $lang === 'ur'
                ? "خریداری انوائس: ".($purchase?->invoice_no ?? '-')."\nتاریخ: ".($purchase?->purchase_date?->format('d M Y') ?? '-')."\nکل رقم: ".Money::format($purchase?->total ?? 0)."\nادا شدہ: ".Money::format($purchase?->paid_amount ?? 0)."\nباقی: ".Money::format($purchase?->remaining_amount ?? 0)
                : "Purchase Invoice: ".($purchase?->invoice_no ?? '-')."\nDate: ".($purchase?->purchase_date?->format('d M Y') ?? '-')."\nTotal: ".Money::format($purchase?->total ?? 0)."\nPaid: ".Money::format($purchase?->paid_amount ?? 0)."\nRemaining: ".Money::format($purchase?->remaining_amount ?? 0),
            'reminder' => $lang === 'ur'
                ? "یاد دہانی: آپ کے اکاؤنٹ میں {$balance} کا بقایا ہے۔ براہ کرم تصفیہ کریں۔"
                : "Reminder: an outstanding balance of {$balance} is pending on your account. Please settle it at your earliest convenience.",
            'custom' => (string) ($custom ?? ''),
            default => $statement,
        };

        return trim($greeting."\n\n".$body.$footer);
    }

    public function url(string $phone, string $message): string
    {
        return 'https://wa.me/'.$this->normalizePhone($phone).'?text='.rawurlencode($message);
    }
}
