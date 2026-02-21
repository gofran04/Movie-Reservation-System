<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\CinemaSeeder;
use Illuminate\Support\Facades\Gate;
use App\Services\StripeWebhookService;
use App\Models\Movie;
use App\Models\Showtime;
use App\Models\Hall;
use App\Models\User;
use App\Models\Payment;
use App\Services\Gateways\FakeSuccessPaymentGateway;
use App\Services\Contracts\PaymentGatewayInterface;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            CinemaSeeder::class,
        ]);

        Gate::before(fn () => true); // Bypass authorization for testing purposes (Allow everything, skip authorization checks.to seedup the testing time)
    }

    public function test_payment_success_flow_confirms_reservation(): void
    {
        $this->app->bind(PaymentGatewayInterface::class,FakeSuccessPaymentGateway::class);

        $user = User::factory()->create();
        $showtime = $this->createShowtime();

        $this->actingAs($user);

        $payload = [
            'showtime_id' => $showtime->id,
            'seat_ids'    => $showtime->hall->seats()->take(1)->pluck('id')->toArray(),
        ];

        // Create reservation
        $reservationResponse = $this->postJson('/api/reservations', $payload);
        $reservationResponse->assertStatus(201);
        $reservationId = $reservationResponse->json('data.id');

        // Call payment API
        $paymentResponse = $this->postJson("/api/payments/{$reservationId}");
        $paymentResponse->assertStatus(200);

        // Get payment record
        $payment = Payment::first();

        // Simulate webhook success callback
        app(StripeWebhookService::class)->handleSuccess($payment->stripe_checkout_session_id, 'fake_intent_123');

        // Assert payment + reservation updated
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'succeeded',
        ]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservationId,
            'status' => 'confirmed',
        ]);
    }

    private function createShowtime()
    {
        $movie = Movie::factory()->create();
        $hall = Hall::factory()->create();
        $showtime = Showtime::factory()->create([
            'movie_id' => $movie->id,
            'hall_id'  => $hall->id,
        ]);

        return $showtime;
    }
}