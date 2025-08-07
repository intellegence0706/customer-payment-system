<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isManager()
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    public function canManageUsers()
    {
        return $this->role === 'admin';
    }

    public function hasAnyRole($roles)
    {
        if (is_string($roles)) {
            return $this->role === $roles;
        }
        
        return in_array($this->role, $roles);
    }
}
