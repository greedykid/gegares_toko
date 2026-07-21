<?php

namespace App\Services\Chatbot;

use App\Models\Order;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Pure parsing/formatting of the AI's raw reply and the chat transcript. Nothing
 * here mutates component state or dispatches events — the Livewire component
 * orchestrates side effects using the plain data these methods return.
 */
class ChatbotResponseParser
{
    /**
     * Extract the last N text-based messages from a chat history to send as
     * multi-turn conversation context to the AI.
     */
    public function conversationMemory(array $chatHistory, int $maxTurns = 8): array
    {
        $memory = [];
        $textMessages = array_filter($chatHistory, function ($chat) {
            // Only include text messages, skip images
            return isset($chat['content'])
                && (! isset($chat['type']) || $chat['type'] === 'text');
        });

        // Take last N messages
        $recent = array_slice($textMessages, -$maxTurns);

        foreach ($recent as $chat) {
            $memory[] = [
                'role' => $chat['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $chat['content'],
            ];
        }

        return $memory;
    }

    /**
     * Pull the trailing ---SUGGESTIONS--- block off the reply.
     *
     * @return array{text: string, suggestions: array}
     */
    public function extractSuggestions(string $aiText): array
    {
        $suggestions = [];
        if (str_contains($aiText, '---SUGGESTIONS---')) {
            $parts = explode('---SUGGESTIONS---', $aiText, 2);
            $aiText = trim($parts[0]);
            if (isset($parts[1])) {
                $rawSuggestions = array_map('trim', explode('|', trim($parts[1])));
                $suggestions = array_filter($rawSuggestions, fn ($s) => ! empty($s) && mb_strlen($s) < 60);
                $suggestions = array_slice($suggestions, 0, 3); // Max 3 suggestions
            }
        }

        return ['text' => $aiText, 'suggestions' => $suggestions];
    }

    /**
     * Pull every ---BUY---Name|Qty tag off the reply (supports multiple products).
     *
     * @return array{text: string, requests: array<int, array{name: string, qty: int}>}
     */
    public function extractBuyRequests(string $aiText): array
    {
        $buyRequests = [];
        if (str_contains($aiText, '---BUY---')) {
            // Collect every "---BUY---Name|Qty" occurrence.
            if (preg_match_all('/---BUY---([^\n|]+)\|\s*(\d+)/', $aiText, $buyMatches, PREG_SET_ORDER)) {
                foreach ($buyMatches as $m) {
                    $buyRequests[] = [
                        'name' => trim($m[1]),
                        'qty' => max(1, (int) $m[2]),
                    ];
                }
            }
            // Strip all buy tags (and any trailing text after the first one) from the visible reply.
            $aiText = trim(preg_replace('/---BUY---.*$/s', '', $aiText));
        }

        return ['text' => $aiText, 'requests' => $buyRequests];
    }

    /**
     * Turn the AI's [[Product Name]] card markers into plain **bold** text once
     * the product cards have already been matched out of the reply.
     */
    public function stripProductTags(string $aiText): string
    {
        return preg_replace("/\[\[(.*?)\]\]/", '**$1**', $aiText);
    }

    /**
     * Find products the AI referenced with the strict [[Product Name]] format and
     * build renderable product cards (with per-user wishlist state).
     */
    public function matchProducts(string $aiText): array
    {
        $cachedProducts = Cache::remember('products.for_matching', 300, function () {
            return Product::select('id', 'name', 'price', 'stock', 'is_available', 'image', 'slug')->get()->toArray();
        });

        $matchedProducts = [];
        foreach ($cachedProducts as $p) {
            // Only match if the AI strictly used the [[Product Name]] format
            if (preg_match("/\[\[".preg_quote($p['name'], '/')."\]\]/i", $aiText)) {
                $matchedProducts[] = $p;
            }
        }

        $foundProducts = [];
        if (! empty($matchedProducts)) {
            $wishlistedIds = [];
            if (Auth::check()) {
                $wishlistedIds = Wishlist::where('user_id', Auth::id())
                    ->whereIn('product_id', array_column($matchedProducts, 'id'))
                    ->pluck('product_id')
                    ->toArray();
            }

            foreach ($matchedProducts as $p) {
                $foundProducts[] = [
                    'id' => $p['id'],
                    'name' => $p['name'],
                    'price' => 'Rp '.number_format((float) $p['price'], 0, ',', '.'),
                    // A product switched off by the admin reads as out of stock,
                    // so the chat card shows "Habis" instead of a buy button.
                    'stock' => $p['is_available'] ? $p['stock'] : 0,
                    'image' => $p['image'] ? asset('storage/'.$p['image']) : null,
                    'url' => route('products.show', $p['slug']),
                    'inWishlist' => in_array($p['id'], $wishlistedIds),
                ];
            }
        }

        return $foundProducts;
    }

    /**
     * Find #GGR-... order numbers the AI mentioned that belong to the current
     * user, and build renderable order cards.
     */
    public function matchOrders(string $aiText): array
    {
        $foundOrders = [];
        if (Auth::check()) {
            preg_match_all('/#GGR-([Y0-9A-Z-]+)/', $aiText, $matches);
            if (! empty($matches[0])) {
                foreach (array_unique($matches[0]) as $orderNum) {
                    $cleanNum = ltrim($orderNum, '#');
                    $order = Order::where('order_number', $cleanNum)
                        ->where('user_id', Auth::id())
                        ->first();

                    if ($order) {
                        $foundOrders[] = [
                            'number' => $order->order_number,
                            'status' => $order->status_label,
                            'color' => $order->status_color,
                            'total' => $order->formatted_total,
                            'date' => $order->created_at->format('d M Y'),
                            'url' => route('orders.show', $order->id),
                        ];
                    }
                }
            }
        }

        return $foundOrders;
    }

    /**
     * Strip lines that merely restate a product/order whose card is already shown,
     * so the reply text does not duplicate the card content.
     */
    public function cleanRedundantText(string $text, array $products, array $orders): string
    {
        $lines = explode("\n", $text);
        $cleanLines = [];

        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine)) {
                $cleanLines[] = $line;

                continue;
            }

