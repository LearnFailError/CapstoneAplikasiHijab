@extends('layouts.admin')

@section('title', 'Login admin — Hijab Store')

@section('content')
    <div class="card mx-auto" style="max-width: 440px">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">Login admin</h1>
            <form method="post" action="{{ route('admin.login.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email admin</label>
                    <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Kata sandi</label>
                    <input class="form-control" id="password" name="password" type="password" required>
                </div>
                <button class="btn btn-dark w-100">Masuk</button>
            </form>
            <p class="small mt-3 mb-0">Pembeli? <a href="{{ route('login') }}">Masuk atau daftar sebagai pembeli</a>.</p>
        </div>
    </div>
@endsection
