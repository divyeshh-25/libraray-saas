<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['branch_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
