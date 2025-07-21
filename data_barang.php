<?php include 'index.php'; ?>

<style>
  .content-wrapper {
    width: 75%;
    margin-left: 300px;
  }
</style>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4 mt-2"><span class="text-muted fw-light">Halo,</span> <?php echo $_SESSION['role']; ?></h4>
    <h4 class="py-3 mb-4">Data Barang</h4>

    <div class="row">
      <div class="col">
        <div class="nav-align-top mb-4">
          <div class="tab-content">
            <div class="tab-pane fade active show">
              <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#tambahModal">
                Tambah Barang
              </button>
              <div class="table-responsive text-nowrap">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Nama Barang</th>
                      <th>Tanggal</th>
                      <th>Harga Awal</th>
                      <th>Harga Akhir</th>
                      <th>Foto</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    include 'config.php'; // PDO

                    $sql = "SELECT 
                    tb_barang.id_barang,
                    tb_barang.nama_barang,
                    tb_barang.harga_awal,
                    tb_barang.tgl,
                    tb_barang.foto,
                    tb_lelang.id_lelang,
                    tb_lelang.harga_akhir,
                    tb_lelang.status
                    FROM tb_barang
                    JOIN tb_lelang ON tb_barang.id_barang = tb_lelang.id_barang";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (count($results) === 0) {
                      echo "<tr><td colspan='7' class='text-center text-muted'>Belum ada barang yang dilelang saat ini.</td></tr>";
                    } else {
                      foreach ($results as $row) {
                        $status = strtolower($row['status']);
                        $badgeClass = 'badge text-white px-3 py-2'; // class dasar

                        if ($status === 'dibuka') {
                          $badgeClass .= ' bg-success';
                          $newStatus = 'ditutup';
                        } elseif ($status === 'ditutup') {
                          $badgeClass .= ' bg-danger';
                          $buttonLabel = 'Buka';
                          $newStatus = 'dibuka';
                        } else {
                          $badgeClass .= ' bg-secondary';
                          $buttonLabel = 'Buka';
                          $newStatus = 'dibuka';
                        }

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['nama_barang']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['tgl']) . "</td>";
                        echo "<td>Rp " . number_format($row['harga_awal'], 0, ',', '.') . "</td>";
                        echo "<td>";
                        if (!empty($row['harga_akhir']) && $row['harga_akhir'] > 0) {
                          echo "Rp " . number_format($row['harga_akhir'], 0, ',', '.');
                        } else {
                          echo "<em>Belum ada</em>";
                        }
                        echo "</td>";

                        echo "<td><img src='uploads/" . htmlspecialchars($row['foto']) . "' width='100'></td>";
                        echo "<td><span class='$badgeClass'>" . strtoupper(htmlspecialchars($row['status'])) . "</span></td>";

                        echo "</tr>";
                      }
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Barang -->
<div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formBarang" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tambahModalLabel">Tambah Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="nama_barang" class="form-label">Nama Barang</label>
          <input type="text" name="nama_barang" id="nama_barang" class="form-control">
        </div>
        <div class="mb-3">
          <label for="tanggal" class="form-label">Tanggal</label>
          <input type="date" name="tgl" id="tanggal" class="form-control">
          <script>
            document.getElementById('tanggal').valueAsDate = new Date();
          </script>
        </div>
        <div class="mb-3">
          <label for="harga_awal" class="form-label">Harga Awal</label>
          <input type="number" name="harga_awal" id="harga_awal" class="form-control">
        </div>
        <div class="mb-3">
          <label for="deskripsi_barang" class="form-label">Deskripsi Barang</label>
          <input type="text" name="deskripsi_barang" id="deskripsi_barang" class="form-control">
        </div>
        <div class="mb-3">
          <label for="foto" class="form-label">Foto Barang</label>
          <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Notifikasi -->
<div class="modal fade" id="notifModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">
      <div id="notifLottie" style="width: 300px; height: 200px; margin: 0 auto;"></div>
      <h5 class="mt-3" id="notifMessage"></h5>
    </div>
  </div>
</div>

<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('formBarang').addEventListener('submit', function(e) {
      e.preventDefault();

      const form = e.target;
      const formData = new FormData(form);
      const nama_barang = formData.get('nama_barang').trim();
      const harga_awal = formData.get('harga_awal').trim();
      const foto = formData.get('foto');
      const tgl = formData.get('tgl')?.trim();

      if (!nama_barang || !harga_awal || !tgl || !foto || foto.size === 0) {
        showNotif('Semua data harus diisi!', false);
        const tambahModal = bootstrap.Modal.getInstance(document.getElementById('tambahModal'));
        tambahModal?.hide();
        return;
      }


      // Lanjutkan AJAX jika valid
      fetch('tambah_barang.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          const success = data.status === 'success';
          showNotif(data.message, success);

          const tambahModal = bootstrap.Modal.getInstance(document.getElementById('tambahModal'));
          tambahModal?.hide();

          if (success) {
            form.reset();
            setTimeout(() => {
              document.getElementById('notifModal')?.classList.remove('show');
              window.location.reload();
            }, 1000);
          }

        });
    });

    function showNotif(message, success = true) {
      const animationPath = success ?
        'https://assets7.lottiefiles.com/packages/lf20_jbrw3hcz.json' :
        'https://lottie.host/e04159cf-06e5-4d1a-a514-edf766d5f393/4rvAT0Erjb.json';

      const container = document.getElementById('notifLottie');
      container.innerHTML = ''; // Bersihkan animasi sebelumnya

      lottie.loadAnimation({
        container: container,
        renderer: 'svg',
        loop: false,
        autoplay: true,
        path: animationPath
      });

      document.getElementById('notifMessage').textContent = message;

      const notifModalEl = document.getElementById('notifModal');
      const notifModal = new bootstrap.Modal(notifModalEl, {
        backdrop: 'static',
        keyboard: false
      });
      notifModal.show();

      // Tutup modal otomatis setelah 2 detik
      setTimeout(() => {
        notifModal.hide();
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
      }, 1000);
    }
  });
</script>