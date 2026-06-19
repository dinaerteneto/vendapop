<?php

namespace Database\Seeders;

use App\Models\PlanLimit;
use Illuminate\Database\Seeder;

class PlanLimitsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('plan-limits.default_limits') as $planType => $limits) {
            PlanLimit::updateOrCreate(
                ['plan_type' => $planType],
                $limits
            );
        }
    }
}
