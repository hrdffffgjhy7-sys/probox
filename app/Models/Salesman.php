<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salesman extends Model
{
    use SoftDeletes;

    protected $fillable = ['agency_id', 'name', 'phone', 'whatsapp', 'address', 'notes', 'is_active', 'created_by'];

    protected $casts = ['is_active' => 'boolean'];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
