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
function format_rupiah_singkat($angka) {
    if (!is_numeric($angka)) {
        return 'Bukan angka';
    }

    $triliun = 1000000000000;
    $miliar = 1000000000;
    $juta = 1000000;

    if ($angka >= $triliun) {
        $hasil = number_format($angka / $triliun, 1, ',', '.');
        return 'Rp ' . $hasil . ' T'; // Contoh: Rp 1,5 T
    } elseif ($angka >= $miliar) {
        $hasil = number_format($angka / $miliar, 1, ',', '.');
        return 'Rp ' . $hasil . ' M'; // Contoh: Rp 2,3 M
    } elseif ($angka >= $juta) {
        $hasil = number_format($angka / $juta, 1, ',', '.');
        return 'Rp ' . $hasil . ' Jt'; // Contoh: Rp 13,7 Jt
    } else {
        // Jika di bawah 1 juta, gunakan format biasa tanpa desimal
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

?>