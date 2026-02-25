<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StripeRefundWebhookService
{
    public function handleSuccess(string $refundReference): void
    {
        DB::transaction(function () use ($refundReference) {

            $payment = Payment::where('refund_reference', $refundReference)->first();

            if (! $payment) {
                Log::error('Refund webhook: Payment not found', [
                    'refund_reference' => $refundReference,
                ]);
                return;
            }

            if ($payment->status === 'refunded') {
                return;
            }

            $payment->update([
                'status' => 'refunded',
            ]);

            $reservation = $payment->reservation;

            $reservation->update([
                'status' => 'cancelled',
            ]);

            $reservation->seats()->detach();

            Log::info('Refund succeeded', [
                'payment_id'       => $payment->id,
                'reservation_id'   => $reservation->id,
                'refund_reference' => $refundReference,
            ]);
        });
    }

    public function handleFailure(string $refundReference): void
    {
        $payment = Payment::where('refund_reference', $refundReference)->first();

        if (! $payment) {
            Log::error('Refund failure webhook: Payment not found', [
                'refund_reference' => $refundReference,
            ]);
            return;
        }

        $payment->update([
            'status' => 'refund_failed',
        ]);

        Log::warning('Refund failed', [
            'payment_id'       => $payment->id,
            'refund_reference' => $refundReference,
        ]);
    }
}