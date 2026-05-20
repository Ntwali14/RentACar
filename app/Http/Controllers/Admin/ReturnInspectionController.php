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

class ReturnInspectionController extends Controller
{
    /**
     * Show the form for creating a return inspection.
     */
    public function create(Reservation $reservation): Response
    {
        $reservation->load(['user', 'car']);

        if ($reservation->status !== ReservationStatus::ACTIVE) {
            abort(403, 'This reservation must be active for a return inspection.');
        }

        $pickupInspection = VehicleInspection::where('reservation_id', $reservation->id)
            ->where('inspection_type', InspectionType::PICKUP)
            ->where('status', InspectionStatus::COMPLETED)
            ->first();

        if (!$pickupInspection) {
            abort(403, 'A completed pickup inspection is required before a return inspection.');
        }

        $existingReturn = VehicleInspection::where('reservation_id', $reservation->id)
            ->where('inspection_type', InspectionType::RETURN)
            ->where('status', InspectionStatus::COMPLETED)
            ->first();

        if ($existingReturn) {
            return redirect()
                ->route('admin.reservations.inspection-comparison', $reservation)
                ->with('info', 'A return inspection already exists for this reservation.');
        }

        $pickupInspection->load('conditionItems');

        return Inertia::render('Admin/Inspections/ReturnCreate', [
            'reservation' => $reservation,
            'pickupInspection' => $pickupInspection,
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
     * Store a newly created return inspection.
     */
    public function store(Request $request, Reservation $reservation)
    {
        $reservation->load('car');

        if ($reservation->status !== ReservationStatus::ACTIVE) {
            abort(403, 'This reservation must be active for a return inspection.');
        }

        $pickupInspection = VehicleInspection::where('reservation_id', $reservation->id)
            ->where('inspection_type', InspectionType::PICKUP)
            ->where('status', InspectionStatus::COMPLETED)
            ->first();

        if (!$pickupInspection) {
            abort(403, 'A completed pickup inspection is required.');
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
            'inspection_type' => InspectionType::RETURN,
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

        $reservation->update(['status' => ReservationStatus::COMPLETED]);
        $reservation->car->update(['status' => CarStatus::CLEANING]);

        return redirect()
            ->route('admin.reservations.inspection-comparison', $reservation)
            ->with('success', 'Return inspection completed successfully.');
    }

    /**
     * Show the comparison between pickup and return inspections.
     */
    public function compare(Reservation $reservation): Response
    {
        $reservation->load(['user', 'car']);

        $pickupInspection = VehicleInspection::where('reservation_id', $reservation->id)
            ->where('inspection_type', InspectionType::PICKUP)
            ->where('status', InspectionStatus::COMPLETED)
            ->first();

        if (!$pickupInspection) {
            abort(404, 'Pickup inspection not found.');
        }

        $returnInspection = VehicleInspection::where('reservation_id', $reservation->id)
            ->where('inspection_type', InspectionType::RETURN)
            ->where('status', InspectionStatus::COMPLETED)
            ->first();

        if (!$returnInspection) {
            abort(404, 'Return inspection not found.');
        }

        $pickupInspection->load(['conditionItems', 'evidence' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }]);

        $returnInspection->load(['conditionItems', 'evidence' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }]);

        $comparison = $this->buildComparison($pickupInspection, $returnInspection);

        return Inertia::render('Admin/Inspections/InspectionComparison', [
            'reservation' => $reservation,
            'pickupInspection' => $pickupInspection,
            'returnInspection' => $returnInspection,
            'comparison' => $comparison,
        ]);
    }

    /**
     * Build a detailed comparison between pickup and return inspections.
     */
    private function buildComparison(VehicleInspection $pickup, VehicleInspection $return): array
    {
        $pickupItems = $pickup->conditionItems->keyBy('area_name');
        $returnItems = $return->conditionItems->keyBy('area_name');

        $comparison = [];
        $allAreas = collect()
            ->merge($pickupItems->keys())
            ->merge($returnItems->keys())
            ->unique()
            ->sort();

        foreach ($allAreas as $area) {
            $pickupItem = $pickupItems->get($area);
            $returnItem = $returnItems->get($area);

            $changed = false;
            $changeReason = null;

            if ($pickupItem && $returnItem) {
                if ($pickupItem->condition_status !== $returnItem->condition_status) {
                    $changed = true;
                    $changeReason = "Condition changed from {$pickupItem->condition_status} to {$returnItem->condition_status}";
                }
                if ($pickupItem->severity !== $returnItem->severity) {
                    $changed = true;
                    if (!$changeReason) {
                        $changeReason = "Severity changed";
                    }
                }
            }

            $comparison[] = [
                'area_name' => $area,
                'pickup' => $pickupItem ? [
                    'condition_status' => $pickupItem->condition_status,
                    'severity' => $pickupItem->severity,
                    'notes' => $pickupItem->notes,
                ] : null,
                'return' => $returnItem ? [
                    'condition_status' => $returnItem->condition_status,
                    'severity' => $returnItem->severity,
                    'notes' => $returnItem->notes,
                ] : null,
                'changed' => $changed,
                'changeReason' => $changeReason,
            ];
        }

        return $comparison;
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
