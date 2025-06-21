<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: /tokosepatu/login.php'); exit(); }

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$params = [];
$sql_where = "";

if (!empty($search)) {
    $sql_where = " WHERE nama_produk LIKE ?";
    $params[] = "%$search%";
}

$stmt = $pdo->prepare("SELECT id, nama_produk, gambar, stok FROM produk" . $sql_where . " ORDER BY stok ASC");
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Manajemen Stok</h1>
    <p class="text-gray-500 mt-1">Perbarui jumlah stok produk dengan cepat.</p>
</div>

<div class="mb-6">
    <form action="stok.php" method="GET">
        <div class="relative">
            <input type="text" name="search" placeholder="Cari nama produk..." class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-500 hover:text-orange-500">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Produk</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ubah Stok</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach($products as $product): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <img src="/tokosepatu/uploads/<?= htmlspecialchars($product['gambar']) ?>" class="w-12 h-12 object-cover rounded-md mr-4">
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($product['nama_produk']) ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span id="stok-display-<?= $product['id'] ?>" class="text-lg font-bold <?= $product['stok'] <= 10 ? 'text-red-500' : 'text-gray-900' ?>">
                            <?= $product['stok'] ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <form class="form-update-stok flex items-center gap-2" data-id="<?= $product['id'] ?>">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="number" name="stok" class="w-24 p-2 border border-gray-300 rounded-md" value="<?= $product['stok'] ?>">
                            <button type="submit" class="bg-orange-500 text-white font-semibold py-2 px-4 rounded-md hover:bg-orange-600">Simpan</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>