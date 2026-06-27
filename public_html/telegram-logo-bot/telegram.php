<?php
function tg_call($method, $params = []) {
    $ch = curl_init(API_URL . $method);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

function tg_send_message($chat_id, $text) {
    return tg_call('sendMessage', [
        'chat_id' => $chat_id,
        'text' => $text
    ]);
}

function tg_send_photo($chat_id, $photo_path, $caption = '') {
    return tg_call('sendPhoto', [
        'chat_id' => $chat_id,
        'photo' => new CURLFile($photo_path),
        'caption' => $caption
    ]);
}

function tg_get_file_path($file_id) {
    $response = tg_call('getFile', ['file_id' => $file_id]);
    if (isset($response['result']['file_path'])) {
        return $response['result']['file_path'];
    }
    return null;
}

function tg_download_file($file_path, $save_to) {
    $url = FILE_URL . $file_path;
    $data = file_get_contents($url);
    if ($data === false) {
        return false;
    }
    return file_put_contents($save_to, $data) !== false;
}
