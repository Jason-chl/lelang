
<?php
// proses_masyarakat.php - File untuk memproses penambahan data masyarakat

// Koneksi ke database
include 'config.php';

// Cek apakah ada request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Inisialisasi response
    $response = [
        'status' => 'error',
        'message' => 'Terjadi kesalahan!'
    ];

    try {
        // Ambil data dari form
        $nama_lengkap = isset($_POST['nama_lengkap']) ? trim($_POST['nama_lengkap']) : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $telp = isset($_POST['telp']) ? trim($_POST['telp']) : '';

        // Validasi input
        if (empty($nama_lengkap) || empty($username) || empty($password) || empty($telp)) {
            $response['message'] = 'Semua field harus diisi!';
            echo json_encode($response);
            exit;
        }

        // Cek apakah username sudah digunakan
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_masyarakat WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $response['message'] = 'Username sudah digunakan!';
            echo json_encode($response);
            exit;
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Simpan data baru
        $stmt = $pdo->prepare("INSERT INTO tb_masyarakat (nama_lengkap, username, password, telp) VALUES (?, ?, ?, ?)");
        
        if($stmt->execute([$nama_lengkap, $username, $hashed_password, $telp])) {
            $response['status'] = 'success';
            $response['message'] = 'Data berhasil ditambahkan!';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }

    // Kirim response dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Jika bukan request POST, redirect ke halaman utama
    header('Location: admin.php');
    exit;
}