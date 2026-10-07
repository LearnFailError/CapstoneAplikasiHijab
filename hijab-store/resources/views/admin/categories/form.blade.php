@extends('layouts.admin')

@section('title', $category->exists ? 'Ubah kategori' : 'Tambah kategori')

@section('content')
    <h1 class="h2 mb-4">{{ $category->exists ? 'Ubah kategori' : 'Tambah kategori' }}</h1>
    <form method="post" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="card card-body" style="max-width: 640px">
        @csrf
        @if ($category->exists) @method('PUT') @endif
        <label class="form-label" for="name">Nama kategori</label>
        <input class="form-control mb-3" id="name" name="name" value="{{ old('name', $category->name) }}" maxlength="120" required>
        <div><button class="btn btn-dark">Simpan</button> <a class="btn btn-outline-secondary" href="{{ route('admin.categories.index') }}">Batal</a></div>
    </form>
@endsection
