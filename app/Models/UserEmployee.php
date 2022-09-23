<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'salary',
        'status'
    ];

    public function recapData()
    {
        return $this->hasMany(RecapData::class);
    }
}
