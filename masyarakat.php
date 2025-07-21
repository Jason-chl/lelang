<?php
include 'index.php';
include 'config.php';

$sql = "SELECT b.*, l.id_lelang, l.tgl_lelang, l.status
        FROM tb_barang b 
        INNER JOIN tb_lelang l ON b.id_barang = l.id_barang
        ORDER BY CASE WHEN l.status = 'dibuka' THEN 1 ELSE 2 END, b.tgl DESC";

try {
    $stmt = $pdo->query($sql);
    $barangList = $stmt->fetchAll(PDO::FETCH_ASSOC); // ambil data jadi array asosiatif
} catch (PDOException $e) {
    echo "Query error: " . $e->getMessage();
    $barangList = []; // set ke array kosong supaya tidak error di foreach
}
?>

<style>
    .content-wrapper {
        width: 75%;
        margin-left: 300px;
    }

    .status-text {
        font-family: inherit;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .scrollable-list {
        max-height: 200px;
        overflow-y: auto;
    }

    .card-img-top {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }

    .btn-bottom {
        position: absolute;
        bottom: 1rem;
        left: 1rem;
        right: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card {
        position: relative;
        height: 100%;
        padding-bottom: 4rem;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4 mt-2"><span class="text-muted fw-light">Halo,</span> <?= $_SESSION['role'] ?></h4>
        <h4 class="py-3 mb-4">Barang yang Dilelang</h4>

        <div class="mb-3">
            <label for="filterStatus" class="form-label">Filter Status:</label>
            <select id="filterStatus" class="form-select" style="width: 200px;">
                <option value="semua">Semua</option>
                <option value="dibuka">Dibuka</option>
                <option value="ditutup">Ditutup</option>
            </select>
        </div>
        <br>
        <div class="row">
            <?php if (count($barangList) === 0): ?>
                <div class="col-12 text-center mt-5">
                    <h5>Tidak ada barang yang sedang dilelang.</h5>
                </div>
            <?php else: ?>
                <?php foreach ($barangList as $barang): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card barang-card" data-status="<?= htmlspecialchars($barang['status']) ?>">
                            <img src="Uploads/<?= htmlspecialchars($barang['foto']) ?>" class="card-img-top" alt="Gambar Barang">
                            <div class="card-body">
                                <h5 class="card-title"><strong>Nama Barang :</strong> <?= htmlspecialchars($barang['nama_barang']) ?></h5>
                                <p class="card-text"><strong>Harga Awal :</strong> Rp <?= number_format($barang['harga_awal'], 0, ',', '.') ?></p>
                                <p class="card-text"><strong>Deskripsi :</strong><br> <?= htmlspecialchars($barang['deskripsi_barang']) ?></p>
                                <p class="card-text"><strong>Tanggal :</strong> <?= htmlspecialchars($barang['tgl']) ?></p>
                            </div>
                            <?php
                            $status = htmlspecialchars($barang['status']);
                            $bgClass = $status == 'dibuka' ? 'bg-success' : 'bg-danger';
                            ?>
                            <div class="btn-bottom">
                                <button
                                    class="btn btn-primary open-modal"
                                    data-id="<?= $barang['id_barang'] ?>"
                                    data-status="<?= $barang['status'] ?>"
                                    data-harga_awal="<?= $barang['harga_awal'] ?>">
                                    <?= $status === 'dibuka' ? 'Lihat Penawaran' : 'Lihat Pemenang' ?>
                                </button>
                                <span class="badge <?= $bgClass ?> text-white px-3 py-2">
                                    <?= strtoupper($status) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Penawaran -->
<div class="modal fade" id="modalPenawaran" tabindex="-1" aria-labelledby="modalPenawaranLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="proses_penawaran.php" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPenawaranLabel">Penawaran Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_barang" id="penawaran-id-barang">

                <!-- Daftar Penawar -->
                <div class="scrollable-list">
                    <ul class="list-group" id="list-penawaran">
                        <li class="list-group-item">Memuat data penawaran...</li>
                    </ul>
                </div>

                <!-- Form Penawaran Baru -->
                <div class="mb-3 mt-4">
                    <label for="harga_penawaran" class="form-label">Harga Penawaran Anda</label>
                    <input type="number" name="harga_penawaran" class="form-control" id="harga_penawaran" min="1">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Kirim Penawaran</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Lottie Notif -->
<div class="modal fade" id="modalNotif" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div id="lottie-warning" style="height: 120px;"></div>
            <h5 class="mt-2" id="notifText">Harus memasukkan nominal!</h5>
        </div>
    </div>
</div>

<!-- Lottie CDN -->
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

<!-- Modal Pemenang -->
<div class="modal fade" id="modalPemenang" tabindex="-1" aria-labelledby="modalPemenangLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div class="modal-header border-0">
                <h5 class="modal-title w-100" id="modalPemenangLabel" style="margin-left: 20px;">Pemenang Lelang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <!-- Animasi: ADA Pemenang -->
                <lottie-player
                    id="animasi-pemenang-ada"
                    src="https://lottie.host/340b0c43-ce33-4d35-ac6f-828486335639/9ucdhxp23q.json"
                    background="transparent"
                    speed="1"
                    style="width: 200px; height: 200px; display: block; margin: 0 auto;"
                    autoplay
                    loop=true
                    hidden>
                </lottie-player>

                <!-- Animasi: TIDAK ADA Pemenang -->
                <lottie-player
                    id="animasi-pemenang-tidak-ada"
                    src="https://lottie.host/265ff300-1c34-4f93-a35a-e1a0eccb4ce3/0tbgTS87Ll.json"
                    background="transparent"
                    speed="1"
                    style="width: 200px; height: 200px; display: block; margin: 0 auto;"
                    autoplay
                    loop=true
                    hidden>
                </lottie-player>

                <!-- Info Pemenang -->
                <div id="pemenang-info" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['notif'])):
    $notif = $_SESSION['notif'];
    $isSuccess = $notif['status'] === 'success';
?>
    <!-- Modal Notifikasi -->
    <div class="modal fade" id="notifModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <div class="modal-body">
                    <lottie-player
                        src="<?= $isSuccess
                                    ? 'https://assets10.lottiefiles.com/packages/lf20_jbrw3hcz.json'  // sukses
                                    : 'https://lottie.host/e04159cf-06e5-4d1a-a514-edf766d5f393/4rvAT0Erjb.json'  // gagal
                                ?>"
                        background="transparent"
                        speed="1"
                        style="width: 300px; height: 200px; margin: 0 auto;"
                        autoplay>
                    </lottie-player>
                    <p class="mt-3"><?= htmlspecialchars($notif['message']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Script untuk tampil & auto-close -->
    <script>
        const notifModalEl = document.getElementById('notifModal');
        const notifModal = new bootstrap.Modal(notifModalEl, {
            backdrop: 'static',
            keyboard: false
        });
        notifModal.show();

        // Tutup otomatis setelah 2 detik
        setTimeout(() => {
            notifModal.hide();
        }, 1000);
    </script>

<?php unset($_SESSION['notif']);
endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.open-modal');
        const form = document.querySelector('#modalPenawaran form');
        const hargaInput = document.getElementById('harga_penawaran');
        let hargaAwal = 0;
        let hargaTertinggi = 0;

        // Saat tombol "Lihat Penawaran" diklik
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const status = this.dataset.status;
                hargaAwal = parseInt(this.dataset.harga_awal);
                hargaTertinggi = 0; // Reset sebelum ambil data baru

                if (status === 'dibuka') {
                    document.getElementById('penawaran-id-barang').value = id;

                    fetch(`get_penawaran.php?id_barang=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            const list = document.getElementById('list-penawaran');
                            list.innerHTML = '';

                            if (data.length > 0) {
                                data.forEach(item => {
                                    const harga = parseInt(item.penawaran_harga);
                                    if (!isNaN(harga) && harga > hargaTertinggi) {
                                        hargaTertinggi = harga;
                                    }

                                    const li = document.createElement('li');
                                    li.classList.add('list-group-item');
                                    li.innerHTML = `<strong>${item.nama_lengkap}</strong> melelang dengan harga Rp ${Number(item.penawaran_harga).toLocaleString('id-ID')}`;
                                    list.appendChild(li);
                                });
                            } else {
                                list.innerHTML = '<li class="list-group-item">Belum ada penawaran.</li>';
                            }

                            const modalPenawaran = new bootstrap.Modal(document.getElementById('modalPenawaran'));
                            modalPenawaran.show();
                        });

                } else {
                    fetch(`get_pemenang.php?id_barang=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            const animasiAda = document.getElementById('animasi-pemenang-ada');
                            const animasiTidakAda = document.getElementById('animasi-pemenang-tidak-ada');
                            const pemenangInfo = document.getElementById('pemenang-info');

                            if (data && data.nama && data.harga) {
                                animasiAda.hidden = false;
                                animasiTidakAda.hidden = true;
                                pemenangInfo.innerHTML = `
                                <h2 style='text-align: center;'>PEMENANG</h2>
                                <p><strong>Nama:</strong> ${data.nama}</p>
                                <p><strong>Dengan Harga Bid:</strong> Rp ${Number(data.harga).toLocaleString('id-ID')}</p>`;
                            } else {
                                animasiAda.hidden = true;
                                animasiTidakAda.hidden = false;
                                pemenangInfo.innerHTML = `<p class="mt-3">Belum ada pemenang lelang.</p>`;
                            }

                            const modalPemenang = new bootstrap.Modal(document.getElementById('modalPemenang'));
                            modalPemenang.show();
                        });
                }
            });
        });

        // Validasi saat submit form penawaran
        form.addEventListener('submit', function(e) {
            const harga = parseInt(hargaInput.value.trim());
            const modalPenawaran = bootstrap.Modal.getInstance(document.getElementById('modalPenawaran'));
            if (modalPenawaran) modalPenawaran.hide();

            lottie.destroy();

            if (isNaN(harga) || harga <= 0) {
                e.preventDefault();
                showNotif('Harus memasukkan nominal penawaran!');
                return;
            }

            if (harga < hargaAwal) {
                e.preventDefault();
                showNotif(`Harga penawaran harus minimal Rp ${hargaAwal.toLocaleString('id-ID')}`);
                return;
            }

            if (harga <= hargaTertinggi) {
                e.preventDefault();
                showNotif(`Penawaran harus lebih tinggi dari penawaran tertinggi saat ini yaitu Rp ${hargaTertinggi.toLocaleString('id-ID')}`);
                return;
            }
        });

        // Fungsi tampilkan modal notifikasi
        function showNotif(pesan) {
            const lottieContainer = document.getElementById('lottie-warning');
            lottieContainer.innerHTML = '';
            document.getElementById('notifText').innerText = pesan;
            lottie.loadAnimation({
                container: lottieContainer,
                renderer: 'svg',
                loop: false,
                autoplay: true,
                path: 'https://lottie.host/265ff300-1c34-4f93-a35a-e1a0eccb4ce3/0tbgTS87Ll.json'
            });
            const modalNotif = new bootstrap.Modal(document.getElementById('modalNotif'), {
                backdrop: 'static',
                keyboard: false
            });
            modalNotif.show();
            hargaInput.focus();
            setTimeout(() => {
                modalNotif.hide();
            }, 1000);
        }

        // Filter status
        const filterSelect = document.getElementById('filterStatus');
        const cardWrappers = document.querySelectorAll('.barang-card');

        filterSelect.addEventListener('change', function() {
            const selected = this.value;

            cardWrappers.forEach(card => {
                const status = card.getAttribute('data-status');
                const wrapper = card.closest('.col-md-4');

                if (selected === 'semua' || status === selected) {
                    wrapper.style.display = '';
                } else {
                    wrapper.style.display = 'none';
                }
            });
        });
    });
</script>