<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: /tokosepatu/login.php'); exit(); }

// --- LOGIKA PROSES FORM (TAMBAH & EDIT) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nama'])) { 
    $id = $_POST['id']; 
    $nama = trim($_POST['nama']); 
    $email = trim($_POST['email']); 
    $telepon = trim($_POST['telepon']); 
    $alamat = trim($_POST['alamat']); 

    if (!empty($nama) && !empty($email)) { 
        if (empty($id)) { // Tambah baru
            $stmt = $pdo->prepare("INSERT INTO customers (nama, email, telepon, alamat) VALUES (?, ?, ?, ?)"); 
            $stmt->execute([$nama, $email, $telepon, $alamat]); 
            $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Customer berhasil ditambahkan.']; 
        } else { // Update
            $stmt = $pdo->prepare("UPDATE customers SET nama = ?, email = ?, telepon = ?, alamat = ? WHERE id = ?"); 
            $stmt->execute([$nama, $email, $telepon, $alamat, $id]); 
            $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Customer berhasil diperbarui.']; 
        } 
    } 
    header('Location: customers.php'); 
    exit(); 
}

// --- LOGIKA HAPUS ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) { 
    $id = $_GET['id'];
    // Cek dulu apakah customer punya pesanan
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE customer_id = ?");
    $stmt_check->execute([$id]);
    if ($stmt_check->fetchColumn() > 0) {
         $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Customer tidak bisa dihapus karena memiliki riwayat pesanan.'];
    } else {
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?"); 
        $stmt->execute([$id]); 
        $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Customer berhasil dihapus.']; 
    }
    header('Location: customers.php'); 
    exit(); 
}

// --- LOGIKA PENGAMBILAN DATA DENGAN STATISTIK ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "
    SELECT 
        c.id, c.nama, c.email, c.telepon, c.alamat,
        COUNT(p.id) AS jumlah_pesanan,
        SUM(CASE WHEN p.status = 'selesai' THEN p.total_harga + p.biaya_pengiriman ELSE 0 END) AS total_belanja
    FROM 
        customers c
    LEFT JOIN 
        pesanan p ON c.id = p.customer_id
";
$params = [];

if (!empty($search)) {
    $sql .= " WHERE c.nama LIKE ? OR c.email LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " GROUP BY c.id, c.nama, c.email, c.telepon, c.alamat ORDER BY c.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Manajemen Customers</h1>
        <p class="text-gray-500 mt-1">Lihat dan kelola data pelanggan toko Anda.</p>
    </div>
    <button onclick="openModal()" class="mt-4 sm:mt-0 flex items-center bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg shadow-sm transition duration-200">
        <i class="fas fa-plus mr-2"></i> Tambah Customer
    </button>
</div>


<div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="p-4 border-b">
        <form action="customers.php" method="GET">
            <div class="relative">
                <input type="text" name="search" placeholder="Cari berdasarkan nama atau email..." class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-500 hover:text-orange-500"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kontak</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Jumlah Pesanan</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Total Belanja</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (count($customers) > 0): ?>
                    <?php foreach($customers as $customer): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($customer['nama']) ?></p>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($customer['alamat']) ?></p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <div><?= htmlspecialchars($customer['email']) ?></div>
                            <div class="text-xs text-gray-400 mt-1"><?= htmlspecialchars($customer['telepon']) ?></div>
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-semibold text-gray-700"><?= $customer['jumlah_pesanan'] ?></td>
                        <td class="px-6 py-4 text-sm font-bold text-green-600"><?= format_rupiah($customer['total_belanja']) ?></td>
                        <td class="px-6 py-4 text-center text-lg">
                            <button onclick="openModal(<?= htmlspecialchars(json_encode($customer)) ?>)" class="text-orange-500 hover:text-orange-700 mr-4"><i class="fas fa-edit"></i></button>
                            <a href="customers.php?action=delete&id=<?= $customer['id'] ?>" class="text-red-500 hover:text-red-700 delete-btn"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-10 text-gray-500"><p>Data customer tidak ditemukan.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="customerModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all -translate-y-12">
        <div class="flex justify-between items-center mb-6">
            <h2 id="modalTitle" class="text-2xl font-bold text-gray-800">Tambah Customer Baru</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        </div>
        <form id="customerForm" action="customers.php" method="POST">
            <input type="hidden" name="id" id="customerId">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group">
                    <input type="text" name="nama" id="nama" class="form-input placeholder-transparent" placeholder="Nama Lengkap" required>
                    <label for="nama" class="form-label">Nama Lengkap</label>
                </div>
                <div class="form-group">
                    <input type="email" name="email" id="email" class="form-input placeholder-transparent" placeholder="Alamat Email" required>
                    <label for="email" class="form-label">Alamat Email</label>
                </div>
                <div class="form-group">
                    <input type="tel" name="telepon" id="telepon" class="form-input placeholder-transparent" placeholder="Nomor Telepon">
                    <label for="telepon" class="form-label">Nomor Telepon</label>
                </div>
                <div class="form-group">
                    <input type="text" name="alamat" id="alamat" class="form-input placeholder-transparent" placeholder="Alamat Lengkap">
                    <label for="alamat" class="form-label">Alamat Lengkap</label>
                </div>
            </div>
            <div class="flex justify-end mt-8">
                <button type="button" onclick="closeModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-5 rounded-lg mr-3">Batal</button>
                <button id="modalSubmitButton" type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('customerModal');
    const modalTitle = document.getElementById('modalTitle');
    const customerForm = document.getElementById('customerForm');
    const customerId = document.getElementById('customerId');
    const nama = document.getElementById('nama');
    const email = document.getElementById('email');
    const telepon = document.getElementById('telepon');
    const alamat = document.getElementById('alamat');
    const modalSubmitButton = document.getElementById('modalSubmitButton');

    function openModal(customer = null) {
        customerForm.reset();
        if (customer) {
            modalTitle.textContent = 'Edit Customer';
            modalSubmitButton.textContent = 'Update';
            customerId.value = customer.id;
            nama.value = customer.nama;
            email.value = customer.email;
            telepon.value = customer.telepon;
            alamat.value = customer.alamat;
        } else {
            modalTitle.textContent = 'Tambah Customer Baru';
            modalSubmitButton.textContent = 'Simpan';
            customerId.value = '';
        }
        // Memicu label untuk "naik" jika form sudah terisi (untuk edit)
        document.querySelectorAll('.form-input').forEach(input => input.dispatchEvent(new Event('input')));
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