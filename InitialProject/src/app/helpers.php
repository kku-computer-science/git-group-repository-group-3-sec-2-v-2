<?php

use Illuminate\Support\Str;

/**
 * MAKE AVATAR FUNCTION
 */
if (! function_exists('makeAvatar')) {
    function makeAvatar($fontPath, $dest, $char)
    {
        $path = $dest;
        $image = imagecreate(200, 200);
        $red = rand(0, 255);
        $green = rand(0, 255);
        $blue = rand(0, 255);
        imagecolorallocate($image, $red, $green, $blue);
        $textcolor = imagecolorallocate($image, 255, 255, 255);
        imagettftext($image, 100, 0, 50, 150, $textcolor, $fontPath, $char);
        imagepng($image, $path);
        imagedestroy($image);

        return $path;
    }
}

if (! function_exists('safe_str_limit')) {
    function safe_str_limit($value, int $limit = 100, string $end = '...'): string
    {
        return Str::limit((string) ($value ?? ''), $limit, $end);
    }
}

?>
