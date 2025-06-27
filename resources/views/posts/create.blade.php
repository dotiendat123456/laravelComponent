@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Tạo bài viết mới</h3>

        {{-- Thông báo lỗi --}}
        @error('error')
            <div class="alert alert-danger">
                {{ $message }}
            </div>
        @enderror


        {{-- Thông báo thành công --}}
        @if (session('success'))
            <x-alert-success :message="session('success')" />
        @endif

        <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Tiêu đề (bắt buộc) --}}
            <div class="mb-3">
                <label for="title" class="form-label">
                    Tiêu đề <span class="text-danger">*</span>
                </label>
                <input type="text" name="title" id="title" value="{{ old('title') }}"
                    class="form-control @error('title') is-invalid @enderror">
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Mô tả (tùy chọn) --}}
            <div class="mb-3">
                <label for="description" class="form-label">Mô tả</label>
                <input type="text" name="description" id="description" value="{{ old('description') }}"
                    class="form-control @error('description') is-invalid @enderror">
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Nội dung (tùy chọn) --}}
            <div class="mb-3">
                <label for="content" class="form-label">Nội dung</label>
                <textarea name="content" id="content" rows="6"
                    class="form-control @error('content') is-invalid @enderror">{{ old('content') }}</textarea>
                @error('content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Ngày đăng (tùy chọn) --}}
            <div class="mb-3">
                <label for="publish_date" class="form-label">Ngày đăng</label>
                <input type="datetime-local" name="publish_date" id="publish_date" value="{{ old('publish_date') }}"
                    class="form-control @error('publish_date') is-invalid @enderror">
                @error('publish_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Ảnh thumbnail (tùy chọn) --}}
            <div class="mb-3">
                <label for="thumbnail" class="form-label">Thumbnail</label>
                <input type="file" name="thumbnail" id="thumbnail"
                    class="form-control @error('thumbnail') is-invalid @enderror">
                @error('thumbnail')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Nút submit --}}
            <button type="submit" class="btn btn-primary">Tạo bài viết</button>
        </form>
    </div>
@endsection

@push('styles')
    {{-- Summernote CSS (nếu dùng) --}}
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote.min.css" rel="stylesheet">
@endpush

@push('scripts')
    {{-- Summernote JS (nếu dùng) --}}
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#content').summernote({
                height: 200
            });
        });
    </script>
@endpush