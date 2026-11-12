<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'attachable_type', 'attachable_id', 'file_name', 'file_path',
        'mime_type', 'size', 'uploaded_by',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }
}
