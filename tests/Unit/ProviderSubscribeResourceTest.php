<?php

namespace Tests\Unit;

use App\Http\Resources\API\ProviderSubscribeResource;
use App\Models\Plans;
use App\Models\ProviderSubscription;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class ProviderSubscribeResourceTest extends TestCase
{
    public function test_resource_prefers_plan_details_from_plans_table(): void
    {
        $plan = new \stdClass();
        $plan->id = 7;
        $plan->title = 'Premium Plan';
        $plan->identifier = 'premium';
        $plan->type = 'monthly';
        $plan->duration = 3;
        $plan->amount = 99.99;
        $plan->description = 'Premium access';
        $plan->plan_type = 'Premium';
        $plan->planlimit = new \stdClass();
        $plan->planlimit->plan_limitation = ['service' => ['limit' => 10]];

        $subscription = new ProviderSubscription([
            'id' => 10,
            'plan_id' => 7,
            'title' => 'Legacy title',
            'identifier' => 'legacy',
            'type' => 'weekly',
            'duration' => 1,
            'amount' => 5,
            'description' => 'Legacy description',
            'plan_type' => 'Legacy',
            'plan_limitation' => json_encode(['legacy' => true]),
        ]);

        $subscription->setRelation('plan', $plan);

        $resource = new ProviderSubscribeResource($subscription);
        $payload = $resource->toArray(new Request());

        $this->assertSame('Premium Plan', $payload['title']);
        $this->assertSame('premium', $payload['identifier']);
        $this->assertSame('monthly', $payload['type']);
        $this->assertSame(3, $payload['duration']);
        $this->assertSame(99.99, $payload['amount']);
        $this->assertSame('Premium access', $payload['description']);
        $this->assertSame('Premium', $payload['plan_type']);
        $this->assertSame(['service' => ['limit' => 10]], $payload['plan_limitation']);
    }
}
