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

        {{-- Form lọc trước khi render (nếu vẫn muốn) --}}
        <form id="searchForm" method="GET" class="row g-2 mb-3">
            <div class="col-auto">
                <input type="text" name="name" value="{{ request('name') }}" class="form-control" placeholder="Tên">
            </div>
            <div class="col-auto">
                <input type="text" name="email" value="{{ request('email') }}" class="form-control" placeholder="Email">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary">Lọc</button>
            </div>
        </form>

        {{-- Bảng --}}
        <div class="table-responsive">
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
    <script>
        $(document).ready(function () {
            $('#usersTable').DataTable({
                pageLength: 5,
                lengthMenu: [[1, 2, 3, 4, 5], [1, 2, 3, 4, 5]],
                ordering: false,
                searching: false,
                language: {
                    url: '//cdn.datatables.net/plug-ins/2.0.0/i18n/vi.json'
                }
            });
        });
    </script>
@endpush