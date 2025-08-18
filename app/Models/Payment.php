<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'tuition_id',
        'order_id',
        'payment_id',
        'signature',
        'amount',
        'status'
    ];

    public function tuition()
    {
        return $this->belongsTo(User::class, 'tuition_id');
    }
}
