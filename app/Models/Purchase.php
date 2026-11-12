<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'supplier_id', 'salesman_id', 'agency_id', 'invoice_no', 'purchase_date',
        'due_date', 'subtotal', 'discount', 'tax', 'additional_charges', 'total',
        'paid_amount', 'remaining_amount', 'status', 'notes', 'attachment_path', 'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'additional_charges' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function salesman()
    {
        return $this->belongsTo(Salesman::class);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid' || bccomp((string) $this->remaining_amount, '0', 2) <= 0;
    }
}
