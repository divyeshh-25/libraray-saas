<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['name' => 'Starter', 'price' => 1499, 'billing_cycle' => 'monthly', 'max_branches' => 1, 'max_students' => 100, 'features' => ['1 branch', 'Up to 100 students', 'Seat allocation', 'Email support']],
            ['name' => 'Growth', 'price' => 3999, 'billing_cycle' => 'monthly', 'max_branches' => 3, 'max_students' => 500, 'features' => ['Up to 3 branches', 'Up to 500 students', 'Fee invoices & receipts', 'Attendance tracking', 'Priority support']],
            ['name' => 'Pro', 'price' => 8999, 'billing_cycle' => 'monthly', 'max_branches' => 10, 'max_students' => 2500, 'features' => ['Up to 10 branches', 'Up to 2,500 students', 'Floor plan designer', 'Custom roles', 'Dedicated manager']],
            ['name' => 'Growth Annual', 'price' => 39999, 'billing_cycle' => 'yearly', 'max_branches' => 3, 'max_students' => 500, 'features' => ['Everything in Growth', 'Two months free']],
        ];

        foreach ($plans as $plan) {
            Plan::query()->firstOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
