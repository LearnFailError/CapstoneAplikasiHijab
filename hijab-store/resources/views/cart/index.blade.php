@extends('layouts.store')

@section('title', 'Keranjang — Hijab Store')

@section('content')
    <h1 class="h2 mb-4">Keranjang belanja</h1>
    @if ($items->isEmpty())
        <div class="card card-body">
            <p>Keranjang Anda masih kosong.</p>
            <a class="btn btn-primary align-self-start" href="{{ route('home') }}">Lihat produk</a>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                @foreach ($items as $item)
                    <article class="card card-body mb-3">
                        <div class="d-flex flex-wrap justify-content-between gap-3">
                            <div>
                                <a class="h5 text-dark" href="{{ route('products.show', $item['product']) }}">{{ $item['product']->name }}</a>
                                <p class="mb-1">Rp {{ number_format((float) $item['product']->price, 0, ',', '.') }} per item</p>
                                <strong>Rp {{ number_format($item['line_total'], 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <form method="post" action="{{ route('cart.update', $item['product']) }}" class="d-flex gap-2">
                                    @csrf @method('PATCH')
                                    <input class="form-control" style="width: 90px" aria-label="Jumlah {{ $item['product']->name }}" type="number" name="quantity" min="1" max="{{ min($item['product']->stock, 99) }}" value="{{ $item['quantity'] }}" required>
                                    <button class="btn btn-outline-secondary">Ubah</button>
                                </form>
                                <form method="post" action="{{ route('cart.destroy', $item['product']) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <aside class="col-lg-4">
                <div class="card card-body">
                    <h2 class="h5">Ringkasan</h2>
                    <div class="d-flex justify-content-between mb-3"><span>Subtotal</span><strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong></div>
                    <p class="small text-muted">Ongkos kirim akan dikonfirmasi oleh admin.</p>
                    <a class="btn btn-primary" href="{{ route('checkout.create') }}">Lanjut ke checkout</a>
                </div>
            </aside>
        </div>
    @endif
@endsection
