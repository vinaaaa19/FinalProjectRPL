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
    mysqli_query($conn, "
        SELECT * FROM users
        WHERE id='".$_SESSION['id']."'
    ")
);

if (isset($_GET['validasi'])) {
    $id = $_GET['validasi'];

    mysqli_query($conn, "
        UPDATE transaksi
        SET status_validasi='Tervalidasi'
        WHERE id_transaksi='$id'
    ");

    header("Location: validasi.php");
    exit;
}

$data = mysqli_query($conn, "
    SELECT transaksi.*, users.nama
    FROM transaksi
    JOIN users ON transaksi.id_user = users.id
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
        }

        .done {
            color: #087f3f;
            font-weight: bold;
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
                        <?php if (!empty($user['foto'])) { ?>
                            <img src="uploads/<?php echo $user['foto']; ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
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

        <div class="table-card">
            <table>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Kasir</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

                <?php
                $no = 1;
                while ($row = mysqli_fetch_assoc($data)) {
                ?>

                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo date('d-m-Y H:i', strtotime($row['tanggal'])); ?></td>
                    <td><?php echo $row['nama']; ?></td>
                    <td>Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                    <td>
                        <?php if ($row['status_validasi'] == "Tervalidasi") { ?>
                            <span class="valid">Tervalidasi</span>
                        <?php } else { ?>
                            <span class="pending">Menunggu</span>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($row['status_validasi'] != "Tervalidasi") { ?>
                            <a href="validasi.php?validasi=<?php echo $row['id_transaksi']; ?>"
                               class="btn"
                               onclick="return confirm('Validasi laporan ini?')">
                                Validasi
                            </a>
                        <?php } else { ?>
                            <span class="done">✔ Selesai</span>
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

</body>
</html>