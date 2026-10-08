<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin — Hijab Store')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Admin Hijab Store</a>
        <div class="d-flex gap-3 align-items-center">
            @auth <span class="text-white small">{{ auth()->user()->name }}</span> @endauth
            <a class="btn btn-sm btn-outline-light" href="{{ route('home') }}">Lihat toko</a>
            @auth
                <form method="post" action="{{ route('admin.logout') }}">@csrf<button class="btn btn-sm btn-warning">Keluar</button></form>
            @endauth
        </div>
    </div>
</nav>
<div class="container py-4">
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @auth
        <nav class="mb-4 d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.dashboard') }}">Ringkasan</a>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.products.index') }}">Produk</a>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.categories.index') }}">Kategori</a>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.orders.index') }}">Pesanan</a>
        </nav>
    @endauth
    @yield('content')
</div>
</body>
</html>
