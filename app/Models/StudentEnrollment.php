<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['student_id', 'branch_id', 'shift_id', 'fee_structure_id', 'seat_allocation_id', 'enrollment_date', 'start_date', 'end_date', 'status'])]
class StudentEnrollment extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'enrollment_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function seatAllocation(): BelongsTo
    {
        return $this->belongsTo(StudentSeatAllocation::class);
    }
}
