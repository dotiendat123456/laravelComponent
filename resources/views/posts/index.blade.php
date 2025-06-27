@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Danh sách bài viết</h3>

        @if (session('success'))
            <x-alert-success :message="session('success')" />
        @endif

        <div class="mb-3 d-flex justify-content-between align-items-center">
            <a href="{{ route('posts.create') }}" class="btn btn-primary">
                Tạo mới bài viết
            </a>

            @if($posts->count())
                <form action="{{ route('posts.destroyAll') }}" method="POST"
                    onsubmit="return confirm('Bạn có chắc chắn muốn xóa tất cả bài viết?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger">
                        Xóa tất cả
                    </button>
                </form>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle table-fixed">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">STT</th>
                        <th style="width: 10%;">Thumbnail</th>
                        <th style="width: 25%;">Tiêu đề</th>
                        <th style="width: 30%;">Mô tả</th>
                        <th style="width: 10%;">Ngày đăng</th>
                        <th style="width: 10%;">Trạng thái</th>
                        <th style="width: 10%;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $index => $post)
                        <tr>
                            <td>{{ $posts->firstItem() + $index }}</td>
                            <td>
                                @if ($post->thumbnail)
                                    <img src="{{ $post->thumbnail }}" width="60" class="rounded border">
                                @else
                                    <span class="text-muted">Không có</span>
                                @endif
                            </td>
                            <td>{{ $post->title }}</td>
                            <td>{{ Str::limit($post->description, 50) }}</td>
                            <td>{{ $post->publish_date ? $post->publish_date->format('d/m/Y') : '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $post->status === 'published' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($post->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-inline-flex align-items-center gap-2">
                                    <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-outline-info p-1">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('posts.edit', $post) }}" class="btn btn-sm btn-outline-warning p-1">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <form action="{{ route('posts.destroy', $post) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger p-1">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Không có bài viết nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $posts->links('pagination::bootstrap-5') }}
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