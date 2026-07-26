<?php

namespace App\Services\Chatbot;

use App\Exceptions\CheckoutException;
use App\Exceptions\PaymentGatewayException;
use App\Models\Address;
use App\Models\Product;
use App\Models\User;
use App\Services\BiteshipService;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;

/**
 * The buying half of the chatbot: filling the cart from [[buy]] tags, offering
 * couriers, and placing the order.
 *
 * Every method returns a reply payload — `status` plus the message the bot should
 * say (content / buttons / suggestions). Nothing here touches Livewire: appending
 * to the transcript, firing `cart-updated`, and redirecting are the component's
 * job, decided from `status`.
 */
class ChatbotCheckout
{
    /** How many courier options to show before it stops being a choice. */
    private const MAX_RATE_BUTTONS = 4;

    public function __construct(
        private readonly CartService $cart,
        private readonly BiteshipService $biteship,
        private readonly OrderService $orders,
    ) {}

    /**
     * Add every product the AI tagged as a buy request to the cart.
     *
     * @param  array<int, array{name: string, qty: int}>  $buyRequests
     * @return array{status: string, text: string, buttons: array, added: int}
     */
    public function fulfillBuyRequests(array $buyRequests): array
    {
        $added = [];        // ['name' => ..., 'qty' => ...]
        $failed = [];       // human-readable failure reasons
        $needVariant = [];  // products the customer must pick a variant for

        foreach ($buyRequests as $req) {
            $product = Product::where('name', $req['name'])->first();

            if (! $product) {
                $failed[] = "**{$req['name']}** tidak ditemukan di katalog";

                continue;
            }

            if ($product->isOutOfStock()) {
                $failed[] = "**{$product->name}** sedang habis";

                continue;
            }

            if ($product->hasVariants()) {
                // The buy tag only carries a product name, not which portion —
                // adding blindly would charge the base price or be refused when
                // the stock lives on a variant. Send them to the product page.
                $needVariant[] = $product;
                $failed[] = "**{$product->name}** punya beberapa varian — silakan pilih dulu di halaman produk";

                continue;
            }

            $result = $this->cart->add($product->id, $req['qty']);

            if ($result['success'] ?? false) {
                $added[] = ['name' => $product->name, 'qty' => $req['qty']];
            } else {
                $failed[] = "**{$product->name}** (".($result['message'] ?? 'stok tidak mencukupi').')';
            }
        }

        $buttons = [];

        if (! empty($added)) {
            if (count($added) === 1) {
                // Keep the familiar single-product confirmation copy.
                $one = $added[0];
                $text = "Saya sudah berhasil memasukkan **{$one['qty']} porsi {$one['name']}** ke keranjang belanja Kakak. Silakan klik tombol di bawah ini untuk memproses pembayaran langsung dari chatbot!";
            } else {
                $lines = array_map(fn ($a) => "• {$a['qty']} porsi {$a['name']}", $added);
                $text = "Siap Kak! Saya sudah memasukkan produk berikut ke keranjang belanja Kakak:\n".implode("\n", $lines)."\n\nSilakan klik tombol di bawah ini untuk memproses pembayaran langsung dari chatbot!";
            }

            if (! empty($failed)) {
                $text .= "\n\nNamun ada yang tidak bisa ditambahkan: ".implode(', ', $failed).'.';
            }

            $buttons[] = [
                'label' => 'Bayar Langsung via Chatbot',
                'action' => 'checkoutDirectly',
                'style' => 'primary',
            ];
            $buttons[] = [
                'label' => 'Buka Halaman Checkout',
                'url' => route('checkout.index'),
                'style' => 'secondary',
            ];
        } else {
            $text = 'Maaf Kak, pesanan belum bisa diproses: '.implode(', ', $failed).'.';
        }

        // Give each variant product a direct button to its page so the customer
        // can pick a portion. A button (not a [[card]] tag) keeps the explanatory
        // line from being stripped by the redundant-text cleanup.
        foreach ($needVariant as $vp) {
            $buttons[] = [
                'label' => 'Pilih Varian: '.$vp->name,
                'url' => route('products.show', $vp),
                'style' => 'secondary',
            ];
        }

        return [
            'status' => empty($added) ? 'nothing_added' : 'added',
            'text' => $text,
            'buttons' => $buttons,
            'added' => count($added),
        ];
    }

    /**
     * Courier choices for the customer's primary address.
     *
     * @return array{status: string, content: string, buttons?: array}
     */
    public function shippingOptions(User $user): array
    {
        if ($blocked = $this->cartProblem()) {
            return $blocked;
        }

        $address = $this->primaryAddress($user);

        if (! $address) {
            return [
                'status' => 'no_address',
                'content' => 'Waduh Kak, Kakak belum menambahkan alamat pengiriman. Silakan tambahkan alamat terlebih dahulu di menu Pengaturan Alamat agar kami dapat memproses pesanan Kakak.',
                'buttons' => [
                    [
                        'label' => 'Tambah Alamat Sekarang',
                        'url' => route('settings.index').'#addresses',
                        'style' => 'primary',
                    ],
                ],
            ];
        }

        $buttons = $this->rateButtons($address);
        $hasRates = ! empty($buttons);

        // There used to be a "JNE Reguler - Rp 9.000" button here for when
        // Biteship returned nothing. It could never work: jne is not among the
        // couriers we ask Biteship for, and the results are filtered to
        // instant/same_day, so the server-side re-quote in OrderService could
        // never match it. It appeared precisely when the customer was already
        // stuck, and every click ended in "pilih ulang kurir" with nothing left
        // to pick. Saying we cannot quote right now is more use than offering a
        // choice that is guaranteed to fail.
        $buttons[] = [
            'label' => 'Atur Pengiriman di Halaman Checkout',
            'url' => route('checkout.index'),
            'style' => $hasRates ? 'secondary' : 'primary',
        ];

        return [
            'status' => $hasRates ? 'quoted' : 'unquotable',
            'content' => $hasRates
                ? "Silakan pilih kurir pengiriman yang Kakak inginkan untuk alamat **{$address->recipient_name} ({$address->city})**:"
                : "Maaf Kak, untuk sementara kami belum bisa menghitung ongkos kirim ke alamat **{$address->recipient_name} ({$address->city})**. Silakan coba lagi beberapa saat lagi, atau lanjutkan lewat halaman checkout ya Kak.",
            'buttons' => $buttons,
        ];
    }

