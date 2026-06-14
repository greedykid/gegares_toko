<?php

namespace App\Services;

class SecurityService
{
    /**
     * Sanitize a string to prevent XSS while allowing safe Markdown rendering.
     * We strip dangerous tags but keep the text for the Markdown parser.
     */
    public static function sanitizeMarkdown(string $content): string
    {
        // Remove known dangerous tags and their contents
        $dangerousTags = [
            '/<script\b[^>]*>([\s\S]*?)<\/script>/i',
            '/<iframe\b[^>]*>([\s\S]*?)<\/iframe>/i',
            '/<object\b[^>]*>([\s\S]*?)<\/object>/i',
            '/<embed\b[^>]*>([\s\S]*?)<\/embed>/i',
            '/<applet\b[^>]*>([\s\S]*?)<\/applet>/i',
            '/<meta\b[^>]*>/i',
            '/<link\b[^>]*>/i',
        ];

        $cleaned = preg_replace($dangerousTags, '', $content);

        // Remove dangerous attributes like onclick, onerror, etc.
        $cleaned = preg_replace('/on\w+\s*=\s*(["\']?).*?\1/i', '', $cleaned);
        
        // Remove javascript: pseudo-protocol
        $cleaned = preg_replace('/javascript\s*:/i', '', $cleaned);

        return $cleaned;
    }

    /**
     * Mask Personally Identifiable Information (PII) like phone numbers and emails.
     */
    public static function maskPII(string $content): string
    {
        // 1. Mask Emails: user@example.com -> u***@example.com
        $content = preg_replace_callback('/([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})/', function($matches) {
            $user = $matches[1];
            $domain = $matches[2];
            $ext = $matches[3];
            $maskedUser = substr($user, 0, 1) . str_repeat('*', max(0, strlen($user) - 1));
            return $maskedUser . '@' . $domain . '.' . $ext;
        }, $content);

        // 2. Mask Indonesian Phone Numbers (various formats)
        // matches 08..., +62..., 62...
        $phoneRegex = '/(\+62|62|0)8[1-9][0-9]{7,10}/';
        $content = preg_replace_callback($phoneRegex, function($matches) {
            $number = $matches[0];
            $visible = substr($number, 0, 4);
            return $visible . str_repeat('*', strlen($number) - 4);
        }, $content);

        return $content;
    }
}
