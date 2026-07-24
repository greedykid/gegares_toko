<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global admin search — returns a small preview of matches across the main
     * entities (products, orders, users, categories) for the navbar search box.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }
        $like = '%'.$q.'%';

        $products = Product::where('name', 'like', $like)->limit(5)->get()
            ->map(fn ($p) => [
                'type' => 'Produk',
                'label' => $p->name,
                'sub' => 'Rp '.number_format((float) $p->price, 0, ',', '.'),
                'url' => route('admin.products.index', ['search' => $p->name]),
            ]);

        $orders = Order::with('user')
            ->where(fn ($w) => $w->where('order_number', 'like', $like)
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)))
            ->latest()->limit(5)->get()
            ->map(fn ($o) => [
                'type' => 'Pesanan',
                'label' => $o->order_number,
                'sub' => trim(($o->user->name ?? 'Tamu').' · '.ucfirst((string) $o->status)),
                'url' => route('admin.orders.index', ['search' => $o->order_number]),
            ]);

        $users = User::where('name', 'like', $like)->orWhere('email', 'like', $like)->limit(5)->get()
            ->map(fn ($u) => [
                'type' => 'Pengguna',
                'label' => $u->name,
                'sub' => $u->email,
                'url' => route('admin.users.index', ['search' => $u->name]),
            ]);

        $categories = Category::where('name', 'like', $like)->limit(5)->get()
            ->map(fn ($c) => [
                'type' => 'Kategori',
                'label' => $c->name,
                'sub' => 'Kategori produk',
                'url' => route('admin.categories.index', ['search' => $c->name]),
            ]);

        return response()->json([
            'results' => $products->concat($orders)->concat($users)->concat($categories)->values(),
        ]);
    }
}
