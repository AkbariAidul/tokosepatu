<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: /tokosepatu/login.php'); exit(); }

// --- LOGIKA PROSES FORM (TAMBAH & EDIT) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kode_kupon'])) {
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

// --- LOGIKA HAPUS ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM kupon WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Kupon berhasil dihapus.'];
    header('Location: kupon.php'); exit();
}

$kupon_list = $pdo->query("SELECT * FROM kupon ORDER BY id DESC")->fetchAll();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Manajemen Kupon Diskon</h1>
        <p class="text-gray-500 mt-1">Buat dan kelola kode diskon untuk promosi.</p>
    </div>
    <button onclick="openModal()" class="mt-4 sm:mt-0 flex items-center bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg shadow-sm">
        <i class="fas fa-plus mr-2"></i> Tambah Kupon
    </button>
</div>

<div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Kode Kupon</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Diskon</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Kadaluarsa</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (count($kupon_list) > 0): ?>
                    <?php foreach($kupon_list as $kupon): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900 "><?= htmlspecialchars($kupon['kode_kupon']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-700"><?= $kupon['jenis_kupon'] == 'persen' ? $kupon['nilai'] . '%' : format_rupiah($kupon['nilai']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= date('d M Y', strtotime($kupon['tanggal_kadaluarsa'])) ?></td>
                        <td class="px-6 py-4 text-center"><span class="px-3 py-1 text-xs font-semibold rounded-full <?= $kupon['status'] == 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>"><?= $kupon['status'] ?></span></td>
                        <td class="px-6 py-4 text-center text-lg">
                            <button onclick="openModal(<?= htmlspecialchars(json_encode($kupon)) ?>)" class="text-orange-500 hover:text-orange-700 mr-4"><i class="fas fa-edit"></i></button>
                            <a href="kupon.php?action=delete&id=<?= $kupon['id'] ?>" class="text-red-500 hover:text-red-700 delete-btn"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-10 text-gray-500">Belum ada kupon yang dibuat.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="kuponModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-lg transform transition-all -translate-y-12">
        <div class="flex justify-between items-center mb-6"><h2 id="modalTitle" class="text-2xl font-bold text-gray-800"></h2><button onclick="closeModal()" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button></div>
        <form id="kuponForm" action="kupon.php" method="POST" class="space-y-6">
            <input type="hidden" name="id" id="kuponId">
            <div class="form-group"><input type="text" name="kode_kupon" id="kode_kupon" class="form-input placeholder-transparent uppercase" placeholder="Kode Kupon" required><label for="kode_kupon" class="form-label">Kode Kupon</label></div>
            <div class="grid grid-cols-2 gap-6">
                <div class="form-group">
                    <select name="jenis_kupon" id="jenis_kupon" class="form-input form-select placeholder-transparent"><option value="persen">Persen (%)</option><option value="tetap">Potongan Tetap (Rp)</option></select>
                    <label for="jenis_kupon" class="form-label">Jenis</label>
                </div>
                <div class="form-group"><input type="number" name="nilai" id="nilai" class="form-input placeholder-transparent" placeholder="Nilai" required><label for="nilai" class="form-label">Nilai</label></div>
            </div>
            <div class="form-group"><input type="date" name="tanggal_kadaluarsa" id="tanggal_kadaluarsa" class="form-input placeholder-transparent" required><label for="tanggal_kadaluarsa" class="form-label">Tanggal Kadaluarsa</label></div>
            <div class="form-group">
                <select name="status" id="status" class="form-input form-select placeholder-transparent"><option value="aktif">Aktif</option><option value="tidak aktif">Tidak Aktif</option></select>
                <label for="status" class="form-label">Status</label>
            </div>
            <div class="flex justify-end mt-8"><button type="button" onclick="closeModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-5 rounded-lg mr-3">Batal</button><button id="modalSubmitButton" type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg">Simpan</button></div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('kuponModal');
    function openModal(kupon = null) {
        const form = document.getElementById('kuponForm');
        form.reset();
        if (kupon) {
            document.getElementById('modalTitle').textContent = 'Edit Kupon';
            document.getElementById('modalSubmitButton').textContent = 'Update';
            document.getElementById('kuponId').value = kupon.id;
            document.getElementById('kode_kupon').value = kupon.kode_kupon;
            document.getElementById('jenis_kupon').value = kupon.jenis_kupon;
            document.getElementById('nilai').value = kupon.nilai;
            document.getElementById('tanggal_kadaluarsa').value = kupon.tanggal_kadaluarsa;
            document.getElementById('status').value = kupon.status;
        } else {
            document.getElementById('modalTitle').textContent = 'Tambah Kupon Baru';
            document.getElementById('modalSubmitButton').textContent = 'Simpan';
            document.getElementById('kuponId').value = '';
        }
        document.querySelectorAll('.form-input').forEach(input => input.dispatchEvent(new Event('input')));
        modal.classList.remove('hidden');
    }
    function closeModal() { modal.classList.add('hidden'); }
    window.onclick = function(event) { if (event.target == modal) { closeModal(); } }
</script>

<?php require_once '../../includes/footer.php'; ?>