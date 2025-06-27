@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Đặt lại mật khẩu</div>

                    <div class="card-body">
                        {{--
                        @if ($errors->has('error_reset'))
                        <x-home.alert-error :message="$errors->first('error_reset')" />
                        @endif --}}
                        @error('error_reset')
                            <div class="alert alert-danger" role="alert">
                                {{ $message }}
                            </div>
                        @enderror


                        <form method="POST" action="{{ route('passwords.update') }}">
                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">

                            {{-- Email --}}
                            <div class="row mb-3">
                                <label for="email" class="col-md-4 col-form-label text-md-end">
                                    Địa chỉ email <span class="text-danger">*</span>
                                </label>

                                <div class="col-md-6">
                                    <input id="email" type="text" class="form-control @error('email') is-invalid @enderror"
                                        name="email" value="{{ $email ?? old('email') }}" autocomplete="email" autofocus>

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Mật khẩu mới --}}
                            <div class="row mb-3">
                                <label for="password" class="col-md-4 col-form-label text-md-end">
                                    Mật khẩu mới <span class="text-danger">*</span>
                                </label>

                                <div class="col-md-6">
                                    <input id="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        autocomplete="new-password">

                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Xác nhận mật khẩu --}}
                            <div class="row mb-3">
                                <label for="password-confirm" class="col-md-4 col-form-label text-md-end">
                                    Xác nhận lại mật khẩu
                                </label>

                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control"
                                        name="password_confirmation" autocomplete="new-password">
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        Đặt lại mật khẩu
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