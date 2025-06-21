<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: /tokosepatu/login.php'); exit(); }

$categories = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();

$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_category = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

$sql_base = "FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id";
$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "p.nama_produk LIKE ?";
    $params[] = "%$search%";
}
if (!empty($selected_category)) {
    $where_clauses[] = "p.kategori_id = ?";
    $params[] = $selected_category;
}
$sql_where = "";
if (!empty($where_clauses)) {
    $sql_where = " WHERE " . implode(" AND ", $where_clauses);
}

$stmt_count = $pdo->prepare("SELECT COUNT(p.id) " . $sql_base . $sql_where);
$stmt_count->execute($params);
$total_data = $stmt_count->fetchColumn();
$total_pages = ceil($total_data / $limit);

$stmt = $pdo->prepare("SELECT p.*, k.nama_kategori " . $sql_base . $sql_where . " ORDER BY p.id DESC LIMIT ? OFFSET ?");
foreach ($params as $key => $value) {
    $stmt->bindValue($key + 1, $value);
}
$stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Manajemen Produk</h1>
        <p class="text-gray-500 mt-1">Kelola semua produk di toko Anda.</p>
    </div>
    <a href="tambah.php" class="mt-4 sm:mt-0 flex items-center bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg shadow-sm transition duration-200">
        <i class="fas fa-plus mr-2"></i> Tambah Produk
    </a>
</div>

<div class="bg-white p-6 rounded-2xl shadow-lg">
    <div class="mb-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-medium text-gray-700 mr-2">Filter:</span>
            <a href="produk.php" class="px-3 py-1 text-sm font-medium rounded-full <?= !$selected_category ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">Semua</a>
            <?php foreach ($categories as $category): ?>
                <a href="?kategori=<?= $category['id'] ?>" class="px-3 py-1 text-sm font-medium rounded-full <?= $selected_category == $category['id'] ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>"><?= htmlspecialchars($category['nama_kategori']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="mb-6">
        <form action="produk.php" method="GET">
            <?php if ($selected_category): ?><input type="hidden" name="kategori" value="<?= $selected_category ?>"><?php endif; ?>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-search text-gray-400"></i></div>
                <input type="text" name="search" placeholder="Cari berdasarkan nama produk..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($search) ?>">
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Produk</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Harga</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Stok</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12"><img class="h-12 w-12 rounded-md object-cover" src="/tokosepatu/uploads/<?= htmlspecialchars($product['gambar'] ?? 'default.png') ?>" alt=""></div>
                                <div class="ml-4"><div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($product['nama_produk']) ?></div></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($product['nama_kategori'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold"><?= format_rupiah($product['harga']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $product['stok'] > 10 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>"><?= $product['stok'] ?></span></td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <a href="edit.php?id=<?= $product['id'] ?>" class="text-orange-500 hover:text-orange-700 mr-4"><i class="fas fa-edit"></i></a>
                            <a href="hapus.php?id=<?= $product['id'] ?>" class="text-red-500 hover:text-red-700 delete-btn"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-10 text-gray-500"><p>Tidak ada produk ditemukan.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6 flex justify-center">
    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&kategori=<?= $selected_category ?>" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?= $i == $page ? 'z-10 bg-orange-500 border-orange-500 text-white' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </nav>
</div>

<?php require_once '../../includes/footer.php'; ?>