<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TuitionNotice extends Model
{
    protected $fillable = [
        'uuid',
        'notice_text',
    ];
}
