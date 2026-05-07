<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FeesChoiceable;
use App\Models\FeesPaid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RevenueAnalysisController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        if (! Auth::user()->hasRole('Super Admin')) {
            $response = [
                'message' => trans('no_permission_message'),
            ];

            return redirect(route('home'))->withErrors($response);
        }

        $currencySettings = getSettings('currency_symbol');
        $currencySymbol = $currencySettings['currency_symbol'] ?? '';

        $baseQuery = FeesChoiceable::query()
            ->join('fees_types', 'fees_types.id', '=', 'fees_choiceables.fees_type_id')
            ->where('fees_choiceables.status', 1)
            ->whereNotNull('fees_choiceables.date')
            ->where(function ($query) {
                $query->where('fees_types.name', 'like', '%id%')
                    ->where('fees_types.name', 'like', '%card%');
            });

        $currentYear = (int) now()->year;
        $currentMonth = (int) now()->month;

        $monthlyRevenueRaw = (clone $baseQuery)
            ->whereYear('fees_choiceables.date', $currentYear)
            ->selectRaw('MONTH(fees_choiceables.date) as month_number, ROUND(SUM(fees_choiceables.total_amount), 2) as total_amount')
            ->groupBy('month_number')
            ->pluck('total_amount', 'month_number');

        $monthlyRevenue = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyRevenue[] = [
                'month' => now()->setMonth($month)->format('F'),
                'total_amount' => (float) ($monthlyRevenueRaw[$month] ?? 0),
            ];
        }

        $yearlyRevenue = (clone $baseQuery)
            ->selectRaw('YEAR(fees_choiceables.date) as year, ROUND(SUM(fees_choiceables.total_amount), 2) as total_amount')
            ->groupBy('year')
            ->orderByDesc('year')
            ->get();

        $registrationLoginQuery = FeesPaid::query()
            ->join('students', 'students.id', '=', 'fees_paids.student_id')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->where('users.status', 1)
            ->where('fees_paids.total_amount', '>', 0)
            ->whereNotNull('fees_paids.date');

        $yearlyRegistrationRevenue = (clone $registrationLoginQuery)
            ->selectRaw('YEAR(fees_paids.date) as year, ROUND(SUM(fees_paids.total_amount), 2) as total_amount')
            ->groupBy('year')
            ->orderByDesc('year')
            ->get();

        $summary = [
            'current_month' => (float) ((clone $baseQuery)
                ->whereYear('fees_choiceables.date', $currentYear)
                ->whereMonth('fees_choiceables.date', $currentMonth)
                ->sum('fees_choiceables.total_amount')),
            'current_year' => (float) ((clone $baseQuery)
                ->whereYear('fees_choiceables.date', $currentYear)
                ->sum('fees_choiceables.total_amount')),
            'overall' => (float) ((clone $baseQuery)
                ->sum('fees_choiceables.total_amount')),
            'registration_login_access' => (float) ((clone $registrationLoginQuery)
                ->sum('fees_paids.total_amount')),
        ];

        return response()->view('revenue.analysis', compact('monthlyRevenue', 'yearlyRevenue', 'yearlyRegistrationRevenue', 'summary', 'currencySymbol', 'currentYear'));
    }
}
