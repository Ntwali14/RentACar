<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DamageReport;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DamageReportController extends Controller
{
    /**
     * Display a listing of damage reports for the current user.
     */
    public function index(): Response
    {
        $damageReports = DamageReport::query()
            ->whereHas('reservation', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->with([
                'car:id,make,model,year',
                'reservation:id,reservation_number,start_date,end_date',
                'disputes',
            ])
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('Client/DamageReports/Index', [
            'damageReports' => $damageReports,
        ]);
    }

    /**
     * Display the specified damage report.
     */
    public function show(DamageReport $damageReport): Response
    {
        if ($damageReport->reservation->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to view this damage report.');
        }

        $damageReport->load([
            'car:id,make,model,year',
            'reservation:id,reservation_number,user_id,start_date,end_date',
            'reservation.user:id,name',
            'pickupInspection:id,mileage,fuel_level,overall_condition,inspection_date',
            'pickupInspection.evidence',
            'returnInspection:id,mileage,fuel_level,overall_condition,inspection_date',
            'returnInspection.evidence',
            'reportedBy:id,name',
            'disputes:id,damage_report_id,customer_id,status,customer_statement,admin_response,created_at',
        ]);

        return Inertia::render('Client/DamageReports/Show', [
            'damageReport' => $damageReport,
        ]);
    }
}
