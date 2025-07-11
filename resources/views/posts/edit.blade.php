@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Chỉnh sửa bài viết</h3>

        {{-- Thông báo lỗi --}}

        @error('error')
            <div class="alert alert-danger" role="alert">
                {{ $message }}
            </div>
        @enderror
        {{-- Thông báo thành công --}}
        @if (session('success'))
            <x-alert-success :message="session('success')" />
        @endif

        <form id="postForm" action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Tiêu đề --}}
            <div class="mb-3">
                <label for="title" class="form-label">Tiêu đề<span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}"
                    class="form-control @error('title') is-invalid @enderror">
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Mô tả --}}
            <div class="mb-3">
                <label for="description" class="form-label">Mô tả<span class="text-danger">*</span></label>
                <input type="text" name="description" id="description" value="{{ old('description', $post->description) }}"
                    class="form-control @error('description') is-invalid @enderror">
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Nội dung --}}
            <div class="mb-3">
                <label class="form-label">Nội dung<span class="text-danger">*</span></label>
                <div id="editor" style="height: 300px;">{!! old('content', $post->content) !!}</div>
                <input type="hidden" name="content" id="content">
                @error('content')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Ngày đăng --}}
            <div class="mb-3">
                <label for="publish_date" class="form-label">Ngày đăng<span class="text-danger">*</span></label>
                <input type="datetime-local" name="publish_date" id="publish_date"
                    value="{{ old('publish_date', $post->publish_date ? $post->publish_date->format('Y-m-d\TH:i') : '') }}"
                    class="form-control @error('publish_date') is-invalid @enderror">
                @error('publish_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Thumbnail --}}
            <div class="mb-3">
                <label for="thumbnail" class="form-label">Thumbnail<span class="text-danger">*</span></label>
                <input type="file" name="thumbnail" id="thumbnail"
                    class="form-control @error('thumbnail') is-invalid @enderror">
                @error('thumbnail')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                @if ($post->thumbnail)
                    <div class="mt-2">
                        <img src="{{ asset($post->thumbnail) }}" alt="Thumbnail hiện tại" style="max-width: 200px;">
                    </div>
                @endif
            </div>


            @can('updateStatus', $post)
                <div class="mb-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                        @foreach (\App\Enums\PostStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ old('status', $post->status->value) == $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>

                </div>
            @endcan


            {{-- Submit --}}
            <button type="submit" class="btn btn-primary">Cập nhật bài viết</button>
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

        // Lấy old content từ Blade (escape quote cẩn thận)
        const oldContent = `{!! old('content') !!}`;
        if (oldContent) {
            quill.root.innerHTML = oldContent;
        }

        document.getElementById('postForm').addEventListener('submit', function (e) {
            // console.log('Quill HTML khi submit:', quill.root.innerHTML);
            // console.log('Quill Delta khi submit:', quill.getContents());
            // console.log('Quill Text khi submit:', quill.getText());
            const plainText = quill.getText().trim();
            if (plainText === '') {
                document.getElementById('content').value = '';
            } else {
                document.getElementById('content').value = quill.root.innerHTML;
            }
        });
    </script>

@endpush