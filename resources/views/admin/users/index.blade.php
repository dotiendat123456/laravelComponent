@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">Danh sách User</h3>

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
        <form id="searchForm" method="GET" class="row g-2 mb-3">
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

        {{-- Bảng + phân trang AJAX --}}
        <div id="usersTable">
            @include('admin.users._table', ['users' => $users])
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .table-fixed {
            table-layout: fixed;
            width: 100%;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Chặn submit ➜ AJAX tìm kiếm
        $('#searchForm').on('submit', function (e) {
            e.preventDefault();
            let query = $(this).serialize();
            fetchUsers(query);
        });

        // Bấm link trang ➜ AJAX
        $(document).on('click', '.pagination a', function (e) {
            e.preventDefault();
            let url = $(this).attr('href');
            let query = url.split('?')[1];
            fetchUsers(query);
        });

        function fetchUsers(query) {
            $.ajax({
                url: "{{ route('admin.users.index') }}?" + query,
                success: function (data) {
                    $('#usersTable').html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
    </script>
@endpush