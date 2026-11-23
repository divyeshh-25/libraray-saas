<?php

namespace App\Services;

use App\Models\FeeInvoice;
use App\Models\FeeTransaction;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Every financial write (invoice creation, payments, receipt numbers) goes
 * through here so it always runs inside a database transaction.
 */
class FeeBilling
{
    /**
     * Bill an enrollment: one invoice for the full amount of its fee plan.
     */
    public function invoiceEnrollment(StudentEnrollment $enrollment): FeeInvoice
    {
        $enrollment->loadMissing('feeStructure');
        $amount = $this->money($enrollment->feeStructure->amount);

        return $this->createInvoice(
            student: $enrollment->student_id,
            amount: $amount,
            invoiceDate: $enrollment->enrollment_date,
            dueDate: $enrollment->start_date,
            feeStructureId: $enrollment->fee_structure_id,
            enrollmentId: $enrollment->id,
            notes: 'Enrollment fee — '.$enrollment->feeStructure->name,
        );
    }

    public function createInvoice(
        int|Student $student,
        string $amount,
        CarbonInterface $invoiceDate,
        CarbonInterface $dueDate,
        ?int $feeStructureId = null,
        ?int $enrollmentId = null,
        ?string $notes = null,
        string $discount = '0.00',
    ): FeeInvoice {
        $studentId = $student instanceof Student ? $student->id : $student;
        $final = bcsub($amount, $discount, 2);

        if (bccomp($final, '0', 2) <= 0) {
            throw ValidationException::withMessages(['amount' => 'The payable amount must be greater than zero.']);
        }

        return DB::transaction(function () use ($studentId, $amount, $discount, $final, $invoiceDate, $dueDate, $feeStructureId, $enrollmentId, $notes) {
            $invoice = FeeInvoice::create([
                'student_id' => $studentId,
                'fee_structure_id' => $feeStructureId,
                'enrollment_id' => $enrollmentId,
                // Placeholder keeps the unique column valid until the id is known.
                'invoice_number' => 'TMP-'.Str::uuid(),
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'total_amount' => $amount,
                'discount_amount' => $discount,
                'final_amount' => $final,
                'paid_amount' => '0.00',
                'balance_amount' => $final,
                'status' => 'unpaid',
                'notes' => $notes,
            ]);

            $invoice->update([
                'invoice_number' => 'INV-'.$invoiceDate->format('ymd').'-'.str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT),
            ]);

            return $invoice;
        });
    }

    /**
     * Apply a full or partial payment to an invoice and issue its receipt.
     *
     * @throws ValidationException when the invoice cannot take this payment
     */
    public function recordPayment(
        int|FeeInvoice $invoice,
        string|float|int $amount,
        string $method,
        ?CarbonInterface $paidAt = null,
        ?string $reference = null,
        ?string $remarks = null,
        ?int $receivedBy = null,
    ): FeeTransaction {
        $amount = $this->money($amount);
        $invoiceId = $invoice instanceof FeeInvoice ? $invoice->id : $invoice;

        if (bccomp($amount, '0', 2) <= 0) {
            throw ValidationException::withMessages(['amount' => 'The payment amount must be greater than zero.']);
        }

        return DB::transaction(function () use ($invoiceId, $amount, $method, $paidAt, $reference, $remarks, $receivedBy) {
            // Lock the invoice so two simultaneous payments cannot overpay it.
            $invoice = FeeInvoice::whereKey($invoiceId)->lockForUpdate()->firstOrFail();

            if ($invoice->status === 'cancelled') {
                throw ValidationException::withMessages(['amount' => 'This invoice has been cancelled.']);
            }

            $balance = $this->money($invoice->balance_amount);

            if (bccomp($balance, '0', 2) <= 0) {
                throw ValidationException::withMessages(['amount' => 'This invoice is already fully paid.']);
            }

            if (bccomp($amount, $balance, 2) > 0) {
                throw ValidationException::withMessages(['amount' => 'The amount exceeds the outstanding balance of ₹'.number_format((float) $balance, 2).'.']);
            }

            $transaction = FeeTransaction::create([
                'invoice_id' => $invoice->id,
                'receipt_number' => 'TMP-'.Str::uuid(),
                'student_id' => $invoice->student_id,
                'amount' => $amount,
                'payment_method' => $method,
                'transaction_date' => $paidAt ?? now(),
                'reference_number' => $reference,
                'status' => 'success',
                'received_by' => $receivedBy,
                'remarks' => $remarks,
            ]);

            $transaction->update([
                'receipt_number' => 'RCT-'.$transaction->transaction_date->format('ymd').'-'.str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT),
            ]);

            $paid = bcadd($this->money($invoice->paid_amount), $amount, 2);
            $newBalance = bcsub($this->money($invoice->final_amount), $paid, 2);

            $invoice->update([
                'paid_amount' => $paid,
                'balance_amount' => $newBalance,
                'status' => bccomp($newBalance, '0', 2) === 0 ? 'paid' : 'partial',
            ]);

            return $transaction->setRelation('invoice', $invoice);
        });
    }

    /**
     * Normalise any numeric input to a two-decimal string for bcmath.
     */
    private function money(string|float|int|null $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
