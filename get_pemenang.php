<?php
header('Content-Type: application/json');
require 'config.php';

$id_barang = $_GET['id_barang'] ?? null;

if (!$id_barang) {
    echo json_encode(['error' => 'ID Barang tidak ditemukan']);
    exit;
}

try {
    // Ambil penawar tertinggi
    $stmt = $pdo->prepare("
    SELECT m.id_user, m.nama_lengkap AS nama, h.penawaran_harga
    FROM history_lelang h
    JOIN tb_masyarakat m ON h.id_user = m.id_user
    WHERE h.id_barang = ?
    ORDER BY h.penawaran_harga DESC
    LIMIT 1
");
    $stmt->execute([$id_barang]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        // Update harga_akhir dan id_user pemenang ke tb_lelang
        $update = $pdo->prepare("UPDATE tb_lelang SET harga_akhir = ?, id_user = ? WHERE id_barang = ?");
        $update->execute([$data['penawaran_harga'], $data['id_user'], $id_barang]);

        echo json_encode([
            'nama' => $data['nama'],
            'harga' => $data['penawaran_harga']
        ]);
    } else {
        echo json_encode(null);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
