@extends('layouts.app')

@section('content')
    @if (session('success'))
        <x-alert-success :message="session('success')" />
    @endif

    <div class="container">
        <h1 class="mb-2">{{ $post->title }}</h1>
        <p class="text-muted">{{ $post->publish_date ? $post->publish_date->format('H:i d/m/Y') : '' }}</p>
        <p class="lead">{{ $post->description }}</p>
        <div>{!! $post->content !!}</div>

        <hr>
        @guest
            <div class="alert alert-warning d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill"></i>
                <span>
                    <a href="{{ route('login') }}">Đăng nhập</a> để thích và bình luận bài viết.
                </span>
            </div>
        @endguest


        {{-- Reaction Section --}}
        <div class="mb-4 d-flex gap-2">
            @auth
                @php
                    // $userReaction = optional($post->userReaction);
                    // $isLiked = $userReaction->type === true;
                    // $isDisliked = $userReaction->type === false;
                    $userReaction = optional($post->userReaction);
                    $isLiked = $userReaction->type?->value === \App\Enums\ReactionType::LIKE->value;
                    $isDisliked = $userReaction->type?->value === \App\Enums\ReactionType::DISLIKE->value;
                @endphp

                <form method="POST" action="{{ route('posts.react', [$post, \App\Enums\ReactionType::LIKE->value]) }}"
                    class="react-form" data-type="like">
                    @csrf
                    <button type="submit"
                        class="btn {{ $isLiked ? 'btn-success' : 'btn-outline-success' }} d-flex align-items-center gap-1 react-btn"
                        data-button="like">
                        <i class="bi {{ $isLiked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }}"></i>
                        <span id="like-count">{{ $post->likes_count }}</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('posts.react', [$post, \App\Enums\ReactionType::DISLIKE->value]) }}"
                    class="react-form" data-type="dislike">
                    @csrf
                    <button type="submit"
                        class="btn {{ $isDisliked ? 'btn-danger' : 'btn-outline-danger' }} d-flex align-items-center gap-1 react-btn"
                        data-button="dislike">
                        <i class="bi {{ $isDisliked ? 'bi-hand-thumbs-down-fill' : 'bi-hand-thumbs-down' }}"></i>
                        <span id="dislike-count">{{ $post->dislikes_count }}</span>
                    </button>
                </form>

            @endauth
        </div>


        {{-- Comment Section --}}
        <h4>Bình luận (<span id="comment-count">{{ $post->comments->count() }}</span>)</h4>

        @auth
            <form method="POST" class="reply-submit-form mb-4" action="{{ route('posts.comments.store', $post) }}">
                @csrf
                <input type="hidden" name="parent_id" value="">
                <input type="hidden" name="level" value="0">
                <div class="mb-3">
                    <textarea name="content" class="form-control" placeholder="Nhập bình luận..." required></textarea>
                </div>
                <button type="submit" class="btn btn-success">Gửi bình luận</button>
            </form>
        @else
            <p><a href="{{ route('login') }}">Đăng nhập</a> để bình luận.</p>
        @endauth

        <div id="comment-list">
            @foreach ($comments as $comment)
                @include('news.single_comment', ['comment' => $comment, 'post' => $post, 'level' => 0])
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Like/Dislike bằng fetch
            document.querySelectorAll('.react-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    })
                        .then(res => res.json())
                        .then(data => {
                            // Cập nhật số lượng
                            document.getElementById('like-count').textContent = data.like_count;
                            document.getElementById('dislike-count').textContent = data.dislike_count;

                            // Nút và icon
                            const likeBtn = document.querySelector('[data-button="like"]');
                            const dislikeBtn = document.querySelector('[data-button="dislike"]');
                            const likeIcon = likeBtn.querySelector('i');
                            const dislikeIcon = dislikeBtn.querySelector('i');

                            // Reset tất cả
                            likeBtn.classList.remove('btn-success');
                            likeBtn.classList.add('btn-outline-success');
                            likeIcon.className = 'bi bi-hand-thumbs-up';

                            dislikeBtn.classList.remove('btn-danger');
                            dislikeBtn.classList.add('btn-outline-danger');
                            dislikeIcon.className = 'bi bi-hand-thumbs-down';

                            // Apply trạng thái mới
                            if (data.current_reaction === 'like') {
                                likeBtn.classList.remove('btn-outline-success');
                                likeBtn.classList.add('btn-success');
                                likeIcon.className = 'bi bi-hand-thumbs-up-fill';
                            }

                            if (data.current_reaction === 'dislike') {
                                dislikeBtn.classList.remove('btn-outline-danger');
                                dislikeBtn.classList.add('btn-danger');
                                dislikeIcon.className = 'bi bi-hand-thumbs-down-fill';
                            }
                        })
                        .catch(err => console.error('Lỗi phản hồi:', err));
                });
            });



            // Gửi bình luận / phản hồi
            document.addEventListener('submit', function (e) {
                if (e.target.matches('.reply-submit-form')) {
                    e.preventDefault();
                    const form = e.target;
                    const formData = new FormData(form);

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.html) {
                                const parentId = data.parent_id;

                                if (parentId) {
                                    // Nếu là phản hồi
                                    const repliesContainer = document.getElementById('replies-' + parentId);
                                    const toggleBtn = document.querySelector('[data-target="replies-' + parentId + '"].toggle-replies');

                                    if (repliesContainer) {
                                        repliesContainer.insertAdjacentHTML('beforeend', data.html);
                                        repliesContainer.classList.remove('d-none');
                                        repliesContainer.style.display = 'block';
                                    }

                                    if (toggleBtn && data.reply_count !== null) {
                                        toggleBtn.textContent = 'Ẩn phản hồi';
                                        toggleBtn.classList.remove('d-none');
                                    }

                                    // Reset form & ẩn
                                    form.reset();
                                    form.parentElement.style.display = 'none';
                                } else {
                                    // Nếu là bình luận gốc
                                    const commentList = document.getElementById('comment-list');
                                    if (commentList) {
                                        commentList.insertAdjacentHTML('beforeend', data.html);
                                    }
                                    form.reset();
                                }

                                // Tăng số bình luận
                                const countEl = document.getElementById('comment-count');
                                if (countEl) {
                                    const current = parseInt(countEl.textContent);
                                    countEl.textContent = current + 1;
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Lỗi gửi bình luận:', error);
                        });
                }

            });



            // Xoá bình luận
            document.addEventListener('submit', function (e) {
                if (e.target.matches('.delete-comment-form')) {
                    e.preventDefault();
                    if (!confirm('Bạn chắc chắn muốn xoá bình luận này?')) return;

                    const form = e.target;
                    const commentId = form.dataset.id;

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new URLSearchParams({ _method: 'DELETE' })
                    })
                        .then(res => res.json())
                        .then(data => {
                            const commentElement = document.getElementById('comment-' + commentId);

                            if (commentElement) {
                                // Đếm tất cả các bình luận con (bao gồm chính nó)
                                const deletedComments = commentElement.querySelectorAll('[id^="comment-"]').length + 1;

                                // Xoá phần tử gốc
                                commentElement.remove();

                                // Giảm số lượng bình luận hiển thị
                                const countEl = document.getElementById('comment-count');
                                if (countEl) {
                                    const current = parseInt(countEl.textContent);
                                    countEl.textContent = Math.max(0, current - deletedComments);
                                }
                            }
                        });
                }
            });


            // Toggle hiển thị form phản hồi và replies
            document.addEventListener('click', function (e) {
                if (e.target.matches('.show-reply-form')) {
                    const target = document.getElementById(e.target.dataset.target);
                    if (target) target.style.display = 'block';
                }

                if (e.target.matches('.cancel-reply')) {
                    const target = document.getElementById(e.target.dataset.target);
                    if (target) target.style.display = 'none';
                }

                if (e.target.matches('.toggle-replies')) {
                    const targetId = e.target.dataset.target;
                    const container = document.getElementById(targetId);
                    if (container) {
                        const isHidden = container.classList.contains('d-none') || container.style.display === 'none';

                        if (isHidden) {
                            container.classList.remove('d-none');
                            container.style.display = 'block';
                            e.target.textContent = 'Ẩn phản hồi';
                        } else {
                            container.classList.add('d-none');
                            container.style.display = 'none';

                            // Tính lại số phản hồi (chỉ đếm phần tử có id="comment-xxx")
                            const replyCount = container.querySelectorAll('[id^="comment-"]').length;
                            e.target.textContent = 'Hiện ' + replyCount + ' phản hồi';
                        }
                    }
                }

            });

        });
    </script>
@endpush