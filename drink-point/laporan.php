<?php
session_start();
include "koneksi.php";

date_default_timezone_set('Asia/Jakarta');

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

$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

$filter_user = "";

if ($_SESSION['role'] == 'karyawan') {
    $filter_user = "AND transaksi.id_user = '".$_SESSION['id']."'";
}

$filter_user_transaksi = "";

if ($_SESSION['role'] == 'karyawan') {
    $filter_user_transaksi = "AND id_user = '".$_SESSION['id']."'";
}

$total_transaksi = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM transaksi 
    WHERE DATE(tanggal) = '$tanggal'
    $filter_user_transaksi
"))['total'];

$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT IFNULL(SUM(total),0) AS total 
    FROM transaksi 
    WHERE DATE(tanggal) = '$tanggal'
    $filter_user_transaksi
"))['total'];

$total_minuman = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT IFNULL(SUM(detail_transaksi.qty),0) AS total
    FROM detail_transaksi
    JOIN transaksi ON detail_transaksi.id_transaksi = transaksi.id_transaksi
    WHERE DATE(transaksi.tanggal) = '$tanggal'
    $filter_user
"))['total'];

$data = mysqli_query($conn, "
    SELECT 
        transaksi.tanggal,
        users.nama AS nama_kasir,
        minuman.nama_minuman,
        minuman.harga,
        detail_transaksi.qty,
        detail_transaksi.subtotal
    FROM detail_transaksi
    JOIN transaksi ON detail_transaksi.id_transaksi = transaksi.id_transaksi
    JOIN minuman ON detail_transaksi.id_minuman = minuman.id_minuman
    JOIN users ON transaksi.id_user = users.id
    WHERE DATE(transaksi.tanggal) = '$tanggal'
    $filter_user
    ORDER BY transaksi.tanggal DESC
");

$riwayat = mysqli_query($conn, "
    SELECT transaksi.*, users.nama
    FROM transaksi
    JOIN users ON transaksi.id_user = users.id
    WHERE 1=1
    $filter_user
    ORDER BY transaksi.tanggal DESC
    LIMIT 20
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan - Drink Point</title>
    <style>

.modal-bukti{

display:none;

position:fixed;

left:0;
top:0;

width:100%;
height:100%;

background:rgba(0,0,0,.55);

justify-content:center;
align-items:center;

z-index:9999;

}

.modal-content-bukti{

background:white;

width:700px;

padding:30px;

border-radius:18px;

text-align:center;

position:relative;

}

.modal-content-bukti img{

max-width:100%;

max-height:450px;

border-radius:12px;

margin:20px 0;

}

.close-modal{

position:absolute;

right:20px;

top:15px;

font-size:32px;

cursor:pointer;

color:#d6001c;

font-weight:bold;

}

.info-bukti{

width:100%;

margin-top:20px;

border-collapse:collapse;

}

.info-bukti td{

padding:10px;

text-align:left;

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

        body { margin:0; font-family:Arial,sans-serif; background:#fff7f7; }
        .container { display:flex; min-height:100vh; }
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
        .logo { font-size:30px; font-weight:bold; margin-bottom:40px; }
        .menu-title { font-size:13px; font-weight:bold; margin:25px 0 12px; }
        .menu a { display:block; color:white; text-decoration:none; padding:15px 18px; border-radius:12px; margin-bottom:10px; font-size:17px; }
        .menu a.active,.menu a:hover { background:rgba(255,255,255,0.25); }

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
        
        .logout-box div { font-size:22px; font-weight:bold; margin-bottom:6px; }

        .top { display:flex; justify-content:flex-end; align-items:center; margin-bottom:45px; }
        .profile { display:flex; align-items:center; gap:15px; }
        .profile-info { text-align:left; line-height:1.3; }
        .profile-info b { font-size:16px; }
        .profile-info span { color:#333; font-size:14px; }
        .avatar-link { text-decoration:none; }
        .avatar-mini { width:45px; height:45px; background:#d6001c; color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:18px; overflow:hidden; }

        .notif { position:relative; font-size:28px; cursor:pointer; display:inline-block; margin-right:10px; }
        .notif-badge { position:absolute; top:-8px; right:-10px; background:#d6001c; color:white; border-radius:50%; font-size:11px; width:20px; height:20px; display:flex; align-items:center; justify-content:center; font-weight:bold; }
        .notif-dropdown { display:none; position:absolute; right:0; top:38px; width:270px; background:white; color:#333; border-radius:12px; box-shadow:0 8px 25px rgba(0,0,0,0.12); padding:15px; z-index:99; font-size:14px; text-align:left; }
        .notif-dropdown p { margin:10px 0; padding:10px; background:#fff5f5; border-radius:8px; }
        .notif:hover .notif-dropdown { display:block; }

        .page-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
        h1 { font-size:36px; margin:0; }
        .subtitle { color:#666; margin-bottom:35px; }
        .date-filter { display:flex; align-items:center; gap:15px; }
        input { padding:13px; border:1px solid #ddd; border-radius:10px; font-size:14px; }
        button { padding:14px 24px; background:#d6001c; color:white; border:none; border-radius:12px; font-weight:bold; cursor:pointer; }

        .summary { display:grid; grid-template-columns:repeat(4,1fr); gap:25px; margin-bottom:35px; }
        .summary-card { background:white; border-radius:18px; padding:25px; box-shadow:0 8px 25px rgba(0,0,0,0.07); min-height:135px; }
        .circle { width:55px; height:55px; border-radius:50%; background:#ffe5e5; color:#d6001c; display:flex; align-items:center; justify-content:center; font-size:24px; margin-bottom:15px; }
        .summary-card p { color:#333; font-weight:bold; margin:0; }
        .summary-card h3 { color:#d6001c; font-size:30px; margin:12px 0 0; }

        .table-card { background:white; border-radius:18px; box-shadow:0 8px 25px rgba(0,0,0,0.07); overflow:hidden; }
        .table-title,.riwayat-title { font-size:24px; margin-bottom:18px; font-weight:bold; }
        .riwayat-title { margin-top:40px; }
        table { width:100%; border-collapse:collapse; }
        th { background:#ffe5e5; padding:16px; text-align:left; font-size:14px; }
        td { padding:17px 16px; border-bottom:1px solid #eee; font-size:14px; }
        .total-row { display:flex; justify-content:space-between; align-items:center; background:#fff1f1; padding:25px 30px; font-size:24px; font-weight:bold; }
        .total-row .amount { color:#d6001c; font-size:34px; }
        .footer { text-align:center; margin-top:45px; color:#777; font-size:13px; }
        .footer span { color:#d6001c; font-weight:bold; }
        .print-header { display:none; }

        @media print {
            .sidebar,.top,.date-filter button,.date-filter input,.footer { display:none; }
            .print-header { display:block; text-align:center; margin-bottom:30px; }
            .print-header h2 { margin:0; }
            .print-header p { margin:5px 0; }
            .content { padding:20px; }
            .container { display:block; }
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
                <a href="stok_bahan.php">📦 Stok Bahan</a>
                <a href="transaksi.php">🛒 Transaksi Penjualan</a>
                <a href="laporan.php" class="active">📊 Laporan Penjualan</a>
                <a href="validasi.php">✅ Validasi Laporan</a>
                <a href="akun_karyawan.php">👥 Akun Karyawan</a>
                <a href="profil_pemilik.php">👤 Profil Saya</a>


           <?php } else { ?>

                <a href="dashboard_karyawan.php">🏠 Dashboard</a>

                <div class="menu-title">MENU KARYAWAN</div>

                <a href="stok_minuman_karyawan.php">🧋 Data Minuman</a>
    <a href="stok_bahan_karyawan.php">📦 Stok Bahan</a>
    <a href="transaksi.php">🛒 Transaksi Penjualan</a>
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
        <a href="stok_minuman_karyawan.php"
           style="color:#333;text-decoration:none;display:block;">
            🥤 Minuman <b><?php echo $m['nama_minuman']; ?></b> habis
            <br>
            <small style="color:#777;">Klik untuk melihat stok minuman</small>
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
                            <?php echo strtoupper(substr($_SESSION['nama'],0,1)); ?>
                        <?php } ?>
                    </div>
                </a>

                <div class="profile-info">
                    <b><?php echo $_SESSION['nama']; ?></b><br>
                    <span><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </div>
        </div>

        <div class="print-header">
            <h2>DRINK POINT</h2>
            <p>Laporan Penjualan Minuman</p>
            <hr>
        </div>

        <div class="page-head">
            <div>
                <h1>Laporan Penjualan</h1>
                <p class="subtitle">Lihat dan cetak laporan penjualan minuman</p>
            </div>

            <form method="GET" class="date-filter">
                <input type="date" name="tanggal" value="<?php echo $tanggal; ?>">
                <button type="submit">Filter</button>
                <button type="button" onclick="window.print()">🖨 Cetak PDF</button>
                <a href="export_excel.php" style="background:#198754;color:white;padding:14px 24px;border-radius:12px;text-decoration:none;font-weight:bold;">
                    📊 Export Excel
                </a>
            </form>
        </div>

        <div class="summary">
            <div class="summary-card">
                <div class="circle">☑️</div>
                <p>Total Transaksi</p>
                <h3><?php echo $total_transaksi; ?></h3>
            </div>

            <div class="summary-card">
                <div class="circle">💰</div>
                <p>Pendapatan Total</p>
                <h3>Rp <?php echo number_format($total_pendapatan,0,',','.'); ?></h3>
            </div>

            <div class="summary-card">
                <div class="circle">🥤</div>
                <p>Minuman Terjual</p>
                <h3><?php echo $total_minuman; ?> gelas</h3>
            </div>

            <div class="summary-card">
                <div class="circle">👤</div>
                <p>Kasir</p>
                <h3><?php echo $_SESSION['nama']; ?></h3>
            </div>
        </div>

        <div class="table-title">Summary</div>

        <div class="table-card">
            <table>
                <tr>
                    <th>No</th>
                    <th>Waktu</th>
                    <th>Nama Minuman</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Total</th>
                    <th>Kasir</th>
                </tr>

                <?php $no = 1; while ($row = mysqli_fetch_assoc($data)) { ?>
                <tr>
                    <td><?php echo str_pad($no++,2,'0',STR_PAD_LEFT); ?></td>
                    <td><?php echo date('H:i', strtotime($row['tanggal'])); ?></td>
                    <td><?php echo $row['nama_minuman']; ?></td>
                    <td><?php echo $row['qty']; ?> gelas</td>
                    <td>Rp <?php echo number_format($row['harga'],0,',','.'); ?></td>
                    <td>Rp <?php echo number_format($row['subtotal'],0,',','.'); ?></td>
                    <td><?php echo $row['nama_kasir']; ?></td>
                </tr>
                <?php } ?>
            </table>

            <div class="total-row">
                <div>Total Laporan</div>
                <div class="amount">Rp <?php echo number_format($total_pendapatan,0,',','.'); ?></div>
            </div>
        </div>

        <div class="riwayat-title">Riwayat Transaksi</div>

        <div class="table-card">
            <table>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Kasir</th>
                    <th>Total</th>
                   <th>Metode</th>
                    <th>Status Bukti</th>
                    <th>Aksi</th>
                    <th>Status Validasi</th>
                </tr>

                <?php $no = 1; while($r = mysqli_fetch_assoc($riwayat)) { ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo date('d-m-Y H:i', strtotime($r['tanggal'])); ?></td>
                    <td><?php echo $r['nama']; ?></td>
                    <td><?php echo $r['metode_pembayaran']; ?></td>
                    <td>Rp <?php echo number_format($r['total'],0,',','.'); ?></td>
                    <td>
                        <?php if ($r['metode_pembayaran'] == "Tunai") { ?>
                            <span style="color:#6b7280;font-weight:bold;">⚪ Tidak Perlu</span>
                        <?php } elseif (empty($r['bukti_pembayaran'])) { ?>
                            <span style="color:#f59e0b;font-weight:bold;">🟠 Belum Upload</span>
                        <?php } else { ?>
                            <span style="color:#16a34a;font-weight:bold;">🟢 Sudah Upload</span>
                        <?php } ?>
                    </td>

                    <td>
                        <?php if ($r['metode_pembayaran'] == "Tunai") { ?>

                            <span style="color:#777;">-</span>

                        <?php } elseif (empty($r['bukti_pembayaran'])) { ?>

                            <a href="upload_bukti.php?id=<?php echo $r['id_transaksi']; ?>"
                            style="background:#f59e0b;color:white;padding:8px 12px;border-radius:8px;text-decoration:none;font-weight:bold;">
                            📷 Upload
                            </a>

                        <?php } else { ?>

                            <button
                                type="button"
                                class="lihatBukti"
                                data-img="uploads/bukti_pembayaran/<?php echo $r['bukti_pembayaran']; ?>"
                                data-kasir="<?php echo $r['nama']; ?>"
                                data-metode="<?php echo $r['metode_pembayaran']; ?>"
                                data-tanggal="<?php echo date('d-m-Y H:i', strtotime($r['tanggal'])); ?>"
                                style="background:#16a34a;color:white;padding:8px 12px;border:none;border-radius:8px;font-weight:bold;cursor:pointer;">
                                👁 Lihat
                            </button>

                        <?php } ?>
                    </td>
                    <td>
                        <?php if($r['status_validasi']=="Tervalidasi"){ ?>
                            <span style="color:green;font-weight:bold;">✔ Tervalidasi</span>
                        <?php } else { ?>
                            <span style="color:orange;font-weight:bold;">Menunggu</span>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>

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

<div id="buktiModal" class="modal-bukti">
    <div class="modal-content-bukti">
        <span class="close-modal">&times;</span>

        <h2>Bukti Pembayaran</h2>

        <img id="gambarBukti" src="">

        <table class="info-bukti">
            <tr>
                <td><b>Kasir</b></td>
                <td id="namaKasir"></td>
            </tr>
            <tr>
                <td><b>Metode</b></td>
                <td id="metodeBayar"></td>
            </tr>
            <tr>
                <td><b>Tanggal</b></td>
                <td id="tanggalBayar"></td>
            </tr>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){

    const modal = document.getElementById("buktiModal");
    const closeBtn = document.querySelector(".close-modal");

    document.querySelectorAll(".lihatBukti").forEach(function(btn){

        btn.addEventListener("click", function(){

            modal.style.display = "flex";

            document.getElementById("gambarBukti").src = this.dataset.img;
            document.getElementById("namaKasir").innerText = this.dataset.kasir;
            document.getElementById("metodeBayar").innerText = this.dataset.metode;
            document.getElementById("tanggalBayar").innerText = this.dataset.tanggal;

        });

    });

    closeBtn.addEventListener("click", function(){
        modal.style.display = "none";
    });

    modal.addEventListener("click", function(e){
        if(e.target === modal){
            modal.style.display = "none";
        }
    });

});
</script>

<?php if(isset($_GET['upload']) && $_GET['upload']=="success"){ ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
Swal.fire({
    icon: 'success',
    title: 'Upload Berhasil',
    text: 'Bukti pembayaran berhasil diunggah.',
    confirmButtonColor: '#dc2626',
    backdrop: false
});
</script>
<?php } ?>

</body>
</html>