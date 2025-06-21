<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: /tokosepatu/login.php'); exit(); }

function get_status_badge($status) {
    switch ($status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'diproses': return 'bg-blue-100 text-blue-800';
        case 'dikirim': return 'bg-indigo-100 text-indigo-800';
        case 'selesai': return 'bg-green-100 text-green-800';
        case 'dibatalkan': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

// ===== LOGIKA PENCARIAN BARU =====
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT p.*, c.nama as nama_customer FROM pesanan p JOIN customers c ON p.customer_id = c.id";
$params = [];

if (!empty($search)) {
    $sql .= " WHERE c.nama LIKE ? OR p.id = ?";
    $params[] = "%$search%";
    $params[] = $search;
}
$sql .= " ORDER BY p.tanggal_pesanan DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Manajemen Pesanan</h1>
    <p class="text-gray-500 mt-1">Lacak dan kelola semua pesanan yang masuk.</p>
</div>

<div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="p-4 border-b">
        <form action="pesanan.php" method="GET">
            <div class="relative">
                <input type="text" name="search" placeholder="Cari berdasarkan nama customer atau ID pesanan..." class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-500 hover:text-orange-500"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">ID Pesanan</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">#<?= $order['id'] ?></td>
                        <td class="px-6 py-4 text-sm text-gray-800"><?= htmlspecialchars($order['nama_customer']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= date('d M Y, H:i', strtotime($order['tanggal_pesanan'])) ?></td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900"><?= format_rupiah($order['total_harga']) ?></td>
                        <td class="px-6 py-4 text-center text-sm">
                            <select name="status" class="status-select text-xs font-semibold rounded-md border-0 p-1.5 focus:outline-none focus:ring-2 focus:ring-orange-500 <?= get_status_badge($order['status']) ?>" data-id="<?= $order['id'] ?>">
                                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="diproses" <?= $order['status'] == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                <option value="dikirim" <?= $order['status'] == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                                <option value="selesai" <?= $order['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="dibatalkan" <?= $order['status'] == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-medium">
                            <a href="detail_pesanan.php?id=<?= $order['id'] ?>" class="text-orange-500 hover:text-orange-700 font-semibold">Lihat Detail</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-16"><p class="text-gray-500">Data tidak ditemukan.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>