<?php
require 'config.php'; // berisi koneksi PDO, contoh: $pdo = new PDO(...);

if (isset($_POST['id_user'])) {
    $id_user = $_POST['id_user'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tb_masyarakat WHERE id_user = :id_user");
        $stmt->execute(['id_user' => $id_user]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $pesan = "Gagal menghapus data! User ini masih memiliki riwayat lelang.";
        } else {
            $pesan = "Gagal menghapus data: " . $e->getMessage();
        }
        echo json_encode(['status' => 'error', 'message' => $pesan]);
    }
}
