<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected string $sessionKey = 'cart';

    protected string $couponKey = 'coupon';

    public function getItems(): array
    {
        return Session::get($this->sessionKey, []);
    }

    public function applyCoupon(string $code): array
    {
        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();

        if (! $coupon) {
            return ['success' => false, 'message' => 'Kode promo tidak valid.'];
        }

        if (! $coupon->isValid()) {
            return ['success' => false, 'message' => 'Kupon sudah tidak berlaku atau kuota habis.'];
        }

        if ($this->getSubtotal() < (float) $coupon->min_purchase) {
            return ['success' => false, 'message' => 'Minimal belanja untuk kupon ini adalah Rp '.number_format($coupon->min_purchase, 0, ',', '.')];
        }

        Session::put($this->couponKey, [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
        ]);

        return ['success' => true, 'message' => 'Kupon berhasil diterapkan!'];
    }

    public function getCoupon(): ?array
    {
        return Session::get($this->couponKey);
    }

    public function removeCoupon(): void
    {
        Session::forget($this->couponKey);
    }

    public function getDiscountAmount(): float
    {
        $coupon = $this->getCoupon();
        if (! $coupon) {
            return 0;
        }

        $subtotal = $this->getSubtotal();

        if ($coupon['type'] === 'percent') {
            return ($subtotal * $coupon['value']) / 100;
        }

        return min((float) $coupon['value'], $subtotal);
    }

    public function getTotal(): float
    {
        return max(0, $this->getSubtotal() - $this->getDiscountAmount());
    }

    public function add(int $productId, int $quantity = 1, ?int $variantId = null): array
    {
        $product = Product::with('variants')->findOrFail($productId);
        $cart = $this->getItems();

        // The admin's availability switch overrides everything, including any
        // stock still sitting on a variant.
        if (! $product->is_available) {
            return ['success' => false, 'message' => 'Produk sedang tidak tersedia.'];
        }

        $variant = null;
        if ($variantId) {
            $variant = $product->variants->firstWhere('id', $variantId);
            if (! $variant) {
                return ['success' => false, 'message' => 'Variasi tidak ditemukan.'];
            }
            if ($variant->stock <= 0) {
                return ['success' => false, 'message' => 'Stok variasi habis.'];
            }
        } else {
            if ($product->isOutOfStock()) {
                return ['success' => false, 'message' => 'Stok produk habis.'];
            }
        }

        $cartKey = $productId.'_'.($variantId ?? '0');
        $existingQty = $cart[$cartKey]['quantity'] ?? 0;
        $maxStock = $variant ? $variant->stock : $product->stock;

        $newQty = $existingQty + $quantity;
        if ($newQty > $maxStock) {
            $newQty = $maxStock;
        }

        $price = $product->price;
        if ($variant && $variant->price) {
            $price += $variant->price;
        }

        $cart[$cartKey] = [
            'id' => $cartKey,
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'name' => $product->name,
            'variant_name' => $variant ? $variant->name : null,
            'price' => $price,
            'image' => $product->image,
            'slug' => $product->slug,
            'quantity' => $newQty,
            'stock' => $maxStock,
        ];

        Session::put($this->sessionKey, $cart);

        return ['success' => true, 'message' => 'Produk ditambahkan ke keranjang.'];
    }

    public function update(string $cartKey, int $quantity): void
    {
        $cart = $this->getItems();

        if (isset($cart[$cartKey])) {
            $maxQty = $cart[$cartKey]['stock'] ?? $quantity;

            if ($quantity <= 0) {
                unset($cart[$cartKey]);
            } else {
                $cart[$cartKey]['quantity'] = min($quantity, $maxQty);
            }

            Session::put($this->sessionKey, $cart);
        }
    }

    public function remove(string $cartKey): void
    {
        $cart = $this->getItems();
        unset($cart[$cartKey]);
        Session::put($this->sessionKey, $cart);
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey);
    }

    public function getCount(): int
    {
        return collect($this->getItems())->sum('quantity');
    }

    public function getSubtotal(): float
    {
        return collect($this->getItems())->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
    }

    public function validateStock(): array
    {
        $cart = $this->getItems();
        if (empty($cart)) {
            return [];
        }

        $errors = [];

        // Batch-load every product (with variants) referenced by the cart in a
        // single query instead of one query per item (avoids N+1).
        $productIds = array_values(array_unique(array_column($cart, 'product_id')));
        $products = Product::with('variants')->whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($cart as $cartKey => $item) {
            $product = $products->get($item['product_id']);

            if (! $product) {
                $errors[] = "Produk '{$item['name']}' sudah tidak tersedia.";
                unset($cart[$cartKey]);

                continue;
            }

            // A product switched off by the admin counts as unavailable no matter
            // what its counters say.
            $available = (bool) $product->is_available;

            $maxStock = $available ? $product->stock : 0;
            if (! empty($item['variant_id'])) {
                $variant = $product->variants->firstWhere('id', $item['variant_id']);
                if (! $variant) {
                    $errors[] = "Variasi untuk '{$item['name']}' sudah tidak tersedia.";
                    unset($cart[$cartKey]);

                    continue;
                }
                $maxStock = $available ? $variant->stock : 0;
            }

            if ($maxStock <= 0) {
                $variantName = isset($item['variant_name']) ? " (Varian: {$item['variant_name']})" : '';
                $errors[] = "Stok '{$product->name}'{$variantName} sudah habis.";
                unset($cart[$cartKey]);

                continue;
            }

            if ($item['quantity'] > $maxStock) {
                $cart[$cartKey]['quantity'] = $maxStock;
                $cart[$cartKey]['stock'] = $maxStock;
                $errors[] = "Stok '{$product->name}' hanya tersisa {$maxStock}.";
            }
        }

        Session::put($this->sessionKey, $cart);

        return $errors;
    }
}
