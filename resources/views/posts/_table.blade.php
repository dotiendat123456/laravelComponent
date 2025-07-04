<table id="postsTable" class="table table-striped table-hover align-middle table-fixed">
    
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
            <td>{{ $loop->iteration }}</td>
            <td>
                @if ($post->thumbnail)
                    <img src="{{ asset($post->thumbnail) }}" width="60" class="rounded border">
                @else
                    <span class="text-muted">Không có</span>
                @endif
            </td>
            <td>{{ $post->title }}</td>
            <td>{{ Str::limit($post->description, 50) }}</td>
            <td>{{ $post->publish_date ? $post->publish_date->format('d/m/Y') : '-' }}</td>
            <td>
                @php
                    $status = $post->status instanceof \App\Enums\PostStatus
                        ? $post->status
                        : \App\Enums\PostStatus::from($post->status);
                @endphp

                <span class="badge
                    @switch($status)
                        @case(\App\Enums\PostStatus::PENDING) bg-secondary @break
                        @case(\App\Enums\PostStatus::APPROVED) bg-success @break
                        @case(\App\Enums\PostStatus::DENY) bg-danger @break
                        @default bg-dark
                    @endswitch">
                    {{ $status->label() }}
                </span>
            </td>
            <td>
                <div class="d-inline-flex align-items-center gap-2">
                    <a href="{{ route('news.show', $post->slug) }}" class="btn btn-sm btn-outline-info p-1"
                       target="_blank">
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
