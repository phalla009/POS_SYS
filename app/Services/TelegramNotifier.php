<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    public static function send(string $message): bool
    {
        $token  = config('services.telegram.token');
        $chatId = config('services.telegram.chat_id');

        if (!$token || !$chatId) {
            Log::warning('Telegram: token or chat_id is empty (check .env and run php artisan config:clear)');
            return false;
        }

        try {
            $http = Http::timeout(10);

            // temporary workaround for local SSL interception (cURL error 60)
            if (config('services.telegram.verify_ssl') === false) {
                $http = $http->withoutVerifying();
            }

            $res = $http->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => $message,
                'parse_mode' => 'HTML',
            ]);

            if ($res->failed()) {
                Log::warning('Telegram API error: ' . $res->status() . ' ' . $res->body());
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Telegram exception: ' . $e->getMessage());
            return false;
        }
    }
}
