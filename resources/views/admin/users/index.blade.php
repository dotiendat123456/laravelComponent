@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Danh sách User</h3>

    {{-- Thông báo thành công --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
     {{-- Thông báo lỗi --}}
        @error('error')
            <div class="alert alert-danger" role="alert">
                {{ $message }}
            </div>
        @enderror

    {{-- Form tìm kiếm --}}
    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="text" name="name" value="{{ request('name') }}" class="form-control" placeholder="Tên">
        </div>
        <div class="col-auto">
            <input type="text" name="email" value="{{ request('email') }}" class="form-control" placeholder="Email">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Tìm kiếm</button>
        </div>
    </form>

    {{-- Table --}}
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Tên</th>
                <th>Email</th>
                <th>Địa chỉ</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->address }}</td>
                    <td>
                        @switch($user->status)
                            @case(App\Enums\UserStatus::PENDING)
                                <span class="badge bg-secondary">{{ $user->status->label() }}</span>
                                @break

                            @case(App\Enums\UserStatus::APPROVED)
                                <span class="badge bg-success">{{ $user->status->label() }}</span>
                                @break

                            @case(App\Enums\UserStatus::REJECTED)
                                <span class="badge bg-danger">{{ $user->status->label() }}</span>
                                @break

                            @case(App\Enums\UserStatus::LOCKED)
                                <span class="badge bg-dark">{{ $user->status->label() }}</span>
                                @break

                            @default
                                <span class="badge bg-light">Không rõ</span>
                        @endswitch
                    </td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-warning">
                            <i class="fa-solid fa-edit"></i> Sửa
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Không có user nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Phân trang --}}
    <div class="mt-3">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
