<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: /tokosepatu/login.php'); exit(); }

// ... (logika untuk Tambah, Edit, Hapus tetap sama, tidak perlu diubah) ...
$edit_mode = false;
$customer_data = ['id' => '', 'nama' => '', 'email' => '', 'telepon' => '', 'alamat' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ... (kode proses POST tidak berubah) ...
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    // ... (kode proses delete tidak berubah) ...
}

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    // ... (kode proses edit tidak berubah) ...
}
// Untuk menghindari duplikasi, kita salin ulang logika yang relevan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nama'])) { $id = $_POST['id']; $nama = trim($_POST['nama']); $email = trim($_POST['email']); $telepon = trim($_POST['telepon']); $alamat = trim($_POST['alamat']); if (!empty($nama) && !empty($email)) { if (empty($id)) { $stmt = $pdo->prepare("INSERT INTO customers (nama, email, telepon, alamat) VALUES (?, ?, ?, ?)"); $stmt->execute([$nama, $email, $telepon, $alamat]); $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Customer berhasil ditambahkan.']; } else { $stmt = $pdo->prepare("UPDATE customers SET nama = ?, email = ?, telepon = ?, alamat = ? WHERE id = ?"); $stmt->execute([$nama, $email, $telepon, $alamat, $id]); $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Customer berhasil diperbarui.']; } } header('Location: customers.php'); exit(); }
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) { $id = $_GET['id']; $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?"); $stmt->execute([$id]); $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Customer berhasil dihapus.']; header('Location: customers.php'); exit(); }
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) { $edit_mode = true; $id = $_GET['id']; $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?"); $stmt->execute([$id]); $customer_data = $stmt->fetch(); }


// ===== LOGIKA PENCARIAN BARU =====
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM customers";
$params = [];
if (!empty($search)) {
    $sql .= " WHERE nama LIKE ? OR email LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Manajemen Customers</h1>
    <p class="text-gray-500 mt-1">Lihat dan kelola data pelanggan toko Anda.</p>
</div>

<div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-4"><?= $edit_mode ? 'Edit Customer' : 'Tambah Customer Baru' ?></h2>
    <form action="customers.php" method="POST">
        <input type="hidden" name="id" value="<?= $customer_data['id'] ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div><label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label><input type="text" name="nama" id="nama" class="w-full rounded-lg border-gray-300" value="<?= htmlspecialchars($customer_data['nama']) ?>" required></div>
            <div><label for="email" class="block text-sm font-medium text-gray-700 mb-1">Alamat Email</label><input type="email" name="email" id="email" class="w-full rounded-lg border-gray-300" value="<?= htmlspecialchars($customer_data['email']) ?>" required></div>
            <div><label for="telepon" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label><input type="tel" name="telepon" id="telepon" class="w-full rounded-lg border-gray-300" value="<?= htmlspecialchars($customer_data['telepon']) ?>"></div>
            <div><label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label><input type="text" name="alamat" id="alamat" class="w-full rounded-lg border-gray-300" value="<?= htmlspecialchars($customer_data['alamat']) ?>"></div>
        </div>
        <div class="flex justify-end mt-6 space-x-3">
            <?php if ($edit_mode): ?><a href="customers.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-5 rounded-lg">Batal</a><?php endif; ?>
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg"><?= $edit_mode ? 'Update Customer' : 'Simpan Customer' ?></button>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="p-4 border-b">
        <form action="customers.php" method="GET">
            <div class="relative">
                <input type="text" name="search" placeholder="Cari berdasarkan nama atau email..." class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-500 hover:text-orange-500"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kontak</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Alamat</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach($customers as $customer): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($customer['nama']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><div><?= htmlspecialchars($customer['email']) ?></div><div class="text-xs text-gray-400"><?= htmlspecialchars($customer['telepon']) ?></div></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($customer['alamat']) ?></td>
                    <td class="px-6 py-4 text-center text-lg">
                        <a href="customers.php?action=edit&id=<?= $customer['id'] ?>" class="text-orange-500 hover:text-orange-700 mr-4"><i class="fas fa-edit"></i></a>
                        <a href="customers.php?action=delete&id=<?= $customer['id'] ?>" class="text-red-500 hover:text-red-700 delete-btn"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>