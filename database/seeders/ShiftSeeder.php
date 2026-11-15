<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        Branch::all()->each(fn (Branch $branch) => Shift::seedDefaultsForBranch($branch->id));
    }
}
