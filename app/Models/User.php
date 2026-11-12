<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'username', 'email', 'phone', 'role', 'role_id', 'locale',
        'is_active', 'password', 'last_login_at', 'last_activity_at', 'password_changed_at', 'must_change_password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasPermission(string $ability): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $role = $this->roleModel;

        return $role ? $role->permissions()->where('name', $ability)->exists() : false;
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
