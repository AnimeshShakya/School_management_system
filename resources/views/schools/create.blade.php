@extends('layouts.master')

@section('title')
    {{ __('create') . ' ' . __('school') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">{{ __('create') . ' ' . __('school') }}</h3>
            <a class="btn btn-sm btn-theme" href="{{ route('schools.index') }}">{{ __('back') }}</a>
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

                        <form action="{{ route('schools.store') }}" method="POST" class="pt-3">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('email') }}</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('phone') }}</label>
                                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('address') }}</label>
                                    <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
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
