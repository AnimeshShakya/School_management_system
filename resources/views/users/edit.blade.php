@extends('layouts.master')

@section('title')
    {{ __('edit') . ' ' . __('user') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">{{ __('edit') . ' ' . __('user') }}</h3>
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

                        <form method="POST" action="{{ route('users.update', $user->id) }}" class="pt-3">
                            @csrf
                            @method('PATCH')

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('first_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" class="form-control" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('last_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" class="form-control" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('email') }} <span class="text-danger">*</span></label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('role') }} <span class="text-danger">*</span></label>
                                    <select id="roles" name="roles[]" class="form-control" multiple required>
                                        @foreach ($roles as $roleValue => $roleLabel)
                                            <option value="{{ $roleValue }}"
                                                {{ in_array($roleValue, old('roles', array_values($userRole)), true) ? 'selected' : '' }}>
                                                {{ $roleLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('current_password') }}</label>
                                    <div class="input-group">
                                        <input type="password" id="current_password" name="current_password" class="form-control" placeholder="{{ __('required_to_change_password') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text" style="cursor:pointer;">
                                                <i class="fa fa-eye-slash" id="toggleCurrentPassword"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">{{ __('enter_current_password_to_change') }}</small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('password') }}</label>
                                    <div class="input-group">
                                        <input type="password" id="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                                        <div class="input-group-append">
                                            <span class="input-group-text" style="cursor:pointer;">
                                                <i class="fa fa-eye-slash" id="togglePassword"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">{{ __('minimum 8 characters') }}</small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('confirm') . ' ' . __('password') }}</label>
                                    <div class="input-group">
                                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                                        <div class="input-group-append">
                                            <span class="input-group-text" style="cursor:pointer;">
                                                <i class="fa fa-eye-slash" id="toggleConfirmPassword"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <small id="password-match-msg" class="form-text"></small>
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

@section('script')
<script>
    // Select2 for roles
    $('#roles').select2({
        placeholder: '{{ __('select') . ' ' . __('role') }}',
        allowClear: false,
    });

    // Password eye toggles
    $('#toggleCurrentPassword').on('click', function () {
        const input = document.querySelector('#current_password');
        input.type = input.type === 'password' ? 'text' : 'password';
        $(this).toggleClass('fa-eye-slash fa-eye');
    });

    $('#togglePassword').on('click', function () {
        const input = document.querySelector('#password');
        input.type = input.type === 'password' ? 'text' : 'password';
        $(this).toggleClass('fa-eye-slash fa-eye');
    });

    $('#toggleConfirmPassword').on('click', function () {
        const input = document.querySelector('#password_confirmation');
        input.type = input.type === 'password' ? 'text' : 'password';
        $(this).toggleClass('fa-eye-slash fa-eye');
    });

    // Real-time password match feedback
    function checkPasswordMatch() {
        const pw = $('#password').val();
        const pwc = $('#password_confirmation').val();
        const msg = $('#password-match-msg');
        const confirmInput = $('#password_confirmation');

        if (!pwc.length) {
            msg.text('').removeClass('text-danger text-success');
            confirmInput.removeClass('is-invalid is-valid');
            return;
        }

        if (pw === pwc) {
            msg.text('{{ __('passwords_match') }}').removeClass('text-danger').addClass('text-success');
            confirmInput.removeClass('is-invalid').addClass('is-valid');
        } else {
            msg.text('{{ __('passwords_not_match') }}').removeClass('text-success').addClass('text-danger');
            confirmInput.removeClass('is-valid').addClass('is-invalid');
        }
    }

    $('#password, #password_confirmation').on('input', checkPasswordMatch);

    // Block submit if passwords are filled but don't match
    $('form').on('submit', function (e) {
        const pw = $('#password').val();
        const pwc = $('#password_confirmation').val();
        if (pw && pw !== pwc) {
            e.preventDefault();
            checkPasswordMatch();
            $('#password_confirmation').focus();
        }
    });
</script>
@endsection