@extends('layouts.admin')

@section('title', 'Kelola produk — Hijab Store')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h2">Produk</h1>
        <a class="btn btn-dark" href="{{ route('admin.products.create') }}">Tambah produk</a>
    </div>
    <div class="card table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Nama</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->name }}</td><td>{{ $product->category->name }}</td>
                    <td>Rp {{ number_format((float) $product->price, 0, ',', '.') }}</td><td>{{ $product->stock }}</td>
                    <td>{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.products.edit', $product) }}">Ubah</a>
                        <form method="post" action="{{ route('admin.products.destroy', $product) }}" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4">Belum ada produk.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $products->links('pagination::bootstrap-5') }}</div>
@endsection
