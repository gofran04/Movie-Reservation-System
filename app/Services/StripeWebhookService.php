<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class StripeWebhookService
{
    public function handleSuccess(string $sessionId, ?string $paymentIntentId = null): void
    {
        DB::transaction(function () use ($sessionId, $paymentIntentId) {

            $payment = Payment::where('gateway_reference', $sessionId)
                ->lockForUpdate()
                ->firstOrFail();

            // Idempotency protection
            if ($payment->status === 'succeeded') {
                return;
            }

            $payment->update([
                'stripe_payment_intent_id' => $paymentIntentId,
                'status' => 'succeeded',
            ]);

            $payment->reservation->update([
                'status' => 'confirmed',
            ]);
        });
    }

    public function handleFailure(string $sessionId): void
    {
        DB::transaction(function () use ($sessionId) {

            $payment = Payment::where('gateway_reference', $sessionId)
                ->lockForUpdate()
                ->first();

            if (!$payment) {
                return;
            }

            // Idempotency protection
            if ($payment->status === 'failed') {
                return;
            }

            $payment->update([
                'status' => 'failed',
            ]);

            $reservation = $payment->reservation;

            $reservation->update([
                'status' => 'cancelled',
            ]);

            // Release seats
            $reservation->seats()->detach();
        });
    }
}
