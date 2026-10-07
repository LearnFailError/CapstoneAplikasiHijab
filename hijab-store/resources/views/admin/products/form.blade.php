@extends('layouts.admin')

@section('title', $product->exists ? 'Ubah produk' : 'Tambah produk')

@section('content')
    <h1 class="h2 mb-4">{{ $product->exists ? 'Ubah produk' : 'Tambah produk' }}</h1>
    <form method="post" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" class="card card-body">
        @csrf
        @if ($product->exists) @method('PUT') @endif
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="name">Nama produk</label>
                <input class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="160">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="category_id">Kategori</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="material">Bahan</label>
                <input class="form-control" id="material" name="material" value="{{ old('material', $product->material) }}" maxlength="100">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="color">Warna</label>
                <input class="form-control" id="color" name="color" value="{{ old('color', $product->color) }}" maxlength="100">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="price">Harga (Rp)</label>
                <input class="form-control" id="price" name="price" type="number" min="0" step="100" value="{{ old('price', $product->price) }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="stock">Stok</label>
                <input class="form-control" id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="description">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="image">Foto produk (maksimal 2 MB)</label>
                <input class="form-control" id="image" name="image" type="file" accept="image/*">
                @if ($product->image)<img class="mt-2 rounded" src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}" width="140">@endif
            </div>
            <div class="col-12 form-check ms-2 mb-3">
                <input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $product->is_active))>
                <label class="form-check-label" for="is_active">Tampilkan di toko</label>
            </div>
        </div>
        <div><button class="btn btn-dark">Simpan</button> <a class="btn btn-outline-secondary" href="{{ route('admin.products.index') }}">Batal</a></div>
    </form>
@endsection
