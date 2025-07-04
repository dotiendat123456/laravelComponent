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

        <!-- FORM TÌM KIẾM (nếu vẫn muốn filter trước khi render) -->
        <form id="searchForm" class="row g-3 mb-3" method="GET" action="{{ route('admin.posts.index') }}">
            <div class="col-auto">
                <input type="text" name="title" value="{{ request('title') }}" class="form-control"
                    placeholder="Tìm theo tiêu đề">
            </div>
            <div class="col-auto">
                <input type="text" name="email" value="{{ request('email') }}" class="form-control"
                    placeholder="Tìm theo email user">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Lọc</button>
            </div>
        </form>

        <!-- NÚT TẠO MỚI & XÓA TẤT CẢ -->
        <div class="mb-3 d-flex justify-content-between">
            <a href="{{ route('admin.posts.create') }}" class="btn btn-success">
                <i class="fa-solid fa-plus"></i> Tạo mới
            </a>

            @if ($posts->count())
                <form action="{{ route('admin.posts.destroyAll') }}" method="POST"
                    onsubmit="return confirm('Bạn có chắc chắn muốn xóa tất cả bài viết?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger">
                        <i class="fa-solid fa-trash"></i> Xóa tất cả
                    </button>
                </form>
            @endif
        </div>

        <!-- BẢNG -->
        <div class="table-responsive">
            @include('admin.posts._table', ['posts' => $posts])
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .table-fixed {
            table-layout: fixed;
            width: 100%;
        }

        #postsTable tbody tr {
            height: 70px;
            /* 👈 Chiều cao hàng cố định */
        }

        #postsTable td {
            vertical-align: middle;
            /* 👈 Canh giữa nội dung theo chiều dọc */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Nếu muốn mô tả nhiều dòng vẫn ẩn */
        #postsTable td .description {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            /* Số dòng tối đa */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#postsTable').DataTable({
                pageLength: 5, // Hiển thị 5 dòng mặc định
                lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
                ordering: false,
                searching: false,
                language: {
                    url: '//cdn.datatables.net/plug-ins/2.0.0/i18n/vi.json'
                }
            });
        });
    </script>
@endpush