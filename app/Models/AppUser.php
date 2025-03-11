<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AppUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'app_users'; // Define custom table name

    protected $fillable = [
        'name',
        'email',
        'password',
        'fcm_tokens',
        'is_login',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'fcm_tokens' => 'array', // Cast JSON to array
        'is_login' => 'boolean',
    ];
}
