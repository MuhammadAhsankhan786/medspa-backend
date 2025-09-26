<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\AppointmentCreated;

class AppointmentController extends Controller
{
    // 🔹 List all appointments (role-based)
    public function index()
    {
        $user = auth()->user();
        $query = Appointment::with(['client','staff','location']);

        if ($user->roles->pluck('name')->contains('client')) {
            $query->where('client_id', $user->id);
        } elseif ($user->roles->pluck('name')->intersect(['provider','reception'])->isNotEmpty()) {
            $query->where('staff_id', $user->id);
        }
        // Admin → all appointments

        return response()->json($query->get());
    }

    // 🔹 Show specific appointment (role + ownership check)
    public function show(Appointment $appointment)
    {
        $user = auth()->user();

        if ($user->roles->pluck('name')->contains('client') && $appointment->client_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->roles->pluck('name')->intersect(['provider','reception'])->isNotEmpty() && $appointment->staff_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($appointment->load(['client','staff','location']));
    }

    // 🔹 Create appointment (client)
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
            'status' => 'pending',
        ]);

        // 🔹 Notify assigned staff (Mail + DB + SMS)
        $staff = User::find($request->staff_id);
        if ($staff) {
            $notification = new AppointmentCreated($appointment);

            $staff->notify($notification);  // ✅ Mail + Database
            $notification->toSms($staff);   // ✅ SMS
        }

        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment' => $appointment->load(['client','staff','location'])
        ], 201);
    }

    // 🔹 Update appointment status (staff/admin)
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

        return response()->json([
            'message' => 'Appointment status updated',
            'appointment' => $appointment->load(['client','staff','location'])
        ]);
    }

    // 🔹 Cancel appointment (client)
    public function destroy(Appointment $appointment)
    {
        $user = auth()->user();

        if (!$user->roles->pluck('name')->contains('client') || $appointment->client_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $appointment->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Appointment cancelled']);
    }

    // 🔹 Client → List only their own appointments
    public function myAppointments()
    {
        $user = auth()->user();

        if (!$user->roles->pluck('name')->contains('client')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $appointments = Appointment::with(['client','staff','location'])
            ->where('client_id', $user->id)
            ->get();

        return response()->json($appointments);
    }
}
