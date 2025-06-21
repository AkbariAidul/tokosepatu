<?php
// helpers/functions.php

/**
 * Mengubah angka menjadi format Rupiah.
 *
 * @param int $angka Angka yang akan diformat.
 * @return string String dalam format Rupiah.
 */
function format_rupiah($angka) {
    if (!is_numeric($angka)) {
        return 'Bukan angka';
    }
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Memotong teks dan menambahkan '...' jika terlalu panjang.
 *
 * @param string $text Teks yang akan dipotong.
 * @param int $limit Jumlah maksimal karakter.
 * @return string Teks yang sudah dipotong.
 */
function potong_teks($text, $limit = 50) {
    if (strlen($text) > $limit) {
        return substr($text, 0, $limit) . '...';
    }
    return $text;
}

?>