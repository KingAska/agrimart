<?php

if (!function_exists('format_phone')) {
    function format_phone($phone)
    {
        // hapus karakter selain angka
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // jika diawali 0 → ubah jadi 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // format tampilan
        $formatted = '+62 ' .
            substr($phone, 2, 4) . ' ' .
            substr($phone, 6, 4) . ' ' .
            substr($phone, 10);

        return [
            'raw' => $phone,       // untuk link wa
            'formatted' => $formatted // untuk tampilan
        ];
    }
}