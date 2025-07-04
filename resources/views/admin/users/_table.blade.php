<table id="usersTable" class="table table-striped table-hover align-middle table-fixed">
    <thead class="table-light">
        <tr>
            <th style="width: 20%;">Tên</th>
            <th style="width: 20%;">Email</th>
            <th style="width: 30%;">Địa chỉ</th>
            <th style="width: 15%;">Trạng thái</th>
            <th style="width: 15%;">Hành động</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($users as $user)
            <tr>
                <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->address }}</td>
                <td>
                    @switch($user->status)
                        @case(App\Enums\UserStatus::PENDING)
                            <span class="badge bg-secondary">{{ $user->status->label() }}</span>
                            @break
                        @case(App\Enums\UserStatus::APPROVED)
                            <span class="badge bg-success">{{ $user->status->label() }}</span>
                            @break
                        @case(App\Enums\UserStatus::REJECTED)
                            <span class="badge bg-danger">{{ $user->status->label() }}</span>
                            @break
                        @case(App\Enums\UserStatus::LOCKED)
                            <span class="badge bg-dark">{{ $user->status->label() }}</span>
                            @break
                        @default
                            <span class="badge bg-light">Không rõ</span>
                    @endswitch
                </td>
                <td>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-warning">
                        <i class="fa-solid fa-edit"></i> Sửa
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted">Không có user nào.</td>
            </tr>
        @endforelse
    </tbody>
</table>
