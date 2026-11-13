<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('expires_at')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_plans');
    }
};
