<?php

namespace App\Http\Controllers\Admin;

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
     * Display a listing of disputes.
     */
    public function index(Request $request): Response
    {
        $status = $request->input('status');

        $statusCounts = Dispute::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $disputes = Dispute::query()
            ->with([
                'damageReport:id,car_id,reservation_id,damage_description,estimated_cost',
                'damageReport.car:id,make,model',
                'reservation:id,reservation_number',
                'customer:id,name,email',
            ])
            ->when($request->string('search')->toString(), function ($query, $search) {
                $search = $request->string('search')->toString();
                $query->where(function ($q) use ($search) {
                    $q->where('customer_statement', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($cq) use ($search) {
                            $cq->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('reservation', function ($rq) use ($search) {
                            $rq->where('reservation_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status && $status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $statuses = collect(DisputeStatus::cases())
            ->mapWithKeys(fn($case) => [$case->value => ['label' => ucfirst(str_replace('_', ' ', $case->value)), 'count' => $statusCounts[$case->value] ?? 0]])
            ->toArray();

        return Inertia::render('Admin/Disputes/Index', [
            'disputes' => $disputes,
            'statuses' => $statuses,
            'filters' => ['search' => $request->string('search')->toString(), 'status' => $status],
        ]);
    }

    /**
     * Display the specified dispute.
     */
    public function show(Dispute $dispute): Response
    {
        $dispute->load([
            'damageReport:id,car_id,reservation_id,damage_description,estimated_cost,customer_liability_status,status',
            'damageReport.car:id,make,model,year',
            'damageReport.pickupInspection:id,mileage,fuel_level,overall_condition',
            'damageReport.pickupInspection.evidence',
            'damageReport.returnInspection:id,mileage,fuel_level,overall_condition',
            'damageReport.returnInspection.evidence',
            'reservation:id,reservation_number,user_id,start_date,end_date',
            'reservation.user:id,name,email',
            'customer:id,name,email',
            'admin:id,name',
        ]);

        return Inertia::render('Admin/Disputes/Show', [
            'dispute' => $dispute,
            'disputeStatuses' => collect(DisputeStatus::cases())
                ->mapWithKeys(fn($case) => [$case->value => ucfirst(str_replace('_', ' ', $case->value))])
                ->toArray(),
            'customerLiabilityStatuses' => collect(CustomerLiabilityStatus::cases())
                ->mapWithKeys(fn($case) => [$case->value => ucfirst(str_replace('_', ' ', $case->value))])
                ->toArray(),
        ]);
    }

    /**
     * Respond to a dispute.
     */
    public function respond(Request $request, Dispute $dispute)
    {
        $validated = $request->validate([
            'admin_response' => 'required|string|min:10',
            'status' => 'required|string|in:' . implode(',', array_map(fn($case) => $case->value, DisputeStatus::cases())),
        ]);

        $dispute->update([
            'admin_response' => $validated['admin_response'],
            'status' => $validated['status'],
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.disputes.show', $dispute)
            ->with('success', 'Response added to dispute.');
    }

    /**
     * Resolve a dispute.
     */
    public function resolve(Request $request, Dispute $dispute)
    {
        $validated = $request->validate([
            'admin_response' => 'required|string|min:10',
            'customer_liability_status' => 'required|string|in:' . implode(',', array_map(fn($case) => $case->value, CustomerLiabilityStatus::cases())),
        ]);

        $dispute->update([
            'admin_response' => $validated['admin_response'],
            'status' => DisputeStatus::RESOLVED,
            'admin_id' => auth()->id(),
        ]);

        $dispute->damageReport->update([
            'status' => DamageReportStatus::RESOLVED,
            'customer_liability_status' => $validated['customer_liability_status'],
        ]);

        return redirect()->route('admin.disputes.show', $dispute)
            ->with('success', 'Dispute resolved.');
    }

    /**
     * Reject a dispute.
     */
    public function reject(Request $request, Dispute $dispute)
    {
        $validated = $request->validate([
            'admin_response' => 'required|string|min:10',
        ]);

        $dispute->update([
            'admin_response' => $validated['admin_response'],
            'status' => DisputeStatus::REJECTED,
            'admin_id' => auth()->id(),
        ]);

        $dispute->damageReport->update([
            'status' => DamageReportStatus::RESOLVED,
            'customer_liability_status' => CustomerLiabilityStatus::ACCEPTED,
        ]);

        return redirect()->route('admin.disputes.show', $dispute)
            ->with('success', 'Dispute rejected.');
    }
}
