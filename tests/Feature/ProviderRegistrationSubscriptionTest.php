<?php

namespace Tests\Feature;

use App\Models\Plans;
use App\Models\ProviderSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderRegistrationSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_registration_uses_first_active_plan_for_subscription(): void
    {
        $plan = Plans::create([
            'title' => 'Starter Plan',
            'identifier' => 'starter',
            'type' => 'monthly',
            'amount' => 0,
            'status' => 1,
            'duration' => '1 month',
            'description' => 'Starter plan',
            'trial_period' => 0,
            'plan_type' => 'Starter',
        ]);

        $response = $this->postJson('/api/register', [
            'first_name' => 'Test',
            'last_name' => 'Provider',
            'username' => 'provideruser',
            'email' => 'provider@example.com',
            'password' => 'password123',
            'contact_number' => '1234567890',
            'user_type' => 'provider',
        ]);

        $response->assertStatus(200);

        $user = User::where('email', 'provider@example.com')->firstOrFail();
        $subscription = ProviderSubscription::where('user_id', $user->id)->first();

        $this->assertNotNull($subscription);
        $this->assertSame($plan->id, $subscription->plan_id);
        $this->assertSame($plan->title, $subscription->title);
        $this->assertSame($plan->identifier, $subscription->identifier);
    }
}
