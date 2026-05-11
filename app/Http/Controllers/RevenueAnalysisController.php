<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FeesPaid;
use App\Models\Students;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RevenueAnalysisController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        if (! Auth::user()->hasRole('Super Admin')) {
            return redirect(route('home'))->withErrors(['message' => trans('no_permission_message')]);
        }

        $currencySettings = getSettings('currency_symbol');
        $currencySymbol = $currencySettings['currency_symbol'] ?? '';

        $currentYear = (int) now()->year;
        $currentMonth = (int) now()->month;

        $baseQuery = FeesPaid::query()
            ->join('students', 'students.id', '=', 'fees_paids.student_id')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->whereNull('fees_paids.deleted_at')
            ->where('fees_paids.total_amount', '>', 0);

        $summary = [
            'total_revenue' => (float) (clone $baseQuery)->sum('fees_paids.total_amount'),
            'current_year' => (float) (clone $baseQuery)->whereYear('fees_paids.date', $currentYear)->sum('fees_paids.total_amount'),
            'current_month' => (float) (clone $baseQuery)->whereYear('fees_paids.date', $currentYear)->whereMonth('fees_paids.date', $currentMonth)->sum('fees_paids.total_amount'),
            'total_registrations' => (int) (clone $baseQuery)->count(),
            'fully_paid' => (int) (clone $baseQuery)->where('students.registration_payment_status', 1)->count(),
            'pending_payment' => (int) (clone $baseQuery)->where('students.registration_payment_status', 0)->count(),
            'with_qr' => (int) Students::whereNotNull('qr_token')->count(),
            'without_qr' => (int) Students::whereNull('qr_token')->count(),
        ];

        $summary['avg_per_student'] = $summary['total_registrations'] > 0
            ? round($summary['total_revenue'] / $summary['total_registrations'], 2)
            : 0.0;

        $monthlyRaw = (clone $baseQuery)
            ->whereYear('fees_paids.date', $currentYear)
            ->selectRaw('MONTH(fees_paids.date) as month_number, ROUND(SUM(fees_paids.total_amount), 2) as total_amount, COUNT(*) as registrations')
            ->groupBy('month_number')
            ->get()
            ->keyBy('month_number');

        $monthlyRevenue = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyRevenue[] = [
                'month' => now()->setMonth($m)->format('M'),
                'total_amount' => (float) ($monthlyRaw[$m]->total_amount ?? 0),
                'registrations' => (int) ($monthlyRaw[$m]->registrations ?? 0),
            ];
        }

        $yearlyRevenue = (clone $baseQuery)
            ->selectRaw('YEAR(fees_paids.date) as year, ROUND(SUM(fees_paids.total_amount), 2) as total_amount, COUNT(*) as registrations, SUM(CASE WHEN students.registration_payment_status = 1 THEN 1 ELSE 0 END) as fully_paid')
            ->groupBy('year')
            ->orderByDesc('year')
            ->get();

        $paymentModes = (clone $baseQuery)
            ->selectRaw('fees_paids.mode, COUNT(*) as count, ROUND(SUM(fees_paids.total_amount), 2) as total_amount')
            ->groupBy('fees_paids.mode')
            ->get()
            ->map(fn ($row) => [
                'label' => match ((int) $row->mode) {
                    1 => 'Cash',
                    2 => 'Online',
                    3 => 'Cheque',
                    default => 'Other',
                },
                'count' => (int) $row->count,
                'total_amount' => (float) $row->total_amount,
            ]);

        $recentRegistrations = FeesPaid::query()
            ->join('students', 'students.id', '=', 'fees_paids.student_id')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->leftJoin('student_sessions', 'student_sessions.student_id', '=', 'fees_paids.student_id')
            ->leftJoin('class_sections', 'class_sections.id', '=', 'student_sessions.class_section_id')
            ->leftJoin('classes', 'classes.id', '=', 'class_sections.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'class_sections.section_id')
            ->whereNull('fees_paids.deleted_at')
            ->where('fees_paids.total_amount', '>', 0)
            ->select(
                'fees_paids.id',
                'fees_paids.date as payment_date',
                'fees_paids.total_amount',
                'students.registration_payment_status',
                'fees_paids.mode',
                'users.first_name',
                'users.last_name',
                'users.email',
                'students.admission_no',
                'students.qr_token',
                DB::raw("CONCAT(COALESCE(classes.name,''), IF(sections.name IS NOT NULL, CONCAT('-', sections.name), '')) as class_section")
            )
            ->orderByDesc('fees_paids.date')
            ->orderByDesc('fees_paids.id')
            ->get();

        return response()->view('revenue.analysis', compact(
            'summary',
            'monthlyRevenue',
            'yearlyRevenue',
            'paymentModes',
            'recentRegistrations',
            'currencySymbol',
            'currentYear',
            'currentMonth'
        ));
    }
}
