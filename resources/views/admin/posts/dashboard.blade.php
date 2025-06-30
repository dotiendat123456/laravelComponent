@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Trang Quản Trị Admin</h1>

        @if (session('success'))
            <x-alert-success :message="session('success')" />
        @endif

        <div class="row">
            <!-- Box Quản lý Bài viết -->
            <div class="col-md-6 mb-4">
                <div class="card border-primary h-100">
                    <div class="card-body">
                        <h5 class="card-title">Quản lý Bài viết</h5>
                        <p class="card-text">Xem, tìm kiếm, chỉnh sửa trạng thái tất cả các bài viết của user.</p>
                        <a href="{{ route('admin.posts.index') }}" class="btn btn-primary">Đi đến Quản lý Bài viết</a>
                    </div>
                </div>
            </div>

            <!-- Box Quản lý Tài khoản -->
            <div class="col-md-6 mb-4">
                <div class="card border-success h-100">
                    <div class="card-body">
                        <h5 class="card-title">Quản lý Tài khoản</h5>
                        <p class="card-text">Xem danh sách tài khoản user, chỉnh sửa thông tin tài khoản.</p>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-success">Đi đến Quản lý Tài khoản</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection