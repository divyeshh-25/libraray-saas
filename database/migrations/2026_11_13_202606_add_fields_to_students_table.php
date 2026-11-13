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
        Schema::table('students', function (Blueprint $table) {
            $table->string('shift', 50)->nullable()->after('status');
            $table->string('seat_label', 50)->nullable()->after('shift');
            $table->decimal('fee_amount', 12, 2)->nullable()->after('seat_label');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['shift', 'seat_label', 'fee_amount']);
            $table->dropSoftDeletes();
        });
    }
};
