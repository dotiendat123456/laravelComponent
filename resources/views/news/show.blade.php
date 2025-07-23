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

        {{-- Reaction Section --}}
        <div class="mb-4 d-flex gap-2">
            @if(Auth::check())
                <form method="POST" action="{{ route('posts.react', [$post, 'like']) }}" class="react-form" data-type="like">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        Like (<span id="like-count">{{ $post->likes()->count() }}</span>)
                    </button>
                </form>

                <form method="POST" action="{{ route('posts.react', [$post, 'dislike']) }}" class="react-form"
                    data-type="dislike">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        Dislike (<span id="dislike-count">{{ $post->dislikes()->count() }}</span>)
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-success">
                    Like (<span id="like-count">{{ $post->likes()->count() }}</span>)
                </a>
                <a href="{{ route('login') }}" class="btn btn-danger">
                    Dislike (<span id="dislike-count">{{ $post->dislikes()->count() }}</span>)
                </a>
            @endif
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

        <div id="comment-list" >
            @foreach ($post->comments->where('parent_id', null) as $comment)
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
                            document.getElementById('like-count').textContent = data.like_count;
                            document.getElementById('dislike-count').textContent = data.dislike_count;
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
                                const parentId = form.querySelector('input[name="parent_id"]').value;

                                if (parentId) {
                                    // Nếu là phản hồi
                                    const repliesContainer = document.querySelector('#replies-' + parentId);
                                    if (repliesContainer) {
                                        repliesContainer.insertAdjacentHTML('beforeend', data.html);
                                    }
                                    form.reset();
                                    form.parentElement.style.display = 'none';
                                } else {
                                    // Nếu là bình luận gốc
                                    const commentList = document.querySelector('#comment-list');
                                    if (commentList) {
                                        commentList.insertAdjacentHTML('beforeend', data.html);
                                    }
                                    form.reset();
                                }

                                //  Luôn tăng số lượng bình luận (cả bình luận và phản hồi)
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
                    const container = document.getElementById(e.target.dataset.target);
                    if (container) {
                        if (container.style.display === 'none' || container.classList.contains('d-none')) {
                            container.style.display = 'block';
                            container.classList.remove('d-none');
                            e.target.textContent = 'Ẩn phản hồi';
                        } else {
                            container.style.display = 'none';
                            container.classList.add('d-none');
                            const count = container.children.length;
                            e.target.textContent = count + ' phản hồi';
                        }
                    }
                }
            });

        });
    </script>
@endpush