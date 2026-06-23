<?php
if (!defined('VATANPARVAR')) require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

function vpy_notify_telegram($message, $chat_id = null) {
    $token = VPY_TELEGRAM_BOT_TOKEN ?: vpy_setting('telegram_bot_token', '');
    $chat = $chat_id ?: (VPY_TELEGRAM_CHAT_ID ?: vpy_setting('telegram_chat_id', ''));
    if (!$token || !$chat) return false;
    $url = 'https://api.telegram.org/bot' . $token . '/sendMessage';
    $payload = http_build_query([
        'chat_id' => $chat,
        'text' => $message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ]);
    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);
    $resp = @file_get_contents($url, false, $ctx);
    return $resp !== false;
}

function vpy_notify_user($user_id, $title, $message, $type = 'info', $url = '') {
    $rows = vpy_read_json('bildirishnomalar', []);
    $rows[] = [
        'id' => vpy_id_next('bildirishnomalar'),
        'user_id' => (int)$user_id,
        'title' => $title,
        'message' => $message,
        'type' => $type,
        'url' => $url,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    vpy_write_json('bildirishnomalar', $rows);
    return true;
}

function vpy_notify_admin($title, $message) {
    foreach (vpy_filter('users', fn($u) => ($u['role'] ?? '') === 'admin') as $a) {
        vpy_notify_user($a['id'], $title, $message, 'admin');
    }
    vpy_notify_telegram("<b>" . htmlspecialchars($title) . "</b>\n" . htmlspecialchars($message));
}

function vpy_notify_unread_count($user_id) {
    return count(vpy_filter('bildirishnomalar', fn($n) => (int)$n['user_id'] === (int)$user_id && empty($n['is_read'])));
}

function vpy_notify_mark_read($id, $user_id) {
    $rows = vpy_read_json('bildirishnomalar', []);
    foreach ($rows as &$r) {
        if ((int)$r['id'] === (int)$id && (int)$r['user_id'] === (int)$user_id) {
            $r['is_read'] = 1;
            $r['read_at'] = date('Y-m-d H:i:s');
        }
    }
    unset($r);
    vpy_write_json('bildirishnomalar', $rows);
}

function vpy_notify_payment_success($user_id, $tariff_name, $amount) {
    $u = vpy_find('users', 'id', $user_id);
    $title = t('bildirish_tolov_muvaffaq', 'To\'lov muvaffaqiyatli');
    $msg = sprintf('%s tarifi faollashtirildi. Summa: %s', $tariff_name, vpy_money($amount));
    vpy_notify_user($user_id, $title, $msg, 'success', '/user/tariflar.php');
    if ($u) {
        vpy_notify_telegram(
            "💰 <b>Yangi to'lov</b>\n" .
            "Foydalanuvchi: " . htmlspecialchars($u['name']) . "\n" .
            "Telefon: " . htmlspecialchars($u['phone']) . "\n" .
            "Tarif: " . htmlspecialchars($tariff_name) . "\n" .
            "Summa: " . vpy_money($amount)
        );
    }
}
