@extends('layouts.admin')

@section('title', 'Kelola pesanan '.$order->number)

@section('content')
    <h1 class="h2 mb-3">Pesanan {{ $order->number }}</h1>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card card-body mb-3">
                <h2 class="h5">Penerima</h2>
                <p class="mb-1">{{ $order->customer_name }} · {{ $order->phone }}</p>
                <p class="mb-1">{{ $order->email }}</p>
                <p class="mb-0">{{ $order->address }}, {{ $order->city }} {{ $order->postal_code }}</p>
                @if ($order->notes)<p class="mt-3 mb-0"><strong>Catatan:</strong> {{ $order->notes }}</p>@endif
            </div>
            <div class="card card-body">
                <h2 class="h5">Item pesanan</h2>
                @foreach ($order->items as $item)
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span>{{ $item->product_name }} × {{ $item->quantity }}</span>
                        <span>Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="d-flex justify-content-between mt-3"><strong>Total sementara</strong><strong>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</strong></div>
            </div>
        </div>
        <div class="col-lg-5">
            <form method="post" action="{{ route('admin.orders.update', $order) }}" class="card card-body">
                @csrf @method('PATCH')
                <h2 class="h5">Perbarui status</h2>
                <label class="form-label" for="status">Status pesanan</label>
                <select class="form-select mb-3" id="status" name="status">
                    @foreach (['pending' => 'Menunggu', 'processing' => 'Diproses', 'shipped' => 'Dikirim', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $order->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="form-label" for="payment_status">Status pembayaran</label>
                <select class="form-select mb-3" id="payment_status" name="payment_status">
                    <option value="unpaid" @selected(old('payment_status', $order->payment_status) === 'unpaid')>Belum dibayar</option>
                    <option value="paid" @selected(old('payment_status', $order->payment_status) === 'paid')>Sudah dibayar</option>
                </select>
                <button class="btn btn-dark">Simpan status</button>
                @if ($order->status === 'cancelled')<p class="small text-muted mt-2 mb-0">Pesanan dibatalkan adalah status akhir.</p>@endif
            </form>
            @if ($order->status === 'cancelled' && $order->payment_status === 'unpaid')
                <form method="post" action="{{ route('admin.orders.destroy', $order) }}" class="mt-3" onsubmit="return confirm('Hapus pesanan yang dibatalkan ini secara permanen?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger">Hapus pesanan batal</button>
                </form>
            @endif
        </div>
    </div>
@endsection
