<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            ['name' => 'Morning', 'start_time' => '06:00', 'end_time' => '12:00'],
            ['name' => 'Afternoon', 'start_time' => '12:00', 'end_time' => '18:00'],
            ['name' => 'Evening', 'start_time' => '18:00', 'end_time' => '23:00'],
            ['name' => 'Full Day', 'start_time' => '06:00', 'end_time' => '23:00'],
        ];

        Branch::all()->each(function (Branch $branch) use ($shifts) {
            foreach ($shifts as $shift) {
                Shift::query()->firstOrCreate(
                    ['branch_id' => $branch->id, 'name' => $shift['name']],
                    ['start_time' => $shift['start_time'], 'end_time' => $shift['end_time'], 'status' => 'active']
                );
            }
        });
    }
}
