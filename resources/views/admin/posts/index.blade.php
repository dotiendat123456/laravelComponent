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

            <button id="btnDeleteAll" type="button" onclick="deleteAllPosts()" class="btn btn-outline-danger">
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
                order: [[0, 'desc']],
                ajax: {
                    url: '{{ route('admin.posts.data') }}',
                    data: function (d) {
                        d.title = $('input[name=title]').val();
                        d.email = $('input[name=email]').val();
                    }
                },
                columns: [
                    { data: 'id' },
                    { data: 'title' },
                    { data: 'email' },
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
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="${editUrl}" class="btn btn-sm btn-outline-warning p-1" title="Sửa">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                                <button onclick="deletePost(${row.id})" class="btn btn-sm btn-outline-danger p-1" title="Xóa">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            `;
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/vi.json'
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
                    url: `{{ route('admin.posts.destroyAll') }}`,
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