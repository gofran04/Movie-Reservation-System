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
use App\Models\Reservation;
use App\Services\Gateways\FakeSuccessPaymentGateway;
use App\Services\Gateways\FakeFailPaymentGateway;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\Gateways\FakeSuccessRefundGateway;
use App\Services\Gateways\FakeFailRefundGateway;
use App\Services\Contracts\RefundGatewayInterface;
use App\Services\Webhooks\Stripe\StripeRefundWebhookService;

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

    public function test_successful_refund_flow_for_confirmed_reservation()
    {
        $this->app->bind(RefundGatewayInterface::class,FakeSuccessRefundGateway::class);

        $user = User::factory()->create();
        $this->actingAs($user);

        // Create confirmed reservation for testing (no nedd to send an API regquest to create a real reservation)
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'status' => 'confirmed',
        ]);

        // Create a successful payment record for the reservation(for testing refund flow, no need to go through the whole payment process again and send API calls, we can directly create a successful payment record in the database)
        $payment = Payment::factory()->create([
                'reservation_id' => $reservation->id,
                'status'         => 'succeeded',
        ]);

        //call refund API
        $this->postJson("/api/reservations/{$reservation->id}/cancel")->assertOk();

        // Refresh payment to get the latest data from the database, and assert the status
        $payment->refresh();
        $this->assertEquals('refund_pending', $payment->status);
        $this->assertNotNull($payment->refund_reference);

        // // Simulate webhook success callback
        app(StripeRefundWebhookService::class)->handleSuccess($payment->refund_reference);

        $payment->refresh();
        $reservation->refresh();

        $this->assertEquals('refunded', $payment->status);
        $this->assertEquals('cancelled', $reservation->status);
        $this->assertCount(0, $reservation->seats);
    }

    public function test_refund_failure_does_not_cancel_reservation()
    {
        $this->app->bind(RefundGatewayInterface::class,FakeFailRefundGateway::class);

        $user = User::factory()->create();
        $showtime = $this->createShowtime();

        $this->actingAs($user);

        $payload = [
            'showtime_id' => $showtime->id,
            'seat_ids'    => $showtime->hall->seats()->take(2)->pluck('id')->toArray(),
        ];

        // Create reservation
        $reservationResponse = $this->postJson('/api/reservations', $payload);
        $reservationResponse->assertStatus(201);

        $reservationId = $reservationResponse->json('data.id');
        $reservation = Reservation::find($reservationId);
        $reservation->update(['status' => 'confirmed']);


        // Create a successful payment record for the reservation(for testing refund flow, no need to go through the whole payment process again and send API calls, we can directly create a successful payment record in the database)
        $payment = Payment::factory()->create([
                'reservation_id' => $reservationId,
                'status'         => 'succeeded',
        ]);

        $response = $this->postJson("/api/reservations/{$reservationId}/cancel");
        $response->assertStatus(500);

        $payment->refresh();
        $reservation->refresh();

        $this->assertEquals('succeeded', $payment->status);
        $this->assertEquals('confirmed', $reservation->status);
        $this->assertCount(2, $reservation->seats);
    }

    public function test_user_can_not_refund_other_users_reservation()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $showtime = $this->createShowtime();

        $this->actingAs($user1);

        $payload = [
            'showtime_id' => $showtime->id,
            'seat_ids'    => $showtime->hall->seats()->take(1)->pluck('id')->toArray(),
        ];

        // create reservation
        $reservationResponse = $this->postJson("/api/reservations",$payload);
        $reservationId = $reservationResponse->json('data.id');

        // Call payment API
        $this->postJson("/api/payments/{$reservationId}");

        $this->actingAs($user2);

        $response = $this->postJson("/api/reservation/{$reservationId}/cancel");
        $response->assertStatus(404); // laravel retur 404 instead of 403 to hide resourse existence(if 403 returned, attackers will infer which IDs exist)
    }

    public function test_user_can_retry_payment_after_failure()
    {
        $user = User::factory()->create();
        $showtime = $this->createShowtime();

        $this->actingAs($user);

        $reservation = Reservation::factory()->pending()->create([
            'user_id' => $user->id,
            'showtime_id' => $showtime->id,
        ]);

        // Call payment API (first attempt - failure)
        $this->app->bind(PaymentGatewayInterface::class,FakeFailPaymentGateway::class);

        $paymentResponse1 = $this->postJson("/api/payments/{$reservation->id}");
        $paymentResponse1->assertStatus(200);

        // Assert payment failed
        $payment1 = Payment::first();

        app(StripePaymentWebhookService::class)->handleFailure($payment1->gateway_reference);
        $this->assertDatabaseHas('payments', [
            'id'     => $payment1->id,
            'status' => 'failed',
        ]);

        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'pending',
        ]);

        // Change the payment gateway to success for retry
        $this->app->bind(PaymentGatewayInterface::class,FakeSuccessPaymentGateway::class);

        // Call payment API (second attempt - success)
        $paymentResponse2 = $this->postJson("/api/payments/{$reservation->id}");
        $paymentResponse2->assertStatus(200);

        // Assert payment succeeded and reservation confirmed
        $payment2 = Payment::latest()->first();

        app(StripePaymentWebhookService::class)->handleSuccess($payment2->gateway_reference, 'fake_intent_123');

        $this->assertDatabaseHas('payments', [
            'id'     => $payment2->id,
            'status' => 'succeeded',
        ]);

        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
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