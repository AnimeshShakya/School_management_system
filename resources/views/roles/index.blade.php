@extends('layouts.master')

@section('title') {{__('role_management')}} @endsection

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
             {{__('role_management')}}
        </h3>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        {{ __('list') . ' ' . __('roles') }}
                    </h4>
                    <table aria-describedby="mydesc" class='table' id='table_list'
                    data-toggle="table" data-url="{{route('roles.data')}}" data-click-to-select="true" data-search="true"
                    data-side-pagination="server" data-pagination="true"
                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                    data-toolbar="#toolbar" data-show-columns="true"
                    data-show-refresh="true" data-fixed-columns="true"
                    data-trim-on-search="false" data-mobile-responsive="true"
                    data-sort-name="id" data-sort-order="desc"
                    data-maintain-selected="true" data-export-types='["txt","excel"]'
                    data-export-options='{ "fileName": "role-list-<?= date('d-m-y') ?>" ,"ignoreColumn": ["operate"]}' data-escape="true">
                    <thead>
                        <tr>
                            <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                            <th scope="col" data-field="no" data-sortable="false">{{ __('no.') }}</th>
                            <th scope="col" data-field="name" data-sortable="false">{{__('name')}}</th>
                            @can('role-edit')
                                <th scope="col" data-escape="false" data-field="operate" data-sortable="false">{{__('action')}}</th>
                            @endcan

                        </tr>
                    </thead>
                </table>
                    {!! $roles->render() !!}
                </div>
            </div>
        </div>

        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        {{ __('manage') . ' ' . __('roles') }}
                    </h4>
                        {!! Form::open(['route' => 'roles.store', 'method' => 'POST','class' => 'pt-3']) !!}
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <label>{{ __('name') }}</label>
                                    {!! Form::text('name', null, ['placeholder' => __('name'), 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <label>{{ __('permission') }}</label>
                                @foreach ($permissionGroups as $groupName => $groupPermissions)
                                    <div class="card mt-3 border">
                                        <div class="px-3 pt-3 pb-2 d-flex justify-content-between align-items-center border-bottom">
                                            <h5 class="mb-0">{{ $groupName }}</h5>
                                            <div class="d-flex align-items-center" style="gap: 6px;">
                                                <button type="button" class="btn btn-sm btn-theme permission-group-toggle" data-group="permission-group-{{ $loop->index }}" data-select-label="Select All" data-clear-label="Clear All">
                                                    Select All
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light permission-group-clear" data-group="permission-group-{{ $loop->index }}">
                                                    Clear
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body py-3">
                                            <div class="row permission-group-{{ $loop->index }}">
                                                @foreach ($groupPermissions as $permissionItem)
                                                    @php
                                                        $parts = explode('-', $permissionItem->name);
                                                        $actionName = strtolower(end($parts));
                                                        $actionLabel = ucwords(str_replace('-', ' ', $actionName));
                                                    @endphp
                                                    <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                                                        <label class="permission-inline-option mb-0">
                                                            {!! Form::checkbox('permission[]', $permissionItem->id, in_array($permissionItem->id, old('permission', [])), ['class' => 'name permission-inline-checkbox', 'data-action' => $actionName]) !!}
                                                            <span>{{ $actionLabel }}</span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <button type="submit" class="btn btn-theme">{{ __('submit') }}</button>
                            </div>
                        {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const groupToggleButtons = document.querySelectorAll('.permission-group-toggle');
        const groupClearButtons = document.querySelectorAll('.permission-group-clear');

        groupToggleButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const groupClass = button.getAttribute('data-group');
                const checkboxes = document.querySelectorAll('.' + groupClass + ' input[type="checkbox"]');
                const allChecked = Array.from(checkboxes).every(function (checkbox) {
                    return checkbox.checked;
                });

                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = !allChecked;
                });

                button.textContent = allChecked ? button.dataset.selectLabel : button.dataset.clearLabel;
            });
        });

        groupClearButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const groupClass = button.getAttribute('data-group');
                const checkboxes = document.querySelectorAll('.' + groupClass + ' input[type="checkbox"]');

                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = false;
                });

                const toggleButton = document.querySelector('.permission-group-toggle[data-group="' + groupClass + '"]');
                if (toggleButton) {
                    toggleButton.textContent = toggleButton.dataset.selectLabel;
                }
            });
        });
    });
</script>

<style>
    .permission-inline-option {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .permission-inline-checkbox {
        -webkit-appearance: checkbox !important;
        appearance: checkbox !important;
        position: static !important;
        opacity: 1 !important;
        width: 16px !important;
        height: 16px !important;
        margin: 0 !important;
    }
</style>

@endsection