            $isRedundant = false;

            // Aggressively remove ANY line that describes a product when its card will be shown
            foreach ($products as $p) {
                if (stripos($line, $p['name']) !== false) {
                    // Remove list items, bold mentions with descriptions, or lines with price info
                    if (preg_match('/^\s*(\d+\.|\*|-)/', $trimmedLine) || stripos($line, 'Rp') !== false || mb_strlen($trimmedLine) > 40) {
                        $isRedundant = true;
                        break;
                    }
                }
            }

            // Check if line looks like a list item for a found order
            foreach ($orders as $o) {
                if (stripos($line, $o['number']) !== false && (stripos($line, 'Status') !== false || stripos($line, 'Total') !== false)) {
                    $isRedundant = true;
                    break;
                }
            }

            if (! $isRedundant) {
                $cleanLines[] = $line;
            }
        }

        // Clean up excessive blank lines
        $result = trim(implode("\n", $cleanLines));
        $result = preg_replace("/\n{3,}/", "\n\n", $result);

        return $result;
    }

    /**
     * Generate contextual follow-up suggestions based on what the AI just talked about.
     */
    public function fallbackSuggestions(string $aiText, string $context): array
    {
        $suggestions = [];
        $lowerText = mb_strtolower($aiText);

        if ($context === 'image_analysis') {
            $suggestions = ['Ada produk serupa?', 'Tips simpan jajanan ini', 'Lihat semua produk'];
        } elseif (str_contains($lowerText, 'pesanan') || str_contains($lowerText, 'order')) {
            $suggestions = ['Lacak pengiriman', 'Cara bayar pesanan', 'Hubungi CS via WhatsApp'];
        } elseif (str_contains($lowerText, 'rekomendasi') || str_contains($lowerText, 'terlaris')) {
            $suggestions = ['Jajanan untuk acara kantor', 'Yang paling murah?', 'Ada promo hari ini?'];
        } elseif (str_contains($lowerText, 'stok') || str_contains($lowerText, 'habis')) {
            $suggestions = ['Kapan restock?', 'Produk serupa yang tersedia', 'Notifikasi saat tersedia'];
        } elseif (str_contains($lowerText, 'pengiriman') || str_contains($lowerText, 'kirim')) {
            $suggestions = ['Ongkir ke Jakarta Selatan?', 'Estimasi waktu sampai', 'Bisa same day?'];
        } elseif (str_contains($lowerText, 'bayar') || str_contains($lowerText, 'pembayaran')) {
            $suggestions = ['Bisa pakai QRIS?', 'Ada cicilan?', 'Cara pakai virtual account'];
        } else {
            // Generic but contextual
            $suggestions = ['Rekomendasi jajanan terlaris', 'Jam buka toko', 'Cara pesan produk'];
        }

        return array_slice($suggestions, 0, 3);
    }
}
