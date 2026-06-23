<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

class VpyTelegramApi {
    public string $token;
    public string $api;

    public function __construct(?string $token = null) {
        $this->token = $token ?: VPY_TELEGRAM_BOT_TOKEN ?: vpy_setting('telegram_bot_token', '');
        $this->api = 'https://api.telegram.org/bot' . $this->token . '/';
    }

    public function call(string $method, array $params = []) {
        if (!$this->token) return ['ok' => false, 'error' => 'no_token'];
        $url = $this->api . $method;
        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($params),
                'timeout' => 8,
                'ignore_errors' => true,
            ]
        ]);
        $resp = @file_get_contents($url, false, $ctx);
        return $resp === false ? ['ok' => false] : (json_decode($resp, true) ?: ['ok' => false]);
    }

    public function send(int|string $chat_id, string $text, array $extras = []) {
        return $this->call('sendMessage', array_merge([
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ], $extras));
    }

    public function setWebhook(string $url) {
        return $this->call('setWebhook', ['url' => $url]);
    }

    public function deleteWebhook() {
        return $this->call('deleteWebhook');
    }

    public function inlineKeyboard(array $rows) {
        return ['reply_markup' => json_encode(['inline_keyboard' => $rows])];
    }
}
