<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecapData extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'claim_type',
        'claim_name',
        'claim_description',
        'nominal',
        'period_month',
        'period_year',
        'employee_id'
    ];

    public function userEmployees()
    {
        return $this->belongsTo(UserEmployee::class, 'employee_id');
    }
}
