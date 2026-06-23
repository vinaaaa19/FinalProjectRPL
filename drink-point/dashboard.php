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

$user = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT * FROM users
        WHERE id='".$_SESSION['id']."'
    ")
);

$total_minuman = mysqli_num_rows(
    mysqli_query($conn, "SELECT * FROM minuman")
);

$total_bahan = mysqli_num_rows(
    mysqli_query($conn, "SELECT * FROM bahan")
);

$total_karyawan = mysqli_num_rows(
    mysqli_query($conn, "SELECT * FROM users WHERE role='karyawan'")
);

$total_transaksi_hari_ini = mysqli_num_rows(
    mysqli_query($conn, "
        SELECT * FROM transaksi
        WHERE DATE(tanggal)=CURDATE()
    ")
);

$total_minuman = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM minuman"));
$total_bahan = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM bahan"));
$total_karyawan = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role='karyawan'"));
$total_transaksi = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM transaksi"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Pemilik - Drink Point</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff7f7;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 270px;
            background: linear-gradient(180deg, #e6001f, #b40018);
            color: white;
            padding: 30px 25px;
            position: relative;
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

        .logout-box {
            position: absolute;
            left: 25px;
            bottom: 25px;
            width: 220px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 15px;
            padding: 18px;
            color: white;
            text-decoration: none;
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

        .content {
            flex: 1;
            padding: 45px;
            background: white;
        }

        .top {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 35px;
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
            font-size: 42px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 35px;
            font-size: 17px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.07);
        }

        .stat-icon {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: #ffe5e5;
            color: #d6001c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 15px;
        }

        .stat-card p {
            margin: 0;
            color: #555;
            font-weight: bold;
        }

        .stat-card h3 {
            margin: 10px 0 0;
            font-size: 34px;
            color: #d6001c;
        }

        .menu-heading {
            font-size: 26px;
            margin-bottom: 20px;
        }

        .stats{
            display:grid;
            grid-template-columns:repeat(4,1fr);
            gap:20px;
            margin-bottom:40px;
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
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }

        .card {
            background: white;
            text-align: center;
            padding: 40px 25px;
            border-radius: 18px;
            text-decoration: none;
            color: #222;
            box-shadow: 0 8px 25px rgba(0,0,0,0.07);
            transition: 0.2s;
            min-height: 200px;
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
            line-height: 1.4;
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
            <a href="dashboard.php" class="active">🏠 Dashboard</a>

            <div class="menu-title">MENU UTAMA</div>

            <a href="data_minuman.php">🧋 Data Minuman</a>
            <a href="stok_bahan.php">📦 Stok Bahan</a>
            <a href="transaksi.php">🛒 Transaksi Penjualan</a>
            <a href="laporan.php">📊 Laporan Penjualan</a>
            <a href="validasi.php">✅ Validasi Laporan</a>
            <a href="akun_karyawan.php">👥 Akun Karyawan</a>
        </div>

        <a href="logout.php" 
             onclick="return confirm('Yakin ingin logout?')" 
             class="logout-box">
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
                                <p>⚠ Stok <?php echo $n['nama_bahan']; ?> menipis</p>
                            <?php } ?>
                        <?php } else { ?>
                            <p>Tidak ada notifikasi</p>
                        <?php } ?>
                    </div>
                </div>

                <a href="profil_pemilik.php" class="avatar-link">
                    <div class="avatar-mini">

                        <?php if(!empty($user['foto'])){ ?>

                        <img src="uploads/<?php echo $user['foto']; ?>"
                            style="
                            width:100%;
                            height:100%;
                            object-fit:cover;
                            border-radius:50%;
                        ">

                        <?php } else { ?>

                        <?php echo strtoupper(substr($_SESSION['nama'],0,1)); ?>

                        <?php } ?>

                        </div>
                </a>

                <div class="profile-info">
                    <b><?php echo $_SESSION['nama']; ?></b><br>
                    <span>Pemilik</span>
                </div>

            </div>
        </div>

        <h1>Dashboard Pemilik</h1>
        <p class="subtitle">Ringkasan sistem dan akses menu utama Drink Point</p>

        <div class="stats">

    <div class="stat-card">
        <h2><?php echo $total_minuman; ?></h2>
        <p>🧋 Total Minuman</p>
    </div>

    <div class="stat-card">
        <h2><?php echo $total_bahan; ?></h2>
        <p>📦 Total Bahan</p>
    </div>

    <div class="stat-card">
        <h2><?php echo $total_karyawan; ?></h2>
        <p>👥 Karyawan</p>
    </div>

    <div class="stat-card">
        <h2><?php echo $total_transaksi_hari_ini; ?></h2>
        <p>🛒 Transaksi Hari Ini</p>
    </div>

</div>

        <h2 class="menu-heading">Menu Utama</h2>

        <div class="grid">
            <a href="data_minuman.php" class="card">
                <div class="icon">🧋</div>
                <h3>Data Minuman</h3>
                <p>Kelola data minuman yang tersedia</p>
            </a>

            <a href="stok_bahan.php" class="card">
                <div class="icon">📦</div>
                <h3>Stok Bahan</h3>
                <p>Kelola persediaan bahan baku minuman</p>
            </a>

            <a href="transaksi.php" class="card">
                <div class="icon">🛒</div>
                <h3>Transaksi Penjualan</h3>
                <p>Catat transaksi penjualan minuman</p>
            </a>

            <a href="laporan.php" class="card">
                <div class="icon">📊</div>
                <h3>Laporan Penjualan</h3>
                <p>Lihat laporan hasil penjualan</p>
            </a>

            <a href="validasi.php" class="card">
                <div class="icon">✅</div>
                <h3>Validasi Laporan</h3>
                <p>Validasi laporan yang sudah dicek</p>
            </a>

            <a href="akun_karyawan.php" class="card">
                <div class="icon">👥</div>
                <h3>Akun Karyawan</h3>
                <p>Kelola akun karyawan usaha</p>
            </a>
        </div>

        <div class="footer">
            © 2026 <span>Drink Point</span>. Semua hak dilindungi.
        </div>
    </div>

</div>

</body>
</html>