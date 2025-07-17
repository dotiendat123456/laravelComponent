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
                            let buttons = '';

                            // Không cho phép chỉnh sửa chính mình
                            if (row.id !== @json(Auth::id())) {
                                buttons += `<a href="${editUrl}" class="btn btn-sm btn-outline-warning" title="Sửa">
                                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                            Sửa
                                        </a>`;

                                if (row.status_value === 3) {
                                    buttons += `<button onclick="toggleStatus(${row.id}, 'unlock')" class="btn btn-sm btn-success ms-1" title="Mở khóa">
                                                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path></svg> 
                                                Mở khóa
                                            </button>`;
                                } else {
                                    buttons += `<button onclick="toggleStatus(${row.id}, 'lock')" class="btn btn-sm btn-danger ms-1" title="Khóa">
                                                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg> 
                                                Khóa
                                            </button>`;
                                }
                            } else {
                                // Nếu là chính mình chỉ hiển thị dấu gạch ngang hoặc không hiển thị gì
                                buttons += `<span class="text-muted"></span>`;
                            }

                            return buttons;
                        }

                    }
                ],

                language: {
                    url: '//cdn.datatables.net/plug-ins/2.3.2/i18n/vi.json'
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
                        _token: @json(csrf_token()),
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