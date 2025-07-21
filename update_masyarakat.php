<?php
header('Content-Type: application/json');
include 'config.php'; // pastikan $pdo sudah tersedia

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = $_POST['id_user'] ?? null;
    $nama = $_POST['nama_lengkap'] ?? null;
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;
    $telp = $_POST['telp'] ?? null;

    if (!$nama || !$username || !$telp) {
        echo json_encode(['status' => 'error', 'message' => 'Data wajib diisi!']);
        exit;
    }

    if (!$id_user) {
        echo json_encode(['status' => 'error', 'message' => 'ID user wajib diisi!']);
        exit;
    }

    try {
        // 🔍 Cek apakah username sudah dipakai oleh user lain
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_masyarakat WHERE username = :username AND id_user != :id");
        $stmt->execute([
            ':username' => $username,
            ':id' => $id_user
        ]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan oleh pengguna lain!']);
            exit;
        }

        // 🔄 Update data (dengan/atau tanpa password)
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE tb_masyarakat SET nama_lengkap = :nama, username = :username, password = :password, telp = :telp WHERE id_user = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nama' => $nama,
                ':username' => $username,
                ':password' => $hashedPassword,
                ':telp' => $telp,
                ':id' => $id_user
            ]);
        } else {
            $sql = "UPDATE tb_masyarakat SET nama_lengkap = :nama, username = :username, telp = :telp WHERE id_user = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nama' => $nama,
                ':username' => $username,
                ':telp' => $telp,
                ':id' => $id_user
            ]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Data berhasil diupdate!']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate data: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak valid']);
}
