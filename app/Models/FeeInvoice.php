<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'fee_structure_id', 'invoice_number', 'invoice_date', 'due_date', 'total_amount', 'discount_amount', 'final_amount', 'status', 'notes'])]
class FeeInvoice extends Model
{
    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
