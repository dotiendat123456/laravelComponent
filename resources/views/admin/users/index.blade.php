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
        // Khai báo biến table để lưu instance DataTable
        let table;

        // Khi tài liệu HTML tải xong, khởi tạo DataTable
        $(document).ready(function () {
            // Khởi tạo DataTable cho bảng #usersTable
            table = $('#usersTable').DataTable({
                processing: true,   // Hiển thị trạng thái loading
                serverSide: true,   // Bật chế độ server-side: phân trang, lọc do server xử lý
                ordering: false,    // KHÓA sắp xếp, vì controller chỉ sắp latest('id')
                searching: false,   // Tắt search mặc định, dùng form ngoài
                pageLength: 5,      // Số dòng mặc định trên 1 trang
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],

                // Cấu hình ajax gọi tới route Laravel
                ajax: {
                    url: '{{ route('admin.users.data') }}',
                    data: function (d) {
                        // Lấy giá trị input form filter
                        d.name = $('input[name=name]').val();
                        d.email = $('input[name=email]').val();
                    }
                },

                // Cấu hình các cột dữ liệu khớp với controller
                columns: [
                    { data: 'name' },    // Tên đầy đủ user
                    { data: 'email' },   // Email user
                    { data: 'address' }, // Địa chỉ
                    { data: 'status' },  // Trạng thái (badge HTML)

                    // Cột nút thao tác (Sửa)
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return `
                                        <a href="/admin/users/${row.id}/edit" class="btn btn-sm btn-outline-warning" title="Sửa">
                                            <i class="fa-solid fa-edit"></i> Sửa
                                        </a>
                                    `;
                        }
                    }
                ],

                // Ngôn ngữ Tiếng Việt
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
                }
            });

            // Bắt sự kiện submit form filter, reload DataTable với dữ liệu mới
            $('#searchForm').on('submit', function (e) {
                e.preventDefault();
                table.ajax.reload();
            });
        });
    </script>

@endpush