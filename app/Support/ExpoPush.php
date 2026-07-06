<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Http;

/**
 * Tiny helper around the Expo Push API. All sends are best-effort: they never throw,
 * so a push failure can't break the request that triggered it.
 */
class ExpoPush
{
    /** Send a push to a single user (no-op if they have no saved token). */
    public static function toUser(?User $user, string $title, string $body, array $data = []): void
    {
        if (!$user || empty($user->expo_push_token)) {
            return;
        }
        self::send([$user->expo_push_token], $title, $body, $data);
    }

    /** Send the same push to many Expo tokens (chunked — Expo accepts up to 100 per call). */
    public static function toTokens(array $tokens, string $title, string $body, array $data = []): int
    {
        $tokens = array_values(array_unique(array_filter($tokens)));
        foreach (array_chunk($tokens, 100) as $chunk) {
            self::send($chunk, $title, $body, $data);
        }
        return count($tokens);
    }

    private static function send(array $to, string $title, string $body, array $data): void
    {
        try {
            Http::acceptJson()->timeout(10)->post('https://exp.host/--/api/v2/push/send', [
                'to' => count($to) === 1 ? $to[0] : $to,
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'priority' => 'high',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            // best-effort — ignore push transport failures
        }
    }
}
