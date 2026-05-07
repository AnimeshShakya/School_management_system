@extends('layouts.master')

@section('title') {{__('show_role')}} @endsection

@section('content')
    @php
        $groupedPermissions = collect($rolePermissions)
            ->groupBy(function ($item) {
                $parts = explode('-', $item->name);
                if (count($parts) === 1) {
                    return ucwords(str_replace('-', ' ', $item->name));
                }

                array_pop($parts);

                return ucwords(str_replace('-', ' ', implode(' ', $parts)));
            })
            ->sortKeys();
    @endphp

<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
            {{__('show_role')}}
        </h3>
        <a class="btn btn-sm btn-theme" href="{{ route('roles.index') }}">{{__('back')}}</a>
    </div>
    <div class="row grid-margin">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>{{__('name')}}:</strong>
                                {{ $role->name }}
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            @if($groupedPermissions->isNotEmpty())
                                @foreach($groupedPermissions as $groupName => $permissions)
                                    <div class="card mt-3 border">
                                        <div class="card-header py-3">
                                            <h5 class="mb-0">{{ $groupName }}</h5>
                                        </div>
                                        <div class="card-body py-3">
                                            <div class="row">
                                                @foreach($permissions as $permission)
                                                    @php
                                                        $parts = explode('-', $permission->name);
                                                        $actionLabel = ucwords(str_replace('-', ' ', end($parts)));
                                                    @endphp
                                                    <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                                                        <div class="form-check">
                                                            <label class="form-check-label d-flex align-items-center">
                                                                <input type="checkbox" class="form-check-input" checked disabled>
                                                                <span>{{ $actionLabel }}</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-warning mt-3 mb-0">No permissions assigned to this role.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
