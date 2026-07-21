<?php

namespace App\Livewire;

use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\BiteshipService;
use App\Services\CartService;
use App\Services\Chatbot\ChatbotContextBuilder;
use App\Services\Chatbot\ChatbotGuard;
use App\Services\Chatbot\ChatbotResponseParser;
use App\Services\GeminiService;
use App\Services\OrderService;
use App\Services\SecurityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Chatbot extends Component
{
    use WithFileUploads;

    public bool $isOpen = false;

    public string $message = '';

    public string $honeyPot = ''; // Honeypot field for bot detection

    public array $chatHistory = [];

    public $image;

    public bool $isTyping = false;

    public function mount()
    {
        // 1. Check if IP is banned
        if ($this->checkBanStatus()) {
            $this->isOpen = true; // Auto-open to show the ban message
            $this->addBotMessage('Sistem keamanan mendeteksi aktivitas mencurigakan dari alamat IP Anda. Akses chatbot telah dibatasi untuk sementara demi menjaga keamanan sistem kami.');

            return;
        }

        if (session()->has('gegares_chat_history')) {
            // 2. Session Integrity Hash Check
            $storedHistory = session('gegares_chat_history');
            $storedHash = session('gegares_chat_hash');
            $calculatedHash = hash_hmac('sha256', serialize($storedHistory), config('app.key'));

            if ($storedHash && hash_equals($storedHash, $calculatedHash)) {
                $this->chatHistory = $storedHistory;
            } else {
                // Potential tampering detected
                $this->logSecurityEvent('session_tampering', 'critical', 'Session hash mismatch');
                $this->resetChatHistory();
            }
        } else {
            $this->resetChatHistory();
        }

        if (request()->hasCookie('gegares_chat_open')) {
            $this->isOpen = request()->cookie('gegares_chat_open') === '1';
        } else {
            $this->isOpen = session('gegares_chat_open', false);
        }

        $isOnPaymentPage = request()->routeIs('orders.payment');
        $isChatbotOrder = false;

        if ($isOnPaymentPage) {
            $routeOrder = request()->route('order');
            $order = null;

            if ($routeOrder instanceof Order) {
                $order = $routeOrder;
            } elseif (is_numeric($routeOrder) || is_string($routeOrder)) {
                $order = Order::find($routeOrder);
            }

            if ($order && str_contains($order->notes ?? '', 'Dipesan otomatis via AI Chatbot')) {
                $isChatbotOrder = true;
            }
        }

        if (request()->query('chatbot_open') == '1' || ($isOnPaymentPage && $isChatbotOrder)) {
            $this->isOpen = true;
            $this->checkRedirectedOrder();
        }

        session(['gegares_chat_open' => $this->isOpen]);

        if ($this->isOpen) {
            $this->checkRecentPaidOrders();
        }
    }

    protected function getRateLimitKey()
    {
        return 'chatbot-'.session()->getId();
    }

    // ─────────────────────────────────────────────────────────────
    //  COLLABORATORS: pure logic lives in dedicated service classes,
    //  this component orchestrates them and owns the UI side effects.
    // ─────────────────────────────────────────────────────────────

    protected function context(): ChatbotContextBuilder
    {
        return app(ChatbotContextBuilder::class);
    }

    protected function parser(): ChatbotResponseParser
    {
        return app(ChatbotResponseParser::class);
    }

    protected function guard(): ChatbotGuard
    {
        return app(ChatbotGuard::class);
    }

    public function toggleChat()
    {
        $this->isOpen = ! $this->isOpen;
        session(['gegares_chat_open' => $this->isOpen]);

        if ($this->isOpen) {
            $this->checkRecentPaidOrders();
            $this->dispatch('chat-opened');
        }
    }

    public function checkRecentPaidOrders()
    {
        if (! $this->isOpen || ! Auth::check()) {
            return;
        }

        // Get paid orders from the last 2 hours
        $recentPaidOrders = Order::where('user_id', Auth::id())
            ->where('payment_status', 'paid')
            ->where('paid_at', '>=', now()->subHours(2))
            ->where('notes', 'LIKE', '%Dipesan otomatis via AI Chatbot%')
            ->get();

        if ($recentPaidOrders->isEmpty()) {
            return;
        }

        $acknowledged = session('gegares_acknowledged_paid_orders', []);
        $updated = false;

        foreach ($recentPaidOrders as $order) {
            if (in_array($order->id, $acknowledged)) {
                continue;
            }

            // Acknowledge this order ID
            $acknowledged[] = $order->id;
            $updated = true;

            // Generate bot message confirming payment success
            $content = "Yey! Pembayaran untuk pesanan dengan nomor order **#{$order->order_number}** senilai **{$order->formatted_total}** telah berhasil kami terima. Terima kasih banyak ya Kak! Pesanan Kakak akan segera kami proses dan kirim.";

            // Add detail / history buttons and follow-up suggestions
            $this->chatHistory[] = [
                'role' => 'assistant',
                'content' => $content,
                'time' => now()->format('H:i'),
                'buttons' => [
                    [
                        'label' => 'Lihat Detail Pesanan',
                        'url' => route('orders.show', $order->id),
                        'style' => 'primary',
                    ],
                    [
                        'label' => 'Lihat Riwayat Pesanan',
                        'url' => route('orders.index'),
                        'style' => 'secondary',
                    ],
                ],
                'suggestions' => [
                    'Lacak pengiriman',
                    'Jam operasional & lokasi toko',
                    'Hubungi CS via WhatsApp',
                ],
            ];
        }

        if ($updated) {
            session(['gegares_acknowledged_paid_orders' => $acknowledged]);
            $this->persist();
            $this->dispatch('bot-replied');
        }
    }

    public function checkRedirectedOrder()
    {
        if (! Auth::check()) {
            return;
        }

        $routeOrder = request()->route('order');
        $order = null;

        if ($routeOrder instanceof Order) {
            $order = $routeOrder->fresh();
        } elseif (is_numeric($routeOrder) || is_string($routeOrder)) {
            $order = Order::find($routeOrder);
        }

        if (! $order || (int) $order->user_id !== (int) Auth::id()) {
            return;
        }

        // Only announce for chatbot orders, unless chatbot_open query param is explicitly 1
        $isChatbotOrder = str_contains($order->notes ?? '', 'Dipesan otomatis via AI Chatbot');
        if (! $isChatbotOrder && request()->query('chatbot_open') !== '1') {
            return;
        }

        if ($order->payment_status === 'paid') {
            if (! $isChatbotOrder) {
                return;
            }
            $acknowledged = session('gegares_acknowledged_paid_orders', []);
            if (! in_array($order->id, $acknowledged)) {
                $acknowledged[] = $order->id;
                session(['gegares_acknowledged_paid_orders' => $acknowledged]);

                $content = "Yey! Pembayaran untuk pesanan dengan nomor order **#{$order->order_number}** senilai **{$order->formatted_total}** telah berhasil kami terima. Terima kasih banyak ya Kak! Pesanan Kakak akan segera kami proses dan kirim.";

                $this->chatHistory[] = [
                    'role' => 'assistant',
                    'content' => $content,
                    'time' => now()->format('H:i'),
                    'buttons' => [
                        [
                            'label' => 'Lihat Detail Pesanan',
                            'url' => route('orders.show', $order->id),
                            'style' => 'primary',
                        ],
                        [
                            'label' => 'Lihat Riwayat Pesanan',
                            'url' => route('orders.index'),
                            'style' => 'secondary',
                        ],
                    ],
                    'suggestions' => [
                        'Lacak pengiriman',
                        'Jam operasional & lokasi toko',
                        'Hubungi CS via WhatsApp',
                    ],
                ];
                $this->persist();
                $this->dispatch('bot-replied');
            }
        } else {
            $acknowledgedUnpaid = session('gegares_acknowledged_unpaid_orders', []);
            if (! in_array($order->id, $acknowledgedUnpaid)) {
                $acknowledgedUnpaid[] = $order->id;
                session(['gegares_acknowledged_unpaid_orders' => $acknowledgedUnpaid]);

                $content = "Halo Kak! Pembayaran untuk pesanan dengan nomor order **#{$order->order_number}** senilai **{$order->formatted_total}** belum selesai atau belum kami terima.\n\nSilakan selesaikan pembayaran Kakak dengan mengeklik tombol **Bayar Sekarang** di bawah ini agar pesanan Kakak dapat segera kami proses.";

                $buttons = [];
                if ($order->pakasir_link) {
                    $buttons[] = [
                        'label' => 'Bayar Sekarang (Pakasir)',
                        'url' => $order->pakasir_link,
                        'style' => 'primary',
                    ];
                }
                $buttons[] = [
                    'label' => 'Lihat Detail Pesanan',
                    'url' => route('orders.show', $order->id),
                    'style' => 'secondary',
                ];

                $this->chatHistory[] = [
                    'role' => 'assistant',
                    'content' => $content,
                    'time' => now()->format('H:i'),
                    'buttons' => $buttons,
                    'suggestions' => [
                        'Cek status pesanan saya',
                        'Cara bayar pesanan',
                        'Hubungi CS via WhatsApp',
                    ],
                ];
                $this->persist();
                $this->dispatch('bot-replied');
            }
        }
    }

    public function checkoutDirectly()
    {
        if (! Auth::check()) {
            return $this->redirectRoute('login');
        }

        $cartService = app(CartService::class);
        $cartItems = $cartService->getItems();

        if (empty($cartItems)) {
            $this->addBotMessage('Keranjang belanja Kakak masih kosong. Silakan tambahkan produk terlebih dahulu.');

            return;
        }

        $errors = $cartService->validateStock();
        if (! empty($errors)) {
            $this->addBotMessage('Waduh Kak, ada kendala stok: '.implode(' ', $errors));

            return;
        }

        $user = Auth::user();
        $address = $user->addresses()->orderByDesc('is_primary')->first();

        if (! $address) {
            $this->chatHistory[] = [
                'role' => 'assistant',
                'content' => 'Waduh Kak, Kakak belum menambahkan alamat pengiriman. Silakan tambahkan alamat terlebih dahulu di menu Pengaturan Alamat agar kami dapat memproses pesanan Kakak.',
                'time' => now()->format('H:i'),
                'buttons' => [
                    [
                        'label' => 'Tambah Alamat Sekarang',
                        'url' => route('settings.index').'#addresses',
                        'style' => 'primary',
                    ],
                ],
            ];
            $this->persist();
            $this->dispatch('bot-replied');

            return;
        }

        // Get shipping rates from Biteship
        $buttons = [];
        $hasRates = false;

        if ($address->area_id) {
            $biteshipService = app(BiteshipService::class);
            try {
                $rates = $biteshipService->getShippingRates(
                    $address->area_id,
                    $cartItems,
                    null,
                    $address->latitude ? (float) $address->latitude : null,
                    $address->longitude ? (float) $address->longitude : null
                );

                if (! empty($rates)) {
                    $hasRates = true;
                    // Limit to top 4 options
                    $limitedRates = array_slice($rates, 0, 4);
                    foreach ($limitedRates as $rate) {
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
                }
            } catch (\Exception $e) {
                Log::error('Chatbot direct checkout shipping estimation failed: '.$e->getMessage());
            }
        }

        // Fallback option if Biteship fails or empty
        if (! $hasRates) {
            $buttons[] = [
                'label' => 'JNE Reguler - Rp 9.000',
                'action' => "placeDirectOrder('jne', 'reg', 9000)",
                'style' => 'primary',
            ];
        }

        // Add a secondary button to do full checkout
        $buttons[] = [
            'label' => 'Atur Pengiriman di Halaman Checkout',
            'url' => route('checkout.index'),
            'style' => 'secondary',
        ];

        // Ask user to select courier
        $this->chatHistory[] = [
            'role' => 'assistant',
            'content' => "Silakan pilih kurir pengiriman yang Kakak inginkan untuk alamat **{$address->recipient_name} ({$address->city})**:",
            'time' => now()->format('H:i'),
            'buttons' => $buttons,
        ];

        $this->persist();
        $this->dispatch('bot-replied');
    }

    public function placeDirectOrder(string $courier, string $service, int $cost)
    {
        if ($this->checkBanStatus()) {
            return;
        }
        if (! Auth::check()) {
            return $this->redirectRoute('login');
        }

        // Remove the courier selection message from history to keep it clean
        if (! empty($this->chatHistory)) {
            $lastIndex = count($this->chatHistory) - 1;
            if (isset($this->chatHistory[$lastIndex]['content'])
                && str_contains($this->chatHistory[$lastIndex]['content'], 'Silakan pilih kurir pengiriman')) {
                array_pop($this->chatHistory);
            }
        }

        $cartService = app(CartService::class);
        $cartItems = $cartService->getItems();

        if (empty($cartItems)) {
            $this->addBotMessage('Keranjang belanja Kakak masih kosong. Silakan tambahkan produk terlebih dahulu.');

            return;
        }

        $errors = $cartService->validateStock();
        if (! empty($errors)) {
            $this->addBotMessage('Waduh Kak, ada kendala stok: '.implode(' ', $errors));

            return;
        }

        $user = Auth::user();
        $address = $user->addresses()->orderByDesc('is_primary')->first();

        if (! $address) {
            $this->addBotMessage('Waduh Kak, Kakak belum menambahkan alamat pengiriman.');

            return;
        }

        // Delegate to the shared service so this chatbot flow and the web
        // checkout build orders identically (and atomically) — see OrderService.
        try {
            ['order' => $order, 'paymentUrl' => $paymentUrl] = app(OrderService::class)
                ->createFromCart($user, [
                    'address_id' => $address->id,
                    'shipping_courier' => $courier,
                    'shipping_service' => $service,
                    'shipping_cost' => $cost,
                    'notes' => 'Dipesan otomatis via AI Chatbot',
                ]);
        } catch (PaymentGatewayException $e) {
            $this->addBotMessage('Maaf Kak, terjadi kendala saat menghubungi payment gateway Pakasir. Silakan coba kembali beberapa saat lagi.');

            return;
        }

        // Dispatch events
        $this->dispatch('cart-updated');
        $this->dispatch('wishlist-updated');
        $this->dispatch('toast', type: 'success', message: 'Pesanan berhasil dibuat!');

        // Append success message with payment link
        $this->chatHistory[] = [
            'role' => 'assistant',
            'content' => "Hore! Pesanan Kakak dengan nomor order **#{$order->order_number}** senilai **{$order->formatted_total}** (sudah termasuk ongkos kirim {$order->shipping_courier} {$order->shipping_service} senilai Rp ".number_format($cost, 0, ',', '.').") telah berhasil dibuat.\n\nSilakan klik tombol **Bayar Sekarang** di bawah ini untuk menyelesaikan pembayaran di Pakasir ya Kak!",
            'time' => now()->format('H:i'),
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

        $this->persist();
        $this->dispatch('bot-replied');
    }

    public function clearChat()
    {
        $this->resetChatHistory();
        $this->persist();
        $this->dispatch('chat-cleared');
    }

    protected function resetChatHistory()
    {
        $this->chatHistory = [
            [
                'role' => 'assistant',
                'content' => 'Halo! Saya asisten Gegares. Ada yang bisa saya bantu? Kamu juga bisa kirim foto jajanan pasar untuk tanya namanya lho!',
                'time' => now()->format('H:i'),
                'suggestions' => [
                    'Rekomendasi jajanan terlaris',
                    'Cek status pesanan saya',
                    'Jam operasional & lokasi toko',
                    'Cara pesan & metode bayar',
                ],
            ],
        ];
        $this->persist();
    }

    protected function persist()
    {
        session(['gegares_chat_history' => $this->chatHistory]);

        // Generate integrity hash
        $hash = hash_hmac('sha256', serialize($this->chatHistory), config('app.key'));
        session(['gegares_chat_hash' => $hash]);
    }

    protected function checkBanStatus(): bool
    {
        return $this->guard()->isBanned();
    }

    protected function logSecurityEvent(string $type, string $severity, ?string $payload = null, array $metadata = [])
    {
        $this->guard()->logSecurityEvent($type, $severity, $payload, $metadata);
    }

    // ─────────────────────────────────────────────────────────────
    //  IMAGE HANDLING (Snap & Buy)
    // ─────────────────────────────────────────────────────────────

    public function updatedImage()
    {
        if ($this->checkBanStatus()) {
            return;
        }
        // 1. Honeypot check
        if (! empty($this->honeyPot)) {
            $this->logSecurityEvent('honeypot_trip', 'medium', 'Image Upload Attempt');

            return;
        }

        // 2. Rate limiting (15 per minute)
        if (RateLimiter::tooManyAttempts($this->getRateLimitKey(), 15)) {
            $this->logSecurityEvent('rate_limit', 'low', 'Image: '.$this->image?->getClientOriginalName());
            $this->addBotMessage('Waduh, pelan-pelan Kak. Gegares Assistant butuh waktu sebentar untuk memproses gambar Kakak.');
            $this->image = null;

            return;
        }
        RateLimiter::hit($this->getRateLimitKey(), 60);

        $this->validate([
            'image' => 'image|max:2048', // 2MB Max
        ]);

        $this->isTyping = true;

        $path = $this->image->getRealPath();
        $base64 = base64_encode(file_get_contents($path));
        $tempUrl = $this->image->temporaryUrl();

        $this->chatHistory[] = [
            'role' => 'user',
            'type' => 'image',
            'content' => $tempUrl,
            'time' => now()->format('H:i'),
        ];

        $this->persist();
        $this->image = null; // Clear upload
        $this->dispatch('process-image', base64: $base64);
    }

    #[On('process-image')]
    public function processImage(string $base64)
    {
        $this->isTyping = true;

        $service = app(GeminiService::class);
        $result = $service->analyzeImage($base64, $this->context()->imageAnalysisPrompt());

        if ($result === 'MODERATION_BLOCK') {
            $this->logSecurityEvent('moderation_block', 'high', 'Image analysis content blocked');
            $this->addBotMessage('Maaf, gambar tersebut tidak dapat diproses karena melanggar kebijakan konten kami.');
        } elseif ($result) {
            $this->processAiResult($result, 'image_analysis');
        } else {
            $this->addBotMessage('Maaf, saya tidak bisa mengenali gambar tersebut. Coba foto yang lebih jelas ya!');
        }

        $this->isTyping = false;
        $this->persist();
    }

    // ─────────────────────────────────────────────────────────────
    //  TEXT MESSAGE HANDLING
    // ─────────────────────────────────────────────────────────────

    public function sendTemplate(string $text)
    {
        $this->message = $text;
        $this->sendMessage();
    }

    public function sendMessage()
    {
        if ($this->checkBanStatus()) {
            return;
        }
        // 1. Honeypot check
        if (! empty($this->honeyPot)) {
            $this->logSecurityEvent('honeypot_trip', 'medium', 'Text: '.$this->message);

            return;
        }

        $userMsg = trim($this->message);
        if ($userMsg === '') {
            return;
        }

        // 2. Input Length Validation
        if (mb_strlen($userMsg) > 500) {
            $this->logSecurityEvent('input_overflow', 'low', substr($userMsg, 0, 100).'...');
            $this->addBotMessage('Maaf Kak, pesannya terlalu panjang. Maksimal 500 karakter saja ya agar Gegares bisa memprosesnya dengan baik.');

            return;
        }

        // 3. Rate limiting (15 per minute)
        if (RateLimiter::tooManyAttempts($this->getRateLimitKey(), 15)) {
            $this->logSecurityEvent('rate_limit', 'low', $userMsg);
            $this->addBotMessage('Waduh, pelan-pelan Kak. Gegares Assistant butuh napas sebentar untuk menjawab semua pesan Kakak.');

            return;
        }
        RateLimiter::hit($this->getRateLimitKey(), 60);

        $this->message = '';

        // Sanitize & Mask PII before storing
        $cleanMsg = SecurityService::sanitizeMarkdown($userMsg);
        $maskedMsg = SecurityService::maskPII($cleanMsg);

        $this->chatHistory[] = [
            'role' => 'user',
            'type' => 'text',
            'content' => $maskedMsg,
            'time' => now()->format('H:i'),
        ];

        $this->isTyping = true;
        $this->persist();
        $this->dispatch('process-ai', userMsg: $maskedMsg);
        $this->dispatch('user-messaged');
    }

    #[On('process-ai')]
    public function processAi(string $userMsg)
    {
        $this->isTyping = true;

        $service = app(GeminiService::class);

        // ── Build structured system prompt ──
        $systemPrompt = $this->context()->systemPrompt();

        // ── Build conversation memory (last 12 turns for richer context) ──
        $conversationMemory = $this->parser()->conversationMemory($this->chatHistory, 12);

        // ── Call AI with multi-turn context ──
        $response = $service->chat(
            message: $userMsg,
            systemContext: $systemPrompt,
            conversationHistory: $conversationMemory,
            // Low temperature: answers are grounded factual lookups against the
            // catalog/store context, not creative writing. Higher values made the
            // model invent bank names, opening hours and delivery estimates.
            temperature: 0.2,
            maxTokens: 1024
        );

        if ($response === 'MODERATION_BLOCK') {
            $this->logSecurityEvent('moderation_block', 'high', $userMsg);
            $this->addBotMessage('Maaf, permintaan Anda tidak dapat diproses karena alasan keamanan konten.');
        } elseif ($response) {
            $this->processAiResult($response, 'text_chat');
        } else {
            $this->addBotMessage('Maaf, saya tidak bisa memproses permintaan Anda saat ini.');
        }

        $this->isTyping = false;
        $this->persist();
    }

    // ─────────────────────────────────────────────────────────────
    //  AI RESULT PROCESSING
    // ─────────────────────────────────────────────────────────────

    protected function addBotMessage(string $content, array $suggestions = [])
    {
        $entry = [
            'role' => 'assistant',
            'content' => $content,
            'time' => now()->format('H:i'),
        ];

        if (! empty($suggestions)) {
            $entry['suggestions'] = $suggestions;
        }

        $this->chatHistory[] = $entry;
        $this->persist();
        $this->dispatch('bot-replied');
    }

    protected function processAiResult(string $aiText, string $context = 'general')
    {
        $parser = $this->parser();

        // ── 0. Extract follow-up suggestions from AI response ──
        ['text' => $aiText, 'suggestions' => $suggestions] = $parser->extractSuggestions($aiText);

        // ── 0.5. Extract buy instructions from AI response (supports multiple products) ──
        ['text' => $aiText, 'requests' => $buyRequests] = $parser->extractBuyRequests($aiText);

        // ── 0.6. Fulfil buy intents. Adding to the cart dispatches UI events, so
        //         this side effect stays in the component (not the pure parser). ──
        $foundButtons = [];
        if (! empty($buyRequests)) {
            ['text' => $aiText, 'buttons' => $foundButtons] = $this->handleBuyRequests($buyRequests, $aiText);
        }

        // ── Generate contextual fallback suggestions if AI didn't provide any ──
        if (empty($suggestions)) {
            $suggestions = $parser->fallbackSuggestions($aiText, $context);
        }

        // ── 1. Find products mentioned by AI (while [[tags]] are still present) ──
        $foundProducts = $parser->matchProducts($aiText);

        // Clean the tags from the final displayed text
        $aiText = $parser->stripProductTags($aiText);

        // ── 2. Find orders mentioned by AI (Pattern #GGR-...) ──
        $foundOrders = $parser->matchOrders($aiText);

        // ── 3. Post-Process: Clean redundant text if cards are found ──
        if (! empty($foundProducts) || ! empty($foundOrders)) {
            $aiText = $parser->cleanRedundantText($aiText, $foundProducts, $foundOrders);
        }

        // ── 4. Build the chat entry ──
        $entry = [
            'role' => 'assistant',
            'content' => $aiText,
            'time' => now()->format('H:i'),
        ];

        if (! empty($foundProducts)) {
            $entry['products'] = $foundProducts;
        }
        if (! empty($foundOrders)) {
            $entry['orders'] = $foundOrders;
        }
        if (! empty($foundButtons)) {
            $entry['buttons'] = $foundButtons;
        }
        if (! empty($suggestions)) {
            $entry['suggestions'] = $suggestions;
        }

        $this->chatHistory[] = $entry;
        $this->persist();
        $this->dispatch('bot-replied');
    }

    /**
     * Add the products the AI asked to buy (via ---BUY--- tags) to the cart and
     * build the confirmation copy + checkout buttons. Kept in the component
     * because it dispatches cart/toast UI events.
     *
     * @param  array<int, array{name: string, qty: int}>  $buyRequests
     * @return array{text: string, buttons: array}
     */
    protected function handleBuyRequests(array $buyRequests, string $aiText): array
    {
        $foundButtons = [];

        if (! Auth::check()) {
            $aiText = 'Maaf Kak, untuk memproses pemesanan, silakan login ke akun Kakak terlebih dahulu agar kami dapat menyiapkan keranjang belanja Anda.';
            $foundButtons[] = [
                'label' => 'Login Sekarang',
                'url' => route('login'),
                'style' => 'primary',
            ];

            return ['text' => $aiText, 'buttons' => $foundButtons];
        }

        $cartService = app(CartService::class);
        $added = [];      // ['name' => ..., 'qty' => ...]
        $failed = [];     // human-readable failure reasons

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

            $result = $cartService->add($product->id, $req['qty']);
            if ($result['success'] ?? false) {
                $added[] = ['name' => $product->name, 'qty' => $req['qty']];
            } else {
                $failed[] = "**{$product->name}** (".($result['message'] ?? 'stok tidak mencukupi').')';
            }
        }

        if (! empty($added)) {
            // Refresh UI state once after all items are added.
            $this->dispatch('cart-updated');
            $this->dispatch('wishlist-updated');
            $this->dispatch('toast', type: 'success', message: 'Produk ditambahkan ke keranjang');

            if (count($added) === 1) {
                // Keep the familiar single-product confirmation copy.
                $one = $added[0];
                $aiText = "Saya sudah berhasil memasukkan **{$one['qty']} porsi {$one['name']}** ke keranjang belanja Kakak. Silakan klik tombol di bawah ini untuk memproses pembayaran langsung dari chatbot!";
            } else {
                $lines = array_map(fn ($a) => "• {$a['qty']} porsi {$a['name']}", $added);
                $aiText = "Siap Kak! Saya sudah memasukkan produk berikut ke keranjang belanja Kakak:\n".implode("\n", $lines)."\n\nSilakan klik tombol di bawah ini untuk memproses pembayaran langsung dari chatbot!";
            }

            if (! empty($failed)) {
                $aiText .= "\n\nNamun ada yang tidak bisa ditambahkan: ".implode(', ', $failed).'.';
            }

            $foundButtons[] = [
                'label' => 'Bayar Langsung via Chatbot',
                'action' => 'checkoutDirectly',
                'style' => 'primary',
            ];
            $foundButtons[] = [
                'label' => 'Buka Halaman Checkout',
                'url' => route('checkout.index'),
                'style' => 'secondary',
            ];
        } else {
            // Nothing could be added.
            $aiText = 'Maaf Kak, pesanan belum bisa diproses: '.implode(', ', $failed).'.';
        }

        return ['text' => $aiText, 'buttons' => $foundButtons];
    }

    // ─────────────────────────────────────────────────────────────
    //  CART & WISHLIST ACTIONS
    // ─────────────────────────────────────────────────────────────

    public function addToCart(int $productId)
    {
        if (! Auth::check()) {
            return $this->redirectRoute('login');
        }

        $this->dispatch('add-to-cart-qty', productId: $productId, quantity: 1);
    }

    public function toggleWishlist(int $productId)
    {
        if (! Auth::check()) {
            return $this->redirectRoute('login');
        }

        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->first();

        $product = Product::find($productId);
        $productName = $product?->name ?? 'Produk';

        $inWishlist = false;
        if ($wishlist) {
            $wishlist->delete();
            $this->dispatch('toast', type: 'info', message: "{$productName} dihapus dari wishlist");
            $inWishlist = false;
        } else {
            Wishlist::create([
                'user_id' => Auth::id(),
                'product_id' => $productId,
            ]);
            $this->dispatch('toast', type: 'success', message: "{$productName} ditambahkan ke wishlist");
            $inWishlist = true;
        }

        // Sync local chat history state for this product
        foreach ($this->chatHistory as &$chat) {
            if (isset($chat['products'])) {
                foreach ($chat['products'] as &$p) {
                    if ($p['id'] == $productId) {
                        $p['inWishlist'] = $inWishlist;
                    }
                }
            }
        }

        $this->persist();
        $this->dispatch('wishlist-updated');
    }

    public function render()
    {
        return view('livewire.chatbot');
    }
}
