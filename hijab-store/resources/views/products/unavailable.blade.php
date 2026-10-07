@extends('layouts.store')

@section('title', 'Pencarian sementara tidak tersedia — Hijab Store')

@section('content')
    <div class="card card-body text-center mx-auto" style="max-width: 640px">
        <h1 class="h3">Pencarian sedang tidak tersedia</h1>
        <p class="mb-4">Layanan pencarian sementara tidak dapat dihubungi. Katalog tetap dapat dibuka dari halaman utama.</p>
        <a class="btn btn-primary align-self-center" href="{{ route('home') }}">Kembali ke katalog</a>
    </div>
@endsection
