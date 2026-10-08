@extends('layouts.admin')

@section('title', 'Kelola kategori — Hijab Store')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h2">Kategori</h1>
        <a class="btn btn-dark" href="{{ route('admin.categories.create') }}">Tambah kategori</a>
    </div>
    <div class="card table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Nama</th><th>Jumlah produk</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td><td>{{ $category->products_count }}</td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.categories.edit', $category) }}">Ubah</a>
                        <form method="post" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline" onsubmit="return confirm('Hapus kategori ini?')">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" @disabled($category->products_count > 0)>Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center py-4">Belum ada kategori.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $categories->links('pagination::bootstrap-5') }}</div>
@endsection
