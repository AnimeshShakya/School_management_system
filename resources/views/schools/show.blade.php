@extends('layouts.master')

@section('title')
    {{ __('show') . ' ' . __('school') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">{{ __('show') . ' ' . __('school') }}</h3>
            <a class="btn btn-sm btn-theme" href="{{ route('schools.index') }}">{{ __('back') }}</a>
        </div>

        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">{{ __('name') }}</label>
                                <div>{{ $school->name }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">{{ __('email') }}</label>
                                <div>{{ $school->email ?? '—' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">{{ __('phone') }}</label>
                                <div>{{ $school->phone ?? '—' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">{{ __('status') }}</label>
                                <div>
                                    @if($school->status)
                                        <span class="badge badge-success">{{ __('active') }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ __('inactive') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">{{ __('total_users') }}</label>
                                <div>{{ $school->users_count }}</div>
                            </div>
                            @if($school->address)
                            <div class="col-md-12 mb-3">
                                <label class="font-weight-bold">{{ __('address') }}</label>
                                <div>{{ $school->address }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
