<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Relation with users table
            $table->foreignId('tuition_id')->constrained('users')->onDelete('cascade');

            $table->string('order_id')->unique(); // Razorpay Order ID
            $table->string('payment_id')->nullable(); // Razorpay Payment ID
            $table->string('signature')->nullable();
            $table->integer('amount');
            $table->enum('status', ['created', 'success', 'failed'])->default('created');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
