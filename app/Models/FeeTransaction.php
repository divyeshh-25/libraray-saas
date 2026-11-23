<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['invoice_id', 'receipt_number', 'student_id', 'amount', 'payment_method', 'transaction_date', 'reference_number', 'status', 'received_by', 'remarks'])]
class FeeTransaction extends Model
{
    public const METHODS = ['cash', 'upi', 'card', 'bank_transfer', 'cheque', 'online'];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'invoice_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Invoice balance left once this payment (and every earlier one) is applied.
     */
    public function balanceAfter(): string
    {
        $paid = FeeTransaction::where('invoice_id', $this->invoice_id)
            ->where('status', 'success')
            ->where('id', '<=', $this->id)
            ->sum('amount');

        return bcsub((string) $this->invoice->final_amount, (string) $paid, 2);
    }
}
