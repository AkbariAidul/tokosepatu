<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

if (!isset($_SESSION['user_id'])) { 
    header('Location: /tokosepatu/login.php'); 
    exit(); 
}

// Proses form jika ada data yang dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        foreach ($_POST as $key => $value) {
            // Gunakan REPLACE INTO atau INSERT ... ON DUPLICATE KEY UPDATE
            // untuk menyederhanakan proses: insert jika belum ada, update jika sudah ada.
            $stmt = $pdo->prepare("
                INSERT INTO pengaturan (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?
            ");
            $stmt->execute([$key, $value, $value]);
        }

        $pdo->commit();
        $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Pengaturan berhasil disimpan.'];

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Terjadi kesalahan saat menyimpan pengaturan.'];
    }

    header('Location: pengaturan.php');
    exit();
}

// Ambil semua pengaturan dari database
$stmt = $pdo->query("SELECT * FROM pengaturan");
$pengaturan_list = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Ambil sebagai pasangan key => value

?>

<?php require_once '../../includes/sidebar.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Pengaturan Toko</h1>
    <p class="text-gray-500 mt-1">Kelola informasi umum dan konfigurasi toko Anda dari sini.</p>
</div>

<div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
    <form action="pengaturan.php" method="POST">
        <div class="space-y-6">
            
            <div>
                <label for="nama_toko" class="block text-sm font-medium text-gray-700">Nama Toko</label>
                <input type="text" name="nama_toko" id="nama_toko" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" value="<?= htmlspecialchars($pengaturan_list['nama_toko'] ?? '') ?>">
            </div>

            <div>
                <label for="alamat_toko" class="block text-sm font-medium text-gray-700">Alamat Toko</label>
                <textarea name="alamat_toko" id="alamat_toko" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"><?= htmlspecialchars($pengaturan_list['alamat_toko'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="email_toko" class="block text-sm font-medium text-gray-700">Email Toko (CS)</label>
                    <input type="email" name="email_toko" id="email_toko" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" value="<?= htmlspecialchars($pengaturan_list['email_toko'] ?? '') ?>">
                </div>
                <div>
                    <label for="telepon_toko" class="block text-sm font-medium text-gray-700">Telepon Toko (CS)</label>
                    <input type="text" name="telepon_toko" id="telepon_toko" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" value="<?= htmlspecialchars($pengaturan_list['telepon_toko'] ?? '') ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nomor_rekening" class="block text-sm font-medium text-gray-700">Nomor Rekening Pembayaran</label>
                    <input type="text" name="nomor_rekening" id="nomor_rekening" placeholder="Contoh: BCA - 1234567890 a/n Toko Anda" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" value="<?= htmlspecialchars($pengaturan_list['nomor_rekening'] ?? '') ?>">
                </div>
                <div>
                    <label for="biaya_pengiriman" class="block text-sm font-medium text-gray-700">Biaya Pengiriman Default (Rp)</label>
                    <input type="number" name="biaya_pengiriman" id="biaya_pengiriman" placeholder="Contoh: 15000" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" value="<?= htmlspecialchars($pengaturan_list['biaya_pengiriman'] ?? '') ?>">
                </div>
            </div>

        </div>

        <div class="flex justify-end mt-8">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg shadow-sm transition-transform transform hover:scale-105">
                <i class="fas fa-save mr-2"></i> Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>