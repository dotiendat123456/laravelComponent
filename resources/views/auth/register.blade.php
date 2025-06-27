@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Đăng ký tài khoản</div>

                    <div class="card-body">

                        {{-- Thông báo thành công --}}
                        @if (session('success'))
                            <x-alert-success :message="session('success')" />
                        @endif

                        {{-- Thông báo lỗi hệ thống
                        @if ($errors->has('register_error'))
                        <x-home.alert-error :message="$errors->first('register_error')" />
                        @endif --}}
                        @error('register_error')
                            <div class="alert alert-danger" role="alert">
                                {{ $message }}
                            </div>
                        @enderror


                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            {{-- Họ --}}
                            <div class="row mb-3">
                                <label for="first_name" class="col-md-4 col-form-label text-md-end">
                                    Họ <span class="text-danger">*</span>
                                </label>
                                <div class="col-md-6">
                                    <input id="first_name" type="text"
                                        class="form-control @error('first_name') is-invalid @enderror" name="first_name"
                                        value="{{ old('first_name') }}" autofocus>

                                    @error('first_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Tên --}}
                            <div class="row mb-3">
                                <label for="last_name" class="col-md-4 col-form-label text-md-end">
                                    Tên <span class="text-danger">*</span>
                                </label>
                                <div class="col-md-6">
                                    <input id="last_name" type="text"
                                        class="form-control @error('last_name') is-invalid @enderror" name="last_name"
                                        value="{{ old('last_name') }}">

                                    @error('last_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="row mb-3">
                                <label for="email" class="col-md-4 col-form-label text-md-end">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                        name="email" value="{{ old('email') }}">

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Mật khẩu --}}
                            <div class="row mb-3">
                                <label for="password" class="col-md-4 col-form-label text-md-end">
                                    Mật khẩu <span class="text-danger">*</span>
                                </label>
                                <div class="col-md-6">
                                    <input id="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password">

                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Xác nhận mật khẩu --}}
                            <div class="row mb-4">
                                <label for="password-confirm" class="col-md-4 col-form-label text-md-end">
                                    Xác nhận mật khẩu <span class="text-danger">*</span>
                                </label>
                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control"
                                        name="password_confirmation">
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        Đăng ký
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