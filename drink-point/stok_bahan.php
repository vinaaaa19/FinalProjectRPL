<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (
    $_SESSION['role'] != 'pemilik' &&
    $_SESSION['role'] != 'karyawan'
) {
    header("Location: login.php");
    exit;
}

$notif_stok = mysqli_query($conn, "SELECT * FROM bahan WHERE status='Menipis'");
$jumlah_notif = mysqli_num_rows($notif_stok);

$notif_minuman_habis = mysqli_query($conn, "
    SELECT * FROM minuman 
    WHERE stok <= 0
");

$jumlah_minuman_habis = mysqli_num_rows($notif_minuman_habis);

$jumlah_notif = $jumlah_notif + $jumlah_minuman_habis;

$notif_bukti = mysqli_query($conn, "
    SELECT * FROM transaksi 
    WHERE metode_pembayaran != 'Tunai'
    AND (bukti_pembayaran IS NULL OR bukti_pembayaran = '')
");

$jumlah_bukti = mysqli_num_rows($notif_bukti);

$jumlah_notif = $jumlah_notif + $jumlah_bukti;

$user = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT * FROM users
        WHERE id='".$_SESSION['id']."'
    ")
);

if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_bahan'];
    $satuan = $_POST['satuan'];
    $stok = $_POST['stok'];

    if ($stok <= 5) {
        $status = "Menipis";
    } else {
        $status = "Aman";
    }

    mysqli_query($conn, "INSERT INTO bahan 
        (nama_bahan, satuan, stok, status)
        VALUES
        ('$nama', '$satuan', '$stok', '$status')
    ");

    header("Location: stok_bahan.php");
    exit;
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    mysqli_query($conn, "
        DELETE FROM bahan
        WHERE id_bahan='$id'
    ");

    header("Location: stok_bahan.php?success=hapus");
    exit;
}

$cari = isset($_GET['cari']) ? $_GET['cari'] : '';

$data = mysqli_query($conn, "
    SELECT * FROM bahan
    WHERE nama_bahan LIKE '%$cari%'
    ORDER BY id_bahan DESC
");

$total_bahan = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM bahan"));

$log_stok = mysqli_query($conn, "
    SELECT
        log_stok.*,
        bahan.nama_bahan,
        users.nama
    FROM log_stok
    JOIN bahan ON log_stok.id_bahan = bahan.id_bahan
    JOIN users ON log_stok.id_user = users.id
    ORDER BY log_stok.tanggal DESC
");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Stok Bahan - Drink Point</title>
    <style>

.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.35);
    justify-content: center;
    align-items: center;
    z-index: 999;
}

.modal-box {
    background: white;
    width: 360px;
    padding: 28px;
    border-radius: 18px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.18);
}

.modal-box h3 {
    margin-top: 0;
    color: #d6001c;
}

.modal-box p {
    color: #555;
}

.modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 25px;
}

.btn-cancel,
.btn-confirm {
    flex: 1;
    padding: 12px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: bold;
    border: none;
    cursor: pointer;
}

.btn-cancel {
    background: #f3f4f6;
    color: #333;
}

.btn-confirm {
    background: #d6001c;
    color: white;
}

        .modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.35);
    justify-content: center;
    align-items: center;
    z-index: 999;
}

.modal-box {
    background: white;
    width: 360px;
    padding: 28px;
    border-radius: 18px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.18);
}

.modal-box h3 {
    margin-top: 0;
    color: #d6001c;
}

.modal-box p {
    color: #555;
}

.modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 25px;
}

.btn-cancel,
.btn-logout {
    flex: 1;
    padding: 12px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: bold;
    border: none;
    cursor: pointer;
}

.btn-cancel {
    background: #f3f4f6;
    color: #333;
}

