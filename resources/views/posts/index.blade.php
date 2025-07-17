{{-- @extends('layouts.app')

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

        <button id="btnDeleteAll" type="button" onclick="deleteAllPosts()" class="btn btn-outline-danger">
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
            // ajax: function (data, callback) {
            //     const page = (data.start / data.length) + 1;

            //     $.get('{{ route('posts.data') }}', {
            //         page: page, // Laravel nhận đúng param này!
            //         length: data.length,
            //         search: data.search.value,
            //         order: data.order,
            //         columns: data.columns,
            //         draw: data.draw
            //     }, function (response) {
            //         callback({
            //             draw: response.draw,
            //             recordsTotal: response.recordsTotal,
            //             recordsFiltered: response.recordsFiltered,
            //             data: response.data
            //         });
            //     });
            // },


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
                        const viewUrl = "{{ route('news.show', ':slug') }}".replace(':slug', row.slug);
                        const editUrl = "{{ route('posts.edit', ':id') }}".replace(':id', row.id);

                        return `
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <a href="${viewUrl}" class="btn btn-sm btn-outline-info p-1" target="_blank" title="Xem">
                                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        </a>
                                        <a href="${editUrl}" class="btn btn-sm btn-outline-warning p-1" title="Sửa">
                                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                        <button onclick="deletePost(${row.id})" class="btn btn-sm btn-outline-danger p-1" title="Xóa">
                                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </div>`;
                    }


                }
            ],

            language: {
                url: '//cdn.datatables.net/plug-ins/2.3.2/i18n/vi.json'
            }
        });
        table.on('xhr.dt', function (e, settings, json, xhr) {
            if (json.recordsTotal === 0) {
                $('#btnDeleteAll').hide();
            } else {
                $('#btnDeleteAll').show();
            }
        });
    });

    function deletePost(id) {
        if (confirm('Bạn có chắc chắn muốn xóa?')) {
            $.ajax({
                // url: `/posts/${id}`,
                url: `{{ route('posts.destroy', ':id') }}`.replace(':id', id),
                type: 'POST',
                data: { _method: 'DELETE', _token: @json(csrf_token()) },
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
                data: { _method: 'DELETE', _token: @json(csrf_token()) },
                success: function () { table.ajax.reload(); },
                error: function () { alert('Xóa tất cả thất bại!'); }
            });
        }
    }
</script>
@endpush --}}



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

            <button id="btnDeleteAll" type="button" onclick="deleteAllPosts()" class="btn btn-outline-danger">
                <i class="bi bi-trash"></i> Xóa tất cả
            </button>
        </div>

        <div class="table-responsive">
            <table id="postsTable" class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>STT</th>
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
                processing: true,
                serverSide: true,
                ordering: true,
                searching: true,
                pageLength: 5,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                // order: [[, 'desc']], // Sắp xếp theo ID thực tế để lấy bài viết mới nhất (không ảnh hưởng STT)

                ajax: '{{ route('posts.data') }}',

                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'thumbnail', orderable: false, searchable: false,
                        render: function (data) {
                            return data ? `<img src="${data}" alt="Thumbnail" width="80">` : '-';
                        }
                    },
                    { data: 'title' },
                    { data: 'description' },
                    { data: 'publish_date' },
                    { data: 'status' },
                    {
                        data: null, orderable: false, searchable: false,
                        render: function (data, type, row) {
                            const viewUrl = "{{ route('news.show', ':slug') }}".replace(':slug', row.slug);
                            const editUrl = "{{ route('posts.edit', ':id') }}".replace(':id', row.id);

                            return `
                                                                        <div class="d-inline-flex align-items-center gap-1">
                                                                            <a href="${viewUrl}" class="btn btn-sm btn-outline-info p-1" target="_blank" title="Xem">
                                                                                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                                            </a>
                                                                            <a href="${editUrl}" class="btn btn-sm btn-outline-warning p-1" title="Sửa">
                                                                                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                                            </a>
                                                                            <button onclick="deletePost(${row.id})" class="btn btn-sm btn-outline-danger p-1" title="Xóa">
                                                                                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                                            </button>
                                                                        </div>`;
                        }
                    }
                ],

                // language: {
                //     url: '//cdn.datatables.net/plug-ins/2.3.2/i18n/vi.json'
                // }
                language: {
                    "emptyTable": "Không có bài viết nào",
                    "search": "Tìm kiếm:",
                    "zeroRecords": "Không tìm thấy kết quả phù hợp",
                    "lengthMenu": "Hiển thị _MENU_ mục mỗi trang",
                    "info": "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
                    "infoEmpty": "Hiển thị 0 đến 0 của 0 mục",
                    "infoFiltered": "(được lọc từ tổng số _MAX_ mục)",
                    "paginate": {
                        "first": "Đầu tiên",   // Nút về trang đầu
                        "previous": "Trước", // Nút trang trước
                        "next": "Sau",     // Nút trang sau
                        "last": "Cuối cùng"     // Nút về trang cuối
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
        });

        function deletePost(id) {
            if (confirm('Bạn có chắc chắn muốn xóa?')) {
                $.ajax({
                    url: `{{ route('posts.destroy', ':id') }}`.replace(':id', id),
                    type: 'POST',
                    data: { _method: 'DELETE', _token: @json(csrf_token()) },
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
                    data: { _method: 'DELETE', _token: @json(csrf_token()) },
                    success: function () { table.ajax.reload(); },
                    error: function () { alert('Xóa tất cả thất bại!'); }
                });
            }
        }
    </script>
@endpush