<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    

    // Cek di tabel tb_petugas
    $stmt = $pdo->prepare("SELECT * FROM tb_petugas WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['id_level'] = $user['id_level'];
        $_SESSION['role'] = ($user['id_level'] == 1) ? 'Admin' : 'Petugas';

        // Tentukan role-nya
        $role = ($user['id_level'] == 1) ? 'Admin' : 'Petugas';
        $redirect = ($user['id_level'] == 1) ? "admin.php" : "barang.php";

        echo json_encode([
            'status' => 'success',
            'message' => "Login berhasil sebagai <strong>$role</strong>! Anda akan diarahkan ke halaman selama beberapa detik...",
            'redirect' => $redirect
        ]);
    } else {
        // Jika tidak ditemukan di petugas, coba di masyarakat
        $stmt = $pdo->prepare("SELECT * FROM tb_masyarakat WHERE username = ?");
        $stmt->execute([$username]);
        $masyarakat = $stmt->fetch();

        if ($masyarakat && password_verify($password, $masyarakat['password'])) {
            $_SESSION['id_user'] = $masyarakat['id_user'];
            $_SESSION['username'] = $masyarakat['username'];
            $_SESSION['nama_lengkap'] = $masyarakat['nama_lengkap'];
            $_SESSION['role'] = 'Masyarakat';

            $role = 'Masyarakat';

            echo json_encode([
                'status' => 'success',
                'message' => "Login berhasil sebagai <strong>$role</strong>! Anda akan diarahkan ke halaman selama beberapa detik...",
                'redirect' => "masyarakat.php"
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => "Username atau password salah!"
            ]);
        }
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => "Akses tidak diizinkan."
    ]);
}
