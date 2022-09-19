<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHrd extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'full_name',
        'email',
        'password',
        'status',
        'failed_login_attempt',
        'is_login',
    ];

    protected $hidden = [
        'password',
    ];
}
