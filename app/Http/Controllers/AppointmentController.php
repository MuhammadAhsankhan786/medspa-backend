<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\AppointmentCreated;

class AppointmentController extends Controller
{
    // List all appointments (role-based)
    public function index()
    {
        $user = auth()->user();
        $query = Appointment::with(['client','staff','location']);

        // Client: only own appointments
        if ($user->roles->pluck('name')->contains('client')) {
            $query->where('client_id', $user->id);
        } 
        // Staff: only assigned appointments
        elseif ($user->roles->pluck('name')->intersect(['provider','reception'])->isNotEmpty()) {
            $query->where('staff_id', $user->id);
        }
        // Admin: all appointments
        // no filter needed

        return $query->get();
    }

    // Create appointment (client)
    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:users,id',
            'location_id' => 'required|exists:locations,id',
            'appointment_time' => 'required|date|after:now',
            'notes' => 'nullable|string',
        ]);

        $appointment = Appointment::create([
            'client_id' => auth()->id(),
            'staff_id' => $request->staff_id,
            'location_id' => $request->location_id,
            'appointment_time' => $request->appointment_time,
            'notes' => $request->notes,
        ]);

        // 🔹 Notify assigned staff
        $staff = User::find($request->staff_id);
        if ($staff) {
            $staff->notify(new AppointmentCreated($appointment));
        }

        return response()->json($appointment, 201);
    }

    // Show specific appointment (role + ownership check)
    public function show(Appointment $appointment)
    {
        $user = auth()->user();

        if ($user->roles->pluck('name')->contains('client') && $appointment->client_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->roles->pluck('name')->intersect(['provider','reception'])->isNotEmpty() && $appointment->staff_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $appointment->load(['client','staff','location']);
    }

    // Approve/Reject/Cancel appointment (staff/admin)
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $user = auth()->user();

        if (
            !$user->roles->pluck('name')->contains('admin') &&
            !($user->roles->pluck('name')->intersect(['provider','reception'])->isNotEmpty() && $appointment->staff_id === $user->id)
        ) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected,cancelled',
        ]);

        $appointment->update(['status' => $request->status]);

        return response()->json($appointment);
    }

    // Cancel appointment (client)
    public function destroy(Appointment $appointment)
    {
        $user = auth()->user();

        if (!$user->roles->pluck('name')->contains('client') || $appointment->client_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $appointment->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Appointment cancelled']);
    }
}

