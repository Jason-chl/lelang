<?php
// proses_register.php
header('Content-Type: application/json');

require 'config.php'; // file config koneksi PDO

$nama = $_POST['nama_petugas'] ?? ''; // bisa juga nama_lengkap tergantung input
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$id_level = $_POST['id_level'] ?? '';

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Cek username di kedua tabel
    $checkUser = $pdo->prepare("SELECT COUNT(*) FROM tb_petugas WHERE username = ? 
                                UNION ALL 
                                SELECT COUNT(*) FROM tb_masyarakat WHERE username = ?");
    $checkUser->execute([$username, $username]);
    $counts = $checkUser->fetchAll(PDO::FETCH_COLUMN);

    if ($counts[0] > 0 || $counts[1] > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Username sudah digunakan'
        ]);
        exit;
    }

    // Simpan berdasarkan role
    if ($id_level == 3) {
        // Role user → tb_masyarakat
        $stmt = $pdo->prepare("INSERT INTO tb_masyarakat (nama_lengkap, username, password, telp) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $username, $hashed_password, $_POST['telp'] ?? '-']);
    } else {
        // Role petugas/admin → tb_petugas
        $stmt = $pdo->prepare("INSERT INTO tb_petugas (nama_petugas, username, password, id_level) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $username, $hashed_password, $id_level]);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Pendaftaran berhasil!',
                'redirect' => 'login.php'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
