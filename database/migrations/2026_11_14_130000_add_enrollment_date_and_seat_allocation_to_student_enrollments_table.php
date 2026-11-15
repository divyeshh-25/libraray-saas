<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->date('enrollment_date')->after('student_id');
            $table->foreignId('seat_allocation_id')->nullable()->after('fee_structure_id')
                ->constrained('student_seat_allocations')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seat_allocation_id');
            $table->dropColumn('enrollment_date');
            $table->dropSoftDeletes();
        });
    }
};
