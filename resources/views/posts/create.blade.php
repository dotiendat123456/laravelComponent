@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Tạo bài viết mới</h2>

        <form>
            <div class="mb-3">
                <label for="title" class="form-label">Tiêu đề</label>
                <input type="text" name="title" id="title" class="form-control">
            </div>

            <button class="btn btn-primary">Lưu</button>
        </form>
    </div>
@endsection