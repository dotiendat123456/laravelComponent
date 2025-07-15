@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">Danh sách User</h3>

        {{-- Thông báo thành công --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Thông báo lỗi --}}
        @error('error')
            <div class="alert alert-danger" role="alert">
                {{ $message }}
            </div>
        @enderror

        {{-- FORM TÌM KIẾM --}}
        <form id="searchForm" method="GET" class="row g-2 mb-3">
            <div class="col-auto">
                <input type="text" name="name" class="form-control" placeholder="Tên">
            </div>
            <div class="col-auto">
                <input type="text" name="email" class="form-control" placeholder="Email">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Lọc</button>
            </div>
        </form>

        {{-- BẢNG --}}
        <div class="table-responsive">
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
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .table-fixed {
            table-layout: fixed;
            width: 100%;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let table;

        $(document).ready(function () {
            table = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                searching: false,
                pageLength: 5,
                lengthMenu: [[1, 3, 5, 10, 15], [1, 3, 5, 10, 15]],

                ajax: {
                    url: '{{ route('admin.users.data') }}',
                    data: function (d) {
                        d.name = $('input[name=name]').val();
                        d.email = $('input[name=email]').val();
                    }
                },

                columns: [
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'address' },

                    {
                        data: null,
                        render: function (data, type, row) {
                            // Hiển thị badge theo status_value
                            let badgeClass = 'secondary';
                            switch (row.status_value) {
                                case 0: badgeClass = 'warning'; break; // PENDING
                                case 1: badgeClass = 'success'; break; // APPROVED
                                case 2: badgeClass = 'danger'; break;  // REJECTED
                                case 3: badgeClass = 'dark'; break;    // LOCKED
                            }
                            return `<span class="badge bg-${badgeClass}">${row.status_label}</span>`;
                        }
                    },

                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            const editUrl = "{{ route('admin.users.edit', ':id') }}".replace(':id', row.id);

                            let toggleBtn = '';

                            if (row.status_value === 3) {
                                toggleBtn = `<button onclick="toggleStatus(${row.id}, 'unlock')" class="btn btn-sm btn-success ms-1" title="Mở khóa">
                                                                    <i class="fa-solid fa-lock-open"></i> Mở khóa
                                                                 </button>`;
                            } else {
                                toggleBtn = `<button onclick="toggleStatus(${row.id}, 'lock')" class="btn btn-sm btn-danger ms-1" title="Khóa">
                                                                    <i class="fa-solid fa-lock"></i> Khóa
                                                                 </button>`;
                            }

                            return `
                                                    <a href="${editUrl}" class="btn btn-sm btn-outline-warning" title="Sửa">
                                                        <i class="fa-solid fa-edit"></i> Sửa
                                                    </a>
                                                    ${toggleBtn}
                                                `;
                        }
                    }
                ],

                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/vi.json'
                }
            });

            $('#searchForm').on('submit', function (e) {
                e.preventDefault();
                table.ajax.reload();
            });
        });

        function toggleStatus(id, action) {
            let confirmMsg = action === 'lock'
                ? 'Bạn có chắc chắn muốn KHÓA tài khoản này?'
                : 'Bạn có chắc chắn muốn MỞ KHÓA tài khoản này?';

            if (confirm(confirmMsg)) {
                $.ajax({
                    url: "{{ route('admin.users.toggleStatus', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        action: action
                    },
                    success: function () {
                        table.ajax.reload();
                    },
                    // error: function () {
                    //     alert('Thao tác thất bại!');
                    // },
                    error: function (xhr) {//xhr: Là đối tượng XMLHttpRequest chứa thông tin phản hồi từ server
                        if (xhr.status === 403) {
                            const msg = xhr.responseJSON?.message || 'Lỗi không xác định';
                            alert(msg); // hoặc toast(msg)
                        }
                    },
                });
            }
        }
    </script>
@endpush