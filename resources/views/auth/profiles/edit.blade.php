@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Cập nhật hồ sơ</div>

                    <div class="card-body">

                        {{-- Thông báo thành công --}}
                        @if (session('success'))
                            <x-alert-success :message="session('success')" />
                        @endif



                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            {{-- Họ --}}
                            <div class="row mb-3">
                                <label for="first_name" class="col-md-4 col-form-label text-md-end">Họ</label>

                                <div class="col-md-6">
                                    <input id="first_name" type="text"
                                        class="form-control @error('first_name') is-invalid @enderror" name="first_name"
                                        value="{{ old('last_name', Auth::user()->first_name) }}">

                                    @error('first_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Tên --}}
                            <div class="row mb-3">
                                <label for="last_name" class="col-md-4 col-form-label text-md-end">Tên</label>

                                <div class="col-md-6">
                                    <input id="last_name" type="text"
                                        class="form-control @error('last_name') is-invalid @enderror" name="last_name"
                                        value="{{ old('last_name', Auth::user()->last_name) }}">

                                    @error('last_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>



                            {{-- Địa chỉ --}}
                            <div class="row mb-3">
                                <label for="address" class="col-md-4 col-form-label text-md-end">Địa chỉ</label>

                                <div class="col-md-6">
                                    <textarea id="address" class="form-control @error('address') is-invalid @enderror"
                                        name="address" rows="3">{{ old('address', auth()->user()->address) }}</textarea>

                                    @error('address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        Cập nhật hồ sơ
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