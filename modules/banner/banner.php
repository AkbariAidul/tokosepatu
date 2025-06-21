<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: /tokosepatu/login.php'); exit(); }

// Proses Tambah Banner
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_banner'])) {
    $judul = $_POST['judul'];
    $subjudul = $_POST['subjudul'];
    $link_button = $_POST['link_button'];
    $teks_button = $_POST['teks_button'];
    $status = $_POST['status'];
    $gambar_nama = '';

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../../uploads/banners/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); } // Buat folder jika belum ada
        
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

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Manajemen Banner Homepage</h1>
    <p class="text-gray-500 mt-1">Kelola gambar promosi yang tampil di halaman depan toko.</p>
</div>

<div class="bg-white p-6 rounded-2xl shadow-lg mb-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Tambah Banner Baru</h2>
    <form action="banner.php" method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="tambah_banner" value="1">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="judul" class="block text-sm font-medium text-gray-700">Judul Banner</label>
                <input type="text" name="judul" id="judul" class="mt-1 block w-full rounded-lg" required>
            </div>
             <div>
                <label for="subjudul" class="block text-sm font-medium text-gray-700">Subjudul (Opsional)</label>
                <input type="text" name="subjudul" id="subjudul" class="mt-1 block w-full rounded-lg">
            </div>
             <div>
                <label for="teks_button" class="block text-sm font-medium text-gray-700">Teks Tombol</label>
                <input type="text" name="teks_button" id="teks_button" value="Belanja Sekarang" class="mt-1 block w-full rounded-lg">
            </div>
             <div>
                <label for="link_button" class="block text-sm font-medium text-gray-700">Link Tujuan</label>
                <input type="text" name="link_button" id="link_button" placeholder="/modules/produk/produk.php?kategori=1" class="mt-1 block w-full rounded-lg">
            </div>
            <div>
                 <label for="gambar" class="block text-sm font-medium text-gray-700">Gambar Banner (Rekomendasi: 1200x400 px)</label>
                 <input type="file" name="gambar" id="gambar" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100" required>
            </div>
             <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-lg">
                    <option value="aktif">Aktif</option>
                    <option value="tidak aktif">Tidak Aktif</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end pt-2">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg shadow-sm">
                <i class="fas fa-plus mr-2"></i>Tambah Banner
            </button>
        </div>
    </form>
</div>

<div class="bg-white p-6 rounded-2xl shadow-lg">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Daftar Banner Aktif</h2>
    <div class="space-y-4">
    <?php if (count($banners) > 0): ?>
        <?php foreach($banners as $banner): ?>
        <div class="flex items-center justify-between p-4 border rounded-lg">
            <div class="flex items-center gap-4">
                <img src="/tokosepatu/uploads/banners/<?= htmlspecialchars($banner['nama_gambar']) ?>" class="w-40 h-16 object-cover rounded-md">
                <div>
                    <p class="font-bold text-gray-800"><?= htmlspecialchars($banner['judul']) ?></p>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($banner['subjudul']) ?></p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $banner['status'] == 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>"><?= $banner['status'] ?></span>
                <div>
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

<?php require_once '../../includes/footer.php'; ?>