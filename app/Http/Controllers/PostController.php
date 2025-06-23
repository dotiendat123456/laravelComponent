<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        // Giả sử chưa có DB thì dùng mảng mẫu
        $posts = [
            ['title' => 'Bài viết 1'],
            ['title' => 'Bài viết 2'],
        ];

        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        return view('posts.create');
    }
}
