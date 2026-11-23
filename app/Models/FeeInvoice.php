<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['student_id', 'fee_structure_id', 'enrollment_id', 'invoice_number', 'invoice_date', 'due_date', 'total_amount', 'discount_amount', 'final_amount', 'paid_amount', 'balance_amount', 'status', 'notes'])]
class FeeInvoice extends Model
{
    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'total_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class)->withTrashed();
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class)->withTrashed();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FeeTransaction::class, 'invoice_id');
    }

    /** Invoices that still have money owing. */
    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->whereIn('status', ['unpaid', 'partial', 'overdue'])->where('balance_amount', '>', 0);
    }
}
