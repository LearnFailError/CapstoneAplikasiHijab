@extends('layouts.store')

@section('title', 'Daftar — Hijab Store')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <h1 class="h2 mb-2">Buat akun pembeli</h1>
            <p class="text-secondary mb-4">Simpan riwayat pembelian dan lanjutkan checkout lebih cepat.</p>
            <form method="post" action="{{ route('register.store') }}" class="card card-body gap-3">
                @csrf
                <div>
                    <label class="form-label" for="name">Nama lengkap</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="120" required autofocus>
                </div>
                <div>
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                </div>
                <div>
                    <label class="form-label" for="password">Kata sandi</label>
                    <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                    <div class="form-text">Gunakan minimal 8 karakter.</div>
                </div>
                <div>
                    <label class="form-label" for="password_confirmation">Ulangi kata sandi</label>
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                </div>
                <button class="btn btn-primary">Buat akun</button>
            </form>
            <p class="mt-3">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>.</p>
        </div>
    </div>
@endsection