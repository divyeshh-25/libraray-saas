<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['branch_id', 'name', 'start_time', 'end_time', 'status'])]
class Shift extends Model
{
    /**
     * The standard shift set every branch gets, shared by ShiftSeeder (run
     * at seed time) and seedDefaultsForBranch() (run on demand for a branch
     * that didn't exist yet when the seeder ran).
     *
     * @return array<int, array{name: string, start_time: string, end_time: string}>
     */
    public static function defaults(): array
    {
        return [
            ['name' => 'Morning', 'start_time' => '06:00', 'end_time' => '12:00'],
            ['name' => 'Afternoon', 'start_time' => '12:00', 'end_time' => '18:00'],
            ['name' => 'Evening', 'start_time' => '18:00', 'end_time' => '23:00'],
            ['name' => 'FullDay', 'start_time' => '06:00', 'end_time' => '23:00'],
        ];
    }

    public static function seedDefaultsForBranch(int $branchId): void
    {
        foreach (self::defaults() as $shift) {
            self::query()->firstOrCreate(
                ['branch_id' => $branchId, 'name' => $shift['name']],
                ['start_time' => $shift['start_time'], 'end_time' => $shift['end_time'], 'status' => 'active']
            );
        }
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
