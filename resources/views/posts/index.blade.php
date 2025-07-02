@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Danh sách bài viết</h3>

        @if (session('success'))
            <x-alert-success :message="session('success')" />
        @endif

        @error('error')
            <div class="alert alert-danger" role="alert">
                {{ $message }}
            </div>
        @enderror

        {{-- FORM TÌM KIẾM --}}
        <form method="GET" action="{{ route('posts.index') }}" id="filterForm" class="mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <input type="text" name="title" class="form-control" placeholder="Tìm theo tiêu đề"
                        value="{{ request('title') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        Tìm kiếm
                    </button>
                </div>
            </div>
        </form>

        <div class="mb-3 d-flex justify-content-between align-items-center">
            <a href="{{ route('posts.create') }}" class="btn btn-success">
                <i class="bi bi-plus"></i> Tạo mới
            </a>

            @if($posts->count())
                <form action="{{ route('posts.destroyAll') }}" method="POST"
                    onsubmit="return confirm('Bạn có chắc chắn muốn xóa tất cả bài viết?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger">
                        <i class="bi bi-trash"></i> Xóa tất cả
                    </button>
                </form>
            @endif
        </div>

        <div class="table-responsive">
            <div id="postsTable">
                @include('posts._table', ['posts' => $posts])
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
        $(document).on('click', '.pagination a', function (e) {
            e.preventDefault();
            let url = $(this).attr('href');
            fetchPosts(url);
        });

        $('#filterForm').on('submit', function (e) {
            e.preventDefault();
            let url = $(this).attr('action') + '?' + $(this).serialize();
            fetchPosts(url);
        });

        function fetchPosts(url) {
            $.ajax({
                url: url,
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