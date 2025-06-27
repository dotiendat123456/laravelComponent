@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Đăng nhập</div>

                    <div class="card-body">

                        {{-- Thông báo thành công --}}
                        @if (session('success'))
                            <x-alert-success :message="session('success')" />
                        @endif

                        {{-- Lỗi trạng thái tài khoản
                        @if ($errors->has('account_status'))
                            <x-home.alert-error :message="$errors->first('account_status')" />
                        @endif --}}
                        @error('account_status')
                            <div class="alert alert-danger" role="alert">
                                  {{ $message }}
                            </div>
                        @enderror
        

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            {{-- Email --}}
                            <div class="row mb-3">
                                <label for="email" class="col-md-4 col-form-label text-md-end">Email <span
                                        class="text-danger">*</span></label>

                                <div class="col-md-6">
                                    <input id="email" type="text" class="form-control @error('email') is-invalid @enderror"
                                        name="email" value="{{ old('email') }}" autocomplete="email" autofocus>

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Mật khẩu --}}
                            <div class="row mb-3">
                                <label for="password" class="col-md-4 col-form-label text-md-end">Mật khẩu <span
                                        class="text-danger">*</span></label>

                                <div class="col-md-6">
                                    <input id="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        autocomplete="current-password">

                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>


                            {{-- Ghi nhớ + Quên mật khẩu --}}
                            <div class="row mb-3">
                                <div class="col-md-6 offset-md-4 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember"
                                         id="remember"{{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">
                                            Ghi nhớ đăng nhập
                                        </label>
                                    </div>

                                    <a class="btn btn-link p-0 m-0 align-baseline" href="{{ route('passwords.request') }}">
                                        Quên mật khẩu
                                    </a>
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        Đăng nhập
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