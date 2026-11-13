<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floor_plan_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_id')->constrained('floors')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('type', 50)->comment('WALL, BLOCKER, SEAT, WASHROOM, ENTRY_GATE, RECEPTION, OFFICE, OTHER');
            $table->string('title', 150)->nullable();
            $table->decimal('x', 10, 2)->default(0);
            $table->decimal('y', 10, 2)->default(0);
            $table->decimal('width', 10, 2)->default(0);
            $table->decimal('height', 10, 2)->default(0);
            $table->decimal('rotation', 6, 2)->default(0);
            $table->json('properties')->nullable();
            $table->integer('z_index')->default(0);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['floor_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floor_plan_objects');
    }
};
