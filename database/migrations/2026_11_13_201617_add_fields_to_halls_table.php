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
        Schema::table('halls', function (Blueprint $table) {
            $table->unsignedInteger('floors')->default(1)->after('description');
            $table->unsignedInteger('rows')->nullable()->after('floors');
            $table->unsignedInteger('cols')->nullable()->after('rows');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('halls', function (Blueprint $table) {
            $table->dropColumn(['floors', 'rows', 'cols']);
            $table->dropSoftDeletes();
        });
    }
};
