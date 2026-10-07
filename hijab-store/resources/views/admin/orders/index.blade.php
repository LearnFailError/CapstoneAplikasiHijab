@extends('layouts.admin')

@section('title', 'Pesanan — Hijab Store')

@section('content')
    <h1 class="h2 mb-3">Pesanan</h1>
    <div class="card table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Nomor</th><th>Pelanggan</th><th>Tanggal</th><th>Total</th><th>Pembayaran</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>{{ $order->number }}</td><td>{{ $order->customer_name }}</td><td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                    <td>{{ $order->payment_method === 'cod' ? 'COD' : 'Transfer manual' }}</td><td>{{ ucfirst($order->status) }}</td>
                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.orders.show', $order) }}">Kelola</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-4">Belum ada pesanan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
@endsection
