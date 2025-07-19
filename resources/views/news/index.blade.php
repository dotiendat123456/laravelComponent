@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">
            <span style="border-left: 4px solid red; padding-left: 8px;">TIN MỚI</span>
        </h3>

        <div id="posts-list">
            @forelse ($posts as $post)
                <div class="row mb-4">
                    <div class="col-md-4">
                        @if ($post->thumbnail)
                            <img src="{{ $post->thumbnail }}" alt="{{ $post->title }}" class="img-fluid rounded news-thumbnail">
                        @else
                            <img src="https://via.placeholder.com/400x250?text=No+Image" alt="No Image"
                                class="img-fluid rounded news-thumbnail">
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h5 class="news-title">
                            <a href="{{ route('news.show', $post) }}" class="text-dark fw-bold">
                                {{ $post->title }}
                            </a>
                        </h5>
                        <p class="text-muted small mb-1">
                            {{ $post->publish_date ? $post->publish_date->format('H:i d/m/Y') : '' }}
                        </p>
                        <p class="news-description">
                            {{ $post->description }}
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
        // Khi người dùng bấm vào link phân trang trong #posts-list
        $(document).on('click', '#posts-list .pagination a', function (e) {
            e.preventDefault(); // Ngăn hành vi chuyển trang mặc định (load trang mới)

            let url = $(this).attr('href'); // Lấy URL của link phân trang

            // Gọi Ajax GET đến URL đó
            $.get(url, function (data) {

                // Do server trả về view đầy đủ (kể cả layout), ta chỉ lấy phần #posts-list
                let html = $(data).find('#posts-list').html();

                // Thay thế nội dung hiện tại của #posts-list bằng nội dung mới
                $('#posts-list').html(html);
            }).fail(function () {
                // Nếu có lỗi khi load dữ liệu
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
            /* Giới hạn 2 dòng */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .news-description {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            /* Giới hạn 3 dòng */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@endpush