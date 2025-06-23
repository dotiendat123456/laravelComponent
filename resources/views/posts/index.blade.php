@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Danh sách bài viết</h2>

        @if (session('success'))
            <x-alert-success :message="session('success')" />
        @endif

        <a href="{{ route('posts.create') }}" class="btn btn-success mb-3">Tạo mới</a>

        <ul class="list-group">
            @forelse ($posts as $post)
                <li class="list-group-item">{{ $post['title'] }}</li>
            @empty
                <li class="list-group-item">Chưa có bài viết nào.</li>
            @endforelse
        </ul>
    </div>
@endsection