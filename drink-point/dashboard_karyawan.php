<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'karyawan') {
    header("Location: dashboard.php");
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

$user = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM users WHERE id='".$_SESSION['id']."'
"));

$id_user = $_SESSION['id'];

$transaksi_hari_ini = mysqli_num_rows(mysqli_query($conn, "
    SELECT * FROM transaksi 
    WHERE id_user='$id_user'
    AND DATE(tanggal)=CURDATE()
"));

$penjualan_hari_ini = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT IFNULL(SUM(total),0) AS total
    FROM transaksi 
    WHERE id_user='$id_user'
    AND DATE(tanggal)=CURDATE()
"));

$bahan_menipis = mysqli_num_rows(mysqli_query($conn, "
    SELECT * FROM bahan WHERE status='Menipis'
"));

$total_minuman_aktif = mysqli_num_rows(mysqli_query($conn, "
    SELECT * FROM minuman WHERE status='Aktif'
"));

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Karyawan - Drink Point</title>
    <style>

.stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin-bottom:35px;
}

.stat-card{
    background:white;
    border-radius:18px;
    padding:22px;
    box-shadow:0 8px 25px rgba(0,0,0,0.07);
}

.stat-card h2{
    margin:0;
    color:#d6001c;
    font-size:32px;
}

.stat-card p{
    margin-top:8px;
    color:#666;
    font-size:14px;
    font-weight:bold;
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
           font-size:30px;
    font-weight:bold;
    margin-bottom:40px;
        }

        .menu-title {
            font-size: 13px;
            font-weight: bold;
            margin: 25px 0 12px;
        }

        .menu a {
           display:block;
    color:white;
    text-decoration:none;
    padding:15px 18px;
    border-radius:12px;
    margin-bottom:10px;
    font-size:17px;
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
            overflow: hidden;
        }

        .avatar-mini img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info {
            text-align: left;
            line-height: 1.3;
        }

        .profile-info span {
            color: #333;
            font-size: 14px;
        }

        h1 {
            font-size: 42px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 45px;
            font-size: 17px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 28px;
        }

        .card {
            background: white;
            text-align: center;
            padding: 45px 25px;
            border-radius: 18px;
            text-decoration: none;
            color: #222;
            box-shadow: 0 8px 25px rgba(0,0,0,0.07);
            transition: 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .icon {
            width: 75px;
            height: 75px;
            margin: auto;
            background: #ffe5e5;
            color: #e6001f;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
        }

        .card h3 {
            margin-bottom: 8px;
            font-size: 23px;
        }

        .card p {
            color: #777;
            font-size: 15px;
        }

        .notif {
            position: relative;
            font-size: 28px;
            cursor: pointer;
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

        .footer {
            text-align: center;
            margin-top: 70px;
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
    <a href="dashboard_karyawan.php" class="active">🏠 Dashboard</a>

    <div class="menu-title">MENU KARYAWAN</div>

    <a href="stok_minuman_karyawan.php">🧋 Data Minuman</a>
    <a href="stok_bahan_karyawan.php">📦 Stok Bahan</a>
    <a href="transaksi.php">🛒 Transaksi Penjualan</a>
    <a href="laporan.php">📊 Laporan Penjualan</a>
    <a href="profil_karyawan.php">👤 Profil Saya</a>
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
                            <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                        <?php } ?>
                    </div>
                </a>

                <div class="profile-info">
                    <b><?php echo $_SESSION['nama']; ?></b><br>
                    <span>Karyawan</span>
                </div>
            </div>
        </div>

        <h1>Dashboard Karyawan</h1>
        <p class="subtitle">Pilih menu untuk menjalankan operasional penjualan Drink Point</p>

        <div class="stats">

    <div class="stat-card">
        <h2><?php echo $transaksi_hari_ini; ?></h2>
        <p>🛒 Transaksi Hari Ini</p>
    </div>

    <div class="stat-card">
        <h2>Rp <?php echo number_format($penjualan_hari_ini['total'],0,',','.'); ?></h2>
        <p>💰 Penjualan Hari Ini</p>
    </div>

    <div class="stat-card">
        <h2><?php echo $bahan_menipis; ?></h2>
        <p>⚠️ Bahan Menipis</p>
    </div>

    <div class="stat-card">
        <h2><?php echo $total_minuman_aktif; ?></h2>
        <p>🧋 Minuman Aktif</p>
    </div>

</div>

        <div class="grid">
            <a href="transaksi.php" class="card">
                <div class="icon">🛒</div>
                <h3>Transaksi Penjualan</h3>
                <p>Input transaksi pelanggan</p>
            </a>

            <a href="stok_bahan.php" class="card">
                <div class="icon">📦</div>
                <h3>Lihat Stok</h3>
                <p>Melihat stok bahan yang tersedia</p>
            </a>

            <a href="data_minuman.php" class="card">
                <div class="icon">🧋</div>
                <h3>Stok Minuman</h3>
                <p>Melihat stok minuman yang tersedia</p>
            </a>

            <a href="laporan.php" class="card">
                <div class="icon">📊</div>
                <h3>Laporan Penjualan</h3>
                <p>Melihat laporan hasil penjualan</p>
            </a>

            <a href="profil_karyawan.php" class="card">
                <div class="icon">👤</div>
                <h3>Profil Saya</h3>
                <p>Melihat profil akun karyawan</p>
            </a>
        </div>

        <div class="footer">
            © <?php echo date('Y'); ?> <span>Drink Point</span>. Semua hak dilindungi.
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

</body>
</html>