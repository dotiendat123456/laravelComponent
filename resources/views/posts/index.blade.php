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
        new DataTable('#postsTable', {
            processing: true,
            serverSide: true,
            searching: true,
            pageLength: 3,
            lengthMenu: [[3, 5, 10, 25, 50], [3, 5, 10, 25, 50]],
            ajax: '{{ route('posts.data') }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'title', name: 'title' },
                { data: 'description', name: 'description' },
                { data: 'publish_date', name: 'publish_date' },
                { data: 'status', name: 'status' },
                {
                    render: function (data, type, row) {
                        return `
                                                                            <a href="/posts/${row.id}" class="btn btn-sm btn-outline-info p-1">
                                                                                <i class="fa-solid fa-eye"></i>
                                                                            </a>
                                                                            <a href="/posts/${row.id}/edit" class="btn btn-sm btn-outline-warning p-1">
                                                                                <i class="fa-solid fa-edit"></i>
                                                                            </a>
                                                                            <button onclick="deletePost(${row.id})" class="btn btn-sm btn-outline-danger p-1">
                                                                                <i class="fa-solid fa-trash"></i>
                                                                            </button>
                                                                        `;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/2.0.0/i18n/vi.json'
            }
        });

        function deletePost(id) {
            if (confirm('Bạn có chắc chắn muốn xóa?')) {
                $.ajax({
                    url: `/posts/${id}`,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function () {
                        $('#postsTable').DataTable().ajax.reload();
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
                    url: `{{ route('posts.destroyAll') }}`,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function () {
                        $('#postsTable').DataTable().ajax.reload();
                    },
                    error: function () {
                        alert('Xóa tất cả thất bại!');
                    }
                });
            }
        }

    </script>
@endpush