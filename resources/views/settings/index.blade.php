@extends('layouts.master')

@section('title')
    {{ __('general_settings') }}
@endsection


@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('general_settings') }}
            </h3>
        </div>
        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form id="frmData" class="general-setting" action="{{ url('settings') }}" novalidate="novalidate" enctype="multipart/form-data">
                            @csrf
                            <h4 class="card-title">
                                {{ __('general_settings') }}
                            </h4>
                            <hr>

                            <div class="row">
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>{{ __('school_name') }}</label>
                                    <input name="school_name" value="{{ isset($settings['school_name']) ? $settings['school_name'] : '' }}" type="text" required placeholder="{{ __('school_name') }}" class="form-control"/>
                                </div>
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>{{ __('school_email') }}</label>
                                    <input name="school_email" value="{{ isset($settings['school_email']) ? $settings['school_email'] : '' }}" type="email" required placeholder="{{ __('school_email') }}" class="form-control"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>{{ __('school_phone') }}</label>
                                    <input name="school_phone" value="{{ isset($settings['school_phone']) ? $settings['school_phone'] : '' }}" type="text" required placeholder="{{ __('school_phone') }}" class="form-control"/>
                                </div>
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>{{ __('school_tagline') }}</label>
                                    <textarea name="school_tagline" placeholder="{{ __('school_tagline') }}" class="form-control">{{ isset($settings['school_tagline']) ? $settings['school_tagline'] : '' }}</textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{ __('time_zone') }}</label>
                                    <select name="time_zone" required class="form-control" style="width:100%">
                                        @foreach ($getTimezoneList as $timezone)
                                            <option value="@php  echo $timezone[2]; @endphp"
                                                {{ isset($settings['time_zone']) ? ($settings['time_zone'] == $timezone[2] ? 'selected' : '') : '' }}>
                                                @php  echo $timezone[2] .' - GMT ' . $timezone[1] .' - '.$timezone[0] @endphp</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{ __('date_formate') }}</label>
                                    <select name="date_formate" required class="form-control">
                                        @foreach ($getDateFormat as $key => $dateformate)
                                            <option value="{{ $key }}"{{ isset($settings['date_formate']) ? ($settings['date_formate'] == $key ? 'selected' : '') : '' }}>{{ $dateformate }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{ __('time_formate') }}</label>
                                    <select name="time_formate" required class="form-control">
                                        @foreach ($getTimeFormat as $key => $timeformate)
                                            <option value="{{ $key }}"{{ isset($settings['time_formate']) ? ($settings['time_formate'] == $key ? 'selected' : '') : '' }}>{{ $timeformate }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{ __('favicon') }} <span class="text-danger">*</span></label>
                                    <input type="file" name="favicon" class="file-upload-default" accept="image/*"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ __('favicon') }}"/>
                                        <span class="input-group-append">
                                          <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                        </span>
                                        <div class="col-md-12">
                                            <img height="50px" src='{{ isset($settings['favicon']) ?url(Storage::url($settings['favicon'])) : '' }}'>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{ __('horizontal_logo') }} <span class="text-danger">*</span></label>
                                    <input type="file" name="logo1" class="file-upload-default" accept="image/*"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ __('logo1') }}"/>
                                        <span class="input-group-append">
                                          <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                        </span>
                                        <div class="col-md-12">
                                            <img height="50px" src='{{ isset($settings['logo1']) ? url(Storage::url($settings['logo1'])) : '' }}'>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{ __('vertical_logo') }} <span class="text-danger">*</span></label>
                                    <input type="file" name="logo2" class="file-upload-default" accept="image/*"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ __('logo2') }}"/>
                                        <span class="input-group-append">
                                          <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                        </span>
                                        <div class="col-md-12">
                                            <img height="50px" src='{{ isset($settings['logo2']) ?  url(Storage::url($settings['logo2'])) : '' }}'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{  __('theme').' '. __('color') }}</label>
                                    <input name="theme_color" value="{{ isset($settings['theme_color']) ? $settings['theme_color'] : '' }}" type="text" required placeholder="{{ __('color') }}" class="color-picker"/>
                                </div>
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{  __('secondary').' '. __('color') }}</label>
                                    <input name="secondary_color" value="{{ isset($settings['secondary_color']) ? $settings['secondary_color'] : '' }}" type="text" required placeholder="{{ __('color') }}" class="color-picker"/>
                                </div>
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{ __('session_years') }}</label>
                                    <select name="session_year" required class="form-control">
                                        @foreach ($session_year as $key => $year)
                                            <option value="{{ $year->id }}"{{ isset($settings['session_year']) ? ($settings['session_year'] == $year->id ? 'selected' : '') : '' }}>{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{ __('school_address') }}</label>
                                    <textarea name="school_address" required placeholder="{{ __('school_address') }}" rows="5" class="form-control">{{ isset($settings['school_address']) ? $settings['school_address'] : '' }}</textarea>
                                </div>
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{ __('login_image') }}</label>
                                    <input type="file" name="login_image" class="file-upload-default" accept="image/*"/>
                                    @php
                                        $loginImagePreview = isset($settings['login_image']) && !empty($settings['login_image'])
                                            ? url(Storage::url(ltrim(str_replace('storage/', '', $settings['login_image']), '/')))
                                            : asset('/assets/images/heroImg1.png');
                                    @endphp
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ __('login_image') }}"/>
                                        <span class="input-group-append">
                                          <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                        </span>
                                        <div class="col-md-12 mt-2">
                                            <img height="50px" src="{{ $loginImagePreview }}">
                                        </div>
                                    </div>
                                </div>
                                  {{-- online payment mode setting --}}
                                  @if(isset($settings['online_payment']))
                                  @if($settings['online_payment'])
                                      <div class="form-inline col-md-4">
                                          <label>{{__('online_payment_mode') }}</label> <span class="ml-1 text-danger">*</span>
                                          <div class="ml-4 d-flex">
                                              <div class="form-check form-check-inline">
                                                  <label class="form-check-label">
                                                      <input type="radio" name="online_payment" class="online_payment_toggle" value="1" checked>
                                                      {{ __('enable') }}
                                                  </label>
                                              </div>
                                              <div class="form-check form-check-inline">
                                                  <label class="form-check-label">
                                                      <input type="radio" name="online_payment" class="online_payment_toggle" value="0">
                                                      {{ __('disable') }}
                                                  </label>
                                              </div>
                                          </div>
                                      </div>
                                  @else
                                      <div class="form-inline col-md-4">
                                          <label>{{__('online_payment_mode') }}</label> <span class="ml-1 text-danger">*</span>
                                          <div class="ml-4 d-flex">
                                              <div class="form-check form-check-inline">
                                                  <label class="form-check-label">
                                                      <input type="radio" name="online_payment" class="online_payment_toggle" value="1">
                                                      {{ __('enable') }}
                                                  </label>
                                              </div>
                                              <div class="form-check form-check-inline">
                                                  <label class="form-check-label">
                                                      <input type="radio" name="online_payment" class="online_payment_toggle" value="0" checked>
                                                      {{ __('disable') }}
                                                  </label>
                                              </div>
                                          </div>
                                      </div>
                                  @endif
                              @else
                                  <div class="form-inline col-md-4">
                                      <label>{{__('online_payment_mode') }}</label> <span class="ml-1 text-danger">*</span>
                                      <div class="ml-4 d-flex">
                                          <div class="form-check form-check-inline">
                                              <label class="form-check-label">
                                                  <input type="radio" name="online_payment" class="online_payment_toggle" value="1" checked>
                                                  {{ __('enable') }}
                                              </label>
                                          </div>
                                          <div class="form-check form-check-inline">
                                              <label class="form-check-label">
                                                  <input type="radio" name="online_payment" class="online_payment_toggle" value="0">
                                                  {{ __('disable') }}
                                              </label>
                                          </div>
                                      </div>
                                  </div>
                              @endif
                              {{-- end of online payment mode setting --}}
                            </div>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <h4 class="card-title">
                                {{ __('social') . ' ' . __('links') }}
                            </h4>
                            <hr>
                            <div class="row">
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{  __('facebook') }}</label>
                                    <input name="facebook" value="{{ isset($settings['facebook']) ? $settings['facebook'] : '' }}" type="text" placeholder="{{  __('facebook').' '. __('url') }}" class="form-control"/>
                                </div>
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{  __('instagram')}}</label>
                                    <input name="instagram" value="{{ isset($settings['instagram']) ? $settings['instagram'] : '' }}" type="text" placeholder="{{  __('instagram').' '. __('url') }}" class="form-control"/>
                                </div>
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{  __('linkedin')}}</label>
                                    <input name="linkedin" value="{{ isset($settings['linkedin']) ? $settings['linkedin'] : '' }}" type="text" placeholder="{{  __('linkedin').' '. __('url') }}" class="form-control"/>
                                </div>
                            </div>
                            <div class="row mb-5">
                                <div class="form-group col-md-12 col-sm-12">
                                    <label>{{  __('google_map_link') }}</label>
                                    <input name="maplink" value="{{ isset($settings['maplink']) ? $settings['maplink'] : '' }}" type="text" placeholder="{{  __('google_map_link') }}" class="form-control"/>
                                </div>
                                <div class="col-sm-12 col-xs-12">
                                    <span style="font-size: 14px; color:"> <b>{{__('Note')}} :- </b>{{__('get_the_link_from_google_map_with_embed_url_and_paste_only_src_from_it')}}</span>
                                </div>
                            </div>

                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <h4 class="card-title">
                                {{ __('recaptcha') }}
                            </h4>
                            <hr>
                            <div class="row">
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{  __('site_key') }}</label>
                                    <input name="recaptcha_site_key" value="{{ isset($settings['recaptcha_site_key']) ? $settings['recaptcha_site_key'] : '' }}" type="text" placeholder="{{  __('site_key')}}" class="form-control"/>
                                </div>
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{  __('secret_key')}}</label>
                                    <input name="recaptcha_secret_key" value="{{ isset($settings['recaptcha_secret_key']) ? $settings['recaptcha_secret_key'] : '' }}" type="text" placeholder="{{  __('secret_key')}}" class="form-control"/>
                                </div>
                                <div class="form-group col-md-4 col-sm-12">
                                    <label>{{  __('status')}}</label><span class="ml-1 text-danger">*</span>
                                    <div class="ml-4 d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" name="recaptcha_status" class="online_payment_toggle" value="1" {{ isset($settings['recaptcha_status']) && $settings['recaptcha_status'] == 1 ? 'checked' : '' }}>
                                                {{ __('enable') }}
                                                
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" name="recaptcha_status" class="online_payment_toggle" value="0" {{ isset($settings['recaptcha_status']) && $settings['recaptcha_status'] == 0 ? 'checked' : '' }}>
                                                {{ __('disable') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input class="btn btn-theme" type="submit" value="Submit">
                        </form>

                        {{-- Payment Toggle PIN --}}
                        <hr>
                        <h4 class="card-title mt-4">
                            <i class="fa fa-lock mr-2"></i>{{ __('payment_toggle_pin_label') }}
                        </h4>
                        <p class="text-muted" style="font-size:0.9rem;">{{ __('payment_toggle_pin_desc') }}</p>
                        <div class="row align-items-end">
                            <div class="form-group col-md-3 col-sm-12 mb-0">
                                <label>{{ __('payment_toggle_pin_label') }}</label>
                                <div class="input-group">
                                    <input type="password" id="pin_setting_input" class="form-control"
                                           placeholder="{{ __('enter_4_digit_pin') }}" maxlength="4" inputmode="numeric" pattern="\d{4}"
                                           value="{{ isset($settings['payment_toggle_pin']) ? $settings['payment_toggle_pin'] : '' }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePinVisibility">
                                            <i class="fa fa-eye-slash"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">{{ __('pin_must_be_4_digits') }}</small>
                            </div>
                            <div class="col-md-2 col-sm-12 mb-3">
                                <button id="save_pin_btn" class="btn btn-theme">
                                    <i class="fa fa-save mr-1"></i>{{ __('save') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type='text/javascript'>
        // ── Payment Toggle PIN ────────────────────────────────────────────
        $('#togglePinVisibility').on('click', function () {
            var $input = $('#pin_setting_input');
            var isPassword = $input.attr('type') === 'password';
            $input.attr('type', isPassword ? 'text' : 'password');
            $(this).find('i').toggleClass('fa-eye-slash fa-eye');
        });

        $('#save_pin_btn').on('click', function () {
            var pin = $('#pin_setting_input').val().trim();
            if (!/^\d{4}$/.test(pin)) {
                showErrorToast('{{ __('pin_must_be_4_digits') }}');
                return;
            }
            var $btn = $(this);
            $btn.prop('disabled', true);
            $.ajax({
                url: '{{ route('students.payment-toggle-pin.update') }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', pin: pin },
                success: function (res) {
                    if (res.error) { showErrorToast(res.message); }
                    else {
                        showSuccessToast(res.message);
                        $('#pin_setting_input').attr('type', 'password');
                        $('#togglePinVisibility').find('i').removeClass('fa-eye').addClass('fa-eye-slash');
                    }
                },
                error: function () { showErrorToast('{{ __('error_occurred') }}'); },
                complete: function () { $btn.prop('disabled', false); }
            });
        });

        // ── General settings ─────────────────────────────────────────────
        if ($(".color-picker").length) {
            $('.color-picker').asColorPicker();
        }

        $("#frmData").validate({
            rules: {
                username: "required",
                password: "required",
            },
            errorPlacement: function (label, element) {
                label.addClass('mt-2 text-danger');
                label.insertAfter(element);
            },
            highlight: function (element, errorClass) {
                $(element).parent().addClass('has-danger')
                $(element).addClass('form-control-danger')
            }
        });
    </script>
@endsection
