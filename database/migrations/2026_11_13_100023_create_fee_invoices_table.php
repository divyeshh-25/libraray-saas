<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('fee_structure_id')->nullable()->constrained('fee_structures')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('invoice_number', 100)->unique();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2);
            $table->string('status', 30)->default('unpaid')->comment('unpaid, partial, paid, overdue, cancelled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_invoices');
    }
};