.btn-logout {
    background: #d6001c;
    color: white;
}

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff7f7;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar{
            width:300px;
            background:linear-gradient(180deg,#e6001f,#b40018);
            color:white;
            padding:30px 25px;

            position:fixed;
            left:0;
            top:0;

            height:100vh;
            overflow-y:auto;
        }

        .content{
             margin-left:330px;
    width:calc(100% - 300px);

            padding:45px;
            background:white;

            height:100vh;
            overflow-y:auto;
        }

        .logo {
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 40px;
        }

        .menu-title {
            font-size: 13px;
            font-weight: bold;
            margin: 25px 0 12px;
        }

        .menu a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px 18px;
            border-radius: 12px;
            margin-bottom: 10px;
            font-size: 17px;
        }

        .menu a.active,
        .menu a:hover {
            background: rgba(255,255,255,0.25);
        }

        .menu {
            padding-bottom: 160px;
        }

        .logout-box {
            position: fixed;
            left: 25px;
            bottom: 25px;
            width: 240px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 15px;
            padding: 18px;
            color: white;
            text-decoration: none;
            z-index: 1000;
        }

        .logout-box div {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .logout-box small {
            opacity: 0.85;
        }

        .logout-box:hover {
            background: rgba(255,255,255,0.18);
        }

        .top {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 45px;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .profile-info {
            text-align: left;
            line-height: 1.3;
        }

        .profile-info b {
            font-size: 16px;
        }

        .profile-info span {
            color: #333;
            font-size: 14px;
        }

        .avatar-link {
            text-decoration: none;
        }

        .avatar-mini {
            width: 45px;
            height: 45px;
            background: #d6001c;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            overflow: hidden;
        }

        .notif {
            position: relative;
            font-size: 28px;
            cursor: pointer;
            display: inline-block;
            margin-right: 10px;
        }

        .notif-badge {
            position: absolute;
            top: -8px;
            right: -10px;
            background: #d6001c;
            color: white;
            border-radius: 50%;
            font-size: 11px;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .notif-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 38px;
            width: 270px;
            background: white;
            color: #333;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            padding: 15px;
            z-index: 99;
            font-size: 14px;
            text-align: left;
        }

        .notif-dropdown p {
            margin: 10px 0;
            padding: 10px;
            background: #fff5f5;
            border-radius: 8px;
        }

        .notif:hover .notif-dropdown {
            display: block;
        }

        h1 {
            font-size: 38px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 35px;
        }

        .form-card,
        .table-card {
            background: white;
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.07);
            margin-bottom: 28px;
        }

        input {
            padding: 13px;
            width: 27%;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
        }

        button {
            padding: 13px 22px;
            background: #d6001c;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #ffe5e5;
            padding: 16px;
            text-align: left;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #eee;
        }

        .badge {
            padding: 7px 14px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }

        .aman {
            background: #d1fae5;
            color: #087f3f;
        }

        .menipis {
            background: #ffedd5;
            color: #c2410c;
        }

        .edit {
            background: #fff0f0;
            color: #d6001c;
            border: 1px solid #ffb3b3;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            margin-right: 8px;
        }

        .hapus {
            background: white;
            color: #d6001c;
            border: 1px solid #d6001c;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
        }

        .total {
            margin-top: 18px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            color: #777;
            font-size: 13px;
        }

        .footer span {
            color: #d6001c;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="sidebar">
        <div class="logo">🥤 Drink Point</div>

        <div class="menu">

    <?php if ($_SESSION['role'] == 'pemilik') { ?>

        <a href="dashboard.php">🏠 Dashboard</a>

        <div class="menu-title">MENU UTAMA</div>

        <a href="data_minuman.php">🧋 Data Minuman</a>
        <a href="stok_bahan.php" class="active">📦 Stok Bahan</a>
        <a href="transaksi.php">🛒 Transaksi Penjualan</a>
        <a href="laporan.php">📊 Laporan Penjualan</a>
        <a href="validasi.php">✅ Validasi Laporan</a>
        <a href="akun_karyawan.php">👥 Akun Karyawan</a>
        <a href="profil_pemilik.php">👤 Profil Saya</a>

    <?php } else { ?>

    <div class="menu">
    <a href="dashboard_karyawan.php">🏠 Dashboard</a>

    <div class="menu-title">MENU KARYAWAN</div>

    <a href="transaksi.php">🛒 Transaksi Penjualan</a>
    <a href="stok_bahan_karyawan.php" class="active">📦 Lihat Stok Bahan</a>
    <a href="stok_minuman_karyawan.php">🧋 Lihat Stok Minuman</a>
    <a href="laporan.php">📊 Laporan Penjualan</a>
    <a href="profil_karyawan.php">👤 Profil Saya</a>

    <?php } ?>

</div>

        <a href="#" onclick="openLogoutModal()" class="logout-box">
            <div>🚪 Logout</div>
            <small>Keluar dari sistem</small>
        </a>
    </div>

    <div class="content">

        <div class="top">
            <div class="profile">

                <div class="notif">
                    🔔
                    <?php if ($jumlah_notif > 0) { ?>
                        <span class="notif-badge"><?php echo $jumlah_notif; ?></span>
                    <?php } ?>

                    <div class="notif-dropdown">
                        <b>Notifikasi</b>

                       <?php if ($jumlah_notif > 0) { ?>

    <?php while ($n = mysqli_fetch_assoc($notif_stok)) { ?>
        <p>
    <a href="<?php echo ($_SESSION['role']=='pemilik') ? 'stok_bahan.php' : 'stok_bahan_karyawan.php'; ?>"
       style="color:#333;text-decoration:none;">
        ⚠ Stok <?php echo $n['nama_bahan']; ?> menipis
        <br>
        <small>Klik untuk cek stok bahan</small>
    </a>
</p>
    <?php } ?>

    <?php while ($m = mysqli_fetch_assoc($notif_minuman_habis)) { ?>
    <p>
        <a href="data_minuman.php" style="color:#333;text-decoration:none;">
            🥤 Minuman <?php echo $m['nama_minuman']; ?> habis
            <br>
            <small>Klik untuk cek data minuman</small>
        </a>
    </p>
<?php } ?>

    <?php while ($b = mysqli_fetch_assoc($notif_bukti)) { ?>
   <p>
    <a href="laporan.php" style="color:#333;text-decoration:none;">
        📷 Bukti pembayaran transaksi 
        #<?php echo $b['id_transaksi']; ?> belum diupload
        <br>
        <small>Klik untuk upload bukti pembayaran</small>
    </a>
</p>
<?php } ?>

<?php } else { ?>

    <p>Tidak ada notifikasi</p>

<?php } ?>
                    </div>
                </div>

                <a href="<?php echo ($_SESSION['role'] == 'karyawan') ? 'profil_karyawan.php' : 'profil_pemilik.php'; ?>" class="avatar-link">
                    <div class="avatar-mini">
                        <?php if (!empty($user['foto'])) { ?>
                            <img src="./uploads/<?php echo $user['foto']; ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                        <?php } else { ?>
                            <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                        <?php } ?>
                    </div>
                </a>

                <div class="profile-info">
                    <b><?php echo $_SESSION['nama']; ?></b><br>
                   <span><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>

            </div>
        </div>

        <h1>Stok Bahan</h1>
        <p class="subtitle">Kelola persediaan bahan baku minuman</p>

        <?php if(isset($_GET['success'])){ ?>

            <div style="
            background:#d1fae5;
            color:#065f46;
            padding:15px;
            border-radius:12px;
            margin-bottom:20px;
            font-weight:bold;
            ">
            ✅ Data bahan berhasil diperbarui
            </div>

            <?php } ?>

        <?php if ($_SESSION['role'] == 'pemilik') { ?>
        <div class="form-card">
            <h3>Tambah Bahan</h3>

            <form method="POST">
                <input type="text" name="nama_bahan" placeholder="Nama bahan" required>
                <input type="text" name="satuan" placeholder="Satuan: gram / kg / pcs" required>
                <input type="number" name="stok" placeholder="Stok" required>
                <button type="submit" name="tambah">+ Tambah Bahan</button>
            </form>
        </div>
    <?php } ?>

    <form method="GET" style="margin-bottom:20px;">
    <input type="text" name="cari" placeholder="Cari nama bahan..."
           value="<?php echo $cari; ?>"
           style="width:300px;">
    <button type="submit">🔍 Cari</button>
    
</form>

        <div class="table-card">
            <table>
                <tr>
                    <th>No</th>
                    <th>Nama Bahan</th>
                    <th>Satuan</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

                <?php
                $no = 1;
                while ($row = mysqli_fetch_assoc($data)) {
                    $classStatus = ($row['status'] == "Aman") ? "aman" : "menipis";
                ?>

                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $row['nama_bahan']; ?></td>
                    <td><?php echo $row['satuan']; ?></td>
                    <td><?php echo $row['stok']; ?></td>
                    <td>
                        <span class="badge <?php echo $classStatus; ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit_bahan.php?id=<?php echo $row['id_bahan']; ?>" class="edit">Edit</a>

                        <?php if ($_SESSION['role'] == 'pemilik') { ?>

                            <a href="#"
                            onclick="openConfirmModal(
                                    'stok_bahan.php?hapus=<?php echo $row['id_bahan']; ?>',
                                    'Hapus Bahan',
                                    'Yakin ingin menghapus bahan ini?',
                                    'Hapus'
                            )"
                            class="hapus">
                            Hapus
                            </a>

                        <?php } ?>
                    </td>
                </tr>

                <?php } ?>
            </table>

            <p class="total">Total Bahan : <?php echo $total_bahan; ?> Item</p>
        </div>

        <?php if ($_SESSION['role'] == 'pemilik') { ?>

        
<div class="table-card">

    <h3>📋 Riwayat Perubahan Stok</h3>

    <table>
        <tr>
            <th>Tanggal</th>
            <th>Pengguna</th>
            <th>Nama Bahan</th>
            <th>Stok Lama</th>
            <th>Stok Baru</th>
        </tr>

        <?php while($log = mysqli_fetch_assoc($log_stok)) { ?>

        <tr>
            <td>
                <?php echo date('d-m-Y H:i', strtotime($log['tanggal'])); ?>
            </td>

            <td>
                <?php echo $log['nama']; ?>
            </td>

            <td>
                <?php echo $log['nama_bahan']; ?>
            </td>

            <td>
                <?php echo $log['stok_lama']; ?>
            </td>

            <td>
                <?php echo $log['stok_baru']; ?>
            </td>
        </tr>

        <?php } ?>

    </table>

</div>

<?php } ?>

        <div class="footer">
            © 2026 <span>Drink Point</span>. Semua hak dilindungi.
        </div>

    </div>
</div>

<div id="logoutModal" class="modal">
    <div class="modal-box">
        <h3>Konfirmasi Logout</h3>
        <p>Yakin ingin keluar dari sistem?</p>

        <div class="modal-actions">
            <button onclick="closeLogoutModal()" class="btn-cancel">Batal</button>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
</div>

<script>
function openLogoutModal() {
    document.getElementById("logoutModal").style.display = "flex";
}

function closeLogoutModal() {
    document.getElementById("logoutModal").style.display = "none";
}
</script>

<div id="confirmModal" class="modal">
    <div class="modal-box">
        <h3 id="modalTitle">Konfirmasi</h3>
        <p id="modalMessage">Yakin ingin melanjutkan?</p>

        <div class="modal-actions">
            <button type="button" onclick="closeConfirmModal()" class="btn-cancel">Batal</button>
            <a href="#" id="modalConfirmBtn" class="btn-confirm">Lanjut</a>
        </div>
    </div>
</div>

<script>
function openConfirmModal(url, title, message, buttonText) {
    document.getElementById("modalTitle").innerText = title;
    document.getElementById("modalMessage").innerText = message;
    document.getElementById("modalConfirmBtn").innerText = buttonText;
    document.getElementById("modalConfirmBtn").href = url;
    document.getElementById("confirmModal").style.display = "flex";
}

function closeConfirmModal() {
    document.getElementById("confirmModal").style.display = "none";
}
</script>

</body>
</html>