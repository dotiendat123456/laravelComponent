@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Danh sách bài viết</h3>

        @if (session('success'))
            <x-alert-success :message="session('success')" />
        @endif

        @error('error')
            <div class="alert alert-danger" role="alert">
                {{ $message }}
            </div>
        @enderror

        {{-- FORM TÌM KIẾM --}}
        {{-- Với DataTables, form này không cần thiết vì có search box sẵn --}}
        {{-- Nếu vẫn muốn giữ, thì cần custom xử lý thêm, tạm thời bỏ để gọn gàng --}}


        <div class="mb-3 d-flex justify-content-between align-items-center">
            <a href="{{ route('posts.create') }}" class="btn btn-success">
                <i class="bi bi-plus"></i> Tạo mới
            </a>

            @if($posts->count())
                <form action="{{ route('posts.destroyAll') }}" method="POST"
                    onsubmit="return confirm('Bạn có chắc chắn muốn xóa tất cả bài viết?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger">
                        <i class="bi bi-trash"></i> Xóa tất cả
                    </button>
                </form>
            @endif
        </div>

        <div class="table-responsive">
            @include('posts._table', ['posts' => $posts])
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .table-fixed {
            table-layout: fixed;
            width: 100%;
        }

        #postsTable tbody tr {
            height: 70px;
            /* 👈 Chiều cao hàng cố định */
        }

        #postsTable td {
            vertical-align: middle;
            /* 👈 Canh giữa nội dung theo chiều dọc */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Nếu muốn mô tả nhiều dòng vẫn ẩn */
        #postsTable td .description {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            /* Số dòng tối đa */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>

@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            var table = $('#postsTable').DataTable({
                pageLength: 5,
                lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],//Tham số đầu: mảng số bản ghi giá trị thật.Tham số sau: mảng label hiển thị.
                ordering: false,
                searching: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/2.0.0/i18n/vi.json'
                }
            });

            // Ghi đè: chỉ lọc Tiêu đề (ví dụ cột thứ 2 = data[2])
            $.fn.dataTable.ext.search.push(
                function (settings, data, dataIndex) {
                    var searchTerm = table.search().toLowerCase();
                    var title = data[2].toLowerCase(); // đếm từ 0 → 2 là cột Tiêu đề
                    return title.includes(searchTerm);
                }
            );
        });
    </script>
@endpush