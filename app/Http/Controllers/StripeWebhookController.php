<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Log full webhook event from Stripe
        Log::info('Stripe webhook received:', $request->all());

        // Optionally handle specific events
        $eventType = $request->type ?? 'unknown';

        switch ($eventType) {
            case 'payment_intent.succeeded':
                Log::info('✅ Payment succeeded: ' . $request->data['object']['id']);
                break;

            case 'payment_intent.payment_failed':
                Log::warning('❌ Payment failed: ' . $request->data['object']['id']);
                break;

            default:
                Log::info('ℹ️ Event type: ' . $eventType);
        }

        return response('Webhook received', 200);
    }
}
