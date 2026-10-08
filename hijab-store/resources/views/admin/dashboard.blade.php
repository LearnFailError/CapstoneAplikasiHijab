@extends('layouts.admin')

@section('title', 'Dashboard admin — Hijab Store')

@section('content')
    <h1 class="h2 mb-4">Ringkasan toko</h1>
    <div class="row g-3 mb-4">
        <div class="col-md-6"><div class="card card-body"><span class="text-muted">Produk</span><strong class="display-6">{{ $productCount }}</strong></div></div>
        <div class="col-md-6"><div class="card card-body"><span class="text-muted">Pesanan menunggu</span><strong class="display-6">{{ $pendingOrders }}</strong></div></div>
    </div>
    <div class="card">
        <div class="card-header bg-white"><h2 class="h5 mb-0">Pesanan terbaru</h2></div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Nomor</th><th>Pelanggan</th><th>Total</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($recentOrders as $order)
                    <tr>
                        <td>{{ $order->number }}</td><td>{{ $order->customer_name }}</td>
                        <td>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td><td>{{ ucfirst($order->status) }}</td>
                        <td><a href="{{ route('admin.orders.show', $order) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4">Belum ada pesanan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
