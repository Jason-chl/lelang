<?php include 'index.php'; ?>

<style>
  .content-wrapper {
    width: 75%;
    margin-left: 300px;
  }
</style>

<!-- Bootstrap JS dan Popper -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<!-- jsPDF and AutoTable for PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<!-- XLSX for Excel generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<!-- FileSaver.js for reliable file downloads -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<!-- Lottie JS untuk animasi -->
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

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
              <button type="button" class="btn btn-outline-success mb-2" onclick="exportToPDF()" title="Export to PDF">
                <i class="fas fa-file-pdf"></i>
              </button>
              <button type="button" class="btn btn-outline-info mb-2" onclick="exportToExcel()" title="Export to Excel">
                <i class="fas fa-file-excel"></i>
              </button>
              <div class="table-responsive text-nowrap">
                <table class="table" id="dataTable">
                  <thead>
                    <tr>
                      <th>Nama Barang</th>
                      <th>Tanggal</th>
                      <th>Harga Awal</th>
                      <th>Harga Akhir</th>
                      <th>Foto</th>
                      <th>Status</th>
                      <th>Aksi</th>
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
                          $buttonLabel = 'Tutup';
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

                        echo "<td>";
                        if (!empty($row['foto']) && file_exists("uploads/" . $row['foto'])) {
                          echo "<img src='uploads/" . htmlspecialchars($row['foto']) . "' alt='Foto Barang' class='img-thumbnail' style='width: 100px; height: auto;'>";
                        } else {
                          echo "<span class='text-muted'>Tidak ada foto</span>";
                        }
                        echo "</td>";
                        echo "<td><span class='$badgeClass'>" . strtoupper(htmlspecialchars($row['status'])) . "</span></td>";

                        if (!empty($row['id_lelang'])) {
                          echo "<td>
                            <button 
                            type='button' 
                            class='btn btn-sm btn-outline-primary ubah-status-btn'
                            data-id='" . htmlspecialchars($row['id_lelang']) . "'
                            data-status='" . htmlspecialchars($newStatus) . "'>
                            " . htmlspecialchars($buttonLabel) . "
                            </button>
                          </td>";
                        } else {
                          echo "<td><span class='text-muted'>Belum ada lelang</span></td>";
                        }

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

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk ekspor ke PDF
    function exportToPDF() {
      console.log('Memulai ekspor PDF...');
      const {
        jsPDF
      } = window.jspdf;
      const doc = new jsPDF();

      doc.text("Data Barang", 14, 20);

      const table = document.getElementById('dataTable');
      if (!table) {
        alert('Tabel tidak ditemukan!');
        console.error('Tabel dengan ID "dataTable" tidak ditemukan.');
        return;
      }

      const rows = table.querySelectorAll('tbody tr');
      const data = [];

      if (rows.length === 1 && rows[0].querySelector('td').getAttribute('colspan')) {
        alert('Tidak ada data untuk diekspor!');
        console.warn('Tabel kosong, hanya berisi pesan "Belum ada barang yang dilelang saat ini."');
        return;
      }

      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 6) {
          const rowData = [
            cells[0].textContent.trim(), // Nama Barang
            cells[1].textContent.trim(), // Tanggal
            cells[2].textContent.trim(), // Harga Awal
            cells[3].textContent.trim(), // Harga Akhir
            cells[4].textContent.trim(), // Foto (nama file)
            cells[5].querySelector('span').textContent.trim() // Status
          ];
          data.push(rowData);
          console.log('Baris data PDF:', rowData);
        }
      });

      console.log('Data untuk ekspor PDF:', data);

      try {
        doc.autoTable({
          head: [
            ['Nama Barang', 'Tanggal', 'Harga Awal', 'Harga Akhir', 'Foto', 'Status']
          ],
          body: data,
          startY: 30,
          theme: 'grid',
          styles: {
            fontSize: 10
          },
          headStyles: {
            fillColor: [0, 123, 255]
          }
        });

        doc.save('data_barang.pdf');
        console.log('File PDF berhasil dibuat dan diunduh.');
      } catch (error) {
        alert('Gagal mengekspor ke PDF: ' + error.message);
        console.error('Kesalahan saat ekspor PDF:', error);
      }
    }

    // Fungsi untuk ekspor ke Excel
    function exportToExcel() {
      console.log('Memulai ekspor Excel...');
      const table = document.getElementById('dataTable');
      if (!table) {
        alert('Tabel tidak ditemukan!');
        console.error('Tabel dengan ID "dataTable" tidak ditemukan.');
        return;
      }

      const rows = table.querySelectorAll('tbody tr');
      const data = [
        ['Nama Barang', 'Tanggal', 'Harga Awal', 'Harga Akhir', 'Foto', 'Status']
      ];

      if (rows.length === 1 && rows[0].querySelector('td').getAttribute('colspan')) {
        alert('Tidak ada data untuk diekspor!');
        console.warn('Tabel kosong, hanya berisi pesan "Belum ada barang yang dilelang saat ini."');
        return;
      }

      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 6) {
          const rowData = [
            cells[0].textContent.trim(), // Nama Barang
            cells[1].textContent.trim(), // Tanggal
            cells[2].textContent.trim(), // Harga Awal
            cells[3].textContent.trim(), // Harga Akhir
            cells[4].textContent.trim(), // Foto (nama file)
            cells[5].querySelector('span').textContent.trim() // Status
          ];
          data.push(rowData);
          console.log('Baris data Excel:', rowData);
        }
      });

      console.log('Data untuk ekspor Excel:', data);

      try {
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, 'Data Barang');

        const wbout = XLSX.write(wb, {
          bookType: 'xlsx',
          type: 'array'
        });
        const blob = new Blob([wbout], {
          type: 'application/octet-stream'
        });
        saveAs(blob, 'data_barang.xlsx');
        console.log('File Excel berhasil dibuat dan diunduh.');
      } catch (error) {
        alert('Gagal mengekspor ke Excel: ' + error.message);
        console.error('Kesalahan saat ekspor Excel:', error);
      }
    }

    // Event listener untuk tombol ekspor
    document.querySelector('.btn-outline-success').addEventListener('click', exportToPDF);
    document.querySelector('.btn-outline-info').addEventListener('click', exportToExcel);

    // Fungsi untuk tombol ubah status dan form barang (tetap sama)
    document.querySelectorAll('.ubah-status-btn').forEach(button => {
      button.addEventListener('click', function() {
        const id_lelang = this.dataset.id;
        const status = this.dataset.status;

        fetch('ubah_status.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
              id_lelang,
              status
            })
          })
          .then(res => res.json())
          .then(data => {
            const notifMessage = document.getElementById('notifMessage');
            const notifAnim = document.getElementById('notifLottie');

            notifMessage.textContent = data.message;
            notifAnim.innerHTML = ''; // Kosongkan animasi sebelumnya

            const animationPath = data.status === 'success' ?
              'https://assets7.lottiefiles.com/packages/lf20_jbrw3hcz.json' :
              'https://lottie.host/e04159cf-06e5-4d1a-a514-edf766d5f393/4rvAT0Erjb.json';

            lottie.loadAnimation({
              container: notifAnim,
              renderer: 'svg',
              loop: false,
              autoplay: true,
              path: animationPath
            });

            const notifModal = new bootstrap.Modal(document.getElementById('notifModal'));
            notifModal.show();

            setTimeout(() => {
              notifModal.hide();
              const backdrop = document.querySelector('.modal-backdrop');
              if (backdrop) backdrop.remove();
              location.reload();
            }, 1000);
          });
      });
    });

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

      fetch('proses_barang.php', {
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

      setTimeout(() => {
        notifModal.hide();
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
      }, 1000);
    }
  });
</script>