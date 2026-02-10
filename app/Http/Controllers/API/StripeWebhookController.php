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

        return response()->json(['status' => 'ok']);
    }

    private function handleCheckoutSuccess($session)
    {
        DB::transaction(function () use ($session) {

            $payment = Payment::where('stripe_checkout_session_id', $session->id)
                ->lockForUpdate()
                ->firstOrFail();

            $payment->update([
                'status' => 'succeeded',
            ]);

            $payment->reservation->update([
                'status' => 'confirmed',
            ]);
        });
    }
}
