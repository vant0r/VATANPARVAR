<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/api.php';

$raw = file_get_contents('php://input');
if (!$raw) { http_response_code(200); echo 'ok'; exit; }
$update = json_decode($raw, true) ?: [];
vpy_log('telegram_update', 'Yangi xabar', $update);

$bot = new VpyTelegramApi();

if (isset($update['message'])) {
    $msg = $update['message'];
    $chat_id = $msg['chat']['id'];
    $text = trim($msg['text'] ?? '');
    $name = $msg['from']['first_name'] ?? 'Foydalanuvchi';

    if ($text === '/start') {
        $welcome = "🚦 <b>Salom, $name!</b>\n\n";
        $welcome .= "<b>VatanParvar Yaypan</b> rasmiy boti.\n\n";
        $welcome .= "Yaypan avtomaktabi uchun yo'l harakati qoidalari nazariy imtihoniga onlayn tayyorlanish platformasi.\n\n";
        $welcome .= "✅ 4000+ rasmiy savollar\n";
        $welcome .= "✅ Aqlli tahlil\n";
        $welcome .= "✅ 96% imtihon muvaffaqiyati\n\n";
        $welcome .= "Sayt: https://" . VPY_DOMAIN;
        $bot->send($chat_id, $welcome, $bot->inlineKeyboard([
            [['text' => '🌐 Saytni ochish', 'url' => 'https://' . VPY_DOMAIN]],
            [['text' => '📚 Testlarni boshlash', 'url' => 'https://' . VPY_DOMAIN . '/register.php']],
            [['text' => '💰 Tariflar', 'url' => 'https://' . VPY_DOMAIN . '/tariflar.php']],
        ]));
    } elseif ($text === '/help' || $text === '/yordam') {
        $bot->send($chat_id, "<b>Buyruqlar:</b>\n/start — Botni boshlash\n/sayt — Sayt havolasi\n/tariflar — Tariflar ro'yxati\n/aloqa — Aloqa\n/yordam — Yordam");
    } elseif ($text === '/sayt') {
        $bot->send($chat_id, "🌐 https://" . VPY_DOMAIN);
    } elseif ($text === '/tariflar') {
        $tariffs = vpy_filter('tariflar', fn($t) => !empty($t['active']));
        usort($tariffs, fn($a, $b) => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));
        $msg = "<b>📋 Tariflar</b>\n\n";
        foreach ($tariffs as $t) {
            $msg .= "🎯 <b>" . htmlspecialchars($t['name']) . "</b>\n";
            $msg .= "💰 " . number_format((float)$t['price'], 0, '.', ' ') . " so'm / " . htmlspecialchars($t['period_label']) . "\n";
            $msg .= "📝 " . htmlspecialchars($t['description']) . "\n\n";
        }
        $bot->send($chat_id, $msg, $bot->inlineKeyboard([[['text' => '🛒 Sotib olish', 'url' => 'https://' . VPY_DOMAIN . '/tariflar.php']]]));
    } elseif ($text === '/aloqa') {
        $bot->send($chat_id, "📞 Aloqa\n\n" . vpy_setting('contact_phone') . "\n📍 " . vpy_setting('contact_address') . "\n📧 " . vpy_setting('contact_email'));
    } else {
        $bot->send($chat_id, "Buyruqlar ro'yxati uchun /yordam yuboring");
    }
}

http_response_code(200);
echo 'ok';
