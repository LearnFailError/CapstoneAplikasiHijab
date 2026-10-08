@extends('layouts.store')

@section('title', $product->name.' — Hijab Store')

@section('content')
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Katalog</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>
    <div class="row g-5">
        <div class="col-md-6">
            @if ($product->image)
                <img class="img-fluid rounded-4 w-100" style="max-height: 520px; object-fit: cover" src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}">
            @else
                <div class="product-placeholder rounded-4" style="height: 420px">Hijab Store</div>
            @endif
        </div>
        <div class="col-md-6">
            <p class="text-secondary">{{ $product->category?->name }}</p>
            <h1 class="h2">{{ $product->name }}</h1>
            <p class="h4 my-3">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>
            <p>{{ $product->description ?: 'Hijab pilihan dengan kualitas nyaman untuk aktivitas sehari-hari.' }}</p>
            <dl class="row">
                <dt class="col-4">Bahan</dt><dd class="col-8">{{ $product->material ?: '—' }}</dd>
                <dt class="col-4">Warna</dt><dd class="col-8">{{ $product->color ?: '—' }}</dd>
                <dt class="col-4">Stok</dt><dd class="col-8">{{ $product->stock }}</dd>
            </dl>
            @if ($product->stock > 0)
                <form method="post" action="{{ route('cart.store') }}" class="d-flex gap-2 align-items-end">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <label class="form-label">Jumlah
                        <input class="form-control" type="number" name="quantity" min="1" max="{{ min($product->stock, 99) }}" value="1" required>
                    </label>
                    <button class="btn btn-primary mb-3">Tambah ke keranjang</button>
                </form>
            @else
                <button class="btn btn-secondary" disabled>Stok habis</button>
            @endif
        </div>
    </div>
    @if ($relatedProducts->isNotEmpty())
        <section class="mt-5">
            <h2 class="h4 mb-3">Produk serupa</h2>
            <div class="row g-3">
                @foreach ($relatedProducts as $relatedProduct)
                    <div class="col-6 col-md-3">@include('products.partials.card', ['product' => $relatedProduct])</div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
