<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Request-scoped security guard for the chatbot: detects banned IPs and records
 * security events, auto-banning an IP once it accumulates enough high/critical
 * violations. Bans and violation counters live in the cache store (no dedicated
 * table), matching the rest of the app's security posture.
 */
class ChatbotGuard
{
    /**
     * Whether the current request's IP is currently banned.
     */
    public function isBanned(): bool
    {
        return Cache::has('banned_ip:' . request()->ip());
    }

    /**
     * Log a chatbot security event and escalate to an auto-ban when the IP
     * trips 5+ high/critical violations within an hour.
     */
    public function logSecurityEvent(string $type, string $severity, ?string $payload = null, array $metadata = []): void
    {
        try {
            Log::warning("Chatbot Security Event: type={$type}, severity={$severity}, ip=" . request()->ip() . ", payload={$payload}");

            // Auto-Ban Logic using Cache
            if (in_array($severity, ['high', 'critical'])) {
                $ip = request()->ip();
                $violationKey = 'security_violations:' . $ip;

                $violations = Cache::get($violationKey, 0) + 1;
                Cache::put($violationKey, $violations, now()->addHour());

                if ($violations >= 5) {
                    Cache::put('banned_ip:' . $ip, true, now()->addDay());
                    Log::warning("IP {$ip} has been automatically banned for 24 hours in cache due to 5+ high/critical security violations.");
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to log security event in cache: " . $e->getMessage());
        }
    }
}
