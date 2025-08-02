<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // Include traits for API tokens, factories, notifications, and soft deletes
    use HasApiTokens;
    use HasFactory, Notifiable, SoftDeletes;

    // Fields that can be mass assigned
    protected $fillable = [
        'name',
        'email',
        'password',
        'role' 
    ];

    // Fields that should be hidden when serialized (e.g., in JSON)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Fields with special casting rules
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Laravel will automatically hash the password
    ];

    // Available user roles
    public static $roles = [
        'admin' => 'Admin',
        'user' => 'User'
    ];

    // Check if the user has admin role
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
