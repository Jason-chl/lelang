<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Masyarakat') {
    $_SESSION['notif'] = [
        'status' => 'error',
        'message' => 'Anda harus login sebagai masyarakat untuk melakukan penawaran.'
    ];
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_barang = $_POST['id_barang'] ?? null;
    $harga_penawaran = $_POST['harga_penawaran'] ?? null;
    $username = $_SESSION['username'];

    if (!$id_barang || !$harga_penawaran) {
        $_SESSION['notif'] = [
            'status' => 'error',
            'message' => 'Data tidak lengkap!'
        ];
        header('Location: masyarakat.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id_user FROM tb_masyarakat WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['notif'] = [
                'status' => 'error',
                'message' => 'User tidak ditemukan.'
            ];
            header('Location: masyarakat.php');
            exit;
        }

        $id_user = $user['id_user'];

        $stmt = $pdo->prepare("SELECT id_lelang FROM tb_lelang WHERE id_barang = ?");
        $stmt->execute([$id_barang]);
        $lelang = $stmt->fetch();

        if (!$lelang) {
            $_SESSION['notif'] = [
                'status' => 'error',
                'message' => 'Lelang tidak ditemukan.'
            ];
            header('Location: masyarakat.php');
            exit;
        }

        $id_lelang = $lelang['id_lelang'];

        $stmt = $pdo->prepare("INSERT INTO history_lelang (id_lelang, id_barang, id_user, penawaran_harga) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_lelang, $id_barang, $id_user, $harga_penawaran]);

        $_SESSION['notif'] = [
            'status' => 'success',
            'message' => 'Penawaran berhasil dikirim!'
        ];
        header('Location: masyarakat.php');
    } catch (PDOException $e) {
        $_SESSION['notif'] = [
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ];
        header('Location: masyarakat.php');
    }
} else {
    $_SESSION['notif'] = [
        'status' => 'error',
        'message' => 'Akses tidak diizinkan.'
    ];
    header('Location: masyarakat.php');
}
