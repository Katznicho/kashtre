<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CallingModuleConfig;
use App\Models\EmergencyAlert;
use App\Models\User;
use App\Services\CallingServiceClient;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use App\Jobs\AnnounceQueuedEmergencyJob;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class EmergencyQueueingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $callingServiceClient = Mockery::mock(CallingServiceClient::class);
        $callingServiceClient->shouldReceive('syncEmergency')->andReturnNull();
        $this->app->instance(CallingServiceClient::class, $callingServiceClient);
    }

    public function test_multiple_emergencies_queue_properly(): void
    {
        $suffix = Str::lower(Str::random(8));

        $business = Business::unguarded(function () use ($suffix) {
            return Business::create([
                'uuid' => (string) Str::uuid(),
                'name' => 'City Health Clinic Testing',
                'email' => 'clinic+' . $suffix . '@example.com',
                'phone' => '0700000000',
                'address' => 'Kampala',
                'account_number' => 'ACC002',
                'currency_code' => 'UGX',
                'default_payment_terms_days' => 30,
                'date' => now()->toDateString(),
            ]);
        });

        $user = User::unguarded(function () use ($business, $suffix) {
            return User::create([
                'uuid' => (string) Str::uuid(),
                'name' => 'Cashier',
                'email' => 'cashier+' . $suffix . '@example.com',
                'email_verified_at' => now(),
                'two_factor_confirmed_at' => now(),
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'status' => 'active',
                'business_id' => $business->id,
                'branch_id' => 1,
                'permissions' => ['Cashier'],
            ]);
        });
        
        // Temporarily put something in session since trigger_global resolves room via session
        session(['room_id' => 1]);

        $config = CallingModuleConfig::create([
            'business_id' => $business->id,
            'is_active' => true,
            'emergency_display_duration' => 30,
            'tts_speed' => 1.0,
            'emergency_repeat_count' => 1,
            'emergency_repeat_interval' => 0,
        ]);

        Queue::fake([\App\Jobs\AnnounceQueuedEmergencyJob::class]);

        // First emergency
        $response1 = $this->actingAs($user)->post(route('emergency.trigger.global'), [
            'message' => 'First Emergency',
            'button_index' => 1,
        ]);
        $response1->assertOk();

        // Ensure it's active immediately
        $alert1 = EmergencyAlert::where('business_id', $business->id)
            ->where('message', 'First Emergency')
            ->first();

        $this->assertNotNull($alert1);
        $this->assertTrue((bool)$alert1->is_active);
        $this->assertNotNull($alert1->activated_at);
        $response2 = $this->actingAs($user)->post(route('emergency.trigger.global'), [
            'message' => 'Second Emergency',
            'button_index' => 2,
        ]);
        $response2->assertOk();

        // Ensure it's queued (is_active = false)
        $alert2 = EmergencyAlert::where('business_id', $business->id)
            ->where('message', 'Second Emergency')
            ->first();

        $this->assertNotNull($alert2);
        $this->assertFalse((bool)$alert2->is_active);
        $this->assertNull($alert2->activated_at);

        // Validate that it's scheduled after the first one + at least 60s
        $diffSeconds = \Carbon\Carbon::parse($alert2->scheduled_announce_at)->diffInSeconds(now());
        $this->assertGreaterThanOrEqual(60, $diffSeconds);

        // Ensure the job was dispatched with delay
        Queue::assertPushed(\App\Jobs\AnnounceQueuedEmergencyJob::class, function ($job) use ($alert2) {
            return $job->alertId === $alert2->id && $job->delay !== null;
        });
    }
}
