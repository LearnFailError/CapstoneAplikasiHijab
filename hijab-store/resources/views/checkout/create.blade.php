@extends('layouts.store')

@section('title', 'Checkout — Hijab Store')

@section('content')
    <h1 class="h2 mb-4">Checkout</h1>
    <div class="row g-4">
        <div class="col-lg-7">
            <form method="post" action="{{ route('checkout.store') }}" class="card card-body">
                @csrf
                <h2 class="h5 mb-3">Data penerima</h2>
                <div class="mb-3">
                    <label class="form-label" for="customer_name">Nama lengkap</label>
                    <input class="form-control" id="customer_name" name="customer_name" value="{{ old('customer_name', $buyer?->name) }}" required maxlength="120">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="phone">Nomor telepon</label>
                        <input class="form-control" id="phone" name="phone" value="{{ old('phone') }}" required maxlength="30">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="email">Email (opsional)</label>
                        <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $buyer?->email) }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="address">Alamat lengkap</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required>{{ old('address') }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label" for="city">Kota/Kabupaten</label>
                        <input class="form-control" id="city" name="city" value="{{ old('city') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="postal_code">Kode pos</label>
                        <input class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required maxlength="20">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="payment_method">Metode pembayaran</label>
                    <select class="form-select" id="payment_method" name="payment_method" required>
                        <option value="cod" @selected(old('payment_method') === 'cod')>Bayar di tempat (COD)</option>
                        <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Transfer manual</option>
                    </select>
                    <small class="text-muted">Instruksi transfer akan diberikan admin setelah pesanan dikonfirmasi.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="notes">Catatan (opsional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                </div>
                <button class="btn btn-primary">Buat pesanan</button>
            </form>
        </div>
        <aside class="col-lg-5">
            <div class="card card-body">
                <h2 class="h5">Item pesanan</h2>
                @foreach ($items as $item)
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span>{{ $item['product']->name }} × {{ $item['quantity'] }}</span>
                        <span>Rp {{ number_format($item['line_total'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="d-flex justify-content-between mt-3"><strong>Subtotal</strong><strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong></div>
                <p class="small text-muted mt-3 mb-0">Total ongkir belum termasuk; admin akan menghubungi Anda untuk konfirmasi.</p>
            </div>
        </aside>
    </div>
@endsection
