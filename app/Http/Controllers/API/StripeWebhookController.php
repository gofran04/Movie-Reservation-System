<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        $event = Webhook::constructEvent(
            $payload,
            $sigHeader,
            config('services.stripe.webhook_secret')
        );

        if ($event->type === 'checkout.session.completed') {
            $this->handleCheckoutSuccess($event->data->object);
        }
        if ($event->type === 'checkout.session.async_payment_failed') {
            $this->handleCheckoutFailure($event->data->object);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleCheckoutSuccess($session)
    {
        DB::transaction(function () use ($session) {

            $payment = Payment::where('stripe_checkout_session_id', $session->id)
                ->lockForUpdate()
                ->firstOrFail();

            $payment->update([
                'stripe_payment_intent_id' => $session->payment_intent, // store payment intent for future refunds. it's needed for refunds because checkout session doesn't have refund endpoint, but payment intent does. it's created automatically by stripe when checkout session is completed.
                'status' => 'succeeded',
            ]);

            $payment->reservation->update([
                'status' => 'confirmed',
            ]);
        });
    }

    private function handleCheckoutFailure($session)
    {
        DB::transaction(function () use ($session) {

            $payment = Payment::where('stripe_checkout_session_id', $session->id)->lockForUpdate()->first();

            if (!$payment) return;

            $payment->update(['status' => 'failed']);

            $reservation = $payment->reservation;

            $reservation->update([
                'status' => 'cancelled',
            ]);

            $reservation->seats()->detach(); // free seats
        });
    }
}
