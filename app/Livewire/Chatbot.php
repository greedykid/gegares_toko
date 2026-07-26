<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\Wishlist;
use App\Services\Chatbot\ChatbotContextBuilder;
use App\Services\Chatbot\ChatbotCheckout;
use App\Services\Chatbot\ChatbotGuard;
use App\Services\Chatbot\ChatbotOrderAnnouncer;
use App\Services\Chatbot\ChatbotResponseParser;
use App\Services\GeminiService;
use App\Services\SecurityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Chatbot extends Component
{
    use WithFileUploads;

    /** Most recent messages kept in history; caps the session row + LW payload. */
    private const MAX_HISTORY = 40;

    public bool $isOpen = false;

    public string $message = '';

    public string $honeyPot = ''; // Honeypot field for bot detection

    // Locked: history is only ever written server-side and rendered read-only, so
    // the browser must not be able to rewrite it — that content is fed back to the
    // AI as memory and is HMAC-signed into the session.
    #[Locked]
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
        $isChatbotOrder = $isOnPaymentPage && $this->announcer()->routeOrderIsFromChatbot();

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
        // Key on the authenticated user, falling back to IP for guests — NOT the
        // session id, which a script could rotate to mint a fresh bucket every
        // request and hammer the paid AI endpoint unthrottled.
        return 'chatbot-'.(Auth::id() ? 'u'.Auth::id() : 'ip'.request()->ip());
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

    protected function announcer(): ChatbotOrderAnnouncer
    {
        return app(ChatbotOrderAnnouncer::class);
    }

    protected function checkout(): ChatbotCheckout
    {
        return app(ChatbotCheckout::class);
    }

    /**
     * Append reply payloads built by the Chatbot services to the transcript.
     * Persists and notifies the browser once for the whole batch, so announcing
     * three settled orders is still a single round of side effects.
     *
     * @param  array<int, array{content: string, buttons?: array, suggestions?: array}>  $replies
     */
    protected function appendReplies(array $replies): void
    {
        if (empty($replies)) {
            return;
        }

        foreach ($replies as $reply) {
            $entry = [
                'role' => 'assistant',
                'content' => $reply['content'],
                'time' => now()->format('H:i'),
            ];

            foreach (['buttons', 'suggestions'] as $extra) {
                if (! empty($reply[$extra])) {
                    $entry[$extra] = $reply[$extra];
                }
            }

            $this->chatHistory[] = $entry;
        }

        $this->persist();
        $this->dispatch('bot-replied');
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

        $this->appendReplies($this->announcer()->recentlyPaid(Auth::id()));
    }

    public function checkRedirectedOrder()
    {
        if (! Auth::check()) {
            return;
        }

        $reply = $this->announcer()->forRedirectedOrder(Auth::id());

        $this->appendReplies($reply ? [$reply] : []);
    }

    public function checkoutDirectly()
    {
        if (! Auth::check()) {
            return $this->redirectRoute('login');
        }

        $this->appendReplies([$this->checkout()->shippingOptions(Auth::user())]);
    }

    public function placeDirectOrder(string $courier, string $service, int $cost)
    {
        if ($this->checkBanStatus()) {
            return;
        }
        if (! Auth::check()) {
            return $this->redirectRoute('login');
        }

        // Drop the courier picker now that a choice has been made, so the
        // transcript does not keep a list of buttons that no longer apply.
        if (! empty($this->chatHistory)) {
            $lastIndex = count($this->chatHistory) - 1;
            if (isset($this->chatHistory[$lastIndex]['content'])
                && str_contains($this->chatHistory[$lastIndex]['content'], 'Silakan pilih kurir pengiriman')) {
                array_pop($this->chatHistory);
            }
        }

        $reply = $this->checkout()->placeOrder(Auth::user(), $courier, $service, $cost);

        if ($reply['status'] === 'placed') {
            $this->dispatch('cart-updated');
            $this->dispatch('wishlist-updated');
            $this->dispatch('toast', type: 'success', message: 'Pesanan berhasil dibuat!');
        }

        $this->appendReplies([$reply]);
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
        // Cap retained history so a long conversation doesn't balloon the session
        // row and the Livewire payload shipped to the browser each request. The
        // AI only reads the last 12 turns anyway.
        if (count($this->chatHistory) > self::MAX_HISTORY) {
            $this->chatHistory = array_slice($this->chatHistory, -self::MAX_HISTORY);
        }

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

        // If the customer says the bot is going in circles, don't spend another
        // AI call repeating ourselves — hand them straight to a human.
        if ($this->userSoundsFrustrated($maskedMsg)) {
            $this->escalateToHuman('Maaf ya Kak kalau jawaban saya belum membantu. Biar lebih cepat dan pasti, Kakak bisa langsung ngobrol dengan admin kami:');
            $this->dispatch('user-messaged');

            return;
        }

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
            // Thread the actual user message so the buy-intent gate reads it
            // directly rather than depending on it already being in history.
            $this->processAiResult($response, 'text_chat', $userMsg);
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

    protected function processAiResult(string $aiText, string $context = 'general', ?string $userMsg = null)
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
            if ($this->userExpressedBuyIntent($userMsg)) {
                ['text' => $aiText, 'buttons' => $foundButtons] = $this->handleBuyRequests($buyRequests, $aiText);
            } else {
                // The model asked to buy, but the customer's own message showed no
                // buy intent (a stray tag, or a prompt-injection attempt). Don't
                // silently fill the cart — surface the products as cards so adding
                // stays an explicit choice.
                foreach ($buyRequests as $req) {
                    $aiText .= ' [['.$req['name'].']]';
                }
            }
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

        // Loop guard: if this reply is basically the previous one again, stop
        // going in circles — add a note and a direct line to a human. Skipped
        // when the reply carries product cards/orders (that is real progress).
        if (empty($foundProducts) && empty($foundOrders) && $this->looksRepetitive($aiText)) {
            $entry['content'] = "Sepertinya jawaban saya belum pas ya, Kak — maaf. Kalau masih kurang jelas, boleh langsung hubungi admin kami biar dibantu lebih cepat.";
            $entry['buttons'] = array_merge($entry['buttons'] ?? [], [[
                'label' => 'Chat Admin via WhatsApp',
                'url' => $this->storeWhatsappUrl('Halo admin Gegares, saya butuh bantuan.'),
                'style' => 'primary',
            ]]);
            $entry['suggestions'] = ['Lihat produk terlaris', 'Jam operasional & lokasi toko'];
        }

        $this->chatHistory[] = $entry;
        $this->persist();
        $this->dispatch('bot-replied');
    }

    /** A wa.me link to the shop's admin, matching the contact-page normalisation. */
    protected function storeWhatsappUrl(string $text = ''): string
    {
        $store = Cache::remember('store_settings', 86400, fn () => (StoreSetting::first() ?? new StoreSetting)->toArray());
        $wa = preg_replace('/[^0-9]/', '', $store['contact_whatsapp'] ?? $store['contact_phone'] ?? '6281234567890');
        if (str_starts_with((string) $wa, '0')) {
            $wa = '62'.substr($wa, 1);
        }

        return 'https://wa.me/'.$wa.($text ? '?text='.rawurlencode($text) : '');
    }

    /**
     * Bail out of the chatbot loop and hand the customer to a human: append a
     * reply with a WhatsApp-admin button. Shared by the frustration and
     * repetition paths so both offer the same clear exit.
     */
    protected function escalateToHuman(string $intro): void
    {
        $this->chatHistory[] = [
            'role' => 'assistant',
            'content' => $intro,
            'time' => now()->format('H:i'),
            'buttons' => [[
                'label' => 'Chat Admin via WhatsApp',
                'url' => $this->storeWhatsappUrl('Halo admin Gegares, saya butuh bantuan.'),
                'style' => 'primary',
            ]],
            'suggestions' => ['Lihat produk terlaris', 'Jam operasional & lokasi toko'],
        ];
        $this->isTyping = false;
        $this->persist();
        $this->dispatch('bot-replied');
    }

    /** Words that say the customer feels the bot is going in circles. */
    protected function userSoundsFrustrated(string $msg): bool
    {
        $t = mb_strtolower($msg);

        foreach ([
            'muter', 'sama aja', 'sama saja', 'gak nyambung', 'ga nyambung', 'nggak nyambung',
            'tidak nyambung', 'gaje', 'gak jelas', 'ga jelas', 'nggak jelas', 'dari tadi',
            'itu itu aja', 'itu-itu aja', 'ngulang', 'gak ngerti kamu', 'gak paham kamu', 'bosen',
        ] as $kw) {
            if (str_contains($t, $kw)) {
                return true;
            }
        }

        return false;
    }

    /** Whether a fresh reply is basically the previous bot reply again. */
    protected function looksRepetitive(string $newText): bool
    {
        if (trim($newText) === '') {
            return false;
        }

        $prev = null;
        for ($i = count($this->chatHistory) - 1; $i >= 0; $i--) {
            if (($this->chatHistory[$i]['role'] ?? null) === 'assistant') {
                $prev = $this->chatHistory[$i]['content'] ?? '';
                break;
            }
        }

        if (! $prev) {
            return false;
        }

        $parser = $this->parser();
        $a = $parser->normalizeForCompare($newText);
        $b = $parser->normalizeForCompare($prev);

        if ($a === '' || $b === '') {
            return false;
        }

        similar_text($a, $b, $percent);

        return $percent >= 85;
    }

    /**
     * Whether the customer's most recent message actually expressed intent to
     * buy. Gates the auto-add so a stray ---BUY--- tag (an over-eager model, or a
     * prompt-injection attempt) cannot fill the cart on its own.
     */
    protected function userExpressedBuyIntent(?string $userMsg = null): bool
    {
        // Prefer the message just sent; fall back to the latest user turn in
        // history (e.g. the image-analysis flow passes nothing).
        $text = $userMsg;
        if ($text === null || $text === '') {
            for ($i = count($this->chatHistory) - 1; $i >= 0; $i--) {
                if (($this->chatHistory[$i]['role'] ?? null) === 'user') {
                    $text = $this->chatHistory[$i]['content'] ?? '';
                    break;
                }
            }
        }

        $text = mb_strtolower((string) $text);

        foreach (['beli', 'pesan', 'pesen', 'order', 'checkout', 'keranjang', 'bungkus', 'tambah', 'ambil', 'mau beli', 'mau pesan', 'mau order'] as $kw) {
            if (str_contains($text, $kw)) {
                return true;
            }
        }

        return false;
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
        if (! Auth::check()) {
            return [
                'text' => 'Maaf Kak, untuk memproses pemesanan, silakan login ke akun Kakak terlebih dahulu agar kami dapat menyiapkan keranjang belanja Anda.',
                'buttons' => [
                    [
                        'label' => 'Login Sekarang',
                        'url' => route('login'),
                        'style' => 'primary',
                    ],
                ],
            ];
        }

        $result = $this->checkout()->fulfillBuyRequests($buyRequests);

        if ($result['added'] > 0) {
            // Refresh UI state once after all items are added.
            $this->dispatch('cart-updated');
            $this->dispatch('wishlist-updated');
            $this->dispatch('toast', type: 'success', message: 'Produk ditambahkan ke keranjang');
        }

        return ['text' => $result['text'], 'buttons' => $result['buttons']];
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
            session(['pending_wishlist_product_id' => $productId]);
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
