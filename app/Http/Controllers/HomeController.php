<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user(); // hoặc User::find(1) nếu bạn test
        return view('home', compact('user'));
    }
}
