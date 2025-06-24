<?php
// Langkah 1: Mulai Output Buffering di baris paling atas
ob_start();

require_once '../../config/database.php';
// Panggil header di awal agar session_start() dieksekusi sekali saja dengan benar
require_once '../../includes/header.php';

// Cek otentikasi dan parameter
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: /tokosepatu/login.php');
    exit();
}

$pengaduan_id = $_GET['id'];

// --- LOGIKA PROSES FORM BALASAN ADMIN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['balas_pengaduan'])) {
    $pesan_balasan = trim($_POST['pesan']);

    if (!empty($pesan_balasan)) {
        try {
            $pdo->beginTransaction();
            
            $stmt_insert = $pdo->prepare("INSERT INTO pesan_pengaduan (pengaduan_id, tipe_pengirim, isi_pesan) VALUES (?, 'admin', ?)");
            $stmt_insert->execute([$pengaduan_id, $pesan_balasan]);

            $stmt_update = $pdo->prepare("UPDATE pengaduan SET status = 'Dibalas Admin', tanggal_update = NOW() WHERE id = ?");
            $stmt_update->execute([$pengaduan_id]);

            $pdo->commit();
            $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Balasan berhasil terkirim.'];

        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Terjadi kesalahan saat mengirim balasan.'];
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'warning', 'title' => 'Opps!', 'text' => 'Pesan balasan tidak boleh kosong.'];
    }
    
    // Karena output buffering aktif, redirect ini akan selalu berhasil
    header("Location: detail_pengaduan.php?id=$pengaduan_id");
    exit();
}


// --- LOGIKA PENGAMBILAN DATA UNTUK DITAMPILKAN ---
$stmt_tiket = $pdo->prepare("SELECT p.*, c.nama as nama_customer FROM pengaduan p JOIN customers c ON p.customer_id = c.id WHERE p.id = ?");
$stmt_tiket->execute([$pengaduan_id]);
$tiket = $stmt_tiket->fetch();

if (!$tiket) {
    require_once '../../includes/sidebar.php';
    echo '<div class="p-6"><h1 class="text-2xl font-bold text-red-600">Error: Pengaduan tidak ditemukan.</h1></div>';
    require_once '../../includes/footer.php';
    exit();
}

$stmt_pesan = $pdo->prepare("SELECT * FROM pesan_pengaduan WHERE pengaduan_id = ? ORDER BY tanggal_kirim ASC");
$stmt_pesan->execute([$pengaduan_id]);
$pesan_list = $stmt_pesan->fetchAll();

require_once '../../includes/sidebar.php';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Detail Pengaduan #P-<?= $pengaduan_id ?></h1>
        <p class="text-gray-500 mt-1">Subjek: <span class="font-semibold"><?= htmlspecialchars($tiket['subjek']) ?></span></p>
    </div>
    <a href="pengaduan.php" class="text-orange-500 hover:underline font-semibold">&larr; Kembali ke Daftar Pengaduan</a>
</div>

<div class="bg-white rounded-2xl shadow-lg">
    <div class="p-6 border-b">
        <p class="text-sm text-gray-600">Customer: <span class="font-bold text-gray-800"><?= htmlspecialchars($tiket['nama_customer']) ?></span></p>
    </div>
    <div class="p-6 space-y-6 h-96 overflow-y-auto">
        <?php if (count($pesan_list) > 0): ?>
            <?php foreach ($pesan_list as $pesan): ?>
                <?php if ($pesan['tipe_pengirim'] == 'customer'): ?>
                    <div class="flex justify-start">
                        <div class="max-w-lg bg-gray-100 p-4 rounded-xl rounded-bl-none">
                            <p class="text-gray-800"><?= nl2br(htmlspecialchars($pesan['isi_pesan'])) ?></p>
                            <p class="text-xs text-gray-400 text-right mt-2"><?= date('d M Y, H:i', strtotime($pesan['tanggal_kirim'])) ?></p>
                        </div>
                    </div>
                <?php else: // Pengirim adalah 'admin' ?>
                    <div class="flex justify-end">
                        <div class="max-w-lg bg-orange-500 text-white p-4 rounded-xl rounded-br-none">
                            <p><?= nl2br(htmlspecialchars($pesan['isi_pesan'])) ?></p>
                            <p class="text-xs text-orange-200 text-right mt-2"><?= date('d M Y, H:i', strtotime($pesan['tanggal_kirim'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-gray-500">Belum ada pesan dalam pengaduan ini.</p>
        <?php endif; ?>
    </div>
    <div class="bg-gray-50 p-6 rounded-b-2xl">
        <form action="detail_pengaduan.php?id=<?= $pengaduan_id ?>" method="POST">
            <input type="hidden" name="balas_pengaduan" value="1">
            <div class="relative">
                <textarea name="pesan" rows="3" class="w-full p-4 pr-32 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="Ketik balasan Anda di sini..." required></textarea>
                <button type="submit" class="absolute top-1/2 right-4 transform -translate-y-1/2 bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg shadow-sm">
                    Kirim Balasan
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
require_once '../../includes/footer.php'; 
// Langkah Terakhir: Kirim semua output yang ditampung ke browser
ob_end_flush();
?>