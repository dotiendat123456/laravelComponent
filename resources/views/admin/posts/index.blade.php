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
        <form class="row g-3 mb-3" method="GET" action="{{ route('admin.posts.index') }}">
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
            <table class="table table-striped table-hover align-middle table-fixed">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 25%;">Tiêu đề</th>
                        <th style="width: 15%;">User Email</th>
                        <th style="width: 15%;">Trạng thái</th>
                        <th style="width: 20%;">Ngày tạo</th>
                        <th style="width: 20%;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr>
                            <td>{{ $post->id }}</td>
                            <td>{{ $post->title }}</td>
                            <td>{{ $post->user->email }}</td>
                            <td>
                                <span class="badge 
                                    @switch($post->status)
                                        @case(0) bg-secondary @break
                                        @case(1) bg-success @break
                                        @case(2) bg-danger @break
                                        @default bg-dark
                                    @endswitch">
                                    @switch($post->status)
                                        @case(0) Bài mới @break
                                        @case(1) Đã phê duyệt @break
                                        @case(2) Từ chối @break
                                        @default Không rõ
                                    @endswitch
                                </span>
                            </td>
                            <td>{{ $post->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="d-inline-flex align-items-center gap-2">
                                    <!-- Xem chi tiết -->
                                    <a href="{{ route('news.show', $post->slug) }}"
                                       class="btn btn-sm btn-outline-info p-1"
                                       target="_blank" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <!-- Sửa -->
                                    <a href="{{ route('admin.posts.edit', $post->id) }}"
                                       class="btn btn-sm btn-outline-warning p-1"
                                       title="Sửa">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <!-- Xóa -->
                                    <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger p-1" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Không có bài viết nào.</td>
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
