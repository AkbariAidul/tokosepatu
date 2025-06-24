<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: /tokosepatu/login.php'); exit(); }

// Logika Proses Form (Tambah & Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nama_kategori'])) {
    $id = $_POST['id']; 
    $nama_kategori = trim($_POST['nama_kategori']);
    if (!empty($nama_kategori)) {
        if (empty($id)) { // Tambah baru
            $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
            $stmt->execute([$nama_kategori]);
            $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Kategori baru berhasil ditambahkan.'];
        } else { // Update
            $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori = ? WHERE id = ?");
            $stmt->execute([$nama_kategori, $id]);
            $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Kategori berhasil diperbarui.'];
        }
    }
    header('Location: kategori.php'); 
    exit();
}

// Logika Hapus
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    // Cek dulu apakah ada produk yang menggunakan kategori ini
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM produk WHERE kategori_id = ?");
    $stmt_check->execute([$id]);
    if ($stmt_check->fetchColumn() > 0) {
        $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Kategori tidak bisa dihapus karena masih digunakan oleh produk.'];
    } else {
        $stmt = $pdo->prepare("DELETE FROM kategori WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Kategori berhasil dihapus.'];
    }
    header('Location: kategori.php'); 
    exit();
}

// Mengambil data kategori beserta jumlah produknya
$stmt = $pdo->query("
    SELECT k.id, k.nama_kategori, COUNT(p.id) as jumlah_produk
    FROM kategori k
    LEFT JOIN produk p ON k.id = p.kategori_id
    GROUP BY k.id, k.nama_kategori
    ORDER BY k.id DESC
");
$kategori_list = $stmt->fetchAll();
?>

<?php require_once '../../includes/sidebar.php'; ?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Manajemen Kategori</h1>
        <p class="text-gray-500 mt-1">Tambah, edit, atau hapus kategori produk.</p>
    </div>
    <button onclick="openModal()" class="mt-4 sm:mt-0 flex items-center bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg shadow-sm transition duration-200">
        <i class="fas fa-plus mr-2"></i> Tambah Kategori
    </button>
</div>

<div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Kategori</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Jumlah Produk</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (count($kategori_list) > 0): ?>
                    <?php $no = 1; foreach($kategori_list as $kategori): ?>
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4 text-sm text-gray-500 font-medium"><?= $no++ ?></td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($kategori['nama_kategori']) ?></td>
                        <td class="px-6 py-4 text-center text-sm text-gray-500">
                            <span class="px-3 py-1 font-semibold rounded-full bg-blue-100 text-blue-800"><?= $kategori['jumlah_produk'] ?></span>
                        </td>
                        <td class="px-6 py-4 text-center text-lg font-medium">
                            <button onclick="openModal('<?= $kategori['id'] ?>', '<?= htmlspecialchars($kategori['nama_kategori']) ?>')" class="text-orange-500 hover:text-orange-700 mr-4 transition-colors"><i class="fas fa-edit"></i></button>
                            <a href="kategori.php?action=delete&id=<?= $kategori['id'] ?>" class="text-red-500 hover:text-red-700 delete-btn transition-colors"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center py-10 text-gray-500"><p>Belum ada kategori yang ditambahkan.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="kategoriModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md transform transition-all -translate-y-12">
        <div class="flex justify-between items-center mb-6">
            <h2 id="modalTitle" class="text-2xl font-bold text-gray-800">Tambah Kategori Baru</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        </div>
        <form id="kategoriForm" action="kategori.php" method="POST">
            <input type="hidden" name="id" id="kategoriId">
            <div class="form-group">
                <input type="text" name="nama_kategori" id="nama_kategori" class="form-input placeholder-transparent" placeholder="Nama Kategori" required>
                <label for="nama_kategori" class="form-label">Nama Kategori</label>
            </div>
            <div class="flex justify-end mt-8">
                <button type="button" onclick="closeModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-5 rounded-lg mr-3">Batal</button>
                <button id="modalSubmitButton" type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('kategoriModal');
    const modalTitle = document.getElementById('modalTitle');
    const kategoriForm = document.getElementById('kategoriForm');
    const kategoriId = document.getElementById('kategoriId');
    const namaKategori = document.getElementById('nama_kategori');
    const modalSubmitButton = document.getElementById('modalSubmitButton');

    function openModal(id = '', nama = '') {
        kategoriForm.reset();
        if (id) {
            modalTitle.textContent = 'Edit Kategori';
            modalSubmitButton.textContent = 'Update';
            kategoriId.value = id;
            namaKategori.value = nama;
        } else {
            modalTitle.textContent = 'Tambah Kategori Baru';
            modalSubmitButton.textContent = 'Simpan';
            kategoriId.value = '';
        }
        // Memicu label untuk "naik" jika form sudah terisi (untuk edit)
        namaKategori.dispatchEvent(new Event('input')); 
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    // Menutup modal jika user klik di luar area modal
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
</script>

<?php require_once '../../includes/footer.php'; ?>