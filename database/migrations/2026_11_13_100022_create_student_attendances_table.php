<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('attendance_date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('status', 30)->comment('present, absent, late, half_day');
            $table->foreignId('marked_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'shift_id', 'attendance_date']);
            $table->index(['branch_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};
