<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_invoices', function (Blueprint $table) {
            $table->foreignId('enrollment_id')->nullable()->after('fee_structure_id')
                ->constrained('student_enrollments')->cascadeOnUpdate()->nullOnDelete();
            $table->decimal('paid_amount', 12, 2)->default(0)->after('final_amount');
            $table->decimal('balance_amount', 12, 2)->default(0)->after('paid_amount');
        });

        Schema::table('fee_transactions', function (Blueprint $table) {
            $table->string('receipt_number', 50)->nullable()->unique()->after('invoice_id');
        });

        // Invoices created by the old "collect fee" flow were stored as already paid
        // with no transaction row. Bring them in line with the new model.
        DB::table('fee_invoices')->where('status', 'paid')->update([
            'paid_amount' => DB::raw('final_amount'),
            'balance_amount' => 0,
        ]);
        DB::table('fee_invoices')->whereNotIn('status', ['paid', 'cancelled'])->update([
            'balance_amount' => DB::raw('final_amount'),
        ]);

        DB::table('fee_invoices')->where('status', 'paid')
            ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('fee_transactions')->whereColumn('fee_transactions.invoice_id', 'fee_invoices.id'))
            ->orderBy('id')
            ->each(function ($invoice) {
                $id = DB::table('fee_transactions')->insertGetId([
                    'invoice_id' => $invoice->id,
                    'student_id' => $invoice->student_id,
                    'amount' => $invoice->final_amount,
                    'payment_method' => 'cash',
                    'transaction_date' => $invoice->invoice_date.' 00:00:00',
                    'status' => 'success',
                    'remarks' => $invoice->notes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('fee_transactions')->where('id', $id)->update([
                    'receipt_number' => 'RCT-'.date('ymd', strtotime($invoice->invoice_date)).'-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('fee_transactions', function (Blueprint $table) {
            $table->dropUnique(['receipt_number']);
            $table->dropColumn('receipt_number');
        });

        Schema::table('fee_invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('enrollment_id');
            $table->dropColumn(['paid_amount', 'balance_amount']);
        });
    }
};
