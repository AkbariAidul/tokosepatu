<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: /tokosepatu/login.php'); exit(); }
$categories = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Tambah Produk Baru</h1>
    <p class="text-gray-500 mt-1">Isi detail produk di bawah ini untuk menambahkannya ke toko.</p>
</div>

<div class="bg-white p-6 md:p-8 rounded-2xl shadow-lg">
    <form action="proses_tambah.php" method="POST" enctype="multipart/form-data">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="form-group">
                    <input type="text" id="nama_produk" name="nama_produk" class="form-input placeholder-transparent" placeholder="Nama Produk" required>
                    <label for="nama_produk" class="form-label">Nama Produk</label>
                </div>
                <div class="form-group">
                    <select id="kategori_id" name="kategori_id" class="form-input form-select placeholder-transparent" required>
                        <option value="" selected disabled>-- Pilih Kategori --</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="kategori_id" class="form-label">Kategori</label>
                </div>
                <div class="form-group">
                    <textarea id="deskripsi" name="deskripsi" rows="5" class="form-input placeholder-transparent" placeholder="Deskripsi"></textarea>
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <input type="number" id="harga" name="harga" placeholder="Contoh: 150000" class="form-input placeholder-transparent" required>
                        <label for="harga" class="form-label">Harga (Rp)</label>
                    </div>
                    <div class="form-group">
                        <input type="number" id="stok" name="stok" placeholder="Contoh: 50" class="form-input placeholder-transparent" required>
                        <label for="stok" class="form-label">Stok</label>
                    </div>
                    <div class="form-group">
                            <input type="number" id="berat" name="berat" placeholder="Contoh: 1200" class="form-input placeholder-transparent" required>
                            <label for="berat" class="form-label">Berat (gram)</label>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Gambar Produk</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                    <div class="space-y-1 text-center">
                        <img id="imagePreview" src="" alt="Image Preview" class="mx-auto h-32 w-32 object-cover rounded-md hidden mb-4">
                        <i id="iconPreview" class="fas fa-image fa-3x text-gray-400 mx-auto"></i>
                        <div class="flex text-sm text-gray-600">
                            <label for="gambar" class="relative cursor-pointer bg-white rounded-md font-medium text-orange-600 hover:text-orange-500">
                                <span>Upload sebuah file</span>
                                <input id="gambar" name="gambar" type="file" class="sr-only" required>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, JPEG</p>
                        <p id="fileName" class="text-xs text-green-600 font-semibold"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="pt-6 flex items-center justify-end space-x-3">
            <a href="produk.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-5 rounded-lg shadow-sm">Batal</a>
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg shadow-sm">Simpan Produk</button>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>