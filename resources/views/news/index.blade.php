@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4"><span style="border-left: 4px solid red; padding-left: 8px;">TIN MỚI</span></h3>

        @forelse ($posts as $post)
            <div class="row mb-4">
                <div class="col-md-4">
                    @if ($post->getFirstMediaUrl('thumbnails'))
                        <img src="{{ $post->getFirstMediaUrl('thumbnails') }}" alt="{{ $post->title }}" class="img-fluid rounded">
                    @else
                        <img src="https://via.placeholder.com/400x250?text=No+Image" class="img-fluid rounded">
                    @endif
                </div>
                <div class="col-md-8">
                    <h5>
                        <a href="{{ route('news.show', $post->slug) }}" class="text-dark fw-bold">
                            {{ $post->title }}
                        </a>
                    </h5>
                    <p class="text-muted small mb-1">
                        {{ $post->publish_date ? $post->publish_date->format('H:i d/m/Y') : '' }}
                    </p>
                    <p>{{ $post->description }}</p>
                </div>
            </div>
        @empty
            <p class="text-muted">Hiện chưa có bài viết nào được phê duyệt.</p>
        @endforelse

        {{ $posts->links('pagination::bootstrap-5') }}
    </div>
@endsection