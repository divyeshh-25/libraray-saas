<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('fee_invoices')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 50);
            $table->dateTime('transaction_date');
            $table->string('reference_number', 150)->nullable()->index();
            $table->string('status', 30);
            $table->foreignId('received_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_transactions');
    }
};
