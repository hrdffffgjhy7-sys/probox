<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierLedger extends Model
{
    protected $table = 'supplier_ledger';

    protected $fillable = [
        'supplier_id', 'entry_date', 'type', 'description', 'reference_type',
        'reference_id', 'debit', 'credit', 'running_balance', 'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'running_balance' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPayable(): bool
    {
        return bccomp((string) $this->running_balance, '0', 2) > 0;
    }
}
