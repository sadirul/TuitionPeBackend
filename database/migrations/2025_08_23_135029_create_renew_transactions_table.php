<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('renew_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->default(DB::raw('UUID()'))->unique();
            $table->unsignedBigInteger('tuition_id');
            $table->foreign('tuition_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unsignedInteger('months');                  // number of months renewed
            $table->decimal('amount', 15, 2);                   // payment amount
            $table->string('status');                           // success|failed|pending
            $table->string('currency')->nullable();             // INR, USD, etc
            $table->string('receipt')->nullable();

            // Razorpay integration fields
            $table->string('razorpay_order_id');
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_signature')->nullable();

            $table->json('json_response')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('renew_transactions');
    }
};
