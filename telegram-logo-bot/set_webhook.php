<?php
require_once __DIR__ . '/config.php';

$webhook_url = 'https://vatanparvaryaypan.uz/telegram-logo-bot/webhook.php';

$ch = curl_init(API_URL . 'setWebhook');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['url' => $webhook_url]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

echo $result;
