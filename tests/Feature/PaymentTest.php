<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\CinemaSeeder;
use Illuminate\Support\Facades\Gate;
use App\Services\Webhooks\Stripe\StripePaymentWebhookService;
use App\Models\Movie;
use App\Models\Showtime;
use App\Models\Hall;
use App\Models\User;
use App\Models\Payment;
use App\Services\Gateways\FakeSuccessPaymentGateway;
use App\Services\Gateways\FakeFailPaymentGateway;
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
        app(StripePaymentWebhookService::class)->handleSuccess($payment->gateway_reference, 'fake_intent_123');

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

    public function test_payment_failure_cancels_reservation_and_releases_seats()
    {
        $this->app->bind(PaymentGatewayInterface::class,FakeFailPaymentGateway::class);

        $user = User::factory()->create();
        $showtime = $this->createShowtime();

        $this->actingAs($user);

        $payload = [
            'showtime_id' => $showtime->id,
            'seat_ids'    => $showtime->hall->seats()->take(1)->pluck('id')->toArray(),
        ];

        //create reservation
        $reservationResponse = $this->postJson('/api/reservations', $payload);
        $reservationResponse->assertStatus(201);

        $reservationId = $reservationResponse->json('data.id');

        //call payment API
        $paymentResponse = $this->postJson("/api/payments/{$reservationId}");
        $paymentResponse->assertStatus(200);

        $payment = Payment::first();

        app(StripePaymentWebhookService::class)->handleFailure($payment->gateway_reference);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'failed',
        ]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservationId,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseCount('reservation_seats', 0);
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