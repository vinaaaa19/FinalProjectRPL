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

if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_minuman'];
    $harga = $_POST['harga'] * 1000;
    $stok = $_POST['stok'];
    $status = "Aktif";

    mysqli_query($conn, "INSERT INTO minuman 
        (nama_minuman, harga, stok, status)
        VALUES
        ('$nama', '$harga', '$stok', '$status')
    ");

    header("Location: data_minuman.php");
    exit;
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM minuman WHERE id_minuman='$id'");
    header("Location: data_minuman.php");
    exit;
}

$data = mysqli_query($conn, "SELECT * FROM minuman");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Minuman - Drink Point</title>
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
            background: #d1fae5;
            color: #067a35;
            padding: 7px 14px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
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
            <a href="dashboard.php">🏠 Dashboard</a>

            <div class="menu-title">MENU UTAMA</div>

            <a href="data_minuman.php" class="active">🧋 Data Minuman</a>
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

        <h1>Data Minuman</h1>
        <p class="subtitle">Kelola data minuman yang tersedia</p>

        <div class="form-card">
            <h3>Tambah Minuman</h3>

            <form method="POST">
                <input type="text" name="nama_minuman" placeholder="Nama minuman" required>
                <input type="number" name="harga" placeholder="Harga contoh: 8 untuk Rp 8.000" required>
                <input type="number" name="stok" placeholder="Stok" required>
                <button type="submit" name="tambah">+ Tambah Minuman</button>
            </form>
        </div>

        <div class="table-card">
            <table>
                <tr>
                    <th>No</th>
                    <th>Nama Minuman</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

                <?php
                $no = 1;
                while ($row = mysqli_fetch_assoc($data)) {
                ?>

                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $row['nama_minuman']; ?></td>
                    <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                    <td><?php echo $row['stok']; ?> gelas</td>
                    <td><span class="badge"><?php echo $row['status']; ?></span></td>
                    <td>
                        <a href="edit_minuman.php?id=<?php echo $row['id_minuman']; ?>" class="edit">Edit</a>
                        <a href="data_minuman.php?hapus=<?php echo $row['id_minuman']; ?>" 
                           onclick="return confirm('Yakin hapus data?')" 
                           class="hapus">Hapus</a>
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