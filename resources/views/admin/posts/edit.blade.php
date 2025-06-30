@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Chỉnh sửa bài viết</h3>

        {{-- Thông báo lỗi --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Vui lòng kiểm tra dữ liệu:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Thông báo thành công --}}
        @if (session('success'))
            <x-alert-success :message="session('success')" />
        @endif

        <form id="postForm" action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Tiêu đề --}}
            <div class="mb-3">
                <label for="title" class="form-label">Tiêu đề</label>
                <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}"
                    class="form-control @error('title') is-invalid @enderror">
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Mô tả --}}
            <div class="mb-3">
                <label for="description" class="form-label">Mô tả</label>
                <input type="text" name="description" id="description" value="{{ old('description', $post->description) }}"
                    class="form-control @error('description') is-invalid @enderror">
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Nội dung --}}
            <div class="mb-3">
                <label class="form-label">Nội dung</label>
                <div id="editor" style="height: 300px;">{!! old('content', $post->content) !!}</div>
                <input type="hidden" name="content" id="content">
                @error('content')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Ngày đăng --}}
            <div class="mb-3">
                <label for="publish_date" class="form-label">Ngày đăng</label>
                <input type="datetime-local" name="publish_date" id="publish_date"
                    value="{{ old('publish_date', $post->publish_date ? $post->publish_date->format('Y-m-d\TH:i') : '') }}"
                    class="form-control @error('publish_date') is-invalid @enderror">
                @error('publish_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Thumbnail --}}
            <div class="mb-3">
                <label for="thumbnail" class="form-label">Thumbnail</label>
                <input type="file" name="thumbnail" id="thumbnail"
                    class="form-control @error('thumbnail') is-invalid @enderror">
                @error('thumbnail')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                @if ($post->getFirstMediaUrl('thumbnails'))
                    <div class="mt-2">
                        <img src="{{ $post->getFirstMediaUrl('thumbnails') }}" alt="Thumbnail hiện tại"
                            style="max-width: 200px;">
                    </div>
                @endif
            </div>

            {{-- Trạng thái --}}
            @if (Auth::user()->isAdmin())
                <div class="mb-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="0" {{ old('status', $post->status) == 0 ? 'selected' : '' }}>Bài viết mới</option>
                        <option value="1" {{ old('status', $post->status) == 1 ? 'selected' : '' }}>Đã phê duyệt</option>
                        <option value="2" {{ old('status', $post->status) == 2 ? 'selected' : '' }}>Khác</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @else
                <input type="hidden" name="status" value="{{ $post->status }}">
                <div class="mb-3">
                    <label class="form-label">Trạng thái</label>
                    <input type="text"
                        class="form-control"
                        value="@switch($post->status)
                                    @case(0) Bài viết mới @break
                                    @case(1) Đã cập nhật @break
                                    @case(2) Khác @break
                                    @default Không rõ
                                @endswitch"
                        readonly>
                </div>
            @endif

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

        document.getElementById('postForm').addEventListener('submit', function (e) {
            document.getElementById('content').value = quill.root.innerHTML;
        });
    </script>
@endpush
