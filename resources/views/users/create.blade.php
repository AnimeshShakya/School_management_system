@extends('layouts.master')

@section('title')
    {{ __('create') . ' ' . __('user') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">{{ __('create') . ' ' . __('user') }}</h3>
            <a class="btn btn-sm btn-theme" href="{{ route('users.index') }}">{{ __('back') }}</a>
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

                        <form action="{{ route('users.store') }}" method="POST" class="pt-3">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('first_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" value="{{ old('first_name') }}" class="form-control" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('last_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" value="{{ old('last_name') }}" class="form-control" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('email') }} <span class="text-danger">*</span></label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('role') }} <span class="text-danger">*</span></label>
                                    <select name="roles[]" class="form-control" multiple required>
                                        @foreach ($roles as $roleValue => $roleLabel)
                                            <option value="{{ $roleValue }}" {{ in_array($roleValue, old('roles', []), true) ? 'selected' : '' }}>
                                                {{ $roleLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('password') }} <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('confirm') . ' ' . __('password') }} <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-theme">{{ __('submit') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection