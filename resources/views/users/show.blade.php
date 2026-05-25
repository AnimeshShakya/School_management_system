@extends('layouts.master')

@section('title')
    {{ __('show') . ' ' . __('user') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">{{ __('show') . ' ' . __('user') }}</h3>
            <a class="btn btn-sm btn-theme" href="{{ route('users.index') }}">{{ __('back') }}</a>
        </div>

        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">{{ __('name') }}</label>
                                <div>{{ trim($user->first_name . ' ' . $user->last_name) }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">{{ __('email') }}</label>
                                <div>{{ $user->email }}</div>
                            </div>
                            @if($user->school)
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">{{ __('school') }}</label>
                                <div>{{ $user->school->name }}</div>
                            </div>
                            @endif
                            <div class="col-md-12">
                                <label class="font-weight-bold">{{ __('role') }}</label>
                                <div>
                                    @forelse ($user->getRoleNames() as $roleName)
                                        <span class="badge badge-success mr-1">{{ $roleName }}</span>
                                    @empty
                                        <span class="badge badge-secondary">N/A</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection