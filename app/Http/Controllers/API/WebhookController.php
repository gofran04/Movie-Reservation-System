<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Webhook;
use App\Services\Webhooks\Stripe\StripePaymentWebhookService;
use App\Services\Webhooks\Stripe\StripeRefundWebhookService;

class WebhookController extends Controller
{
    protected StripePaymentWebhookService $StripePaymentWebhookService;
    protected StripeRefundWebhookService $stripeRefundWebhookService;

    public function __construct(StripePaymentWebhookService $StripePaymentWebhookService, StripeRefundWebhookService $stripeRefundWebhookService)
    {
        $this->StripePaymentWebhookService = $StripePaymentWebhookService;
        $this->stripeRefundWebhookService = $stripeRefundWebhookService;
    }

    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Invalid webhook signature'
            ], Response::HTTP_BAD_REQUEST);
        }

        match ($event->type) {

            // Payment success
            'checkout.session.completed' => $this->StripePaymentWebhookService
                ->handleSuccess(
                    $event->data->object->id,
                    $event->data->object->payment_intent
                ),

            // Payment failure
            'checkout.session.async_payment_failed' => $this->StripePaymentWebhookService
                ->handleFailure(
                    $event->data->object->id
                ),

            // Refund success
            'refund.updated' => $this->stripeRefundWebhookService
                ->handleSuccess(
                    $event->data->object->id
                ),

            // Refund failure
            'refund.failed' => $this->stripeRefundWebhookService
                ->handleFailure(
                    $event->data->object->id
                ),

            default => null
        };

        return response()->json(['status' => 'ok']);
    }
}
