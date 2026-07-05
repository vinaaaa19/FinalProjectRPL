<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'pemilik') {
    header("Location: dashboard_karyawan.php");
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

if (isset($_GET['validasi'])) {
    $id = $_GET['validasi'];

    $cek = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT metode_pembayaran, bukti_pembayaran
        FROM transaksi
        WHERE id_transaksi='$id'
    "));

    if (
        $cek['metode_pembayaran'] != "Tunai" &&
        empty($cek['bukti_pembayaran'])
    ) {
        header("Location: validasi.php?error=bukti");
        exit;
    }

    mysqli_query($conn, "
        UPDATE transaksi 
        SET status_validasi='Tervalidasi'
        WHERE id_transaksi='$id'
    ");

    header("Location: validasi.php");
    exit;
}

$tanggal_filter = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where = "WHERE 1=1";

if ($tanggal_filter != '') {
    $where .= " AND DATE(transaksi.tanggal) = '$tanggal_filter'";
}

if ($status_filter == 'tervalidasi') {
    $where .= " AND transaksi.status_validasi = 'Tervalidasi'";
}

if ($status_filter == 'belum_validasi') {
    $where .= " AND transaksi.status_validasi != 'Tervalidasi'";
}

if ($status_filter == 'menunggu_bukti') {
    $where .= " 
    AND transaksi.metode_pembayaran != 'Tunai'
    AND (transaksi.bukti_pembayaran IS NULL OR transaksi.bukti_pembayaran = '')
    ";
}

$data = mysqli_query($conn, "
    SELECT transaksi.*, users.nama
    FROM transaksi
    JOIN users ON transaksi.id_user = users.id
    $where
    ORDER BY transaksi.tanggal DESC
");

$total_laporan = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM transaksi"));

$tervalidasi = mysqli_num_rows(mysqli_query($conn, "
    SELECT * FROM transaksi
    WHERE status_validasi='Tervalidasi'
"));

$belum = mysqli_num_rows(mysqli_query($conn, "
    SELECT * FROM transaksi
    WHERE status_validasi IS NULL
    OR status_validasi=''
"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Validasi Laporan - Drink Point</title>

    <style>

    .error-box{
    background:#fee2e2;
    color:#991b1b;
    padding:16px 20px;
    border-radius:14px;
    margin-bottom:22px;
    font-weight:bold;
    border-left:6px solid #dc2626;
    box-shadow:0 6px 18px rgba(0,0,0,0.06);
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
            font-size: 36px;
            margin: 0 0 8px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }

        .success-box {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 35px;
        }

        .summary-card {
            background: white;
            border-radius: 18px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.07);
        }

        .summary-card p {
            margin: 0;
            color: #333;
            font-weight: bold;
        }

        .summary-card h3 {
            color: #d6001c;
            font-size: 34px;
            margin: 12px 0 0;
        }

        .table-card {
            background: white;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.07);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #ffe5e5;
            padding: 16px;
            text-align: left;
            font-size: 14px;
        }

        td {
            padding: 17px 16px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .valid {
            background: #d1fae5;
            color: #087f3f;
            padding: 7px 14px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }

        .pending {
            background: #ffedd5;
            color: #c2410c;
            padding: 7px 14px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }

        .btn {
            background: #d6001c;
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: bold;
            display: inline-block;
        }

        .done {
            color: #087f3f;
            font-weight: bold;
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

        .footer {
            text-align: center;
            margin-top: 45px;
            color: #777;
            font-size: 13px;
        }

        .footer span {
            color: #d6001c;
            font-weight: bold;
        }

        .filter-box{
    display:flex;
    gap:12px;
    margin-bottom:25px;
    align-items:center;
}

.filter-box input,
.filter-box select{
    padding:12px 15px;
    border:1px solid #ddd;
    border-radius:12px;
    font-size:14px;
}

.filter-box button{
    background:#d6001c;
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:12px;
    font-weight:bold;
    cursor:pointer;
}

.btn-delete-mode{
    background:#dc2626;
    color:white;
    border:none;
    padding:12px 22px;
    border-radius:10px;
    font-weight:600;
    cursor:pointer;
    transition:.2s;
}

.btn-delete-mode:hover{
    background:#b91c1c;
}

.btn-cancel-mode{
    background:#6b7280;
    color:white;
    border:none;
    padding:12px 22px;
    border-radius:10px;
    font-weight:600;
    cursor:pointer;
    transition:.2s;
}

.btn-cancel-mode:hover{
    background:#4b5563;
}

.hapus-action{
    display:none;
    gap:10px;
    align-items:center;
    margin-bottom:15px;
}

.btn-delete-mode{
    background:#dc2626;
    color:white;
    border:none;
    padding:12px 22px;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
}

.btn-cancel-mode{
    background:#6b7280;
    color:white;
    border:none;
    padding:12px 22px;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
}

    </style>
</head>

<body>

<div class="container">

    <div class="sidebar">
        <div class="logo">🥤 Drink Point</div>

        <div class="menu">
            <a href="dashboard.php">🏠 Dashboard</a>

            <div class="menu-title">MENU UTAMA</div>

            <a href="data_minuman.php">🧋 Data Minuman</a>
            <a href="stok_bahan.php">📦 Stok Bahan</a>
            <a href="transaksi.php">🛒 Transaksi Penjualan</a>
            <a href="laporan.php">📊 Laporan Penjualan</a>
            <a href="validasi.php" class="active">✅ Validasi Laporan</a>
            <a href="akun_karyawan.php">👥 Akun Karyawan</a>
            <a href="profil_pemilik.php">👤 Profil Saya</a>
        </div>

        <a href="#" onclick="openConfirmModal('logout.php','Konfirmasi Logout','Yakin ingin keluar dari sistem?','Logout')" class="logout-box">
            <div>🚪 Logout</div>
            <small>Keluar dari sistem</small>
        </a>
    </div>

    <div class="content">
            <?php if(isset($_GET['error']) && $_GET['error'] == 'bukti'){ ?>
        <div class="error-box">
            ⚠ Transaksi QRIS / Transfer belum memiliki bukti pembayaran.
            <br>
            Silakan upload bukti pembayaran terlebih dahulu sebelum validasi.
        </div>
    <?php } ?>

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

                <a href="profil_pemilik.php" class="avatar-link">
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
                    <span>Pemilik</span>
                </div>

            </div>
        </div>

        <h1>Validasi Laporan</h1>
        <p class="subtitle">Pemilik dapat mengecek dan memvalidasi laporan transaksi penjualan.</p>

        <?php if(isset($_GET['success'])){ ?>
            <div class="success-box">
                ✅ Laporan berhasil divalidasi
            </div>
        <?php } ?>

        <div class="summary">
            <div class="summary-card">
                <p>Total Laporan</p>
                <h3><?php echo $total_laporan; ?></h3>
            </div>

            <div class="summary-card">
                <p>Sudah Tervalidasi</p>
                <h3><?php echo $tervalidasi; ?></h3>
            </div>

            <div class="summary-card">
                <p>Belum Tervalidasi</p>
                <h3><?php echo $belum; ?></h3>
            </div>
        </div>

        <form method="GET" class="filter-box">

    <input type="date" name="tanggal" value="<?php echo $tanggal_filter; ?>">

    <select name="status">
        <option value="">Semua Status</option>
        <option value="tervalidasi" <?php if($status_filter=="tervalidasi") echo "selected"; ?>>
            Sudah Tervalidasi
        </option>
        <option value="belum_validasi" <?php if($status_filter=="belum_validasi") echo "selected"; ?>>
            Belum Validasi
        </option>
        <option value="menunggu_bukti" <?php if($status_filter=="menunggu_bukti") echo "selected"; ?>>
            Menunggu Bukti Upload
        </option>
    </select>

    <button type="submit">Filter</button>

</form>

<?php if ($_SESSION['role'] == 'pemilik') { ?>

<button
    type="button"
    id="btnModeHapus"
    onclick="aktifkanModeHapus()"
    class="btn-delete-mode"
    style="margin-bottom:20px;">

    🗑 Mode Hapus

</button>

<div id="hapusAction" class="hapus-action">

    <button type="button" onclick="openDeleteModal()" class="btn-delete-mode">
        🗑 Hapus Terpilih
    </button>

    <button type="button" onclick="batalModeHapus()" class="btn-cancel-mode">
        Batal
    </button>

</div>

<?php } ?>

<form method="POST" action="hapus_laporan_terpilih.php" id="formHapus">

<div class="table-card">
    <table>
        <tr>
            <th class="hapus-col" style="display:none;">
                <input type="checkbox" id="checkAll">
            </th>
            <th>No</th>
            <th>Tanggal</th>
            <th>Kasir</th>
            <th>Metode</th>
            <th>Total</th>
            <th>Status Bukti</th>
            <th>Status Validasi</th>
            <th>Aksi</th>
        </tr>

        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($data)) {
        ?>

        <tr>
            <td class="hapus-col" style="display:none;">
                <?php if($row['status_validasi']=="Tervalidasi"){ ?>
                    <input type="checkbox" name="hapus_id[]" value="<?php echo $row['id_transaksi']; ?>">
                <?php } else { ?>
                    -
                <?php } ?>
            </td>

            <td><?php echo $no++; ?></td>
            <td><?php echo date('d-m-Y H:i', strtotime($row['tanggal'])); ?></td>
            <td><?php echo $row['nama']; ?></td>
            <td><?php echo $row['metode_pembayaran']; ?></td>
            <td>Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></td>

            <td>
                <?php if ($row['metode_pembayaran'] == "Tunai") { ?>
                    <span style="color:#6b7280;font-weight:bold;">⚪ Tidak Perlu</span>
                <?php } elseif (empty($row['bukti_pembayaran'])) { ?>
                    <span style="color:#f59e0b;font-weight:bold;">🟠 Belum Upload</span>
                <?php } else { ?>
                    <span style="color:#16a34a;font-weight:bold;">🟢 Sudah Upload</span>
                <?php } ?>
            </td>

            <td>
                <?php if($row['status_validasi']=="Tervalidasi"){ ?>
                    <span style="color:green;font-weight:bold;">✔ Tervalidasi</span>
                <?php } else { ?>
                    <span style="color:orange;font-weight:bold;">Menunggu</span>
                <?php } ?>
            </td>

            <td>
                <?php if($row['status_validasi']=="Tervalidasi"){ ?>

                    <span style="color:#777;font-weight:bold;">Selesai</span>

                <?php } elseif ($row['metode_pembayaran'] != "Tunai" && empty($row['bukti_pembayaran'])) { ?>

                    <span style="background:#f59e0b;color:white;padding:8px 12px;border-radius:8px;font-weight:bold;">
                        📷 Menunggu Bukti
                    </span>

                <?php } else { ?>

                    <a href="validasi.php?validasi=<?php echo $row['id_transaksi']; ?>"
                       style="background:#16a34a;color:white;padding:8px 12px;border-radius:8px;text-decoration:none;font-weight:bold;">
                       ✔ Validasi
                    </a>

                <?php } ?>
            </td>
        </tr>

        <?php } ?>
    </table>
</div>

</form>
        <div class="footer">
            © 2026 <span>Drink Point</span>. Semua hak dilindungi.
        </div>

    </div>

</div>

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

<div id="deleteModal" class="modal">
    <div class="modal-box">
        <h3>🗑 Konfirmasi Penghapusan</h3>

        <p>Laporan yang dipilih akan dihapus permanen.</p>

        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">
                Batal
            </button>

            <button type="button" class="btn-confirm" onclick="submitDelete()">
                Ya, Hapus
            </button>
        </div>
    </div>
</div>

<script>
function aktifkanModeHapus(){
    document.querySelectorAll(".hapus-col").forEach(function(el){
        el.style.display = "table-cell";
    });

    document.getElementById("hapusAction").style.display = "flex";
    document.getElementById("btnModeHapus").style.display = "none";
}

function batalModeHapus(){
    document.querySelectorAll(".hapus-col").forEach(function(el){
        el.style.display = "none";
    });

    document.querySelectorAll("input[name='hapus_id[]']").forEach(function(cb){
        cb.checked = false;
    });

    document.getElementById("checkAll").checked = false;
    document.getElementById("hapusAction").style.display = "none";
    document.getElementById("btnModeHapus").style.display = "inline-block";
}

document.getElementById("checkAll").addEventListener("change", function(){
    document.querySelectorAll("input[name='hapus_id[]']").forEach(function(cb){
        cb.checked = document.getElementById("checkAll").checked;
    });
});

function openDeleteModal(){
    const dipilih = document.querySelectorAll("input[name='hapus_id[]']:checked");

    if(dipilih.length === 0){
        alert("Pilih minimal satu laporan yang ingin dihapus.");
        return;
    }

    document.getElementById("deleteModal").style.display = "flex";
}

function closeDeleteModal(){
    document.getElementById("deleteModal").style.display = "none";
}

function submitDelete(){
    document.getElementById("formHapus").submit();
}
</script>

</body>
</html>