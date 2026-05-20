<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DamageReport;
use App\Models\Reservation;
use App\Models\VehicleInspection;
use App\Models\InspectionEvidence;
use App\Enums\InspectionType;
use App\Enums\InspectionStatus;
use App\Enums\DamageReportStatus;
use App\Enums\CustomerLiabilityStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DamageReportController extends Controller
{
    /**
     * Display a listing of damage reports.
     */
    public function index(Request $request): Response
    {
        $status = $request->input('status');

        $statusCounts = DamageReport::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $damageReports = DamageReport::query()
            ->with(['car:id,make,model', 'reservation:id,user_id,reservation_number', 'reservation.user:id,name'])
            ->when($request->string('search')->toString(), function ($query, $search) {
                $search = $request->string('search')->toString();
                $query->where(function ($q) use ($search) {
                    $q->where('damage_description', 'like', "%{$search}%")
                        ->orWhereHas('reservation', function ($rq) use ($search) {
                            $rq->where('reservation_number', 'like', "%{$search}%")
                                ->orWhereHas('user', function ($uq) use ($search) {
                                    $uq->where('name', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when($status && $status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $statuses = collect(DamageReportStatus::cases())
            ->mapWithKeys(fn($case) => [$case->value => ['label' => ucfirst(str_replace('_', ' ', $case->value)), 'count' => $statusCounts[$case->value] ?? 0]])
            ->toArray();

        return Inertia::render('Admin/DamageReports/Index', [
            'damageReports' => $damageReports,
            'statuses' => $statuses,
            'filters' => ['search' => $request->string('search')->toString(), 'status' => $status],
        ]);
    }

    /**
     * Show the form for creating a damage report.
     */
    public function create(Reservation $reservation): Response
    {
        $reservation->load(['user', 'car']);

        $pickupInspection = VehicleInspection::where('reservation_id', $reservation->id)
            ->where('inspection_type', InspectionType::PICKUP)
            ->where('status', InspectionStatus::COMPLETED)
            ->first();

        $returnInspection = VehicleInspection::where('reservation_id', $reservation->id)
            ->where('inspection_type', InspectionType::RETURN)
            ->where('status', InspectionStatus::COMPLETED)
            ->first();

        if (!$pickupInspection || !$returnInspection) {
            abort(403, 'Both pickup and return inspections are required to create a damage report.');
        }

        $pickupInspection->load(['conditionItems', 'evidence']);
        $returnInspection->load(['conditionItems', 'evidence']);

        return Inertia::render('Admin/DamageReports/Create', [
            'reservation' => $reservation,
            'pickupInspection' => $pickupInspection,
            'returnInspection' => $returnInspection,
            'customerLiabilityStatuses' => collect(CustomerLiabilityStatus::cases())
                ->mapWithKeys(fn($case) => [$case->value => ucfirst(str_replace('_', ' ', $case->value))])
                ->toArray(),
            'damageReportStatuses' => collect(DamageReportStatus::cases())
                ->mapWithKeys(fn($case) => [$case->value => ucfirst(str_replace('_', ' ', $case->value))])
                ->toArray(),
        ]);
    }

    /**
     * Store a newly created damage report.
     */
    public function store(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'damage_description' => 'required|string|min:10',
            'estimated_cost' => 'nullable|numeric|min:0',
            'customer_liability_status' => 'required|string|in:' . implode(',', array_map(fn($case) => $case->value, CustomerLiabilityStatus::cases())),
            'admin_decision' => 'nullable|string',
            'status' => 'required|string|in:' . implode(',', array_map(fn($case) => $case->value, DamageReportStatus::cases())),
        ]);

        $pickupInspection = VehicleInspection::where('reservation_id', $reservation->id)
            ->where('inspection_type', InspectionType::PICKUP)
            ->where('status', InspectionStatus::COMPLETED)
            ->first();

        $returnInspection = VehicleInspection::where('reservation_id', $reservation->id)
            ->where('inspection_type', InspectionType::RETURN)
            ->where('status', InspectionStatus::COMPLETED)
            ->first();

        if (!$pickupInspection || !$returnInspection) {
            abort(403, 'Both inspections are required.');
        }

        $damageReport = DamageReport::create([
            'car_id' => $reservation->car_id,
            'reservation_id' => $reservation->id,
            'pickup_inspection_id' => $pickupInspection->id,
            'return_inspection_id' => $returnInspection->id,
            'reported_by' => auth()->id(),
            'damage_description' => $validated['damage_description'],
            'estimated_cost' => $validated['estimated_cost'],
            'customer_liability_status' => $validated['customer_liability_status'],
            'admin_decision' => $validated['admin_decision'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.damageReports.show', $damageReport)
            ->with('success', 'Damage report created successfully.');
    }

    /**
     * Display the specified damage report.
     */
    public function show(DamageReport $damageReport): Response
    {
        $damageReport->load([
            'car:id,make,model,year',
            'reservation:id,reservation_number,user_id,start_date,end_date',
            'reservation.user:id,name,email',
            'pickupInspection:id,mileage,fuel_level,overall_condition,inspection_date',
            'pickupInspection.evidence',
            'returnInspection:id,mileage,fuel_level,overall_condition,inspection_date',
            'returnInspection.evidence',
            'reportedBy:id,name',
            'disputes',
        ]);

        return Inertia::render('Admin/DamageReports/Show', [
            'damageReport' => $damageReport,
            'customerLiabilityStatuses' => collect(CustomerLiabilityStatus::cases())
                ->mapWithKeys(fn($case) => [$case->value => ucfirst(str_replace('_', ' ', $case->value))])
                ->toArray(),
            'damageReportStatuses' => collect(DamageReportStatus::cases())
                ->mapWithKeys(fn($case) => [$case->value => ucfirst(str_replace('_', ' ', $case->value))])
                ->toArray(),
        ]);
    }

    /**
     * Show the form for editing the specified damage report.
     */
    public function edit(DamageReport $damageReport): Response
    {
        $damageReport->load([
            'car:id,make,model,year',
            'reservation:id,reservation_number,user_id',
            'reservation.user:id,name',
            'pickupInspection:id,mileage,fuel_level,overall_condition',
            'pickupInspection.evidence',
            'returnInspection:id,mileage,fuel_level,overall_condition',
            'returnInspection.evidence',
        ]);

        return Inertia::render('Admin/DamageReports/Edit', [
            'damageReport' => $damageReport,
            'customerLiabilityStatuses' => collect(CustomerLiabilityStatus::cases())
                ->mapWithKeys(fn($case) => [$case->value => ucfirst(str_replace('_', ' ', $case->value))])
                ->toArray(),
            'damageReportStatuses' => collect(DamageReportStatus::cases())
                ->mapWithKeys(fn($case) => [$case->value => ucfirst(str_replace('_', ' ', $case->value))])
                ->toArray(),
        ]);
    }

    /**
     * Update the specified damage report.
     */
    public function update(Request $request, DamageReport $damageReport)
    {
        $validated = $request->validate([
            'damage_description' => 'required|string|min:10',
            'estimated_cost' => 'nullable|numeric|min:0',
            'customer_liability_status' => 'required|string|in:' . implode(',', array_map(fn($case) => $case->value, CustomerLiabilityStatus::cases())),
            'admin_decision' => 'nullable|string',
            'status' => 'required|string|in:' . implode(',', array_map(fn($case) => $case->value, DamageReportStatus::cases())),
        ]);

        $damageReport->update($validated);

        return redirect()->route('admin.damageReports.show', $damageReport)
            ->with('success', 'Damage report updated successfully.');
    }

    /**
     * Resolve a damage report.
     */
    public function resolve(Request $request, DamageReport $damageReport)
    {
        $validated = $request->validate([
            'customer_liability_status' => 'required|string|in:' . implode(',', array_map(fn($case) => $case->value, CustomerLiabilityStatus::cases())),
        ]);

        $damageReport->update([
            'status' => DamageReportStatus::RESOLVED,
            'customer_liability_status' => $validated['customer_liability_status'],
        ]);

        return redirect()->route('admin.damageReports.show', $damageReport)
            ->with('success', 'Damage report resolved.');
    }

    /**
     * Reject a damage report.
     */
    public function reject(Request $request, DamageReport $damageReport)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|min:5',
        ]);

        $damageReport->update([
            'status' => DamageReportStatus::REJECTED,
            'admin_decision' => $validated['reason'] ?? 'Report rejected',
        ]);

        return redirect()->route('admin.damageReports.show', $damageReport)
            ->with('success', 'Damage report rejected.');
    }
}
