<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\VehicleInspection;
use App\Models\VehicleConditionItem;
use App\Models\InspectionEvidence;
use App\Enums\InspectionType;
use App\Enums\InspectionStatus;
use App\Enums\ReservationStatus;
use App\Enums\CarStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PickupInspectionController extends Controller
{
    /**
     * Show the form for creating a pickup inspection.
     */
    public function create(Reservation $reservation): Response
    {
        $reservation->load(['user', 'car']);

        if ($reservation->status !== ReservationStatus::CONFIRMED) {
            abort(403, 'This reservation cannot have a pickup inspection. It must be confirmed first.');
        }

        $existingInspection = VehicleInspection::where('reservation_id', $reservation->id)
            ->where('inspection_type', InspectionType::PICKUP)
            ->where('status', InspectionStatus::COMPLETED)
            ->first();

        if ($existingInspection) {
            return redirect()
                ->route('admin.reservations.show', $reservation)
                ->with('info', 'A pickup inspection already exists for this reservation.');
        }

        return Inertia::render('Admin/Inspections/PickupCreate', [
            'reservation' => $reservation,
            'conditionStatuses' => collect(\App\Enums\ConditionStatus::cases())
                ->mapWithKeys(fn($case) => [$case->value => ucfirst($case->value)])
                ->toArray(),
            'severities' => collect(\App\Enums\Severity::cases())
                ->mapWithKeys(fn($case) => [$case->value => ucfirst($case->value)])
                ->toArray(),
            'checklist_areas' => $this->getChecklistAreas(),
        ]);
    }

    /**
     * Store a newly created pickup inspection.
     */
    public function store(Request $request, Reservation $reservation)
    {
        $reservation->load('car');

        if ($reservation->status !== ReservationStatus::CONFIRMED) {
            abort(403, 'This reservation cannot have a pickup inspection.');
        }

        $validated = $request->validate([
            'mileage' => 'nullable|numeric|min:0',
            'fuel_level' => 'nullable|string|max:50',
            'overall_condition' => 'required|string|max:100',
            'notes' => 'nullable|string',
            'inspection_date' => 'nullable|date',
            'condition_items' => 'required|array',
            'condition_items.*.area_name' => 'required|string|max:100',
            'condition_items.*.condition_status' => 'required|string|max:50',
            'condition_items.*.severity' => 'nullable|string|max:50',
            'condition_items.*.notes' => 'nullable|string',
            'evidence' => 'nullable|array',
            'evidence.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'evidence_captions' => 'nullable|array',
        ]);

        $inspection = VehicleInspection::create([
            'car_id' => $reservation->car_id,
            'reservation_id' => $reservation->id,
            'inspected_by' => auth()->id(),
            'inspection_type' => InspectionType::PICKUP,
            'mileage' => $validated['mileage'],
            'fuel_level' => $validated['fuel_level'],
            'overall_condition' => $validated['overall_condition'],
            'notes' => $validated['notes'],
            'inspection_date' => $validated['inspection_date'] ?? now(),
            'status' => InspectionStatus::COMPLETED,
        ]);

        foreach ($validated['condition_items'] as $item) {
            if (!empty($item['area_name'])) {
                VehicleConditionItem::create([
                    'inspection_id' => $inspection->id,
                    'area_name' => $item['area_name'],
                    'condition_status' => $item['condition_status'],
                    'severity' => $item['severity'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        }

        if ($request->hasFile('evidence')) {
            foreach ($request->file('evidence') as $index => $file) {
                $path = $file->store('inspection-evidence', 'public');
                $caption = $validated['evidence_captions'][$index] ?? '';

                InspectionEvidence::create([
                    'inspection_id' => $inspection->id,
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                    'caption' => $caption,
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        $reservation->update(['status' => ReservationStatus::ACTIVE]);
        $reservation->car->update(['status' => CarStatus::RENTED]);

        return redirect()
            ->route('admin.reservations.show', $reservation)
            ->with('success', 'Pickup inspection completed successfully. Reservation is now active.');
    }

    /**
     * Get standard checklist areas for vehicle inspection.
     */
    private function getChecklistAreas(): array
    {
        return [
            'Front bumper',
            'Rear bumper',
            'Left side',
            'Right side',
            'Windshield',
            'Tires',
            'Interior',
            'Dashboard',
            'Boot/trunk',
            'Lights',
            'Mirrors',
        ];
    }
}
