@php
    $margin = $level * 30;
@endphp

<div id="comment-{{ $comment->id }}" style="margin-left: {{ $margin }}px; margin-bottom: 15px;">
    <strong>{{ $comment->user->name }}</strong> -
    <small>{{ $comment->created_at->format('d/m/Y H:i') }}</small>
    <p>{{ $comment->content }}</p>

    @auth
        {{-- Nút phản hồi --}}
        <button class="btn btn-sm btn-outline-secondary show-reply-form" data-target="reply-form-{{ $comment->id }}">
            Phản hồi
        </button>

        {{-- Nút xoá nếu là chủ sở hữu hoặc admin --}}
        @if (auth()->id() === $comment->user_id || auth()->user()->isAdmin())
            <form method="POST" class="d-inline delete-comment-form" data-id="{{ $comment->id }}"
                action="{{ route('posts.comments.destroy', $comment->id) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger ms-2">Xoá</button>
            </form>
        @endif

        {{-- Form phản hồi (ẩn) --}}
        <div id="reply-form-{{ $comment->id }}" class="reply-form" style="display:none; margin-top:10px;">
            <form method="POST" class="reply-submit-form" data-parent="{{ $comment->id }}"
                action="{{ route('posts.comments.store', $post) }}">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <input type="hidden" name="level" value="{{ $level + 1 }}">
                <textarea name="content" class="form-control mb-2" placeholder="Viết phản hồi..." required></textarea>
                <button type="submit" class="btn btn-sm btn-primary">Gửi</button>
                <button type="button" class="btn btn-sm btn-light cancel-reply"
                    data-target="reply-form-{{ $comment->id }}">Hủy</button>
            </form>
        </div>
    @endauth

    {{-- Hiển thị replies đệ quy (luôn hiển thị) --}}
    <div class="replies" id="replies-{{ $comment->id }}">
        @foreach ($comment->replies as $reply)
            @include('news.single_comment', ['comment' => $reply, 'post' => $post, 'level' => $level + 1])
        @endforeach
    </div>
    {{-- Dấu gạch ngang sau comment cha --}}
    @if ($level === 0)
        <hr class="comment-divider my-4">
    @endif
</div>