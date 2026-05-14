@php
    $lang = Session::get('language');
@endphp
<link rel="stylesheet" href="{{ asset('/assets/css/vendor.bundle.base.css') }}" async>

<link rel="stylesheet" href="{{ asset('/assets/fonts/font-awesome.min.css') }}" async />
<link rel="stylesheet" href="{{ asset('/assets/select2/select2.min.css') }}" async>
<link rel="stylesheet" href="{{ asset('/assets/jquery-toast-plugin/jquery.toast.min.css') }}">
<link rel="stylesheet" href="{{ asset('/assets/color-picker/color.min.css') }}" async>
@if ($lang)
    @if ($lang->is_rtl)
        <link rel="stylesheet" href="{{ asset('/assets/css/rtl.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('/assets/css/style.css') }}">
    @endif
@else
    <link rel="stylesheet" href="{{ asset('/assets/css/style.css') }}">
@endif
<link rel="stylesheet" href="{{ asset('/assets/css/admin-modern.css') }}">
<link rel="stylesheet" href="{{ asset('/assets/css/datepicker.min.css') }}" async>
<link rel="stylesheet" href="{{ asset('/assets/css/daterangepicker.css') }}">
<link rel="stylesheet" href="{{ asset('/assets/css/ekko-lightbox.css') }}">

<link rel="stylesheet" href="{{ asset('/assets/bootstrap-table/bootstrap-table.min.css') }}">
<link rel="stylesheet" href="{{ asset('/assets/bootstrap-table/fixed-columns.min.css') }}">
<link rel="stylesheet" href="{{ asset('/assets/bootstrap-table/reorder-rows.css') }}">

<script src="{{ route('common.language.read') }}"></script>

<link rel="shortcut icon" href="{{ url(Storage::url(env('FAVICON'))) }}" />
@php
    $theme_color = getSettings('theme_color');
    $secondary_color = getSettings('secondary_color');

    $theme_color = $theme_color['theme_color'] ?? '#7367f0';
    $secondary_color = $secondary_color['secondary_color'] ?? '#2c2c2c';
@endphp
@php
    $login_image_setting = getSettings('login_image');
    $login_image_path = $login_image_setting['login_image'] ?? env('LOGIN_IMAGE');
    $default_login_image = asset('/assets/images/heroImg1.png');

    if (!empty($login_image_path)) {
        if (str_starts_with($login_image_path, 'http://') || str_starts_with($login_image_path, 'https://')) {
            $parsed_path = parse_url($login_image_path, PHP_URL_PATH);
            if (is_string($parsed_path) && str_contains($parsed_path, '/storage/')) {
                $login_image_path = ltrim(explode('/storage/', $parsed_path, 2)[1] ?? '', '/');
            }
        }

        if (str_starts_with($login_image_path, 'storage/')) {
            $login_image_path = ltrim(substr($login_image_path, 8), '/');
        }
    }

    if (!empty($login_image_path) && Storage::disk('public')->exists($login_image_path)) {
        $login_image = url(Storage::url($login_image_path));
    } else {
        $login_image = $default_login_image;
    }

@endphp
<style>
    :root {
        --theme-color: {{ $theme_color }};
        --image-url: url('{{ $login_image }}');
    }
</style>
<script>
    var baseUrl = "{{ URL::to('/') }}";
    const onErrorImage = (e) => {
        if (!e || !e.target) return;
        e.target.onerror = null;
        e.target.src = "{{ asset('/assets/images/dummyImg.png') }}";
    };
</script>
