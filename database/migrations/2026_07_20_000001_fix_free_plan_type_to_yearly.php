<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix Plan 1 (Free plan) from weekly to yearly so new providers get a 1-year free subscription
        DB::table('plans')->where('id', 1)->update([
            'type'         => 'yearly',
            'trial_period' => null,
            'updated_at'   => now(),
        ]);

        // Also fix any existing provider_subscriptions that were assigned as weekly for plan_id=1
        // Recalculate their end_at to start_at + 1 year
        $subs = DB::table('provider_subscriptions')
            ->where('plan_id', 1)
            ->where('type', 'weekly')
            ->get();

        foreach ($subs as $sub) {
            $endAt = \Carbon\Carbon::parse($sub->start_at)->addYear()->format('Y-m-d H:i:s');
            DB::table('provider_subscriptions')->where('id', $sub->id)->update([
                'type'       => 'yearly',
                'end_at'     => $endAt,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('plans')->where('id', 1)->update([
            'type'         => 'weekly',
            'trial_period' => 7,
            'updated_at'   => now(),
        ]);
    }
};
