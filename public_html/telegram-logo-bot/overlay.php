<?php
function overlay_logo_bottom_right($base_image_path, $logo_image_path, $output_path) {
    $base = imagecreatefromstring(file_get_contents($base_image_path));
    if ($base === false) {
        return false;
    }

    $logo = imagecreatefrompng($logo_image_path);
    if ($logo === false) {
        $logo = imagecreatefromstring(file_get_contents($logo_image_path));
    }
    if ($logo === false) {
        return false;
    }

    $base_w = imagesx($base);
    $base_h = imagesy($base);

    $logo_size = (int)($base_w * 0.12);
    if ($logo_size < 60) $logo_size = 60;
    if ($logo_size > 250) $logo_size = 250;

    $margin = (int)($base_w * 0.015);
    if ($margin < 8) $margin = 8;

    $logo_resized = imagecreatetruecolor($logo_size, $logo_size);
    imagecopyresampled(
        $logo_resized, $logo,
        0, 0, 0, 0,
        $logo_size, $logo_size,
        imagesx($logo), imagesy($logo)
    );

    $x = $base_w - $logo_size - $margin;
    $y = $base_h - $logo_size - $margin;

    imagecopy($base, $logo_resized, $x, $y, 0, 0, $logo_size, $logo_size);

    imagejpeg($base, $output_path, 92);

    imagedestroy($base);
    imagedestroy($logo);
    imagedestroy($logo_resized);

    return true;
}
