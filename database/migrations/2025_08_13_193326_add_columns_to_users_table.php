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
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->default(DB::raw('UUID()'))->unique()->after('id');
            $table->unsignedBigInteger('tuition_id')->nullable()->after('uuid');
            $table->string('tuition_name')->nullable()->after('tuition_id');
            $table->string('username')->unique()->after('name');
            $table->string('mobile', 20)->nullable()->after('username');
            $table->string('address')->nullable()->after('mobile');
            $table->string('role')->default('tuition')->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'address',
                'mobile',
                'username',
                'tuition_name',
                'tuition_id',
                'uuid',
            ]);
        });
    }
};
