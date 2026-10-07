@extends('layouts.store')

@section('title', 'Pesanan dibuat — Hijab Store')

@section('content')
    <div class="card card-body mx-auto" style="max-width: 760px">
        <div class="text-center py-3">
            <p class="display-6">✓</p>
            <h1 class="h2">Pesanan berhasil dibuat</h1>
            <p class="text-muted">Simpan nomor pesanan ini untuk komunikasi dengan admin.</p>
            <p class="h4">{{ $order->number }}</p>
        </div>
        <dl class="row">
            <dt class="col-5">Nama penerima</dt><dd class="col-7">{{ $order->customer_name }}</dd>
            <dt class="col-5">Alamat</dt><dd class="col-7">{{ $order->address }}, {{ $order->city }} {{ $order->postal_code }}</dd>
            <dt class="col-5">Pembayaran</dt><dd class="col-7">{{ $order->payment_method === 'cod' ? 'Bayar di tempat (COD)' : 'Transfer manual' }}</dd>
            <dt class="col-5">Status</dt><dd class="col-7">{{ ucfirst($order->status) }}</dd>
        </dl>
        <h2 class="h5">Rincian</h2>
        @foreach ($order->items as $item)
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span>{{ $item->product_name }} × {{ $item->quantity }}</span>
                <span>Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</span>
            </div>
        @endforeach
        <div class="d-flex justify-content-between fs-5 mt-3"><strong>Total sementara</strong><strong>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</strong></div>
        <p class="small text-muted mt-3">Biaya pengiriman akan dikonfirmasi admin. Untuk pembayaran transfer manual, tunggu instruksi pembayaran dari admin.</p>
        <a class="btn btn-primary align-self-start mt-2" href="{{ route('home') }}">Kembali ke katalog</a>
    </div>
@endsection
