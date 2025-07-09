@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Danh sách Bài viết</h3>
        @if (session('success'))
            <x-alert-success :message="session('success')" />
        @endif

        @error('error')
            <div class="alert alert-danger" role="alert">
                {{ $message }}
            </div>
        @enderror

        <div class="mb-3 d-flex justify-content-between align-items-center">
            <a href="{{ route('posts.create') }}" class="btn btn-success">
                <i class="bi bi-plus"></i> Tạo mới
            </a>

            <button type="button" onclick="deleteAllPosts()" class="btn btn-outline-danger">
                <i class="bi bi-trash"></i> Xóa tất cả
            </button>
        </div>

        <div class="table-responsive">
            <table id="postsTable" class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Thumbnail</th>
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
        let table;

        $(document).ready(function () {
            table = $('#postsTable').DataTable({
                processing: true,      //  Hiển thị trạng thái "Đang xử lý..." khi load dữ liệu
                serverSide: true,      //  Bật chế độ server-side: phân trang, sort, search đều do server xử lý
                ordering: true,        //  Cho phép sắp xếp các cột (nếu cột khai báo orderable: true)
                searching: true,       //  Hiển thị ô tìm kiếm mặc định (có thể tắt nếu dùng filter riêng)
                pageLength: 5,         //  Mặc định số dòng trên 1 trang là 5
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]], //  Các tuỳ chọn số dòng/trang
                order: [[0, 'desc']],  //  Mặc định sắp xếp cột index 0 (ID) giảm dần → mới nhất lên đầu
                ajax: '{{ route('posts.data') }}', //  URL route Laravel trả JSON cho DataTables


                columns: [
                    { data: 'id' }, // 0: ID
                    {
                        data: 'thumbnail', orderable: false, searchable: false,
                        render: function (data) {
                            return data ? `<img src="${data}" alt="Thumbnail" width="80">` : '-';
                        }
                    },
                    { data: 'title' },        // 2: Tiêu đề
                    { data: 'description' },  // 3: Mô tả
                    { data: 'publish_date' }, // 4: Ngày đăng
                    { data: 'status' },       // 5: Trạng thái
                    {
                        data: null, orderable: false, searchable: false,
                        render: function (data, type, row) {
                            return `
                                                        <div class="d-inline-flex align-items-center gap-1">
                                                            <a href="/news/${row.slug}" class="btn btn-sm btn-outline-info p-1" target="_blank" title="Xem">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </a>
                                                            <a href="/posts/${row.id}/edit" class="btn btn-sm btn-outline-warning p-1" title="Sửa">
                                                                <i class="fa-solid fa-edit"></i>
                                                            </a>
                                                            <button onclick="deletePost(${row.id})" class="btn btn-sm btn-outline-danger p-1" title="Xóa">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </div>`;
                        }
                    }
                ],

                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
                }
            });
        });

        function deletePost(id) {
            if (confirm('Bạn có chắc chắn muốn xóa?')) {
                $.ajax({
                    url: `/posts/${id}`,
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function () { table.ajax.reload(); },
                    error: function () { alert('Xóa thất bại!'); }
                });
            }
        }

        function deleteAllPosts() {
            if (confirm('Bạn có chắc chắn muốn xóa tất cả?')) {
                $.ajax({
                    url: `{{ route('posts.destroyAll') }}`,
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function () { table.ajax.reload(); },
                    error: function () { alert('Xóa tất cả thất bại!'); }
                });
            }
        }
    </script>
@endpush