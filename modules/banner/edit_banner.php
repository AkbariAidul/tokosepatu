<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: /tokosepatu/login.php'); exit(); }

if (!isset($_GET['id'])) { header('Location: banner.php'); exit(); }
$id = $_GET['id'];

// Proses Update Banner
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $subjudul = $_POST['subjudul'];
    $link_button = $_POST['link_button'];
    $teks_button = $_POST['teks_button'];
    $status = $_POST['status'];
    $gambar_lama = $_POST['gambar_lama'];
    $gambar_nama = $gambar_lama;

    // Cek jika ada gambar baru diupload
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0 && !empty($_FILES['gambar']['name'])) {
        $target_dir = "../../uploads/banners/";
        $gambar_nama = uniqid() . '-' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $gambar_nama;
        
        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            // Hapus gambar lama jika upload baru berhasil
            if ($gambar_lama && file_exists($target_dir . $gambar_lama)) {
                unlink($target_dir . $gambar_lama);
            }
        } else {
            $gambar_nama = $gambar_lama; // Gagal upload, pakai gambar lama
        }
    }

    $stmt = $pdo->prepare("UPDATE banner SET judul=?, subjudul=?, link_button=?, teks_button=?, nama_gambar=?, status=? WHERE id=?");
    $stmt->execute([$judul, $subjudul, $link_button, $teks_button, $gambar_nama, $status, $id]);
    $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Banner berhasil diperbarui.'];
    header('Location: banner.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM banner WHERE id = ?");
$stmt->execute([$id]);
$banner = $stmt->fetch();
if (!$banner) { header('Location: banner.php'); exit(); }
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Edit Banner</h1>
    <p class="text-gray-500 mt-1">Anda sedang mengubah banner: <?= htmlspecialchars($banner['judul']) ?></p>
</div>

<div class="bg-white p-6 rounded-2xl shadow-lg">
    <form action="edit_banner.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="gambar_lama" value="<?= $banner['nama_gambar'] ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="judul" class="block text-sm font-medium text-gray-700">Judul Banner</label>
                <input type="text" name="judul" id="judul" class="mt-1 block w-full rounded-lg" value="<?= htmlspecialchars($banner['judul']) ?>" required>
            </div>
             <div>
                <label for="subjudul" class="block text-sm font-medium text-gray-700">Subjudul (Opsional)</label>
                <input type="text" name="subjudul" id="subjudul" class="mt-1 block w-full rounded-lg" value="<?= htmlspecialchars($banner['subjudul']) ?>">
            </div>
             <div>
                <label for="teks_button" class="block text-sm font-medium text-gray-700">Teks Tombol</label>
                <input type="text" name="teks_button" id="teks_button" class="mt-1 block w-full rounded-lg" value="<?= htmlspecialchars($banner['teks_button']) ?>">
            </div>
             <div>
                <label for="link_button" class="block text-sm font-medium text-gray-700">Link Tujuan</label>
                <input type="text" name="link_button" id="link_button" class="mt-1 block w-full rounded-lg" value="<?= htmlspecialchars($banner['link_button']) ?>">
            </div>
            <div>
                 <label class="block text-sm font-medium text-gray-700">Gambar Banner</label>
                 <img src="/tokosepatu/uploads/banners/<?= htmlspecialchars($banner['nama_gambar']) ?>" class="w-48 h-auto object-cover rounded-md my-2">
                 <input type="file" name="gambar" id="gambar" class="mt-1 block w-full text-sm">
                 <small class="text-gray-500">Kosongkan jika tidak ingin mengubah gambar.</small>
            </div>
             <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-lg">
                    <option value="aktif" <?= $banner['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="tidak aktif" <?= $banner['status'] == 'tidak aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end pt-4 gap-3">
            <a href="banner.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-5 rounded-lg">Batal</a>
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg">Update Banner</button>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>