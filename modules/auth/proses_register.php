<?php
require_once '../../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($nama_lengkap) || empty($email) || empty($password)) {
        header('Location: ../../register.php');
        exit();
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$nama_lengkap, $email, $hashed_password]);

        $_SESSION['flash_message'] = [
            'type' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Akun berhasil dibuat. Silakan login.'
        ];
        header('Location: ../../login.php');
    } catch (PDOException $e) {
         $_SESSION['flash_message'] = [
            'type' => 'error',
            'title' => 'Gagal!',
            'text' => 'Email sudah terdaftar atau terjadi kesalahan.'
        ];
        header('Location: ../../register.php');
    }
}
?>