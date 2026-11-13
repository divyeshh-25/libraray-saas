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
        Schema::table('branches', function (Blueprint $table) {
            $table->string('city', 100)->nullable()->after('name');
            $table->time('opens_at')->nullable()->after('status');
            $table->time('closes_at')->nullable()->after('opens_at');
            $table->string('weekly_off', 20)->nullable()->after('closes_at');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['city', 'opens_at', 'closes_at', 'weekly_off']);
            $table->dropSoftDeletes();
        });
    }
};
