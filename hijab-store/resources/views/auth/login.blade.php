@extends('layouts.store')

@section('title', 'Masuk — Hijab Store')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <h1 class="h2 mb-2">Masuk ke akun</h1>
            <p class="text-secondary mb-4">Lanjutkan belanja dan pantau pesanan hijab Anda.</p>
            <form method="post" action="{{ route('login.store') }}" class="card card-body gap-3">
                @csrf
                <div>
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                </div>
                <div>
                    <label class="form-label" for="password">Kata sandi</label>
                    <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
                </div>
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" value="1">
                    <span class="form-check-label">Ingat saya</span>
                </label>
                <button class="btn btn-primary">Masuk</button>
            </form>
            <p class="mt-3">Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai pembeli</a>.</p>
            <p class="small text-secondary">Admin toko? <a href="{{ route('admin.login') }}">Masuk ke panel admin</a>.</p>
        </div>
    </div>
@endsection