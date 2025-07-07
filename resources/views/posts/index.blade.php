@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Danh sách Bài viết</h3>

        <div class="mb-3 d-flex justify-content-between align-items-center">
            <a href="{{ route('posts.create') }}" class="btn btn-success">
                <i class="bi bi-plus"></i> Tạo mới
            </a>

            {{-- <form action="{{ route('posts.destroyAll') }}" method="POST"
                onsubmit="return confirm('Bạn có chắc chắn muốn xóa tất cả bài viết?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i> Xóa tất cả
                </button>
            </form> --}}
            <button type="button" onclick="deleteAllPosts()" class="btn btn-outline-danger">
                <i class="bi bi-trash"></i> Xóa tất cả
            </button>

        </div>

        <div class="table-responsive">
            <table id="postsTable" class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Tiêu đề</th>
                        <th>Mô tả</th>
                        <th>Ngày đăng</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Biến table để lưu instance DataTable, có thể tái sử dụng (reload, redraw)
        let table;

        // Khi tài liệu HTML tải xong, khởi tạo DataTable
        $(document).ready(function () {
            // Gọi DataTable cho bảng có ID #postsTable
            table = $('#postsTable').DataTable({
                processing: true,   // Hiển thị trạng thái "Đang xử lý..." khi load dữ liệu
                serverSide: true,   // Bật chế độ server-side: phân trang, sắp xếp, filter do server xử lý
                ordering: true,     // Cho phép sắp xếp các cột (vì controller hỗ trợ orderBy)
                searching: true,    // Bật search toàn bảng mặc định (nếu không dùng form filter riêng)
                pageLength: 5,      // Số dòng hiển thị mặc định mỗi trang
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]], // Tùy chọn số dòng/trang
                order: [[0, 'desc']], // Đây! Sắp mặc định theo cột ID DESC

                // Cấu hình AJAX để gọi route Laravel trả JSON
                ajax: '{{ route('posts.data') }}',

                // Định nghĩa các cột hiển thị, tên key phải khớp JSON mà controller trả về
                columns: [
                    { data: 'id' },           // Cột ID bài viết
                    { data: 'title' },        // Cột tiêu đề bài viết
                    { data: 'email' },        // Cột email người tạo bài viết
                    { data: 'status' },       // Cột trạng thái bài viết (label/badge)
                    { data: 'created_at' },   // Cột ngày tạo bài viết (định dạng d/m/Y)

                    // Cột cuối: hiển thị các nút hành động (Xem, Sửa, Xóa)
                    {
                        orderable: false,   // Không cho phép sắp xếp cột này
                        searchable: false,  // Không cho phép tìm kiếm trong cột này
                        render: function (data, type, row) {
                            // Render HTML nút Xem, Sửa, Xóa, gắn ID và Slug bài viết
                            return `
                                    <a href="/posts/${row.slug}" class="btn btn-sm btn-outline-info p-1" title="Xem">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="/posts/${row.id}/edit" class="btn btn-sm btn-outline-warning p-1" title="Sửa">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <button onclick="deletePost(${row.id})" class="btn btn-sm btn-outline-danger p-1" title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                `;
                        }
                    }
                ],

                // Thiết lập ngôn ngữ DataTables sang Tiếng Việt (file CDN)
                language: {
                    url: '//cdn.datatables.net/plug-ins/2.0.0/i18n/vi.json'
                }
            });
        });

        // Hàm xử lý xóa 1 bài viết (nhận ID bài viết)
        function deletePost(id) {
            // Hiển thị hộp thoại xác nhận trước khi xóa
            if (confirm('Bạn có chắc chắn muốn xóa?')) {
                $.ajax({
                    url: `/posts/${id}`, // URL Laravel endpoint xóa bài viết theo ID
                    type: 'POST',        // Laravel yêu cầu gửi POST + _method
                    data: {
                        _method: 'DELETE',         // Laravel hiểu đây là DELETE (method spoofing)
                        _token: '{{ csrf_token() }}' // Token CSRF xác thực form
                    },
                    success: function () {
                        // Nếu xóa thành công, reload lại bảng để dữ liệu mới nhất
                        table.ajax.reload();
                    },
                    error: function () {
                        // Nếu có lỗi, hiển thị cảnh báo
                        alert('Xóa thất bại!');
                    }
                });
            }
        }

        // Hàm xử lý xóa tất cả bài viết
        function deleteAllPosts() {
            // Hộp thoại xác nhận xóa toàn bộ
            if (confirm('Bạn có chắc chắn muốn xóa tất cả?')) {
                $.ajax({
                    url: `{{ route('posts.destroyAll') }}`, // URL Laravel endpoint xóa tất cả
                    type: 'POST',
                    data: {
                        _method: 'DELETE',         // Laravel hiểu đây là DELETE
                        _token: '{{ csrf_token() }}'
                    },
                    success: function () {
                        // Reload lại bảng sau khi xóa tất cả
                        table.ajax.reload();
                    },
                    error: function () {
                        alert('Xóa tất cả thất bại!');
                    }
                });
            }
        }
    </script>


@endpush