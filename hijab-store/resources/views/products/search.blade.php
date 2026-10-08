@extends('layouts.store')

@section('title', 'Cari Produk — Hijab Store')

@section('content')
    <h1 class="h2 mb-4">Cari hijab</h1>
    <form method="get" action="{{ route('products.search') }}" class="card card-body mb-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="q">Kata kunci</label>
                <input class="form-control" id="q" name="q" value="{{ $query }}" placeholder="Nama, bahan, atau warna">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="category_id">Kategori</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="material">Bahan</label>
                <select class="form-select" id="material" name="material">
                    <option value="">Semua bahan</option>
                    @foreach ($materials as $material)
                        <option value="{{ $material }}" @selected(request('material') === $material)>{{ $material }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="color">Warna</label>
                <select class="form-select" id="color" name="color">
                    <option value="">Semua warna</option>
                    @foreach ($colors as $color)
                        <option value="{{ $color }}" @selected(request('color') === $color)>{{ $color }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="min_price">Harga minimum</label>
                <input class="form-control" id="min_price" name="min_price" type="number" min="0" value="{{ request('min_price') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="max_price">Harga maksimum</label>
                <input class="form-control" id="max_price" name="max_price" type="number" min="0" value="{{ request('max_price') }}">
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary">Cari produk</button>
                <a class="btn btn-outline-secondary" href="{{ route('products.search') }}">Hapus filter</a>
            </div>
        </div>
    </form>

    @if ($facets !== [])
        <div class="d-flex flex-wrap gap-3 mb-3 small text-secondary">
            @foreach ($facets as $facet)
                @php
                    $facetValues = collect($facet['counts'])->take(5)->map(function (array $count) use ($facet, $categories) {
                        if ($facet['field_name'] === 'category_id') {
                            return $categories->firstWhere('id', (int) $count['value'])?->name ?? $count['value'];
                        }

                        return $count['value'];
                    });
                    $facetLabel = $facet['field_name'] === 'category_id' ? 'Kategori' : ucfirst($facet['field_name']);
                @endphp
                <span>{{ $facetLabel }}: {{ $facetValues->implode(', ') }}</span>
            @endforeach
        </div>
    @endif

    <div class="row g-3">
        @forelse ($results as $product)
            <div class="col-6 col-md-4 col-lg-3">@include('products.partials.card', ['product' => $product])</div>
        @empty
            <div class="col"><div class="alert alert-light">Produk tidak ditemukan. Coba ubah kata kunci atau filter.</div></div>
        @endforelse
    </div>
    <div class="mt-4">{{ $results->links('pagination::bootstrap-5') }}</div>
@endsection
