<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Webhook;
use App\Services\StripeWebhookService;

class WebhookController extends Controller
{
    protected StripeWebhookService $stripeWebhookService;

    public function __construct(StripeWebhookService $stripeWebhookService)
    {
        $this->stripeWebhookService = $stripeWebhookService;
    }

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
            $this->stripeWebhookService->handleSuccess($event->data->object->id, $event->data->object->payment_intent);
        }
        if ($event->type === 'checkout.session.async_payment_failed') {
            $this->stripeWebhookService->handleFailure($event->data->object->id);
        }

        return response()->json(['status' => 'ok']);
    }
}
