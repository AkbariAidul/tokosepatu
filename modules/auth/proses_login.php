<?php
require_once '../../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'title' => 'Gagal Login',
            'text' => 'Email dan password tidak boleh kosong.'
        ];
        header('Location: ../../login.php');
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nama'] = $user['nama_lengkap'];
        header('Location: ../../index.php');
        exit();
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'title' => 'Gagal Login',
            'text' => 'Email atau password salah.'
        ];
        header('Location: ../../login.php');
        exit();
    }
}
?>