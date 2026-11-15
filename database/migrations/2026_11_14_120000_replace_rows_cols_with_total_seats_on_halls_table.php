<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rows/cols implied a seat count disconnected from the floor plan
     * designer's actual seat objects. A single target seat count is kept
     * instead, so the hall form and the floor plan describe the same thing.
     */
    public function up(): void
    {
        Schema::table('halls', function (Blueprint $table) {
            $table->unsignedInteger('total_seats')->nullable()->after('floors');
        });

        DB::table('halls')->whereNotNull('rows')->whereNotNull('cols')->get()->each(function ($hall) {
            DB::table('halls')->where('id', $hall->id)->update(['total_seats' => $hall->rows * $hall->cols]);
        });

        Schema::table('halls', function (Blueprint $table) {
            $table->dropColumn(['rows', 'cols']);
        });
    }

    public function down(): void
    {
        Schema::table('halls', function (Blueprint $table) {
            $table->unsignedInteger('rows')->nullable()->after('floors');
            $table->unsignedInteger('cols')->nullable()->after('rows');
        });

        Schema::table('halls', function (Blueprint $table) {
            $table->dropColumn('total_seats');
        });
    }
};
