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
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')
                ->default(DB::raw('(UUID())'))
                ->unique();

            // relations
            $table->foreignId('tuition_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->cascadeOnDelete();

            // fields
            $table->decimal('monthly_fees', 10, 2)->default(0);
            $table->string('year_month', 50);
            $table->boolean('is_paid')->default(false);

            $table->timestamps();
            $table->unique(['student_id', 'year_month']);

            // Optional helpful indexes
            $table->index(['student_id', 'year_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
