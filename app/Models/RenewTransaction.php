<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RenewTransaction extends Model
{
    protected $fillable = [
        'tuition_id',
        'months',
        'amount',
        'description',
        'status',
        'currency',
        'receipt',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'json_response',
    ];

    protected $casts = [
        'json_response' => 'array',   // auto cast JSON into array
    ];

    // Relationship: each transaction belongs to a tuition (user/organization)
    public function tuition()
    {
        return $this->belongsTo(User::class, 'tuition_id');
    }
}
