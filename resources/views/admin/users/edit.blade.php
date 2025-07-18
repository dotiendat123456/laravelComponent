@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Chỉnh sửa User</h3>

        {{-- Thông báo lỗi --}}
        @error('error')
            <div class="alert alert-danger" role="alert">
                {{ $message }}
            </div>
        @enderror

        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Họ<span class="text-danger">*</span></label>
                <input name="first_name" value="{{ old('first_name', $user->first_name) }}"
                    class="form-control @error('first_name') is-invalid @enderror">
                @error('first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Tên<span class="text-danger">*</span></label>
                <input name="last_name" value="{{ old('last_name', $user->last_name) }}"
                    class="form-control @error('last_name') is-invalid @enderror">
                @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Address</label>
                <input name="address" value="{{ old('address', $user->address) }}"
                    class="form-control @error('address') is-invalid @enderror">
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    @foreach (App\Enums\UserStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ old('status', $user->status->value) == $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn btn-primary">Cập nhật</button>
        </form>
    </div>
@endsection