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

        {{-- Form lọc dữ liệu theo tiêu đề và trạng thái --}}
        <form id="searchForm" class="row g-3 mb-3">
            <div class="col-auto">
                <input type="text" name="title" id="filterTitle" class="form-control" placeholder="Tìm theo tiêu đề">
            </div>
            <div class="col-auto">
                <input type="text" name="email" id="filterEmail" class="form-control" placeholder="Tìm theo email">
            </div>

            <div class="col-auto">
                <select name="status" id="filterStatus" class="form-select">
                    <option value="" {{ request()->has('status') ? '' : 'selected' }}>Tất cả trạng thái</option>
                    @foreach (\App\Enums\PostStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ (string) request('status') === (string) $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
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

            <button id="btnDeleteAll" type="button" onclick="deleteAllPosts()" class="btn btn-outline-danger">
                <i class="fa-solid fa-trash"></i> Xóa tất cả
            </button>
        </div>

        <!-- BẢNG -->
        <div class="table-responsive">
            <table id="postsTable" class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>STT</th>
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
        let table;

        $(document).ready(function () {
            table = $('#postsTable').DataTable({
                processing: true,
                serverSide: true,
                // serverSide: false,
                ordering: true,
                searching: false,
                pageLength: 5,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                order: [0, 'desc'],
                ajax: function (data, callback) {
                    const page = (data.start / data.length) + 1; // Tính toán page từ start và length

                    // Gửi request tới route posts.data
                    $.get('{{ route('admin.posts.index') }}', {
                        page: page, // Laravel cần param này để phân trang
                        length: data.length, // Số lượng mỗi trang
                        draw: data.draw,     // Dùng để đồng bộ với client
                        search: data.search.value, // Tìm kiếm từ DataTables (nếu có)
                        order: data.order,   // Sắp xếp cột
                        columns: data.columns, // Cột được gửi lên
                        title: $('#filterTitle').val(), // Lọc tiêu đề
                        email: $('#filterEmail').val(), // Lọc email
                        status: $('#filterStatus').val() // Lọc trạng thái
                    }, function (response) {
                        // Callback để DataTable hiển thị dữ liệu
                        callback({
                            draw: response.draw,
                            recordsTotal: response.recordsTotal, // Tổng số bản ghi
                            recordsFiltered: response.recordsFiltered, // Số bản ghi sau khi lọc
                            data: response.data // Dữ liệu trả về
                        });
                    });
                },
                columns: [
                    { // STT
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + 1; // Số thứ tự trong trang hiện tại
                        }
                    },
                    { data: 'title' },
                    { data: 'email.email' },//truyền user lấy ra email
                    { data: 'status' },
                    { data: 'created_at' },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            //  Dùng route name với placeholder + replace
                            const viewUrl = "{{ route('news.show', ':slug') }}".replace(':slug', row.slug);
                            const editUrl = "{{ route('admin.posts.edit', ':id') }}".replace(':id', row.id);

                            return `
                                                    <a href="${viewUrl}" class="btn btn-sm btn-outline-info p-1" target="_blank" title="Xem">
                                                                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                    </a>
                                                    <a href="${editUrl}" class="btn btn-sm btn-outline-warning p-1" title="Sửa">
                                                                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                    </a>
                                                    <button onclick="deletePost(${row.id})" class="btn btn-sm btn-outline-danger p-1" title="Xóa">
                                                                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                    </button>
                                                `;
                        }
                    }
                ],
                language: {
                    "emptyTable": "Không có bài viết nào",
                    "search": "Tìm kiếm:",
                    "zeroRecords": "Không tìm thấy kết quả phù hợp",
                    "lengthMenu": "Hiển thị _MENU_ mục mỗi trang",
                    "info": "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
                    "infoEmpty": "Hiển thị 0 đến 0 của 0 mục",
                    "infoFiltered": "(được lọc từ tổng số _MAX_ mục)",
                    "paginate": {
                        "first": "Đầu tiên",
                        "previous": "Trước",
                        "next": "Sau",
                        "last": "Cuối cùng"
                    }
                }
            });

            table.on('xhr.dt', function (e, settings, json, xhr) {
                if (json.recordsTotal === 0) {
                    $('#btnDeleteAll').hide();
                } else {
                    $('#btnDeleteAll').show();
                }
            });

            $('#searchForm').on('submit', function (e) {
                e.preventDefault();
                table.ajax.reload();
            });
        });

        //  Hàm xoá 1 bài viết dùng route name + replace
        function deletePost(id) {
            if (confirm('Bạn có chắc chắn muốn xóa bài viết này?')) {

                $.ajax({
                    url: "{{ route('admin.posts.destroy', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: @json(csrf_token())
                    },
                    success: function () {
                        table.ajax.reload();
                    },
                    error: function () {
                        alert('Xóa thất bại!');
                    }
                });
            }
        }

        function deleteAllPosts() {
            if (confirm('Bạn có chắc chắn muốn xóa tất cả bài viết?')) {
                $.ajax({
                    url: `{{ route('admin.posts.destroy_all') }}`,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: @json(csrf_token())
                    },
                    success: function () {
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