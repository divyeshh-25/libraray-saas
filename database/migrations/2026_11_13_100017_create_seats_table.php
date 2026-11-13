<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_plan_object_id')->unique()->constrained('floor_plan_objects')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('seat_number', 50);
            $table->string('seat_type', 50)->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['branch_id', 'seat_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
