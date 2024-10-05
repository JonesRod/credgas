<?php
function generateRandomString($length = 6): string {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ/\!?@#*-%';
    $charactersLength = strlen(string: $characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(min: 0, max: $charactersLength - 1)];
    }
    return $randomString;
}

