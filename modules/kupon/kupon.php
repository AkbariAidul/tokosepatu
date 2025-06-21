<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: /tokosepatu/login.php'); exit(); }

$edit_mode = false;
$kupon_data = ['id' => '', 'kode_kupon' => '', 'jenis_kupon' => 'persen', 'nilai' => '', 'tanggal_kadaluarsa' => '', 'status' => 'aktif'];

// Proses Tambah / Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $kode_kupon = trim(strtoupper($_POST['kode_kupon']));
    $jenis_kupon = $_POST['jenis_kupon'];
    $nilai = $_POST['nilai'];
    $tanggal_kadaluarsa = $_POST['tanggal_kadaluarsa'];
    $status = $_POST['status'];

    if (!empty($kode_kupon) && !empty($nilai) && !empty($tanggal_kadaluarsa)) {
        if (empty($id)) { // Tambah baru
            $stmt = $pdo->prepare("INSERT INTO kupon (kode_kupon, jenis_kupon, nilai, tanggal_kadaluarsa, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$kode_kupon, $jenis_kupon, $nilai, $tanggal_kadaluarsa, $status]);
            $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Kupon berhasil ditambahkan.'];
        } else { // Update
            $stmt = $pdo->prepare("UPDATE kupon SET kode_kupon=?, jenis_kupon=?, nilai=?, tanggal_kadaluarsa=?, status=? WHERE id=?");
            $stmt->execute([$kode_kupon, $jenis_kupon, $nilai, $tanggal_kadaluarsa, $status, $id]);
            $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Kupon berhasil diperbarui.'];
        }
    }
    header('Location: kupon.php'); exit();
}

// Proses Hapus
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM kupon WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Kupon berhasil dihapus.'];
    header('Location: kupon.php'); exit();
}

// Mode Edit
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_mode = true;
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM kupon WHERE id = ?");
    $stmt->execute([$id]);
    $kupon_data = $stmt->fetch();
}

$kupon_list = $pdo->query("SELECT * FROM kupon ORDER BY id DESC")->fetchAll();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Manajemen Kupon Diskon</h1>
    <p class="text-gray-500 mt-1">Buat dan kelola kode diskon untuk promosi toko Anda.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-2xl shadow-lg">
            <h2 class="text-xl font-semibold text-gray-800 mb-5"><?= $edit_mode ? 'Edit Kupon' : 'Tambah Kupon Baru' ?></h2>
            <form action="kupon.php" method="POST" class="space-y-4">
                <input type="hidden" name="id" value="<?= $kupon_data['id'] ?>">
                <div>
                    <label for="kode_kupon" class="block text-sm font-medium text-gray-700">Kode Kupon</label>
                    <input type="text" name="kode_kupon" id="kode_kupon" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm uppercase" value="<?= htmlspecialchars($kupon_data['kode_kupon']) ?>" required>
                </div>
                 <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="jenis_kupon" class="block text-sm font-medium text-gray-700">Jenis</label>
                        <select name="jenis_kupon" id="jenis_kupon" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                            <option value="persen" <?= $kupon_data['jenis_kupon'] == 'persen' ? 'selected' : '' ?>>Persen (%)</option>
                            <option value="tetap" <?= $kupon_data['jenis_kupon'] == 'tetap' ? 'selected' : '' ?>>Potongan Tetap (Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label for="nilai" class="block text-sm font-medium text-gray-700">Nilai</label>
                        <input type="number" name="nilai" id="nilai" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" value="<?= htmlspecialchars($kupon_data['nilai']) ?>" required>
                    </div>
                </div>
                <div>
                    <label for="tanggal_kadaluarsa" class="block text-sm font-medium text-gray-700">Tanggal Kadaluarsa</pre>
                    <input type="date" name="tanggal_kadaluarsa" id="tanggal_kadaluarsa" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" value="<?= htmlspecialchars($kupon_data['tanggal_kadaluarsa']) ?>" required>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                        <option value="aktif" <?= $kupon_data['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="tidak aktif" <?= $kupon_data['status'] == 'tidak aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>
                <div class="flex items-center space-x-2 pt-3">
                    <?php if ($edit_mode): ?>
                        <a href="kupon.php" class="w-full text-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2.5 px-4 rounded-lg shadow-sm">Batal</a>
                    <?php endif; ?>
                    <button type="submit" class="w-full flex justify-center items-center bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-sm">
                        <i class="fas <?= $edit_mode ? 'fa-save' : 'fa-plus' ?> mr-2"></i>
                        <?= $edit_mode ? 'Update Kupon' : 'Simpan Kupon' ?>
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
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kode Kupon</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Diskon</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kadaluarsa</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($kupon_list as $kupon): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900 "><?= htmlspecialchars($kupon['kode_kupon']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-700"><?= $kupon['jenis_kupon'] == 'persen' ? $kupon['nilai'] . '%' : format_rupiah($kupon['nilai']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= date('d M Y', strtotime($kupon['tanggal_kadaluarsa'])) ?></td>
                        <td class="px-6 py-4 text-center"><span class="px-3 py-1 text-xs font-semibold rounded-full <?= $kupon['status'] == 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>"><?= $kupon['status'] ?></span></td>
                        <td class="px-6 py-4 text-center text-lg">
                            <a href="kupon.php?action=edit&id=<?= $kupon['id'] ?>" class="text-orange-500 hover:text-orange-700 mr-4"><i class="fas fa-edit"></i></a>
                            <a href="kupon.php?action=delete&id=<?= $kupon['id'] ?>" class="text-red-500 hover:text-red-700 delete-btn"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>