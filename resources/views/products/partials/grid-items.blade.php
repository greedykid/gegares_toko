{{-- One batch of product cards. Rendered inline on first paint and returned on
     its own for each "load more" fetch, so both paths share identical markup. --}}
@foreach ($products as $product)
    @include('components.product-card-grid', ['product' => $product])
@endforeach
