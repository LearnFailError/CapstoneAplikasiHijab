@extends('layouts.store')

@section('title', 'Hijab Store — Katalog')

@section('content')
    <section class="rounded-4 p-4 p-md-5 mb-5" style="background: #f1e9e3">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <p class="text-uppercase small fw-semibold mb-2">Koleksi pilihan</p>
                <h1 class="display-5 fw-semibold">Temukan hijab yang terasa seperti kamu.</h1>
                <p class="lead mb-4">Pilihan bahan dan warna untuk menemani setiap harimu.</p>
                <a class="btn btn-primary btn-lg" href="{{ route('products.search') }}">Jelajahi koleksi</a>
            </div>
        </div>
    </section>

    @if ($categories->isNotEmpty())
        <section class="mb-5">
            <h2 class="h4 mb-3">Kategori</h2>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($categories as $category)
                    <a class="btn btn-outline-secondary" href="{{ route('products.search', ['category_id' => $category->id]) }}">
                        {{ $category->name }} <span class="text-muted">({{ $category->products_count }})</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 mb-0">Produk terbaru</h2>
            <a href="{{ route('products.search') }}">Lihat semua</a>
        </div>
        <div class="row g-3">
            @forelse ($products as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    @include('products.partials.card', ['product' => $product])
                </div>
            @empty
                <div class="col"><div class="alert alert-light">Belum ada produk. Silakan kembali lagi nanti.</div></div>
            @endforelse
        </div>
        <div class="mt-4">{{ $products->links('pagination::bootstrap-5') }}</div>
    </section>
@endsection
