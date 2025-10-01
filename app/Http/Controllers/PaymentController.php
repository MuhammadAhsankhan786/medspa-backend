<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET')); // Set Stripe secret
    }

    // List all payments (Admin/Provider)
    public function index()
    {
        try {
            $user = auth()->user();

            $query = Payment::with(['client.clientUser', 'appointment', 'package']);

            if ($user->role === 'client') {
                $query->where('client_id', $user->id);
            }

            return response()->json($query->get());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Store a new payment
    public function store(Request $request)
    {
        try {
            $request->validate([
                'client_id'      => 'required|exists:clients,id',
                'appointment_id' => 'nullable|exists:appointments,id',
                'package_id'     => 'nullable|exists:packages,id',
                'amount'         => 'required|numeric',
                'payment_method' => 'required|in:stripe,cash',
                'tips'           => 'nullable|numeric',
                'commission'     => 'nullable|numeric',
                'status'         => 'required|in:pending,completed,canceled',
            ]);

            $payment = Payment::create($request->all());

            // Audit log
            AuditLog::create([
                'user_id'    => auth()->id(),
                'action'     => 'create',
                'table_name' => 'payments',
                'record_id'  => $payment->id,
                'new_data'   => json_encode($payment),
            ]);

            return response()->json([
                'message' => 'Payment created successfully',
                'payment' => $payment->load(['client.clientUser', 'appointment', 'package'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Show specific payment
    public function show($id)
    {
        try {
            $payment = Payment::with(['client.clientUser', 'appointment', 'package'])->findOrFail($id);
            $user = auth()->user();

            if ($user->role === 'client' && $payment->client_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json($payment);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update payment
    public function update(Request $request, $id)
    {
        try {
            $payment = Payment::findOrFail($id);

            $request->validate([
                'amount'         => 'nullable|numeric',
                'payment_method' => 'nullable|in:stripe,cash',
                'tips'           => 'nullable|numeric',
                'commission'     => 'nullable|numeric',
                'status'         => 'nullable|in:pending,completed,canceled',
            ]);

            $oldData = $payment->toArray();

            $payment->update($request->only(['amount','payment_method','tips','commission','status']));

            // Audit log
            AuditLog::create([
                'user_id'    => auth()->id(),
                'action'     => 'update',
                'table_name' => 'payments',
                'record_id'  => $payment->id,
                'old_data'   => json_encode($oldData),
                'new_data'   => json_encode($payment),
            ]);

            return response()->json([
                'message' => 'Payment updated successfully',
                'payment' => $payment->load(['client.clientUser', 'appointment', 'package'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete payment
    public function destroy($id)
    {
        try {
            $payment = Payment::findOrFail($id);
            $oldData = $payment->toArray();

            $payment->delete();

            // Audit log
            AuditLog::create([
                'user_id'    => auth()->id(),
                'action'     => 'delete',
                'table_name' => 'payments',
                'record_id'  => $id,
                'old_data'   => json_encode($oldData),
            ]);

            return response()->json(['message' => 'Payment deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Stripe Payment
    public function pay(Request $request)
    {
        try {
            $request->validate(['payment_id' => 'required|exists:payments,id']);
            $payment = Payment::findOrFail($request->payment_id);

            $intent = PaymentIntent::create([
                'amount' => $payment->amount * 100,
                'currency' => 'usd',
                'payment_method_types' => ['card'],
            ]);

            return response()->json([
                'client_secret' => $intent->client_secret,
                'payment' => $payment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Stripe payment failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
