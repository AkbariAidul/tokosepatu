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
/**
 * Mengoptimasi gambar (resize & compress) saat diupload.
 * @param string $source_path Path gambar asli yang diupload.
 * @param string $destination_path Path tujuan untuk menyimpan gambar hasil optimasi.
 * @param int $max_width Lebar maksimal gambar.
 * @param int $quality Kualitas kompresi untuk JPG (0-100).
 * @return bool True jika berhasil, False jika gagal.
 */
function optimize_image($source_path, $destination_path, $max_width = 1000, $quality = 80) {
    list($width, $height, $type) = getimagesize($source_path);

    $new_width = $width;
    $new_height = $height;

    // Hitung dimensi baru jika gambar lebih besar dari lebar maksimal
    if ($width > $max_width) {
        $ratio = $width / $height;
        $new_width = $max_width;
        $new_height = $max_width / $ratio;
    }

    $thumb = imagecreatetruecolor($new_width, $new_height);
    $source = null;

    // Buat gambar dari file sumber berdasarkan tipenya
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($source_path);
            // Menjaga transparansi untuk PNG
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            break;
        case IMAGETYPE_GIF:
             $source = imagecreatefromgif($source_path);
             break;
        default:
            return false;
    }

    // Resize dan simpan gambar baru
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    $success = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($thumb, $destination_path, $quality);
            break;
        case IMAGETYPE_PNG:
            // Kompresi PNG (0-9), 9 paling tinggi
            $success = imagepng($thumb, $destination_path, 9); 
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($thumb, $destination_path);
            break;
    }
    
    // Bebaskan memori
    imagedestroy($source);
    imagedestroy($thumb);

    return $success;
}
?>