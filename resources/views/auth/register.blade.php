@extends('layouts.app')

@section('content')
<div class="container mt-4 ">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5 mt-5">
            <div class="card auth-box">
                {{-- <div class="card-header text-center">Log in to <b>Podsolution<span class="site-color">Shopify</span></b></div> --}}
                <div class="card-body">
                    <div class="text-center mb-2">
                        <svg width="120" height="120" viewBox="0 0 1200 1200" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M590.276 100L534.053 156.538L513.511 177.201L431.275 259.938L375.051 316.476L354.509 337.139L312.037 379.879L275 417.116V780.124L593.003 1100V775.627V770.125L646.483 716.329L709.515 652.927L751.987 610.187L831.496 530.209L868.516 492.972L924.723 436.417L590.276 100ZM513.511 906.93L485.263 878.516L429.039 821.961L366.008 758.558L354.509 746.992V535.728L414.814 596.389L471.038 652.944L513.511 695.666V906.93ZM513.511 582.557L368.243 436.434L431.275 373.031L487.482 316.476L513.494 290.311V582.557H513.511ZM752.004 497.094L709.532 539.834L653.308 596.372L593.003 657.032V535.728V530.226V422.619V417.116V215.852L693.054 316.493L749.278 373.048L752.004 375.79L812.309 436.451L752.004 497.094Z" fill="#1A1A1A"/>
                        </svg>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="name" class="col-form-label">{{ __('Name') }}</label>
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="company" class="col-form-label">Company Name</label>
                                <input id="company" type="company" class="form-control @error('company') is-invalid @enderror" name="company" required>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="email" class="col-form-label">{{ __('E-Mail Address') }}</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="password" class="col-form-label">{{ __('Password') }}</label>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="password-confirm" class="col-form-label">{{ __('Confirm Password') }}</label>
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="form-group row mb-1">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary px-5 mb-1">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
