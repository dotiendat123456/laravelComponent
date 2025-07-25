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

        {{-- FORM TÌM KIẾM
        <form id="searchForm" method="GET" class="row g-2 mb-3">
            <div class="col-auto">
                <input type="text" id="filterName" name="name" class="form-control" placeholder="Tên">
            </div>
            <div class="col-auto">
                <input type="text" id="filterEmail" name="email" class="form-control" placeholder="Email">
            </div>
            <div class="col-auto">
                <select name="status" id="filterStatus" class="form-select">
                    <option value="" {{ request()->has('status') ? '' : 'selected' }}>Tất cả trạng thái</option>
                    @foreach (\App\Enums\UserStatus::cases() as $status)
                    <option value="{{ $status->value }}" {{ (string) request('status')===(string) $status->value ?
                        'selected' : '' }}>
                        {{ $status->label() }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Lọc</button>
            </div>
        </form> --}}

        <x-filter-form action="{{ route('admin.users.index') }}" :fields="[
            ['name' => 'name', 'label' => 'Tên', 'placeholder' => 'Tên'],
            ['name' => 'email', 'label' => 'Email', 'placeholder' => 'Email']
        ]"
        :statuses="\App\Enums\UserStatus::cases()" />


        {{-- BẢNG
        <div class="table-responsive">
            <table id="usersTable" class="table table-striped table-hover align-middle table-fixed">
                <thead class="table-light">
                    <tr>
                        <th class="text-start">STT</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Địa chỉ</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
            </table>
        </div> --}}
        <x-table id="usersTable" :columns="[
            ['label' => 'STT', 'class' => 'text-start'],
            ['label' => 'Tên'],
            ['label' => 'Email'],
            ['label' => 'Địa chỉ'],
            ['label' => 'Trạng thái'],
            ['label' => 'Hành động']
        ]" />



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
                ordering: true,
                searching: false,
                pageLength: 5,
                lengthMenu: [[1, 3, 5, 10, 15], [1, 3, 5, 10, 15]],
                order: [0, 'desc'],
                // ajax: function (data, callback) {
                //     const page = (data.start / data.length) + 1; // Tính toán page từ start và length

                //     // Gửi request tới route posts.data
                //     $.get(@json(route('admin.users.index')), {
                //         page: page, // Laravel cần param này để phân trang
                //         length: data.length, // Số lượng mỗi trang
                //         draw: data.draw,     // Dùng để đồng bộ với client
                //         search: data.search.value, // Tìm kiếm từ DataTables (nếu có)
                //         order: data.order,   // Sắp xếp cột
                //         columns: data.columns, // Cột được gửi lên
                //         name: $('#filterName').val(), // Lọc tiêu đề
                //         email: $('#filterEmail').val(), // Lọc Email
                //         status: $('#filterStatus').val(), // Lọc Status
                //     }, function (response) {
                //         // Callback để DataTable hiển thị dữ liệu
                //         console.log(response.data);
                //         callback({
                //             draw: response.draw,
                //             recordsTotal: response.recordsTotal, // Tổng số bản ghi
                //             recordsFiltered: response.recordsFiltered, // Số bản ghi sau khi lọc
                //             data: response.data // Dữ liệu trả về
                //         });
                //     });
                // },
                <x-datatable.ajax :url="route('admin.users.index')">
                    params['name'] = $('#filterName').val();
                    params['email'] = $('#filterEmail').val();
                    params['status'] = $('#filterStatus').val();
                </x-datatable.ajax>

                columns: [
                    { // STT
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + 1; // Số thứ tự trong trang hiện tại
                        },
                        className: 'text-start',
                        width: '50px'
                    },
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'address' },

                    {
                        data: null,
                        // render: function (data, type, row) {
                        //     // Hiển thị badge theo status_value
                        //     let badgeClass = 'secondary';
                        //     switch (row.status_value) {
                        //         case 0: badgeClass = 'warning'; break; // PENDING
                        //         case 1: badgeClass = 'success'; break; // APPROVED
                        //         case 2: badgeClass = 'danger'; break;  // REJECTED
                        //         case 3: badgeClass = 'dark'; break;    // LOCKED
                        //     }
                        //     return `<span class="badge bg-${badgeClass}">${row.status_label}</span>`;
                        // }
                        render: function (data, type, row) {
                            return `<span class="badge bg-${row.status_badge_class}">${row.status_label}</span>`;
                        }
                    },

                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            const editUrl = @json(route('admin.users.edit', ':id')).replace(':id', row.id);
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

                // Tái sử dụng language component
                @include('components.datatable.language')
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
                    url: @json(route('admin.users.toggleStatus', ':id')).replace(':id', id),
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