@extends('layouts.master')

@section('title')
    {{ __('manage') . ' ' . __('role') }}
@endsection

@section('content')
    @php
        $permissionGroups = collect($permission)
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
                {{ __('manage') . ' ' . __('role') }}
            </h3>
            <a class="btn btn-sm btn-theme" href="{{ route('roles.index') }}">{{ __('back') }}</a>
        </div>
        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('roles.update', $role->id) }}">
                            @csrf
                            @method('PATCH')
                            <div class="form-group">
                                <label><strong>{{ __('name') }}:</strong></label>
                                <input type="text" name="name" value="{{ old('name', $role->name) }}" placeholder="{{ __('name') }}" class="form-control">
                            </div>

                            <label><strong>{{ __('permission') }}:</strong></label>
                            @foreach ($permissionGroups as $groupName => $groupPermissions)
                                <div class="card mt-3 border">
                                    <div class="card-header py-3">
                                        <h5 class="mb-0">{{ $groupName }}</h5>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="row">
                                            @foreach ($groupPermissions as $permissionItem)
                                                @php
                                                    $parts = explode('-', $permissionItem->name);
                                                    $actionLabel = ucwords(str_replace('-', ' ', end($parts)));
                                                @endphp
                                                <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                                                    <div class="form-check">
                                                        <label class="form-check-label d-flex align-items-center">
                                                            <input
                                                                type="checkbox"
                                                                name="permission[]"
                                                                value="{{ $permissionItem->id }}"
                                                                class="name form-check-input"
                                                                {{ in_array($permissionItem->id, old('permission', $rolePermissions)) ? 'checked' : '' }}>
                                                            <span>{{ $actionLabel }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <input type="submit" class="btn btn-theme mt-3" value="{{ __('submit') }}">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
