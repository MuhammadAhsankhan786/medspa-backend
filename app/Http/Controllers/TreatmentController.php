<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TreatmentController extends Controller
{
    /**
     * Display a listing of the treatments.
     */
    public function index()
    {
        $user = auth()->user();

        $query = Treatment::with(['appointment.client.user', 'appointment.staff']);

        // Client → only their own treatments
        if ($user->role === 'client') {
            $query->whereHas('appointment', function ($q) use ($user) {
                $q->where('client_id', $user->id);
            });
        }

        return response()->json($query->get());
    }

    /**
     * Store a newly created treatment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'provider_id'    => 'required|exists:users,id',
            'treatment_type' => 'required|string',
            'cost'           => 'required|numeric',
            'status'         => 'required|string',
            'description'    => 'nullable|string',
            'notes'          => 'nullable|string',
            'before_photo'   => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'after_photo'    => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'treatment_date' => 'required|date',
        ]);

        $beforePhoto = $request->hasFile('before_photo')
            ? $request->file('before_photo')->store('treatments', 'public')
            : null;

        $afterPhoto = $request->hasFile('after_photo')
            ? $request->file('after_photo')->store('treatments', 'public')
            : null;

        $treatment = Treatment::create([
            'appointment_id' => $request->appointment_id,
            'provider_id'    => $request->provider_id,
            'treatment_type' => $request->treatment_type,
            'cost'           => $request->cost,
            'status'         => $request->status,
            'description'    => $request->description,
            'notes'          => $request->notes,
            'before_photo'   => $beforePhoto,
            'after_photo'    => $afterPhoto,
            'treatment_date' => $request->treatment_date,
        ]);

        return response()->json([
            'message'   => 'Treatment created successfully',
            'treatment' => $treatment->load(['appointment.client.user', 'appointment.staff']),
        ], 201);
    }

    /**
     * Display the specified treatment.
     */
    public function show(string $id)
    {
        $treatment = Treatment::with(['appointment.client.user', 'appointment.staff'])->findOrFail($id);
        $user = auth()->user();

        if ($user->role === 'client' && $treatment->appointment->client_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($treatment);
    }

    /**
     * Update the specified treatment.
     */
    public function update(Request $request, string $id)
    {
        $treatment = Treatment::findOrFail($id);

        $request->validate([
            'treatment_type' => 'nullable|string',
            'cost'           => 'nullable|numeric',
            'status'         => 'nullable|string',
            'description'    => 'nullable|string',
            'notes'          => 'nullable|string',
            'before_photo'   => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'after_photo'    => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'treatment_date' => 'nullable|date',
        ]);

        if ($request->hasFile('before_photo')) {
            $treatment->before_photo = $request->file('before_photo')->store('treatments', 'public');
        }

        if ($request->hasFile('after_photo')) {
            $treatment->after_photo = $request->file('after_photo')->store('treatments', 'public');
        }

        $treatment->update($request->only([
            'treatment_type', 'cost', 'status', 'description', 'notes', 'treatment_date'
        ]));

        return response()->json([
            'message'   => 'Treatment updated successfully',
            'treatment' => $treatment->load(['appointment.client.user', 'appointment.staff']),
        ]);
    }

    /**
     * Remove the specified treatment.
     */
    public function destroy(string $id)
    {
        $treatment = Treatment::findOrFail($id);
        $treatment->delete();

        return response()->json(['message' => 'Treatment deleted successfully']);
    }
}
