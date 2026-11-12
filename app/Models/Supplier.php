<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'agency_id', 'salesman_id', 'phone', 'whatsapp', 'address',
        'opening_balance', 'opening_balance_type', 'current_balance', 'notes',
        'is_active', 'archived_at', 'created_by', 'last_transaction_at',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'archived_at' => 'datetime',
        'last_transaction_at' => 'datetime',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function salesman()
    {
        return $this->belongsTo(Salesman::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function ledger()
    {
        return $this->hasMany(SupplierLedger::class);
    }

    public function adjustments()
    {
        return $this->hasMany(Adjustment::class);
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopePayable($query)
    {
        return $query->where('current_balance', '>', 0);
    }

    public function scopeReceivable($query)
    {
        return $query->where('current_balance', '<', 0);
    }

    public function getPayableAmountAttribute(): string
    {
        return bccomp((string) $this->current_balance, '0', 2) > 0 ? (string) $this->current_balance : '0';
    }

    public function getReceivableAmountAttribute(): string
    {
        return bccomp((string) $this->current_balance, '0', 2) < 0 ? ltrim((string) $this->current_balance, '-') : '0';
    }

    public static function totalPayable(): string
    {
        return (string) static::whereNull('deleted_at')->where('current_balance', '>', 0)->sum('current_balance');
    }

    public static function totalReceivable(): string
    {
        return (string) DB::table('suppliers')->whereNull('deleted_at')->where('current_balance', '<', 0)->sum(DB::raw('ABS(current_balance)'));
    }
}
