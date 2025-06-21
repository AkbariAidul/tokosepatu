<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

if (!isset($_SESSION['user_id'])) { 
    header('Location: /tokosepatu/login.php'); 
    exit(); 
}

// Fungsi untuk memberikan badge warna berdasarkan status pengaduan
function get_pengaduan_status_badge($status) {
    switch ($status) {
        case 'Baru':
            return 'bg-blue-100 text-blue-800';
        case 'Dibalas Admin':
            return 'bg-green-100 text-green-800';
        case 'Dibalas Customer':
            return 'bg-orange-100 text-orange-800';
        case 'Ditutup':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Ambil semua data pengaduan, gabungkan dengan nama customer, urutkan berdasarkan update terakhir
$stmt = $pdo->query("
    SELECT p.*, c.nama as nama_customer 
    FROM pengaduan p
    JOIN customers c ON p.customer_id = c.id
    ORDER BY p.tanggal_update DESC
");
$pengaduan_list = $stmt->fetchAll();
?>

<?php require_once '../../includes/sidebar.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Pengaduan Customer Service</h1>
    <p class="text-gray-500 mt-1">Kelola semua pesan dan pengaduan yang masuk dari customer.</p>
</div>

<div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tiket ID</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Subjek</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Update Terakhir</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">

                <?php if (count($pengaduan_list) > 0): ?>
                    <?php foreach ($pengaduan_list as $pengaduan): ?>
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#P-<?= $pengaduan['id'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-semibold"><?= htmlspecialchars($pengaduan['subjek']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($pengaduan['nama_customer']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d M Y, H:i', strtotime($pengaduan['tanggal_update'])) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full <?= get_pengaduan_status_badge($pengaduan['status']) ?>">
                                <?= htmlspecialchars($pengaduan['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <a href="detail_pengaduan.php?id=<?= $pengaduan['id'] ?>" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition-all duration-200 transform hover:scale-105">
                                Balas
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-16">
                            <i class="fas fa-envelope-open-text fa-3x text-gray-300 mb-3"></i>
                            <h3 class="text-lg font-medium text-gray-600">Belum Ada Pengaduan Masuk</h3>
                            <p class="text-sm text-gray-400 mt-1">Saat ada pengaduan dari customer, data akan muncul di sini.</p>
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>