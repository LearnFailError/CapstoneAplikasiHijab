<article class="card h-100 shadow-sm">
    <a href="{{ route('products.show', $product) }}" class="text-decoration-none">
        @if ($product->image)
            <img class="card-img-top product-image" src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}">
        @else
            <div class="product-placeholder">Hijab Store</div>
        @endif
    </a>
    <div class="card-body d-flex flex-column">
        <p class="small text-secondary mb-1">{{ $product->category?->name }}</p>
        <h3 class="h6"><a class="text-dark text-decoration-none" href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
        <p class="fw-semibold mb-2">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>
        <p class="small text-muted mt-auto mb-3">Stok: {{ $product->stock }}</p>
        @if ($product->stock > 0)
            <form method="post" action="{{ route('cart.store') }}" class="mt-auto">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button class="btn btn-primary w-100">Tambah ke keranjang</button>
            </form>
        @else
            <button class="btn btn-secondary w-100" disabled>Stok habis</button>
        @endif
    </div>
</article>
