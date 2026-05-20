<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DamageReport;
use App\Enums\DisputeStatus;
use App\Enums\DamageReportStatus;
use App\Enums\CustomerLiabilityStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DisputeController extends Controller
{
    /**
     * Show the form for creating a dispute.
     */
    public function create(DamageReport $damageReport): Response
    {
        if ($damageReport->reservation->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to dispute this report.');
        }

        if ($damageReport->status === DamageReportStatus::REJECTED) {
            abort(403, 'Cannot dispute a rejected damage report.');
        }

        $existingDispute = Dispute::where('damage_report_id', $damageReport->id)
            ->where('customer_id', auth()->id())
            ->whereIn('status', [DisputeStatus::SUBMITTED, DisputeStatus::REVIEWING])
            ->first();

        if ($existingDispute) {
            abort(403, 'An active dispute already exists for this damage report.');
        }

        $damageReport->load([
            'car:id,make,model,year',
            'reservation:id,reservation_number,start_date,end_date',
            'pickupInspection:id,mileage,fuel_level,overall_condition',
            'pickupInspection.evidence',
            'returnInspection:id,mileage,fuel_level,overall_condition',
            'returnInspection.evidence',
        ]);

        return Inertia::render('Client/Disputes/Create', [
            'damageReport' => $damageReport,
        ]);
    }

    /**
     * Store a newly created dispute.
     */
    public function store(Request $request, DamageReport $damageReport)
    {
        if ($damageReport->reservation->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to dispute this report.');
        }

        if ($damageReport->status === DamageReportStatus::REJECTED) {
            abort(403, 'Cannot dispute a rejected damage report.');
        }

        $existingDispute = Dispute::where('damage_report_id', $damageReport->id)
            ->where('customer_id', auth()->id())
            ->whereIn('status', [DisputeStatus::SUBMITTED, DisputeStatus::REVIEWING])
            ->first();

        if ($existingDispute) {
            abort(403, 'An active dispute already exists for this damage report.');
        }

        $validated = $request->validate([
            'customer_statement' => 'required|string|min:10',
        ]);

        $dispute = Dispute::create([
            'damage_report_id' => $damageReport->id,
            'reservation_id' => $damageReport->reservation_id,
            'customer_id' => auth()->id(),
            'customer_statement' => $validated['customer_statement'],
            'status' => DisputeStatus::SUBMITTED,
        ]);

        $damageReport->update([
            'status' => DamageReportStatus::UNDER_REVIEW,
            'customer_liability_status' => CustomerLiabilityStatus::DISPUTED,
        ]);

        return redirect()->route('client.disputes.show', $dispute)
            ->with('success', 'Dispute submitted successfully. We will review your statement and respond within 5 business days.');
    }

    /**
     * Display the specified dispute.
     */
    public function show(Dispute $dispute): Response
    {
        if ($dispute->customer_id !== auth()->id()) {
            abort(403, 'You do not have permission to view this dispute.');
        }

        $dispute->load([
            'damageReport:id,car_id,reservation_id,damage_description,estimated_cost,customer_liability_status,status',
            'damageReport.car:id,make,model,year',
            'damageReport.pickupInspection:id,mileage,fuel_level,overall_condition',
            'damageReport.pickupInspection.evidence',
            'damageReport.returnInspection:id,mileage,fuel_level,overall_condition',
            'damageReport.returnInspection.evidence',
            'reservation:id,reservation_number,start_date,end_date',
            'admin:id,name',
        ]);

        return Inertia::render('Client/Disputes/Show', [
            'dispute' => $dispute,
            'disputeStatuses' => collect(DisputeStatus::cases())
                ->mapWithKeys(fn($case) => [$case->value => ucfirst(str_replace('_', ' ', $case->value))])
                ->toArray(),
        ]);
    }
}
