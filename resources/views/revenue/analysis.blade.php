@extends('layouts.master')

@section('title')
    Revenue Analysis
@endsection

@section('content')
<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center">
        <h3 class="page-title">
            <span class="page-title-icon bg-theme text-white mr-2">
                <i class="fa fa-bar-chart"></i>
            </span>
            Revenue Analysis
        </h3>
        <span class="text-muted small">
            <i class="fa fa-calendar mr-1"></i>
            {{ now()->format('F Y') }}
        </span>
    </div>

    {{-- ===== KPI CARDS ROW 1 ===== --}}
    <div class="row">
        <div class="col-xl-3 col-md-6 stretch-card grid-margin">
            <div class="card bg-gradient-primary card-img-holder text-white">
                <div class="card-body">
                    <img src="{{ asset(config('global.CIRCLE_SVG')) }}" class="card-img-absolute" alt="circle">
                    <h6 class="font-weight-normal mb-2 text-white-50">Total Revenue (All Time)</h6>
                    <h3 class="mb-1 font-weight-bold">{{ $currencySymbol }} {{ number_format($summary['total_revenue'], 2) }}</h3>
                    <p class="mb-0 small">
                        <i class="fa fa-users mr-1"></i>
                        {{ $summary['total_registrations'] }} registration{{ $summary['total_registrations'] !== 1 ? 's' : '' }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 stretch-card grid-margin">
            <div class="card bg-gradient-success card-img-holder text-white">
                <div class="card-body">
                    <img src="{{ asset(config('global.CIRCLE_SVG')) }}" class="card-img-absolute" alt="circle">
                    <h6 class="font-weight-normal mb-2 text-white-50">This Year ({{ $currentYear }})</h6>
                    <h3 class="mb-1 font-weight-bold">{{ $currencySymbol }} {{ number_format($summary['current_year'], 2) }}</h3>
                    <p class="mb-0 small">
                        <i class="fa fa-line-chart mr-1"></i>
                        {{ number_format($summary['current_year'] > 0 && $summary['total_revenue'] > 0 ? ($summary['current_year'] / $summary['total_revenue']) * 100 : 0, 1) }}% of total
                    </p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 stretch-card grid-margin">
            <div class="card bg-gradient-info card-img-holder text-white">
                <div class="card-body">
                    <img src="{{ asset(config('global.CIRCLE_SVG')) }}" class="card-img-absolute" alt="circle">
                    <h6 class="font-weight-normal mb-2 text-white-50">This Month ({{ now()->format('M Y') }})</h6>
                    <h3 class="mb-1 font-weight-bold">{{ $currencySymbol }} {{ number_format($summary['current_month'], 2) }}</h3>
                    <p class="mb-0 small">
                        <i class="fa fa-credit-card mr-1"></i>
                        Avg {{ $currencySymbol }} {{ number_format($summary['avg_per_student'], 2) }} / student
                    </p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 stretch-card grid-margin">
            <div class="card bg-gradient-warning card-img-holder text-white">
                <div class="card-body">
                    <img src="{{ asset(config('global.CIRCLE_SVG')) }}" class="card-img-absolute" alt="circle">
                    <h6 class="font-weight-normal mb-2 text-white-50">QR Codes Generated</h6>
                    <h3 class="mb-1 font-weight-bold">{{ $summary['with_qr'] }}</h3>
                    <p class="mb-0 small">
                        <i class="fa fa-qrcode mr-1"></i>
                        {{ $summary['without_qr'] }} student{{ $summary['without_qr'] !== 1 ? 's' : '' }} pending QR
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== KPI CARDS ROW 2 ===== --}}
    <div class="row">
        <div class="col-xl-4 col-md-6 stretch-card grid-margin">
            <div class="card border-left-success">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-success text-white mr-3" style="width:50px;height:50px;display:flex;align-items:center;justify-content:center;">
                        <i class="fa fa-check-circle fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Fully Paid</p>
                        <h4 class="mb-0 font-weight-bold text-success">{{ $summary['fully_paid'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 stretch-card grid-margin">
            <div class="card border-left-danger">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-danger text-white mr-3" style="width:50px;height:50px;display:flex;align-items:center;justify-content:center;">
                        <i class="fa fa-clock-o fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Pending Payment</p>
                        <h4 class="mb-0 font-weight-bold text-danger">{{ $summary['pending_payment'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-12 stretch-card grid-margin">
            <div class="card border-left-primary">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-primary text-white mr-3" style="width:50px;height:50px;display:flex;align-items:center;justify-content:center;">
                        <i class="fa fa-dollar fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Avg Revenue / Student</p>
                        <h4 class="mb-0 font-weight-bold text-primary">{{ $currencySymbol }} {{ number_format($summary['avg_per_student'], 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== CHARTS ROW ===== --}}
    <div class="row">
        {{-- Monthly Bar Chart --}}
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h4 class="card-title mb-0">Monthly Registration Revenue</h4>
                            <p class="text-muted small mb-0">{{ $currentYear }} — fee collected per month</p>
                        </div>
                        <span class="badge badge-primary px-2 py-1">{{ $currentYear }}</span>
                    </div>
                    <canvas id="monthlyRevenueChart" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- Payment Mode Donut --}}
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-1">Payment Methods</h4>
                    <p class="text-muted small mb-3">Breakdown by payment mode</p>
                    @if ($paymentModes->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fa fa-pie-chart fa-3x mb-3 d-block"></i>
                            No payment data yet
                        </div>
                    @else
                        <canvas id="paymentModeChart" height="180"></canvas>
                        <div class="mt-3">
                            @foreach ($paymentModes as $mode)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small">{{ $mode['label'] }}</span>
                                    <span class="font-weight-bold small">
                                        {{ $currencySymbol }} {{ number_format($mode['total_amount'], 2) }}
                                        <span class="text-muted">({{ $mode['count'] }})</span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===== REGISTRATIONS TABLE + YEARLY SUMMARY ===== --}}
    <div class="row">
        {{-- Registrations Table --}}
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="card-title mb-0">Student Registration Payments</h4>
                            <p class="text-muted small mb-0">All ID card + QR code registrations</p>
                        </div>
                        <input type="text" id="regSearch" class="form-control form-control-sm w-auto"
                               placeholder="Search student..." style="min-width:180px">
                    </div>
                    <div class="table-responsive" style="max-height:420px;overflow-y:auto;">
                        <table class="table table-hover table-sm" id="regTable">
                            <thead class="thead-light" style="position:sticky;top:0;">
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Amount</th>
                                    <th>Mode</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>QR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentRegistrations as $i => $reg)
                                    <tr>
                                        <td class="text-muted small">{{ $i + 1 }}</td>
                                        <td>
                                            <div class="font-weight-bold small">{{ $reg->first_name }} {{ $reg->last_name }}</div>
                                            <div class="text-muted" style="font-size:0.75rem;">{{ $reg->admission_no ?? $reg->email }}</div>
                                        </td>
                                        <td class="small">{{ $reg->class_section ?: '—' }}</td>
                                        <td class="font-weight-bold small text-success">
                                            {{ $currencySymbol }} {{ number_format((float) $reg->total_amount, 2) }}
                                        </td>
                                        <td>
                                            @if ((int) $reg->mode === 2)
                                                <span class="badge badge-info" style="font-size:0.7rem;">Online</span>
                                            @elseif ((int) $reg->mode === 3)
                                                <span class="badge badge-secondary" style="font-size:0.7rem;">Cheque</span>
                                            @else
                                                <span class="badge badge-light border" style="font-size:0.7rem;">Cash</span>
                                            @endif
                                        </td>
                                        <td class="small text-nowrap">{{ $reg->payment_date ?? '—' }}</td>
                                        <td>
                                            @if ($reg->registration_payment_status)
                                                <span class="badge badge-success" style="font-size:0.7rem;">Paid</span>
                                            @else
                                                <span class="badge badge-danger" style="font-size:0.7rem;">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (!empty($reg->qr_token))
                                                <span title="QR Generated" class="text-success"><i class="fa fa-qrcode"></i></span>
                                            @else
                                                <span title="No QR" class="text-muted"><i class="fa fa-minus"></i></span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x d-block mb-2"></i>
                                            No registration payments recorded yet
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Yearly Summary --}}
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-1">Year-over-Year</h4>
                    <p class="text-muted small mb-3">Annual revenue summary</p>
                    @if ($yearlyRevenue->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fa fa-calendar fa-3x mb-3 d-block"></i>
                            No yearly data yet
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Year</th>
                                        <th class="text-right">Revenue</th>
                                        <th class="text-center">Reg.</th>
                                        <th class="text-center">Paid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($yearlyRevenue as $yr)
                                        <tr class="{{ (int) $yr->year === $currentYear ? 'table-active font-weight-bold' : '' }}">
                                            <td>
                                                {{ $yr->year }}
                                                @if ((int) $yr->year === $currentYear)
                                                    <span class="badge badge-primary ml-1" style="font-size:0.65rem;">Current</span>
                                                @endif
                                            </td>
                                            <td class="text-right text-success">{{ $currencySymbol }} {{ number_format((float) $yr->total_amount, 2) }}</td>
                                            <td class="text-center">{{ $yr->registrations }}</td>
                                            <td class="text-center">{{ $yr->fully_paid }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold bg-light">
                                        <td>Total</td>
                                        <td class="text-right text-primary">{{ $currencySymbol }} {{ number_format($summary['total_revenue'], 2) }}</td>
                                        <td class="text-center">{{ $summary['total_registrations'] }}</td>
                                        <td class="text-center">{{ $summary['fully_paid'] }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif

                    {{-- Business model note --}}
                    <div class="mt-3 p-3 rounded" style="background:#f8f9ff;border:1px solid #e3e6f0;">
                        <p class="mb-1 small font-weight-bold text-primary">
                            <i class="fa fa-info-circle mr-1"></i> Revenue Model
                        </p>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            Revenue is earned each time a school registers a student and generates their
                            ID card with a unique QR code for attendance tracking.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Monthly Bar Chart ──────────────────────────────
    var monthLabels  = @json(collect($monthlyRevenue)->pluck('month'));
    var monthAmounts = @json(collect($monthlyRevenue)->pluck('total_amount'));
    var monthRegs    = @json(collect($monthlyRevenue)->pluck('registrations'));

    var barCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [
                {
                    label: 'Revenue ({{ $currencySymbol }})',
                    data: monthAmounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y-revenue',
                },
                {
                    label: 'Registrations',
                    data: monthRegs,
                    type: 'line',
                    fill: false,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderWidth: 2,
                    pointRadius: 4,
                    yAxisID: 'y-regs',
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                yAxes: [
                    {
                        id: 'y-revenue',
                        type: 'linear',
                        position: 'left',
                        ticks: { beginAtZero: true },
                        gridLines: { drawOnChartArea: true }
                    },
                    {
                        id: 'y-regs',
                        type: 'linear',
                        position: 'right',
                        ticks: { beginAtZero: true, stepSize: 1 },
                        gridLines: { drawOnChartArea: false }
                    }
                ]
            },
            legend: { position: 'bottom' },
            tooltips: {
                mode: 'index',
                intersect: false
            }
        }
    });

    @if (!$paymentModes->isEmpty())
    // ── Payment Mode Donut ────────────────────────────
    var modeLabels  = @json($paymentModes->pluck('label'));
    var modeAmounts = @json($paymentModes->pluck('total_amount'));

    var pieCtx = document.getElementById('paymentModeChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: modeLabels,
            datasets: [{
                data: modeAmounts,
                backgroundColor: ['#36a2eb', '#ff6384', '#ffce56', '#4bc0c0'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            cutoutPercentage: 65,
            legend: { display: false }
        }
    });
    @endif

    // ── Registration table search ─────────────────────
    document.getElementById('regSearch').addEventListener('keyup', function () {
        var filter = this.value.toLowerCase();
        var rows = document.querySelectorAll('#regTable tbody tr');
        rows.forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    });
});
</script>
@endsection
