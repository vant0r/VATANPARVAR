<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/telegram.php';
require_once __DIR__ . '/overlay.php';

if (!is_dir(TMP_DIR)) {
    mkdir(TMP_DIR, 0755, true);
}

$update = json_decode(file_get_contents('php://input'), true);

if (!$update) {
    exit;
}

$message = $update['message'] ?? null;
if (!$message) {
    exit;
}

$chat_id = $message['chat']['id'];

if (isset($message['photo'])) {
    $photos = $message['photo'];
    $largest = end($photos);
    $file_id = $largest['file_id'];

    $file_path = tg_get_file_path($file_id);
    if (!$file_path) {
        tg_send_message($chat_id, "Rasmni olishda xatolik yuz berdi.");
        exit;
    }

    $input_path = TMP_DIR . '/' . $file_id . '.jpg';
    $output_path = TMP_DIR . '/' . $file_id . '_out.jpg';

    if (!tg_download_file($file_path, $input_path)) {
        tg_send_message($chat_id, "Rasmni yuklab olishda xatolik yuz berdi.");
        exit;
    }

    $ok = overlay_logo_bottom_right($input_path, LOGO_PATH, $output_path);

    if ($ok) {
        tg_send_photo($chat_id, $output_path);
    } else {
        tg_send_message($chat_id, "Rasmni qayta ishlashda xatolik yuz berdi.");
    }

    @unlink($input_path);
    @unlink($output_path);
} elseif (isset($message['text']) && $message['text'] === '/start') {
    tg_send_message($chat_id, "Rasm yuboring, men unga logotipni qo'yib qaytaraman.");
} else {
    tg_send_message($chat_id, "Iltimos, rasm yuboring.");
}
