@extends('layouts.master')

@section('title')
    Revenue Analysis
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">Revenue Analysis</h3>
        </div>

        <div class="row">
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card bg-gradient-info card-img-holder text-white">
                    <div class="card-body">
                        <h4 class="font-weight-normal mb-2">Current Month</h4>
                        <h2 class="mb-0">{{ $currencySymbol }} {{ number_format($summary['current_month'], 2) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card bg-gradient-success card-img-holder text-white">
                    <div class="card-body">
                        <h4 class="font-weight-normal mb-2">Current Year</h4>
                        <h2 class="mb-0">{{ $currencySymbol }} {{ number_format($summary['current_year'], 2) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card bg-gradient-primary card-img-holder text-white">
                    <div class="card-body">
                        <h4 class="font-weight-normal mb-2">Overall ID Card Revenue</h4>
                        <h2 class="mb-0">{{ $currencySymbol }} {{ number_format($summary['overall'], 2) }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Monthly Revenue ({{ $currentYear }})</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th class="text-right">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($monthlyRevenue as $monthRevenue)
                                        <tr>
                                            <td>{{ $monthRevenue['month'] }}</td>
                                            <td class="text-right">{{ $currencySymbol }} {{ number_format($monthRevenue['total_amount'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center">No monthly revenue found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Yearly Revenue</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Year</th>
                                        <th class="text-right">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($yearlyRevenue as $yearRevenue)
                                        <tr>
                                            <td>{{ $yearRevenue->year }}</td>
                                            <td class="text-right">{{ $currencySymbol }} {{ number_format((float) $yearRevenue->total_amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center">No yearly revenue found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="mb-0 text-muted">
                            Revenue is calculated from paid optional fee entries where fee type name contains both "id" and "card".
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
