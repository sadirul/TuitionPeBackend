<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    protected $fillable = [
        'uuid',
        'tuition_id',
        'class_name',
        'section',
    ];

    public function tuition()
    {
        return $this->belongsTo(User::class, 'tuition_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id', 'id');
    }
}
