<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_seat_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('seat_id')->constrained('seats')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('active')->comment('active, completed, cancelled');
            $table->foreignId('allocated_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['seat_id', 'shift_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_seat_allocations');
    }
};
