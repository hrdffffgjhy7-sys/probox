<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agency extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'phone', 'whatsapp', 'address', 'notes', 'is_active', 'created_by'];

    protected $casts = ['is_active' => 'boolean'];

    public function salesmen()
    {
        return $this->hasMany(Salesman::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
