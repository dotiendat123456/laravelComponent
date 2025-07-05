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

        {{-- FORM TÌM KIẾM --}}
        <form id="searchForm" method="GET" class="row g-2 mb-3">
            <div class="col-auto">
                <input type="text" name="name" class="form-control" placeholder="Tên">
            </div>
            <div class="col-auto">
                <input type="text" name="email" class="form-control" placeholder="Email">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Lọc</button>
            </div>
        </form>

        {{-- BẢNG --}}
        <div class="table-responsive">
            <table id="usersTable" class="table table-striped table-hover align-middle table-fixed">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%;">Tên</th>
                        <th style="width: 20%;">Email</th>
                        <th style="width: 30%;">Địa chỉ</th>
                        <th style="width: 15%;">Trạng thái</th>
                        <th style="width: 15%;">Hành động</th>
                    </tr>
                </thead>
            </table>
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
        let table;

        $(document).ready(function () {
            table = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                searching: false,
                pageLength: 1,
                lengthMenu: [[1, 3, 5, 10, 15], [1, 3, 5, 10, 15]],
                ajax: {
                    url: '{{ route('admin.users.data') }}',
                    data: function (d) {
                        d.name = $('input[name=name]').val();
                        d.email = $('input[name=email]').val();
                    }
                },
                columns: [
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'address' },
                    { data: 'status' },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return `
                                        <a href="/admin/users/${row.id}/edit" class="btn btn-sm btn-outline-warning">
                                            <i class="fa-solid fa-edit"></i> Sửa
                                        </a>
                                    `;
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/2.0.0/i18n/vi.json'
                }
            });

            $('#searchForm').on('submit', function (e) {
                e.preventDefault();
                table.ajax.reload();
            });
        });
    </script>
@endpush