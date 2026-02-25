<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Webhook;
use App\Services\StripeWebhookService;
use App\Services\StripeRefundWebhookService;

class WebhookController extends Controller
{
    protected StripeWebhookService $stripeWebhookService;
    protected StripeRefundWebhookService $stripeRefundWebhookService;

    public function __construct(StripeWebhookService $stripeWebhookService, StripeRefundWebhookService $stripeRefundWebhookService)
    {
        $this->stripeWebhookService = $stripeWebhookService;
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
            'checkout.session.completed' => $this->stripeWebhookService
                ->handleSuccess(
                    $event->data->object->id,
                    $event->data->object->payment_intent
                ),

            // Payment failure
            'checkout.session.async_payment_failed' => $this->stripeWebhookService
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
    // public function handle(Request $request)
    // {
    //     $payload = $request->getContent();
    //     $sigHeader = $request->header('Stripe-Signature');

    //     $event = Webhook::constructEvent(
    //         $payload,
    //         $sigHeader,
    //         config('services.stripe.webhook_secret')
    //     );

    //     if ($event->type === 'checkout.session.completed') {
    //         $this->stripeWebhookService->handleSuccess($event->data->object->id, $event->data->object->payment_intent);
    //     }
    //     if ($event->type === 'checkout.session.async_payment_failed') {
    //         $this->stripeWebhookService->handleFailure($event->data->object->id);
    //     }

    //     return response()->json(['status' => 'ok']);
    // }
}
