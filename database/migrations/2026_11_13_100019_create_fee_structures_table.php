<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 150);
            $table->decimal('amount', 12, 2);
            $table->string('duration_type', 30)->comment('daily, weekly, monthly, yearly');
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['branch_id', 'shift_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
