<div class="table-responsive">
    <table id="postsTable" class="table table-striped table-hover align-middle table-fixed">
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
                    <td>{{ $post->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="d-inline-flex align-items-center gap-2">
                            <a href="{{ route('news.show', $post->slug) }}"
                               class="btn btn-sm btn-outline-info p-1"
                               target="_blank" title="Xem chi tiết">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            <a href="{{ route('admin.posts.edit', $post->id) }}"
                               class="btn btn-sm btn-outline-warning p-1"
                               title="Sửa">
                                <i class="fa-solid fa-edit"></i>
                            </a>

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
