<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_GET['id'])) { header('Location: produk.php'); exit(); }

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { header('Location: produk.php'); exit(); }
$categories = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Edit Produk</h1>
    <p class="text-gray-500 mt-1">Anda sedang mengubah detail untuk: <?= htmlspecialchars($product['nama_produk']) ?></p>
</div>

<div class="bg-white p-6 md:p-8 rounded-2xl shadow-lg">
    <form action="proses_edit.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $product['id'] ?>">
        <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($product['gambar']) ?>">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                 <div>
                    <label for="nama_produk" class="block text-sm font-medium text-gray-700">Nama Produk</label>
                    <input type="text" id="nama_produk" name="nama_produk" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" value="<?= htmlspecialchars($product['nama_produk']) ?>" required>
                </div>
                <div>
                    <label for="kategori_id" class="block text-sm font-medium text-gray-700">Kategori</label>
                    <select id="kategori_id" name="kategori_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" required>
                        <?php foreach($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= ($product['kategori_id'] == $category['id']) ? 'selected' : '' ?>><?= htmlspecialchars($category['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="5" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"><?= htmlspecialchars($product['deskripsi']) ?></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="harga" class="block text-sm font-medium text-gray-700">Harga (Rp)</label>
                        <input type="number" id="harga" name="harga" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" value="<?= $product['harga'] ?>" required>
                    </div>
                    <div>
                        <label for="stok" class="block text-sm font-medium text-gray-700">Stok</label>
                        <input type="number" id="stok" name="stok" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" value="<?= $product['stok'] ?>" required>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Gambar Produk</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                    <div class="space-y-1 text-center">
                        <img id="imagePreview" src="/tokosepatu/uploads/<?= htmlspecialchars($product['gambar']) ?>" alt="Image Preview" class="mx-auto h-32 w-32 object-cover rounded-md mb-4">
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