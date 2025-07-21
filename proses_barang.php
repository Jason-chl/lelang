<?php
include 'config.php';

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan saat menyimpan data.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $nama_barang = $_POST['nama_barang'] ?? '';
    $harga_awal = $_POST['harga_awal'] ?? '';
    $tgl = $_POST['tgl'] ?? date('Y-m-d');
    $deskripsi_barang = $_POST['deskripsi_barang'] ?? '';
    $foto = $_FILES['foto'];

    if (empty($nama_barang) || empty($harga_awal) || empty($foto['name']) || empty($deskripsi_barang)) {
      $response['message'] = 'Semua field harus diisi!';
    } else {
      $target_dir = "uploads/";
      $filename = time() . "_" . basename($foto["name"]);
      $target_file = $target_dir . $filename;

      if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
      }

      if (move_uploaded_file($foto["tmp_name"], $target_file)) {
        // 1. Simpan ke tb_barang
        $stmt = $pdo->prepare("INSERT INTO tb_barang (nama_barang, harga_awal, foto, deskripsi_barang, tgl) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nama_barang, $harga_awal, $filename, $deskripsi_barang, $tgl]);

        // 2. Ambil ID barang terakhir
        $id_barang = $pdo->lastInsertId();

        // 3. Masukkan ke tb_lelang dengan status default "dibuka" dan harga_akhir NULL atau sama dengan harga_awal
        $stmt2 = $pdo->prepare("INSERT INTO tb_lelang (id_barang, harga_akhir, status) VALUES (?, ?, ?)");
        $stmt2->execute([$id_barang, null, 'ditutup']);

        $response['status'] = 'success';
        $response['message'] = 'Barang dan data lelang berhasil ditambahkan!';
      } else {
        $response['message'] = 'Gagal mengupload gambar.';
      }
    }
  } catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
  }
}

header('Content-Type: application/json');
echo json_encode($response);
?>