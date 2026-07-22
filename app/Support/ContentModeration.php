<?php

namespace App\Support;

/**
 * A light content gate for customer reviews.
 *
 * Reviews publish instantly by design, so the shop's rating updates without a
 * moderator in the loop. This keeps that fast path for ordinary reviews while
 * holding back the obviously abusive ones for an admin to look at first — the
 * caller stores those as not-approved rather than dropping them.
 *
 * Whole-word matching on a normalised string, deliberately: it would rather let
 * a borderline word through than hold back a genuine review because "assalam"
 * happens to contain "ass".
 */
class ContentModeration
{
    /** Words that hold a review for moderation instead of publishing it. */
    protected static array $blocklist = [
        'anjing', 'anjg', 'anjay', 'bangsat', 'bajingan', 'kontol', 'memek', 'ngentot',
        'entot', 'jancok', 'jancuk', 'asu', 'kampret', 'tolol', 'goblok', 'bego', 'babi',
        'pepek', 'peler', 'titit', 'brengsek', 'keparat', 'bangke', 'tai', 'taik', 'kntl',
        'fuck', 'fck', 'shit', 'bitch', 'asshole', 'bastard', 'dick', 'pussy', 'cunt',
    ];

    public static function containsProfanity(?string $text): bool
    {
        if ($text === null || trim($text) === '') {
            return false;
        }

        // Normalise so light obfuscation still reads: lowercase, undo common
        // leet substitutions, keep only letters/spaces, and collapse a character
        // repeated 3+ times ("anjiiing" -> "anjing").
        $normalized = mb_strtolower($text);
        $normalized = strtr($normalized, [
            '0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's', '7' => 't', '@' => 'a', '$' => 's',
        ]);
        $normalized = preg_replace('/[^a-z\s]/', ' ', $normalized);
        $normalized = preg_replace('/(.)\1{2,}/', '$1', $normalized);

        $words = array_flip(preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        foreach (static::$blocklist as $bad) {
            if (isset($words[$bad])) {
                return true;
            }
        }

        return false;
    }
}
