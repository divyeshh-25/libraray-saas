<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnUpdate()->nullOnDelete();
            $table->string('name', 150);
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('password');
            $table->string('user_type', 30)->comment('owner, staff, student');
            $table->string('status', 30)->default('active');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['tenant_id', 'email']);
            $table->index(['tenant_id', 'user_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
