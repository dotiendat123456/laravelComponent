@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Tạo bài viết mới</h3>

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

        <form id="postForm" action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Tiêu đề --}}
            {{-- <div class="mb-3">
                <label for="title" class="form-label">
                    Tiêu đề <span class="text-danger">*</span>
                </label>
                <input type="text" name="title" id="title" value="{{ old('title') }}"
                    class="form-control @error('title') is-invalid @enderror">
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div> --}}
            <x-form.input 
                name="title" 
                label="Tiêu đề" 
                required 
            />

            {{-- Mô tả --}}
            {{-- <div class="mb-3">
                <label for="description" class="form-label">Mô tả<span class="text-danger">*</span></label>
                <input type="text" name="description" id="description" value="{{ old('description') }}"
                    class="form-control @error('description') is-invalid @enderror">
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div> --}}
            <x-form.input 
                name="description" 
                label="Mô tả" 
                required 
            />

            {{-- Nội dung --}}
            {{-- <div class="mb-3">
                <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                <div id="editor" style="height: 300px;"></div>
                <input type="hidden" name="content" id="content">
                @error('content')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div> --}}
            <div class="mb-3">
                <label class="form-label">Nội dung<span class="text-danger">*</span></label>
                
                <x-quill-editor 
                    name="content" 
                    :value="old('content')" 
                    height="300" 
                />

                @error('content')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>


            {{-- Ngày đăng --}}
            {{-- <div class="mb-3">
                <label for="publish_date" class="form-label">Ngày đăng<span class="text-danger">*</span></label>
                <input type="datetime-local" name="publish_date" id="publish_date" value="{{ old('publish_date') }}"
                    class="form-control @error('publish_date') is-invalid @enderror">
                @error('publish_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div> --}}
            <x-form.input 
                name="publish_date" 
                label="Ngày đăng" 
                type="datetime-local" 
                required
            />

            {{-- Ảnh thumbnail --}}
            {{-- <div class="mb-3">
                <label for="thumbnail" class="form-label">Thumbnail<span class="text-danger">*</span></label>
                <input type="file" name="thumbnail" id="thumbnail"
                    class="form-control @error('thumbnail') is-invalid @enderror">
                @error('thumbnail')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div> --}}
            <x-form.file-input 
                name="thumbnail" 
                label="Thumbnail" 
                :required="true" 
            />

            {{-- Nút submit --}}
            <button type="submit" class="btn btn-primary">Tạo bài viết</button>
        </form>
    </div>
@endsection

{{-- @push('styles')
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

@endpush --}}