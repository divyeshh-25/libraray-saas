<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\FeeStructure;
use App\Models\Shift;
use Illuminate\Database\Seeder;

class FeeStructureSeeder extends Seeder
{
    /**
     * One monthly plan per shift, per branch. Depends on shifts already
     * existing for the branch (see ShiftSeeder / Shift::seedDefaultsForBranch).
     *
     * @return array<string, int>
     */
    private static function monthlyAmounts(): array
    {
        return [
            'Morning' => 1200,
            'Afternoon' => 1500,
            'Evening' => 1500,
            'FullDay' => 2500,
        ];
    }

    public function run(): void
    {
        $amounts = self::monthlyAmounts();

        Branch::all()->each(function (Branch $branch) use ($amounts) {
            Shift::seedDefaultsForBranch($branch->id);

            Shift::where('branch_id', $branch->id)->get()->each(function (Shift $shift) use ($branch, $amounts) {
                FeeStructure::firstOrCreate(
                    ['branch_id' => $branch->id, 'shift_id' => $shift->id, 'duration_type' => 'monthly'],
                    [
                        'name' => $shift->name.' Monthly',
                        'amount' => $amounts[$shift->name] ?? 1200,
                        'status' => 'active',
                    ]
                );
            });
        });
    }
}
