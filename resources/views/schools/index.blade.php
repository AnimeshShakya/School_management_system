@extends('layouts.master')

@section('title')
    {{ __('schools') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">{{ __('schools') }}</h3>
            <a class="btn btn-theme" href="{{ route('schools.create') }}">{{ __('create') . ' ' . __('school') }}</a>
        </div>

        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{ __('list') . ' ' . __('schools') }}</h4>

                        @if (Session::has('success'))
                            <div class="alert alert-success">{{ Session::get('success') }}</div>
                        @endif

                        @if (Session::has('error'))
                            <div class="alert alert-danger">{{ Session::get('error') }}</div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('no.') }}</th>
                                        <th>{{ __('name') }}</th>
                                        <th>{{ __('email') }}</th>
                                        <th>{{ __('phone') }}</th>
                                        <th>{{ __('status') }}</th>
                                        <th>{{ __('action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($schools as $key => $school)
                                        <tr>
                                            <td>{{ $schools->firstItem() + $key }}</td>
                                            <td>{{ $school->name }}</td>
                                            <td>{{ $school->email }}</td>
                                            <td>{{ $school->phone }}</td>
                                            <td>
                                                @if($school->status)
                                                    <span class="badge badge-success">{{ __('active') }}</span>
                                                @else
                                                    <span class="badge badge-danger">{{ __('inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a class="btn btn-xs btn-gradient-info btn-rounded btn-icon"
                                                    href="{{ route('schools.show', $school->id) }}">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a class="btn btn-xs btn-gradient-primary btn-rounded btn-icon"
                                                    href="{{ route('schools.edit', $school->id) }}">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form class="d-inline"
                                                    action="{{ route('schools.destroy', $school->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-gradient-danger btn-rounded btn-icon">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No schools found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $schools->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
