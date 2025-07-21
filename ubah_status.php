<?php
include 'config.php';

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_lelang = $_POST['id_lelang'] ?? '';
        $status = $_POST['status'] ?? '';

        if (empty($id_lelang) || empty($status)) {
            $response['message'] = 'Data tidak lengkap!';
            echo json_encode($response);
            exit;
        }

        if ($status === 'ditutup') {
            // Cari penawaran tertinggi
            $stmt = $pdo->prepare("SELECT id_user, penawaran_harga FROM history_lelang WHERE id_lelang = :id_lelang ORDER BY penawaran_harga DESC LIMIT 1");
            $stmt->execute(['id_lelang' => $id_lelang]);
            $highestBid = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($highestBid) {
                // Update status + harga_akhir + id_user
                $stmt = $pdo->prepare("UPDATE tb_lelang SET status = :status, harga_akhir = :harga_akhir, id_user = :id_user WHERE id_lelang = :id_lelang");
                $stmt->execute([
                    'status' => $status,
                    'harga_akhir' => $highestBid['penawaran_harga'],
                    'id_user' => $highestBid['id_user'],
                    'id_lelang' => $id_lelang
                ]);
            } else {
                // Tidak ada penawaran → hanya ubah status
                $stmt = $pdo->prepare("UPDATE tb_lelang SET status = :status WHERE id_lelang = :id_lelang");
                $stmt->execute([
                    'status' => $status,
                    'id_lelang' => $id_lelang
                ]);
            }

            $response['status'] = 'success';
            $response['message'] = 'Lelang berhasil ditutup!';
        } elseif ($status === 'dibuka') {
            // Reset harga_akhir dan id_user saat dibuka kembali
            $stmt = $pdo->prepare("UPDATE tb_lelang SET status = :status, harga_akhir = NULL, id_user = NULL WHERE id_lelang = :id_lelang");
            $stmt->execute([
                'status' => $status,
                'id_lelang' => $id_lelang
            ]);

            $response['status'] = 'success';
            $response['message'] = 'Lelang berhasil dibuka!';
        } else {
            // Status lain (fallback)
            $stmt = $pdo->prepare("UPDATE tb_lelang SET status = :status WHERE id_lelang = :id_lelang");
            $stmt->execute([
                'status' => $status,
                'id_lelang' => $id_lelang
            ]);

            $response['status'] = 'success';
            $response['message'] = 'Status berhasil diubah!';
        }

    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    header('Location: barang.php');
    exit;
}
