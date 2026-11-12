<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
{
    use SoftDeletes;

    protected $table = 'purchase_returns';

    protected $fillable = [
        'supplier_id', 'purchase_id', 'invoice_no', 'return_date',
        'amount', 'reason', 'reference_no', 'created_by',
    ];

    protected $casts = [
        'return_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
