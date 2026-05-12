@extends('layouts.master')

@section('title')
  {{ __('users') }}
@endsection

@section('content')
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">{{ __('users') }}</h3>
      @can('staff-create')
        <div class="page-header-right">
          <a class="btn btn-theme" href="{{ route('users.create') }}">{{ __('create') . ' ' . __('user') }}</a>
        </div>
      @endcan
    </div>

    <div class="row grid-margin">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">{{ __('list') . ' ' . __('users') }}</h4>

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
                    <th>{{ __('role') }}</th>
                    <th>{{ __('action') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($data as $key => $user)
                    <tr>
                      <td>{{ $data->firstItem() + $key }}</td>
                      <td>{{ trim($user->first_name . ' ' . $user->last_name) }}</td>
                      <td>{{ $user->email }}</td>
                      <td>
                        @forelse ($user->getRoleNames() as $roleName)
                          <span class="badge badge-success mr-1">{{ $roleName }}</span>
                        @empty
                          <span class="badge badge-secondary">N/A</span>
                        @endforelse
                      </td>
                      <td>
                        <a class="btn btn-xs btn-gradient-info btn-rounded btn-icon"
                          href="{{ route('users.show', $user->id) }}">
                          <i class="fa fa-eye"></i>
                        </a>
                        @can('staff-edit')
                          <a class="btn btn-xs btn-gradient-primary btn-rounded btn-icon"
                            href="{{ route('users.edit', $user->id) }}">
                            <i class="fa fa-edit"></i>
                          </a>
                        @endcan
                        @can('staff-delete')
                          <form class="d-inline"
                            action="{{ route('users.destroy', $user->id) }}"
                            method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-gradient-danger btn-rounded btn-icon">
                              <i class="fa fa-trash"></i>
                            </button>
                          </form>
                        @endcan
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-center">No users found.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="mt-3">
              {{ $data->links() }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection