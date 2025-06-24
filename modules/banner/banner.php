<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: /tokosepatu/login.php'); exit(); }

// --- LOGIKA PROSES TAMBAH BANNER (DIKEMBALIKAN KE SINI) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_banner'])) {
    $judul = $_POST['judul'];
    $subjudul = $_POST['subjudul'];
    $link_button = $_POST['link_button'];
    $teks_button = $_POST['teks_button'];
    $status = $_POST['status'];
    $gambar_nama = '';

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0 && !empty($_FILES['gambar']['name'])) {
        $target_dir = "../../uploads/banners/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $gambar_nama = uniqid() . '-' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $gambar_nama;
        
        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            $stmt = $pdo->prepare("INSERT INTO banner (judul, subjudul, link_button, teks_button, nama_gambar, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$judul, $subjudul, $link_button, $teks_button, $gambar_nama, $status]);
            $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Banner berhasil ditambahkan.'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Gagal mengupload gambar.'];
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Gambar banner wajib diisi.'];
    }
    header('Location: banner.php');
    exit();
}

$banners = $pdo->query("SELECT * FROM banner ORDER BY id DESC")->fetchAll();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Manajemen Banner Homepage</h1>
        <p class="text-gray-500 mt-1">Kelola gambar promosi di halaman depan toko.</p>
    </div>
    <button onclick="openModal()" class="mt-4 sm:mt-0 flex items-center bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg shadow-sm">
        <i class="fas fa-plus mr-2"></i> Tambah Banner
    </button>
</div>

<div class="bg-white p-6 rounded-2xl shadow-lg">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Daftar Banner</h2>
    <div class="space-y-4">
    <?php if (count($banners) > 0): ?>
        <?php foreach($banners as $banner): ?>
        <div class="flex flex-wrap items-center justify-between p-4 border rounded-lg hover:bg-gray-50 gap-4">
            <div class="flex items-center gap-4">
                <img src="/tokosepatu/uploads/banners/<?= htmlspecialchars($banner['nama_gambar']) ?>" class="w-40 h-16 object-cover rounded-md flex-shrink-0">
                <div>
                    <p class="font-bold text-gray-800"><?= htmlspecialchars($banner['judul']) ?></p>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($banner['subjudul']) ?></p>
                </div>
            </div>
            <div class="flex items-center gap-4 flex-shrink-0">
                <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $banner['status'] == 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>"><?= $banner['status'] ?></span>
                <div class="text-lg">
                    <a href="edit_banner.php?id=<?= $banner['id'] ?>" class="text-orange-500 hover:text-orange-700 mr-3"><i class="fas fa-edit"></i></a>
                    <a href="hapus_banner.php?id=<?= $banner['id'] ?>" class="text-red-500 hover:text-red-700 delete-btn"><i class="fas fa-trash"></i></a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center text-gray-500 py-8">Belum ada banner yang ditambahkan.</p>
    <?php endif; ?>
    </div>
</div>

<div id="bannerModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all -translate-y-12">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Tambah Banner Baru</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        </div>
        <form action="banner.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="tambah_banner" value="1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group"><input type="text" name="judul" id="judul" class="form-input placeholder-transparent" placeholder="Judul Banner" required><label for="judul" class="form-label">Judul Banner</label></div>
                <div class="form-group"><input type="text" name="subjudul" id="subjudul" class="form-input placeholder-transparent" placeholder="Subjudul (Opsional)"><label for="subjudul" class="form-label">Subjudul (Opsional)</label></div>
                <div class="form-group"><input type="text" name="teks_button" id="teks_button" class="form-input placeholder-transparent" placeholder="Teks Tombol" value="Belanja Sekarang"><label for="teks_button" class="form-label">Teks Tombol</label></div>
                <div class="form-group"><input type="text" name="link_button" id="link_button" class="form-input placeholder-transparent" placeholder="/modules/produk/produk.php"><label for="link_button" class="form-label">Link Tujuan</label></div>
            </div>
            <div>
                <label for="gambar" class="block text-sm font-medium text-gray-700 mb-2">Gambar Banner (Rekomendasi: 1200x400 px)</label>
                <input type="file" name="gambar" id="gambar" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100" required>
            </div>
            <div class="form-group">
                <select name="status" id="status" class="form-input form-select placeholder-transparent"><option value="aktif">Aktif</option><option value="tidak aktif">Tidak Aktif</option></select>
                <label for="status" class="form-label">Status</label>
            </div>
            <div class="flex justify-end pt-4"><button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg shadow-sm"><i class="fas fa-plus mr-2"></i>Tambah Banner</button></div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('bannerModal');
    function openModal() {
        modal.classList.remove('hidden');
    }
    function closeModal() {
        modal.classList.add('hidden');
    }
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
</script>


<?php require_once '../../includes/footer.php'; ?>