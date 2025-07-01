@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Tạo bài viết mới</h3>

        {{-- Thông báo lỗi --}}
        @error('error')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        {{-- Thông báo thành công --}}
        @if (session('success'))
            <x-alert-success :message="session('success')" />
        @endif

        <form id="postForm" action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
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

            {{-- Nội dung (Quill Editor) --}}
            <div class="mb-3">
                <label class="form-label">Nội dung</label>
                <div id="editor" style="height: 300px;">{!! old('content') !!}</div>
                <input type="hidden" name="content" id="content">
                @error('content')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Ngày đăng --}}
            <div class="mb-3">
                <label for="publish_date" class="form-label">Ngày đăng</label>
                <input type="datetime-local" name="publish_date" id="publish_date" value="{{ old('publish_date') }}"
                    class="form-control @error('publish_date') is-invalid @enderror">
                @error('publish_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Ảnh thumbnail --}}
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
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        const quill = new Quill('#editor', {
            theme: 'snow'
        });

        document.getElementById('postForm').addEventListener('submit', function (e) {
            document.getElementById('content').value = quill.root.innerHTML;
        });
    </script>
@endpush