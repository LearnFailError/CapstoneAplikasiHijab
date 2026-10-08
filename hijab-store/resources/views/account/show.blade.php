@extends('layouts.store')

@section('title', 'Akun pembeli — Hijab Store')

@section('content')
    <header class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <p class="text-secondary mb-1">Akun pembeli</p>
            <h1 class="h2 mb-1">Halo, {{ auth()->user()->name }}</h1>
            <p class="text-secondary mb-0">{{ auth()->user()->email }}</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('products.search') }}">Lanjut belanja</a>
    </header>

    <section aria-labelledby="orders-heading">
        <div class="d-flex align-items-baseline justify-content-between mb-3">
            <h2 class="h4 mb-0" id="orders-heading">Riwayat pesanan</h2>
            <span class="small text-secondary">{{ $orders->total() }} pesanan</span>
        </div>
        <div class="table-responsive border rounded bg-white">
            <table class="table align-middle mb-0">
                <thead><tr><th>Nomor</th><th>Tanggal</th><th>Item</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="fw-semibold">{{ $order->number }}</td>
                        <td>{{ $order->created_at->format('d/m/Y') }}</td>
                        <td>{{ $order->items_count }}</td>
                        <td>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                        <td>{{ ['pending' => 'Menunggu', 'processing' => 'Diproses', 'shipped' => 'Dikirim', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'][$order->status] ?? ucfirst($order->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-5">
                        <p class="mb-1">Belum ada pesanan di akun ini.</p>
                        <a href="{{ route('home') }}">Jelajahi katalog hijab</a>
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
    </section>
@endsection