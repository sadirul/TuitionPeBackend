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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('generate_fee_sms')->default(false)->after('is_verified');
            $table->boolean('mark_fee_paid_sms')->default(false)->after('generate_fee_sms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['generate_fee_sms', 'mark_fee_paid_sms']);
        });
    }
};
