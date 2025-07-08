@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">Danh sách bài viết (Admin)</h3>

        @if (session('success'))
            <x-alert-success :message="session('success')" />
        @endif

        @error('error')
            <div class="alert alert-danger" role="alert">
                {{ $message }}
            </div>
        @enderror

        <!-- FORM TÌM KIẾM -->
        <form id="searchForm" class="row g-3 mb-3">
            <div class="col-auto">
                <input type="text" name="title" class="form-control" placeholder="Tìm theo tiêu đề">
            </div>
            <div class="col-auto">
                <input type="text" name="email" class="form-control" placeholder="Tìm theo email user">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Lọc</button>
            </div>
        </form>

        <!-- TẠO MỚI & XÓA TẤT CẢ -->
        <div class="mb-3 d-flex justify-content-between">
            <a href="{{ route('admin.posts.create') }}" class="btn btn-success">
                <i class="fa-solid fa-plus"></i> Tạo mới
            </a>

            <button type="button" onclick="deleteAllPosts()" class="btn btn-outline-danger">
                <i class="fa-solid fa-trash"></i> Xóa tất cả
            </button>
        </div>

        <!-- BẢNG -->
        <div class="table-responsive">
            <table id="postsTable" class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Tiêu đề</th>
                        <th>Email User</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Khai báo biến table để lưu instance DataTable, dễ tái sử dụng
        let table;

        $(document).ready(function () {
            // Khởi tạo DataTable cho bảng #postsTable
            table = $('#postsTable').DataTable({
                processing: true, // Hiển thị trạng thái "Đang xử lý"
                serverSide: true, // Server xử lý phân trang, sắp xếp, filter
                ordering: true,   // Cho phép sắp xếp (controller đã xử lý orderBy)
                searching: false, // Tắt ô search mặc định, dùng form ngoài
                pageLength: 5,    // Số bản ghi mặc định mỗi trang
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]], // Tùy chọn số bản ghi
                order: [[0, 'desc']], // Mặc định sắp xếp ID giảm dần


                // Cấu hình AJAX để gọi route Laravel
                ajax: {
                    url: '{{ route('admin.posts.data') }}', // Endpoint Laravel trả JSON

                    // Gửi thêm tham số filter (title, email) từ form #searchForm
                    data: function (d) {
                        d.title = $('input[name=title]').val();  // Lấy giá trị input name=title
                        d.email = $('input[name=email]').val();  // Lấy giá trị input name=email
                    }
                },

                // Định nghĩa các cột hiển thị, phải khớp key JSON backend trả về
                columns: [
                    { data: 'id' },           // Cột ID bài viết
                    { data: 'title' },        // Cột tiêu đề bài viết
                    { data: 'email' },        // Cột email người tạo bài viết
                    { data: 'status' },       // Cột trạng thái bài viết (label/badge)
                    { data: 'created_at' },   // Cột ngày tạo bài viết

                    // Cột cuối: hiển thị nút hành động (Xem, Sửa, Xóa)
                    {
                        data: null,
                        orderable: false,     // Không cho sắp xếp cột này
                        searchable: false,    // Không cho tìm kiếm cột này
                        render: function (data, type, row) {
                            // Trả HTML các nút thao tác: xem bài, sửa, xóa
                            return `
                                    <a href="/news/${row.slug}" class="btn btn-sm btn-outline-info p-1" target="_blank" title="Xem">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="/admin/posts/${row.id}/edit" class="btn btn-sm btn-outline-warning p-1" title="Sửa">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <button onclick="deletePost(${row.id})" class="btn btn-sm btn-outline-danger p-1" title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                `;
                        }
                    }
                ],

                // Cấu hình ngôn ngữ DataTables sang Tiếng Việt
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
                }
            });

            // Bắt sự kiện submit form filter (#searchForm)
            $('#searchForm').on('submit', function (e) {
                e.preventDefault(); // Ngăn form reload trang
                table.ajax.reload(); // Reload DataTable với tham số filter mới
            });
        });

        // Hàm xử lý xóa 1 bài viết, nhận ID
        function deletePost(id) {
            // Hiển thị hộp thoại xác nhận xóa
            if (confirm('Bạn có chắc chắn muốn xóa bài viết này?')) {
                $.ajax({
                    url: `/admin/posts/${id}`, // Endpoint xóa bài viết theo ID
                    type: 'POST',               // Laravel cần method spoofing
                    data: {
                        _method: 'DELETE',      // Laravel sẽ hiểu là DELETE
                        _token: '{{ csrf_token() }}' // Gửi kèm token CSRF
                    },
                    success: function () {
                        // Xóa xong reload lại bảng
                        table.ajax.reload();
                    },
                    error: function () {
                        // Nếu lỗi, báo thất bại
                        alert('Xóa thất bại!');
                    }
                });
            }
        }

        // Hàm xử lý xóa tất cả bài viết
        function deleteAllPosts() {
            // Hộp thoại xác nhận xóa toàn bộ
            if (confirm('Bạn có chắc chắn muốn xóa tất cả bài viết?')) {
                $.ajax({
                    url: `{{ route('admin.posts.destroyAll') }}`, // Endpoint Laravel xóa all
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function () {
                        table.ajax.reload(); // Reload lại bảng sau khi xóa
                    },
                    error: function () {
                        alert('Xóa tất cả thất bại!');
                    }
                });
            }
        }
    </script>


@endpush