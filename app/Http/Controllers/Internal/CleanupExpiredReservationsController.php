<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class CleanupExpiredReservationsController extends Controller
{
    public function __invoke(Request $request)
    {
        $secret = config('services.cron.secret');

        if (! $secret || ! hash_equals($secret, $request->header('X-Cron-Secret', ''))) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        Artisan::call('reservations:cleanup-expired');

        return response()->json([
            'message' => 'Expired reservations cleanup completed.',
        ]);
    }
}