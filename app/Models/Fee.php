<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    protected $fillable = [
        'uuid',
        'tuition_id',
        'student_id',
        'monthly_fees',
        'year_month',
        'is_paid',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'monthly_fees' => 'decimal:2',
    ];

    public function tuition()
    {
        return $this->belongsTo(User::class, 'tuition_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
