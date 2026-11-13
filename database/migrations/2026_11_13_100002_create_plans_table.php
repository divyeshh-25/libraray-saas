<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('price', 12, 2)->default(0);
            $table->string('billing_cycle', 30);
            $table->unsignedInteger('max_branches')->nullable();
            $table->unsignedInteger('max_students')->nullable();
            $table->json('features')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
