<?php
// File: buat_data_pesanan.php (Versi 2.0 - Dengan Biaya Kirim Dinamis)

require_once __DIR__ . '/config/database.php';
echo "<pre>"; // Agar output di browser lebih mudah dibaca

try {
    // 1. Ambil semua data yang diperlukan
    $customer_ids = $pdo->query("SELECT id FROM customers")->fetchAll(PDO::FETCH_COLUMN);
    
    // Ambil produk beserta beratnya
    $products = $pdo->query("SELECT id, harga, berat FROM produk")->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil tarif pengiriman per KG dari pengaturan
    $stmt_setting = $pdo->query("SELECT setting_value FROM pengaturan WHERE setting_key = 'biaya_pengiriman'");
    $biaya_per_kg = $stmt_setting->fetchColumn() ?? 10000; // Default 10rb per kg jika tidak ada
    
    if (count($customer_ids) == 0 || count($products) == 0) {
        die("GAGAL: Pastikan ada data di tabel 'customers' dan 'produk' (termasuk data beratnya) terlebih dahulu.");
    }

    echo "Memulai proses pembuatan data pesanan dummy (v2.0)...\n";
    echo "Tarif pengiriman diatur ke: Rp " . number_format($biaya_per_kg) . "/kg.\n";
    $total_pesanan_dibuat = 0;

    // 2. Loop untuk setiap hari dalam 180 hari terakhir
    for ($i = 180; $i >= 0; $i--) {
        $tanggal_pesanan = date('Y-m-d H:i:s', strtotime("-$i days"));
        $jumlah_pesanan_harian = rand(0, 5);
        if ($jumlah_pesanan_harian == 0) continue;

        echo "\nMembuat $jumlah_pesanan_harian pesanan untuk tanggal " . date('d M Y', strtotime($tanggal_pesanan)) . "...\n";

        // 3. Loop untuk setiap pesanan harian
        for ($j = 0; $j < $jumlah_pesanan_harian; $j++) {
            $pdo->beginTransaction();

            $random_customer_id = $customer_ids[array_rand($customer_ids)];
            $jumlah_item_per_pesanan = rand(1, 3);
            $order_total_harga = 0;
            $order_total_berat = 0; // Berat dalam gram
            $items_in_order = [];

            // 4. Loop untuk setiap item dalam satu pesanan
            for ($k = 0; $k < $jumlah_item_per_pesanan; $k++) {
                $random_product = $products[array_rand($products)];
                $product_id = $random_product['id'];
                $product_harga = $random_product['harga'];
                $product_berat = $random_product['berat'];
                $product_jumlah = rand(1, 2);

                $order_total_harga += $product_harga * $product_jumlah;
                $order_total_berat += $product_berat * $product_jumlah; // Akumulasi berat
                
                $items_in_order[] = [
                    'produk_id' => $product_id,
                    'jumlah' => $product_jumlah,
                    'harga_saat_pesan' => $product_harga
                ];
            }

            // 5. Kalkulasi biaya pengiriman final
            $total_berat_kg = ceil($order_total_berat / 1000); // bulatkan ke atas
            if ($total_berat_kg == 0 && $order_total_berat > 0) $total_berat_kg = 1;
            $final_biaya_pengiriman = $total_berat_kg * $biaya_per_kg;

            // 6. Insert ke tabel 'pesanan' dengan data baru
            $stmt_pesanan = $pdo->prepare(
                "INSERT INTO pesanan (customer_id, tanggal_pesanan, total_harga, biaya_pengiriman, status) 
                 VALUES (?, ?, ?, ?, 'selesai')"
            );
            $stmt_pesanan->execute([$random_customer_id, $tanggal_pesanan, $order_total_harga, $final_biaya_pengiriman]);
            $pesanan_id = $pdo->lastInsertId();

            // 7. Insert ke tabel 'detail_pesanan'
            $stmt_detail = $pdo->prepare("INSERT INTO detail_pesanan (pesanan_id, produk_id, jumlah, harga_saat_pesan) VALUES (?, ?, ?, ?)");
            foreach ($items_in_order as $item) {
                $stmt_detail->execute([$pesanan_id, $item['produk_id'], $item['jumlah'], $item['harga_saat_pesan']]);
            }
            
            $pdo->commit();
            $total_pesanan_dibuat++;
        }
    }

    echo "\n-----------------------------------\n";
    echo "PROSES SELESAI!\n";
    echo "Total $total_pesanan_dibuat pesanan dummy baru berhasil dibuat.\n";
    echo "Silakan refresh halaman Dashboard dan Manajemen Pesanan Anda.\n";
    echo "-----------------------------------\n";

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error: " . $e->getMessage());
}

echo "</pre>";
?>