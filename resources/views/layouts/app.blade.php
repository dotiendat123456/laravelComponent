<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Main CSS/JS -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- Datatables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css" />

    @stack('styles')
</head>

<body>
    <!-- NAVBAR TRÊN CÙNG -->
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Laravel') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left -->
                <ul class="navbar-nav me-auto"></ul>

                <!-- Right -->
                <ul class="navbar-nav ms-auto">
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">Đăng nhập</a>
                            </li>
                        @endif

                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">Đăng ký</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    Hồ sơ cá nhân
                                </a>

                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Đăng xuất
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    @can('viewAny', App\Models\Post::class)
        <!--  TRƯỜNG HỢP ADMIN: SIDEBAR + CONTENT -->
        <div id="app" class="d-flex" style="padding-top: 56px;">
            <!-- Sidebar nằm dưới Navbar -->
            <nav id="sidebar" class="bg-light border-end position-fixed"
                style="width: 240px; top: 56px; bottom: 0; overflow-y: auto;">
                <div class="p-3">
                    <h4 class="text-center">Admin</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a href="{{ route('admin.posts.index') }}" class="nav-link">
                                Quản lý Bài viết
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.users.index') }}" class="nav-link">
                                Quản lý Tài khoản
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('news.index') }}" class="nav-link">
                                Chi tiết bài viết
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Nội dung bên phải -->
            <div class="flex-grow-1" style="margin-left: 240px;">
                <main class="py-4 container">
                    @yield('content')
                </main>
            </div>
        </div>
    @else
        <!--  TRƯỜNG HỢP USER: FULL WIDTH -->
        <div id="app" style="padding-top: 56px;">
            <main class="py-4 container">
                @yield('content')
            </main>
        </div>
    @endcan
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>

    @stack('scripts')
</body>


</html>