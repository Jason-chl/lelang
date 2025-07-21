<?php
require 'config.php';

$id_barang = $_GET['id_barang'] ?? 0;

$sql = "SELECT h.penawaran_harga, m.nama_lengkap
        FROM history_lelang h
        JOIN tb_masyarakat m ON h.id_user = m.id_user
        WHERE h.id_barang = ?
        ORDER BY h.penawaran_harga DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_barang]);
$penawaran = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($penawaran);
