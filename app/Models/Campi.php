<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Campi extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
    ];

    protected $hidden = [
        'password',
    ];
}
