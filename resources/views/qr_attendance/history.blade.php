@extends('layouts.master')

@section('title')
    {{ __('QR Attendance History') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="fa fa-history"></i>
                </span>
                {{ __('QR Attendance History') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title mb-0">
                                {{ __('Scans for') }}: <strong>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</strong>
                            </h4>
                            <a href="{{ route('qr-attendance.index') }}" class="btn btn-gradient-primary btn-sm">
                                <i class="fa fa-qrcode"></i> {{ __('Back to Scanner') }}
                            </a>
                        </div>

                        {{-- Date filter --}}
                        <form method="GET" action="{{ route('qr-attendance.history') }}" class="mb-4">
                            <div class="input-group" style="max-width: 280px;">
                                <input type="date" name="date" class="form-control" value="{{ $date }}"
                                    max="{{ now()->toDateString() }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-gradient-info">
                                        <i class="fa fa-filter"></i> {{ __('Filter') }}
                                    </button>
                                </div>
                            </div>
                        </form>

                        @if ($logs->isEmpty())
                            <div class="text-center text-muted py-5">
                                <i class="fa fa-inbox fa-3x mb-3 d-block"></i>
                                <p>{{ __('No QR attendance scans recorded for this date.') }}</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Student') }}</th>
                                            <th>{{ __('Admission No') }}</th>
                                            <th>{{ __('class') }} {{ __('section') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Scanned At') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($logs as $index => $log)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    @if ($log->student->user->image)
                                                        <img src="{{ asset('storage/' . $log->student->user->getRawOriginal('image')) }}"
                                                            class="rounded-circle me-2" width="30" height="30" alt="">
                                                    @endif
                                                    {{ $log->student->user->full_name }}
                                                </td>
                                                <td>{{ $log->student->admission_no }}</td>
                                                <td>
                                                    {{ optional($log->classSection->class)->name }}
                                                    -
                                                    {{ optional($log->classSection->section)->name }}
                                                </td>
                                                <td>
                                                    @if ($log->status === 'present')
                                                        <span class="badge badge-success">{{ __('Present') }}</span>
                                                    @elseif ($log->status === 'holiday')
                                                        <span class="badge badge-warning">{{ __('Holiday') }}</span>
                                                    @else
                                                        <span class="badge badge-danger">{{ __('Absent') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $log->created_at->format('h:i A') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <p class="text-muted small mt-2">
                                {{ __('Total') }}: {{ $logs->count() }} {{ __('scans') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
