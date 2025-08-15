<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'tuition_id',
        'user_id',
        'class_id',
        'gender',
        'guardian_name',
        'guardian_contact',
        'monthly_fees',
        'admission_year',
    ];

    // Relationships
    public function tuition()
    {
        return $this->belongsTo(User::class, 'tuition_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
