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
                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
            </div>
        </form>

        <!-- NÚT TẠO MỚI TRÁI & XÓA TẤT CẢ PHẢI -->
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
            <div id="postsTable">
                @include('admin.posts._table', ['posts' => $posts])
            </div>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Chặn submit ➜ AJAX tìm kiếm
        $('#searchForm').on('submit', function (e) {
            e.preventDefault();
            let query = $(this).serialize();
            fetchPosts(query);
        });

        // Bấm phân trang ➜ AJAX
        $(document).on('click', '.pagination a', function (e) {
            e.preventDefault();
            let url = $(this).attr('href');
            let query = url.split('?')[1];
            fetchPosts(query);
        });

        function fetchPosts(query) {
            $.ajax({
                url: "{{ route('admin.posts.index') }}" + '?' + query,
                success: function (data) {
                    $('#postsTable').html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
    </script>
@endpush