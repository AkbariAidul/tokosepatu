<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: /tokosepatu/login.php'); exit(); }

$edit_mode = false;
$kategori_data = ['id' => '', 'nama_kategori' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id']; 
    $nama_kategori = trim($_POST['nama_kategori']);
    if (!empty($nama_kategori)) {
        if (empty($id)) {
            $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
            $stmt->execute([$nama_kategori]);
            $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Kategori berhasil ditambahkan.'];
        } else {
            $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori = ? WHERE id = ?");
            $stmt->execute([$nama_kategori, $id]);
            $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Kategori berhasil diperbarui.'];
        }
    }
    header('Location: kategori.php'); 
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM kategori WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Kategori berhasil dihapus.'];
    header('Location: kategori.php'); 
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_mode = true;
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM kategori WHERE id = ?");
    $stmt->execute([$id]);
    $kategori_data = $stmt->fetch();
}

$kategori_list = $pdo->query("SELECT * FROM kategori ORDER BY id DESC")->fetchAll();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Manajemen Kategori</h1>
    <p class="text-gray-500 mt-1">Tambah, edit, atau hapus kategori produk.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-2xl shadow-lg h-full">
            <h2 class="text-xl font-semibold text-gray-800 mb-5"><?= $edit_mode ? 'Edit Kategori' : 'Tambah Kategori Baru' ?></h2>
            <form action="kategori.php" method="POST" class="space-y-4">
                <input type="hidden" name="id" value="<?= $kategori_data['id'] ?>">
                <div>
                    <label for="nama_kategori" class="block text-sm font-medium text-gray-700">Nama Kategori</label>
                    <input type="text" name="nama_kategori" id="nama_kategori" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" value="<?= htmlspecialchars($kategori_data['nama_kategori']) ?>" required>
                </div>
                <div class="flex items-center space-x-2 pt-3">
                    <?php if ($edit_mode): ?>
                        <a href="kategori.php" class="w-full flex justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2.5 px-4 rounded-lg shadow-sm">Batal</a>
                    <?php endif; ?>
                    <button type="submit" class="w-full flex justify-center items-center bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-transform transform hover:scale-105">
                        <i class="fas <?= $edit_mode ? 'fa-save' : 'fa-plus' ?> mr-2"></i>
                        <?= $edit_mode ? 'Update' : 'Simpan' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Kategori</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (count($kategori_list) > 0): ?>
                        <?php $no = 1; foreach($kategori_list as $kategori): ?>
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium"><?= $no++ ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($kategori['nama_kategori']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-lg font-medium">
                                <a href="kategori.php?action=edit&id=<?= $kategori['id'] ?>" class="text-orange-500 hover:text-orange-700 mr-4 transition-colors"><i class="fas fa-edit"></i></a>
                                <a href="kategori.php?action=delete&id=<?= $kategori['id'] ?>" class="text-red-500 hover:text-red-700 delete-btn transition-colors"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center py-10 text-gray-500">
                                <p>Belum ada kategori.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>