    /**
     * Turn the cart into an order with the courier the customer picked.
     *
     * @return array{status: string, content: string, buttons?: array, suggestions?: array}
     */
    public function placeOrder(User $user, string $courier, string $service, int $cost): array
    {
        if ($blocked = $this->cartProblem()) {
            return $blocked;
        }

        $address = $this->primaryAddress($user);

        if (! $address) {
            return [
                'status' => 'no_address',
                'content' => 'Waduh Kak, Kakak belum menambahkan alamat pengiriman.',
            ];
        }

        // Delegate to the shared service so this chatbot flow and the web
        // checkout build orders identically (and atomically) — see OrderService.
        try {
            // $cost never sets the price — the caller is a public Livewire method,
            // so OrderService re-quotes it. It is passed as the figure the button
            // showed, so an order is stopped rather than silently charged more if
            // the rate moved between the buttons rendering and this click.
            ['order' => $order, 'paymentUrl' => $paymentUrl] = $this->orders->createFromCart($user, [
                'address_id' => $address->id,
                'shipping_courier' => $courier,
                'shipping_service' => $service,
                'source' => 'chatbot',
                'expected_shipping_cost' => $cost,
            ]);
        } catch (CheckoutException $e) {
            return [
                'status' => 'rejected',
                'content' => 'Maaf Kak, pesanan belum bisa diproses: '.$e->getMessage(),
            ];
        } catch (PaymentGatewayException) {
            return [
                'status' => 'gateway_error',
                'content' => 'Maaf Kak, terjadi kendala saat menghubungi payment gateway Pakasir. Silakan coba kembali beberapa saat lagi.',
            ];
        }

        $shippingCost = number_format((float) $order->shipping_cost, 0, ',', '.');

        return [
            'status' => 'placed',
            'content' => "Hore! Pesanan Kakak dengan nomor order **#{$order->order_number}** senilai **{$order->formatted_total}** (sudah termasuk ongkos kirim {$order->shipping_courier} {$order->shipping_service} senilai Rp {$shippingCost}) telah berhasil dibuat.\n\nSilakan klik tombol **Bayar Sekarang** di bawah ini untuk menyelesaikan pembayaran di Pakasir ya Kak!",
            'buttons' => [
                [
                    'label' => 'Bayar Sekarang (Pakasir)',
                    'url' => $paymentUrl,
                    'style' => 'primary',
                ],
                [
                    'label' => 'Lihat Detail Pesanan',
                    'url' => route('orders.show', $order->id),
                    'style' => 'secondary',
                ],
            ],
            'suggestions' => [
                'Cek status pesanan saya',
                'Jam operasional & lokasi toko',
            ],
        ];
    }

    /** Whatever stops the cart from being checked out, or null when it is fine. */
    private function cartProblem(): ?array
    {
        if (empty($this->cart->getItems())) {
            return [
                'status' => 'empty_cart',
                'content' => 'Keranjang belanja Kakak masih kosong. Silakan tambahkan produk terlebih dahulu.',
            ];
        }

        $errors = $this->cart->validateStock();

        if (! empty($errors)) {
            return [
                'status' => 'stock_error',
                'content' => 'Waduh Kak, ada kendala stok: '.implode(' ', $errors),
            ];
        }

        return null;
    }

    private function primaryAddress(User $user): ?Address
    {
        return $user->addresses()->orderByDesc('is_primary')->first();
    }

    /** @return array<int, array> */
    private function rateButtons(Address $address): array
    {
        if (! $address->area_id) {
            return [];
        }

        try {
            $rates = $this->biteship->getShippingRates(
                $address->area_id,
                $this->cart->getItems(),
                null,
                $address->latitude ? (float) $address->latitude : null,
                $address->longitude ? (float) $address->longitude : null
            );
        } catch (\Exception $e) {
            Log::error('Chatbot direct checkout shipping estimation failed: '.$e->getMessage());

            return [];
        }

        $buttons = [];

        foreach (array_slice($rates, 0, self::MAX_RATE_BUTTONS) as $rate) {
            $courierName = strtoupper($rate['courier_code']);
            $serviceName = $rate['courier_service_name'] ?? 'Regular';
            $priceFormatted = 'Rp '.number_format($rate['price'], 0, ',', '.');
            $duration = isset($rate['duration']) ? " ({$rate['duration']})" : '';

            $buttons[] = [
                'label' => "{$courierName} {$serviceName} - {$priceFormatted}{$duration}",
                'action' => "placeDirectOrder('{$rate['courier_code']}', '{$rate['courier_service_code']}', {$rate['price']})",
                'style' => 'primary',
            ];
        }

        return $buttons;
    }
}
