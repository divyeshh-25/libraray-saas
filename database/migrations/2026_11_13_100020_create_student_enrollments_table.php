<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('fee_structure_id')->constrained('fee_structures')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['branch_id', 'shift_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
