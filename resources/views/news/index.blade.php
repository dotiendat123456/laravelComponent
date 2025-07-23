@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">
            <span style="border-left: 4px solid red; padding-left: 8px;">TIN MỚI</span>
        </h3>

        <div id="posts-list">
            @forelse ($posts as $post)
                <div class="row mb-4 pb-3 border-bottom">
                    <div class="col-md-4">
                        @if ($post->thumbnail)
                            <img src="{{ $post->thumbnail }}" alt="{{ $post->title }}" class="img-fluid rounded news-thumbnail">
                        @else
                            <img src="https://via.placeholder.com/400x250?text=No+Image" alt="No Image"
                                class="img-fluid rounded news-thumbnail">
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h5 class="news-title mb-1">
                            <a href="{{ route('news.show', $post) }}" class="text-dark fw-bold">
                                {{ $post->title }}
                            </a>
                        </h5>

                        <p class="text-muted small mb-2">
                            Ngày đăng: {{ $post->publish_date ? $post->publish_date->format('H:i d/m/Y') : '' }}
                        </p>

                        <p class="news-description mb-2">
                            {{ $post->description }}
                        </p>

                        <p class="text-muted small mt-2 mb-0">
                            Like: {{ $post->likes_count ?? $post->likes()->count() }} |
                            Dislike: {{ $post->dislikes_count ?? $post->dislikes()->count() }} |
                            Bình luận: {{ $post->comments_count ?? $post->comments()->count() }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-muted">Hiện chưa có bài viết nào được phê duyệt.</p>
            @endforelse

            {{ $posts->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Phân trang AJAX
        $(document).on('click', '#posts-list .pagination a', function (e) {
            e.preventDefault();
            let url = $(this).attr('href');
            $.get(url, function (data) {
                let html = $(data).find('#posts-list').html();
                $('#posts-list').html(html);
            }).fail(function () {
                alert('Lỗi khi tải trang.');
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .news-thumbnail {
            height: 200px;
            width: 100%;
            object-fit: cover;
        }

        .news-title a {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .news-description {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@endpush