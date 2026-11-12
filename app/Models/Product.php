<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'sku', 'unit', 'purchase_rate', 'sale_rate', 'stock_qty',
        'low_stock_alert', 'is_active', 'notes',
    ];

    protected $casts = [
        'purchase_rate' => 'decimal:2',
        'sale_rate' => 'decimal:2',
        'stock_qty' => 'decimal:3',
        'low_stock_alert' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function purchases()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function lowStock(): bool
    {
        return bccomp((string) $this->stock_qty, (string) $this->low_stock_alert, 3) <= 0;
    }
}
