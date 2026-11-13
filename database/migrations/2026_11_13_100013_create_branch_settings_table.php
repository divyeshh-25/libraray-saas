<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('setting_key', 150);
            $table->json('setting_value')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'setting_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_settings');
    }
};
