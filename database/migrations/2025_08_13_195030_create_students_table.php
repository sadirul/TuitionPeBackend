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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->default(DB::raw('UUID()'))->unique();
            $table->unsignedBigInteger('tuition_id');
            $table->unsignedBigInteger('user_id');   
            $table->unsignedBigInteger('class_id');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('guardian_name');
            $table->string('guardian_contact');
            $table->decimal('monthly_fees', 10, 2);
            $table->year('admission_year');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('tuition_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
