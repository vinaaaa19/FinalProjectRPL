<?php
session_start();
include "koneksi.php";

date_default_timezone_set('Asia/Jakarta');

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

$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

$total_transaksi = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM transaksi 
    WHERE DATE(tanggal) = '$tanggal'
"))['total'];

$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT IFNULL(SUM(total),0) AS total 
    FROM transaksi 
    WHERE DATE(tanggal) = '$tanggal'
"))['total'];

$total_minuman = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT IFNULL(SUM(detail_transaksi.qty),0) AS total
    FROM detail_transaksi
    JOIN transaksi ON detail_transaksi.id_transaksi = transaksi.id_transaksi
    WHERE DATE(transaksi.tanggal) = '$tanggal'
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
    ORDER BY transaksi.tanggal DESC
");

$riwayat = mysqli_query($conn, "
    SELECT transaksi.*, users.nama
    FROM transaksi
    JOIN users ON transaksi.id_user = users.id
    ORDER BY transaksi.tanggal DESC
    LIMIT 20
");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan - Drink Point</title>
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

        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        h1 {
            font-size: 36px;
            margin: 0;
        }

        .subtitle {
            color: #666;
            margin-bottom: 35px;
        }

        .date-filter {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        input {
            padding: 13px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
        }

        button {
            padding: 14px 24px;
            background: #d6001c;
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: bold;
            cursor: pointer;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 35px;
        }

        .summary-card {
            background: white;
            border-radius: 18px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.07);
            min-height: 135px;
        }

        .circle {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: #ffe5e5;
            color: #d6001c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .summary-card p {
            color: #333;
            font-weight: bold;
            margin: 0;
        }

        .summary-card h3 {
            color: #d6001c;
            font-size: 30px;
            margin: 12px 0 0;
        }

        .table-card {
            background: white;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.07);
            overflow: hidden;
        }

        .table-title {
            font-size: 24px;
            margin-bottom: 18px;
            font-weight: bold;
        }

        .riwayat-title{
    font-size:24px;
    font-weight:bold;
    margin-top:40px;
    margin-bottom:18px;
}

.btn-detail{
    background:#d6001c;
    color:white;
    padding:8px 14px;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
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

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff1f1;
            padding: 25px 30px;
            font-size: 24px;
            font-weight: bold;
        }

        .total-row .amount {
            color: #d6001c;
            font-size: 34px;
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

        .print-header{
            display:none;
        }

        @media print {
            .sidebar,
            .top,
            .date-filter button,
            .date-filter input,
            .footer {
                display: none;
            }

            .print-header{
                display:block;
                text-align:center;
                margin-bottom:30px;
            }

            .print-header h2{
                margin:0;
            }

            .print-header p{
                margin:5px 0;
            }

            .content {
                padding: 20px;
            }

            .container {
                display: block;
            }
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
            <a href="laporan.php" class="active">📊 Laporan Penjualan</a>
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
                <a href="export_excel.php" style="
                background:#198754;
                color:white;
                padding:14px 24px;
                border-radius:12px;
                text-decoration:none;
                font-weight:bold;
                ">
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
                <h3>Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></h3>
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

                <?php
                $no = 1;
                while ($row = mysqli_fetch_assoc($data)) {
                ?>

                <tr>
                    <td><?php echo str_pad($no++, 2, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo date('H:i', strtotime($row['tanggal'])); ?></td>
                    <td><?php echo $row['nama_minuman']; ?></td>
                    <td><?php echo $row['qty']; ?> gelas</td>
                    <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($row['subtotal'], 0, ',', '.'); ?></td>
                    <td><?php echo $row['nama_kasir']; ?></td>
                </tr>

                <?php } ?>
            </table>

            <div class="total-row">
                <div>Total Laporan</div>
                <div class="amount">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></div>
            </div>
        </div>

        <div class="riwayat-title">
    Riwayat Transaksi
</div>

<div class="table-card">

    <table>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Kasir</th>
            <th>Metode</th>
            <th>Total</th>
            <th>Status</th>
        </tr>

        <?php
        $no = 1;
        while($r = mysqli_fetch_assoc($riwayat)){
        ?>

        <tr>
            <td><?php echo $no++; ?></td>

            <td>
                <?php echo date('d-m-Y H:i', strtotime($r['tanggal'])); ?>
            </td>

            <td>
                <?php echo $r['nama']; ?>
            </td>

            <td>
                <?php echo $r['metode_pembayaran']; ?>
            </td>

            <td>
                Rp <?php echo number_format($r['total'],0,',','.'); ?>
            </td>

            <td>
                <?php
                if($r['status_validasi']=="Tervalidasi"){
                    echo "<span style='color:green;font-weight:bold'>✔ Tervalidasi</span>";
                }else{
                    echo "<span style='color:orange;font-weight:bold'>Menunggu</span>";
                }
                ?>
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