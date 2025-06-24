<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: /tokosepatu/login.php');
    exit();
}

if (!isset($_GET['id'])) { header('Location: produk.php'); exit(); }

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->execute([$id]);
$produk = $stmt->fetch();
if (!$produk) { header('Location: produk.php'); exit(); }

$categories = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Edit Produk</h1>
    <p class="text-gray-500 mt-1">Anda sedang mengubah detail untuk: <?= htmlspecialchars($produk['nama_produk']) ?></p>
</div>

<div class="bg-white p-6 md:p-8 rounded-2xl shadow-lg">
    <form action="proses_edit.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $produk['id'] ?>">
        <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($produk['gambar']) ?>">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="form-group">
                    <input type="text" id="nama_produk" name="nama_produk" class="form-input placeholder-transparent" placeholder="Nama Produk" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
                    <label for="nama_produk" class="form-label">Nama Produk</label>
                </div>
                <div class="form-group">
                    <select id="kategori_id" name="kategori_id" class="form-input form-select placeholder-transparent" required>
                        <?php foreach($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= ($produk['kategori_id'] == $category['id']) ? 'selected' : '' ?>><?= htmlspecialchars($category['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="kategori_id" class="form-label">Kategori</label>
                </div>
                <div class="form-group">
                    <textarea id="deskripsi" name="deskripsi" rows="5" class="form-input placeholder-transparent" placeholder="Deskripsi"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <input type="number" id="harga" name="harga" class="form-input placeholder-transparent" placeholder="Harga (Rp)" value="<?= $produk['harga'] ?>" required>
                        <label for="harga" class="form-label">Harga (Rp)</label>
                    </div>
                    <div class="form-group">
                        <input type="number" id="stok" name="stok" class="form-input placeholder-transparent" placeholder="Stok" value="<?= $produk['stok'] ?>" required>
                        <label for="stok" class="form-label">Stok</label>
                    </div>
                    <div class="form-group">
                            <input type="number" id="berat" name="berat" class="form-input placeholder-transparent" placeholder="Berat (gram)" value="<?= $produk['berat'] ?>" required>
                            <label for="berat" class="form-label">Berat (gram)</label>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Gambar Produk</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                    <div class="space-y-1 text-center">
                        <img id="imagePreview" src="/tokosepatu/uploads/<?= htmlspecialchars($produk['gambar']) ?>" alt="Image Preview" class="mx-auto h-32 w-32 object-cover rounded-md mb-4">
                        <i id="iconPreview" class="fas fa-image fa-3x text-gray-400 mx-auto hidden"></i>
                        <div class="flex text-sm text-gray-600">
                            <label for="gambar" class="relative cursor-pointer bg-white rounded-md font-medium text-orange-600 hover:text-orange-500">
                                <span>Ubah gambar</span>
                                <input id="gambar" name="gambar" type="file" class="sr-only">
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">Kosongkan jika tidak diubah</p>
                        <p id="fileName" class="text-xs text-green-600 font-semibold"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="pt-6 flex items-center justify-end space-x-3">
            <a href="produk.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-5 rounded-lg shadow-sm">Batal</a>
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg shadow-sm">Update Produk</button>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>