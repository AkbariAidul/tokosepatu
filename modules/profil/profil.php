<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

if (!isset($_SESSION['user_id'])) { 
    header('Location: /tokosepatu/login.php'); 
    exit(); 
}

$admin_id = $_SESSION['user_id'];

// ===== LOGIKA PHP UNTUK MEMPROSES FORM (YANG SEBELUMNYA HILANG) =====
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Cek form mana yang disubmit
    if (isset($_POST['update_profil'])) {
        // Proses Update Profil
        $nama_lengkap = trim($_POST['nama_lengkap']);
        if (!empty($nama_lengkap)) {
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ? WHERE id = ?");
            $stmt->execute([$nama_lengkap, $admin_id]);
            
            // Update session agar nama langsung berubah di welcome message
            $_SESSION['user_nama'] = $nama_lengkap;
            $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Profil berhasil diperbarui.'];
        }
    } elseif (isset($_POST['update_password'])) {
        // Proses Ganti Password
        $password_lama = $_POST['password_lama'];
        $password_baru = $_POST['password_baru'];
        $konfirmasi_password = $_POST['konfirmasi_password'];

        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$admin_id]);
        $user = $stmt->fetch();

        // Verifikasi password lama
        if ($user && password_verify($password_lama, $user['password'])) {
            // Cek jika password baru tidak kosong dan cocok dengan konfirmasi
            if (!empty($password_baru) && $password_baru === $konfirmasi_password) {
                $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $admin_id]);
                $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Sukses!', 'text' => 'Password berhasil diganti.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Password baru tidak cocok atau kosong.'];
            }
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Password lama yang Anda masukkan salah.'];
        }
    }
    header('Location: profil.php');
    exit();
}
// =================================================================

// Ambil data admin saat ini untuk ditampilkan di form
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Profil Saya</h1>
    <p class="text-gray-500 mt-1">Kelola informasi pribadi dan keamanan akun Anda.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-2xl shadow-lg h-full">
            <h2 class="text-xl font-semibold text-gray-800 mb-5">Informasi Profil</h2>
            <form action="profil.php" method="POST" class="space-y-6">
                <input type="hidden" name="update_profil" value="1">
                <div class="form-group">
                    <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-input placeholder-transparent" placeholder="Nama Lengkap" value="<?= htmlspecialchars($admin['nama_lengkap']) ?>" required>
                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                </div>
                <div class="form-group">
                    <input type="email" name="email" id="email" class="form-input bg-gray-200 text-gray-500 placeholder-transparent" placeholder="Email" value="<?= htmlspecialchars($admin['email']) ?>" disabled>
                    <label for="email" class="form-label">Email (tidak bisa diubah)</label>
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-sm">Update Profil</button>
                </div>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white p-6 rounded-2xl shadow-lg h-full">
            <h2 class="text-xl font-semibold text-gray-800 mb-5">Ganti Password</h2>
            <form action="profil.php" method="POST" class="space-y-6">
                <input type="hidden" name="update_password" value="1">
                <div class="form-group">
                    <input type="password" name="password_lama" id="password_lama" class="form-input placeholder-transparent" placeholder="Password Lama" required>
                    <label for="password_lama" class="form-label">Password Lama</label>
                </div>
                <div class="form-group">
                    <input type="password" name="password_baru" id="password_baru" class="form-input placeholder-transparent" placeholder="Password Baru" required>
                    <label for="password_baru" class="form-label">Password Baru</label>
                </div>
                 <div class="form-group">
                    <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-input placeholder-transparent" placeholder="Konfirmasi Password Baru" required>
                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                </div>
                <div class="pt-2">
                     <button type="submit" class="w-full bg-slate-700 hover:bg-slate-800 text-white font-semibold py-2.5 px-4 rounded-lg shadow-sm">Ganti Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>