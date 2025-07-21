<?php include 'index.php'; ?>

<style>
  .py-3 {
    margin-left: 300px;
  }

  .card {
    width: 75%;
    margin-left: 300px;
  }

  .table-hover {
    overflow: hidden;
  }

  .table-hover tbody tr {
    transition: all 0.3s ease-in-out;
  }

  .table-hover tbody tr:hover {
    background-color: #f1f1f1;
    transform: scale(1.01);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .card-header .btn {
    margin-right: 10px;
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

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4 mt-2"><span class="text-muted fw-light">Halo,</span> <?php echo $_SESSION['role']; ?></h4>
    <h4 class="py-3 mb-4">Data Masyarakat</h4>

    <!-- Basic Layout -->
    <div class="row">
      <div class="card">
        <h5 class="card-header">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal" style="margin-left: -20px;">
            Tambah Masyarakat
          </button>
          <button type="button" class="btn btn-outline-success" onclick="exportToPDF()" title="Export to PDF">
            <i class="fas fa-file-pdf"></i>
          </button>
          <button type="button" class="btn btn-outline-info" onclick="exportToExcel()" title="Export to Excel">
            <i class="fas fa-file-excel"></i>
          </button>
        </h5>
        <div class="table-responsive text-nowrap">
          <table class="table table-hover" id="dataTable">
            <thead>
              <tr>
                <th>Nama Lengkap</th>
                <th>Username</th>
                <th>Password</th>
                <th>Telp</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              <?php
              include 'config.php';
              $stmt = $pdo->query("SELECT * FROM tb_masyarakat");
              $rows = $stmt->fetchAll();

              if (count($rows) > 0):
                foreach ($rows as $row):
              ?>
                  <tr>
                    <td><i class="menu-icon tf-icons bx bx-user"></i><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                    <td><?= htmlspecialchars($row['username']); ?></td>
                    <td>********</td>
                    <td><?= htmlspecialchars($row['telp']); ?></td>
                    <td>
                      <button class="btn btn-outline-success"
                        data-bs-toggle="modal"
                        data-bs-target="#viewModal"
                        data-nama_lengkap="<?= htmlspecialchars($row['nama_lengkap']) ?>"
                        data-username="<?= htmlspecialchars($row['username']) ?>"
                        data-password="<?= htmlspecialchars($row['password']) ?>"
                        data-telp="<?= htmlspecialchars($row['telp']) ?>">
                        <i class="fa fa-eye"></i>
                      </button>

                      <button class="btn btn-outline-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#hapusModal"
                        data-id_user="<?= $row['id_user'] ?>"
                        data-nama_lengkap="<?= htmlspecialchars($row['nama_lengkap']) ?>">
                        <i class="fa fa-trash"></i>
                      </button>

                      <button class="btn btn-outline-warning"
                        data-bs-toggle="modal"
                        data-bs-target="#editModal"
                        data-id_user="<?= $row['id_user'] ?>"
                        data-nama_lengkap="<?= htmlspecialchars($row['nama_lengkap']) ?>"
                        data-username="<?= htmlspecialchars($row['username']) ?>"
                        data-password="<?= htmlspecialchars($row['password']) ?>"
                        data-telp="<?= htmlspecialchars($row['telp']) ?>">
                        <i class="fa fa-pen"></i>
                      </button>
                    </td>
                  </tr>
                <?php
                endforeach;
              else:
                ?>
                <tr>
                  <td colspan="5" class="text-center text-muted">Tidak ada data saat ini.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>

          <!-- Modal Tambah -->
          <div class="modal fade" id="tambahModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <form id="formTambah" action="proses_masyarakat.php" method="post">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Tambah Masyarakat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                      <div class="col mb-3">
                        <label for="nameLarge" class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="nameLarge" class="form-control" placeholder="Masukkan Nama Lengkap">
                      </div>
                    </div>
                    <div class="row g-2">
                      <div class="col mb-0">
                        <label for="usernameLarge" class="form-label">Username</label>
                        <input type="text" name="username" id="usernameLarge" class="form-control" placeholder="Masukkan Username">
                      </div>
                      <div class="col mb-0">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                          <input
                            type="password"
                            id="password"
                            class="form-control"
                            name="password"
                            placeholder="Masukkan Password"
                            aria-describedby="toggle-password" />
                          <span class="input-group-text" id="toggle-password" onclick="togglePassword('password', 'toggle-icon')" style="cursor: pointer;">
                            <i class="bx bx-hide" id="toggle-icon"></i>
                          </span>
                        </div>
                      </div>
                    </div>
                    <div class="row mt-3">
                      <div class="col mb-0">
                        <label for="telpLarge" class="form-label">Telp</label>
                        <input type="number" name="telp" id="telpLarge" class="form-control" placeholder="Masukkan Telp">
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- Modal Edit -->
          <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <form id="formEdit" action="update_masyarakat.php" method="post">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Masyarakat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="id_user" id="edit_id">
                    <div class="row">
                      <div class="col mb-3">
                        <label for="edit_nama" class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="edit_nama" class="form-control" placeholder="Masukkan Nama Lengkap">
                      </div>
                    </div>
                    <div class="row g-2">
                      <div class="col mb-0">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" placeholder="Masukkan Username">
                      </div>
                      <div class="col mb-0">
                        <label for="edit_password" class="form-label">Password (Kosongkan jika tidak diubah)</label>
                        <div class="input-group">
                          <input
                            type="password"
                            id="edit_password"
                            class="form-control"
                            name="password"
                            placeholder="Masukkan Password Baru"
                            aria-describedby="toggle-edit-password" />
                          <span class="input-group-text" id="toggle-edit-password" onclick="togglePassword('edit_password', 'toggle-edit-icon')" style="cursor: pointer;">
                            <i class="bx bx-hide" id="toggle-edit-icon"></i>
                          </span>
                        </div>
                      </div>
                    </div>
                    <div class="row mt-3">
                      <div class="col mb-0">
                        <label for="edit_telp" class="form-label">Telp</label>
                        <input type="number" name="telp" id="edit_telp" class="form-control" placeholder="Masukkan Telp">
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- Modal View -->
          <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Detail Masyarakat</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <dl class="row">
                    <dt class="col-sm-3">Nama Lengkap</dt>
                    <dd class="col-sm-9" id="view_nama_lengkap"></dd>

                    <dt class="col-sm-3">Username</dt>
                    <dd class="col-sm-9" id="view_username"></dd>

                    <dt class="col-sm-3">Password</dt>
                    <dd class="col-sm-9" id="view_password">********</dd>

                    <dt class="col-sm-3">Telp</dt>
                    <dd class="col-sm-9" id="view_telp"></dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Konfirmasi Hapus -->
          <div class="modal fade" id="hapusModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Konfirmasi Hapus</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                  <p>Apakah Anda yakin ingin menghapus data untuk <strong id="nama_hapus"></strong>?</p>
                  <div class="mt-3">
                    <form id="formHapus" action="hapus_masyarakat.php" method="post">
                      <input type="hidden" name="id_user" id="hapus_id">
                      <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Notifikasi -->
          <div class="modal fade" id="notifikasiModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content text-center">
                <div class="modal-body">
                  <div id="notifikasi-animation" style="height: 150px"></div>
                  <h5 id="notifikasi-message" class="mt-3"></h5>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Gagal Hapus -->
          <div class="modal fade" id="modalGagalHapus" tabindex="-1" aria-labelledby="modalGagalHapusLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered text-center">
              <div class="modal-content p-4">
                <lottie-player
                  src="https://assets10.lottiefiles.com/packages/lf20_jz4ih9yw.json"
                  background="transparent" speed="1" style="width: 150px; height: 150px; margin: auto;" autoplay>
                </lottie-player>
                <h5 class="mt-3">Gagal Menghapus!</h5>
                <p class="text-muted" id="pesanGagalHapus"></p>
                <button type="button" class="btn btn-danger mt-2" data-bs-dismiss="modal">Tutup</button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- Lottie JS (untuk animasi) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>

<script>
  // Toggle Password
  function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      icon.classList.remove("bx-hide");
      icon.classList.add("bx-show");
    } else {
      passwordInput.type = "password";
      icon.classList.remove("bx-show");
      icon.classList.add("bx-hide");
    }
  }

  // Export to PDF
  function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    doc.text("Data Masyarakat", 14, 20);
    
    const table = document.getElementById('dataTable');
    if (!table) {
      alert('Tabel tidak ditemukan!');
      return;
    }
    
    const rows = table.querySelectorAll('tbody tr');
    const data = [];
    
    if (rows.length === 1 && rows[0].querySelector('td').getAttribute('colspan')) {
      alert('Tidak ada data untuk diekspor!');
      return;
    }
    
    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      if (cells.length >= 4) {
        data.push([
          cells[0].textContent.replace(cells[0].querySelector('i')?.outerHTML || '', '').trim(),
          cells[1].textContent,
          cells[2].textContent,
          cells[3].textContent
        ]);
      }
    });
    
    doc.autoTable({
      head: [['Nama Lengkap', 'Username', 'Password', 'Telp']],
      body: data,
      startY: 30,
      theme: 'grid',
      styles: { fontSize: 10 },
      headStyles: { fillColor: [0, 123, 255] }
    });
    
    doc.save('data_masyarakat.pdf');
  }

  function exportToExcel() {
    console.log('Memulai ekspor Excel...');
    const table = document.getElementById('dataTable');
    if (!table) {
      alert('Tabel tidak ditemukan!');
      console.error('Tabel dengan ID "dataTable" tidak ditemukan.');
      return;
    }
    
    const rows = table.querySelectorAll('tbody tr');
    const data = [['Nama Lengkap', 'Username', 'Password', 'Telp']];
    
    if (rows.length === 1 && rows[0].querySelector('td').getAttribute('colspan')) {
      alert('Tidak ada data untuk diekspor!');
      console.warn('Tabel kosong, hanya berisi pesan "Tidak ada data saat ini."');
      return;
    }
    
    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      if (cells.length >= 4) {
        const rowData = [
          cells[0].textContent.replace(cells[0].querySelector('i')?.outerHTML || '', '').trim(),
          cells[1].textContent,
          cells[2].textContent,
          cells[3].textContent
        ];
        data.push(rowData);
        console.log('Baris data:', rowData); // Debugging
      }
    });
    
    console.log('Data untuk ekspor:', data); // Debugging
    
    try {
      const wb = XLSX.utils.book_new();
      const ws = XLSX.utils.aoa_to_sheet(data);
      XLSX.utils.book_append_sheet(wb, ws, 'Data Masyarakat');
      
      const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
      const blob = new Blob([wbout], { type: 'application/octet-stream' });
      saveAs(blob, 'data_masyarakat.xlsx');
      console.log('File Excel berhasil dibuat dan diunduh.');
    } catch (error) {
      alert('Gagal mengekspor ke Excel: ' + error.message);
      console.error('Kesalahan saat ekspor Excel:', error);
    }
  }

  // Tangkap tombol hapus dan tampilkan modal konfirmasi hapus dengan data
  var hapusModal = document.getElementById('hapusModal');
  hapusModal.addEventListener('show.bs.modal', function(event) {
    var button = event.relatedTarget;
    var idUser = button.getAttribute('data-id_user');
    var nama = button.getAttribute('data-nama_lengkap');

    hapusModal.querySelector('#hapus_id').value = idUser;
    hapusModal.querySelector('#nama_hapus').textContent = nama;
  });

  // Tangani submit form hapus dengan AJAX
  document.getElementById('formHapus').addEventListener('submit', function(e) {
    e.preventDefault(); // cegah reload halaman

    var idUser = document.getElementById('hapus_id').value;

    fetch('hapus_masyarakat.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'id_user=' + encodeURIComponent(idUser)
      })
      .then(res => res.json())
      .then(data => {
        var hapusModalInstance = bootstrap.Modal.getInstance(hapusModal);
        hapusModalInstance.hide();

        // Set animasi dan pesan pada modal notifikasi
        var notifModal = new bootstrap.Modal(document.getElementById('notifikasiModal'));
        var messageEl = document.getElementById('notifikasi-message');

        // Clear container animasi dulu (jika sudah ada animasi sebelumnya)
        var animContainer = document.getElementById('notifikasi-animation');
        animContainer.innerHTML = '';

        if (data.status === 'success') {
          messageEl.textContent = 'Data berhasil dihapus!';
          // Load animasi sukses dari lottiefiles
          lottie.loadAnimation({
            container: animContainer,
            renderer: 'svg',
            loop: false,
            autoplay: true,
            path: 'https://assets10.lottiefiles.com/packages/lf20_jbrw3hcz.json' // contoh animasi sukses
          });
        } else {
          messageEl.textContent = data.message || 'Terjadi kesalahan.';
          // Load animasi error dari lottiefiles
          lottie.loadAnimation({
            container: animContainer,
            renderer: 'svg',
            loop: false,
            autoplay: true,
            path: 'https://lottie.host/e04159cf-06e5-4d1a-a514-edf766d5f393/4rvAT0Erjb.json' // contoh animasi gagal/error
          });
        }

        notifModal.show();

        setTimeout(() => {
          notifModal.hide();
          window.location.reload();
        }, 1000);
      })
      .catch(err => {
        alert('Terjadi kesalahan: ' + err.message);
      });
  });

  // Modal Edit
  var editModal = document.getElementById('editModal');
  editModal.addEventListener('show.bs.modal', function(event) {
    var button = event.relatedTarget;
    var id = button.getAttribute('data-id_user');
    var nama = button.getAttribute('data-nama_lengkap');
    var username = button.getAttribute('data-username');
    var password = button.getAttribute('data-password');
    var telp = button.getAttribute('data-telp');

    editModal.querySelector('#edit_id').value = id;
    editModal.querySelector('#edit_nama').value = nama;
    editModal.querySelector('#edit_username').value = username;
    editModal.querySelector('#edit_password').value = ''; // kosongkan agar tidak otomatis terisi
    editModal.querySelector('#edit_telp').value = telp;
  });

  // Modal View
  var viewModal = document.getElementById('viewModal');
  viewModal.addEventListener('show.bs.modal', function(event) {
    var button = event.relatedTarget;
    var nama = button.getAttribute('data-nama_lengkap');
    var username = button.getAttribute('data-username');
    var password = button.getAttribute('data-password');
    var telp = button.getAttribute('data-telp');

    viewModal.querySelector('#view_nama_lengkap').textContent = nama;
    viewModal.querySelector('#view_username').textContent = username;
    viewModal.querySelector('#view_password').textContent = '********'; // jangan tampilkan password asli
    viewModal.querySelector('#view_telp').textContent = telp;
  });

  function ajaxSubmitForm(formId, url) {
    const form = document.getElementById(formId);

    form.addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(form);

      fetch(url, {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          // Tutup modal form
          const modalEl = form.closest('.modal');
          const modalInstance = bootstrap.Modal.getInstance(modalEl);
          if (modalInstance) modalInstance.hide();

          // Setup modal notifikasi
          const notifModalEl = document.getElementById('notifikasiModal');
          const notifModal = new bootstrap.Modal(notifModalEl);
          const messageEl = document.getElementById('notifikasi-message');
          const animContainer = document.getElementById('notifikasi-animation');
          animContainer.innerHTML = '';

          if (data.status === 'success') {
            messageEl.textContent = data.message || 'Berhasil!';
            lottie.loadAnimation({
              container: animContainer,
              renderer: 'svg',
              loop: false,
              autoplay: true,
              path: 'https://assets10.lottiefiles.com/packages/lf20_jbrw3hcz.json' // animasi sukses
            });
          } else {
            messageEl.textContent = data.message || 'Gagal melakukan aksi.';
            lottie.loadAnimation({
              container: animContainer,
              renderer: 'svg',
              loop: false,
              autoplay: true,
              path: 'https://lottie.host/e04159cf-06e5-4d1a-a514-edf766d5f393/4rvAT0Erjb.json' // animasi gagal/error
            });
          }

          notifModal.show();

          setTimeout(() => {
            notifModal.hide();
            window.location.reload();
          }, 1000);
        })
        .catch(err => {
          alert('Terjadi kesalahan: ' + err.message);
        });
    });
  }

  // Pasang AJAX submit pada form tambah dan edit
  ajaxSubmitForm('formTambah', 'proses_masyarakat.php');
  ajaxSubmitForm('formEdit', 'update_masyarakat.php');
</script>