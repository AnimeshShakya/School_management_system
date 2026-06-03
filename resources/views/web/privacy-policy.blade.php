@extends('web.master')

@section('title')
    {{ __('privacy_policy') }}
@endsection
@section('content')
  <div class="main">
    <div class="breadcrumb">
      <div class="container">
        <div class="contentWrapper">
          <span class="title"> {{ __('privacy_policy') }}</span>
          <span>
            <span class="home"><a href="{{url('/')}}">{{ __('home') }}</a></span>
            <span><i class="fa-solid fa-angles-right"></i></span>
            <span class="page">{{ __('privacy_policy') }}</span>
          </span>
        </div>
      </div>
    </div>

    <section class="commonMT">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="contentWrapper privacyContent">
              {!! $content !!}
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
