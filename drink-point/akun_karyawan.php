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
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = "karyawan";
    $status = "Aktif";
    $tanggal_daftar = date('Y-m-d');

    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah digunakan!'); window.location='akun_karyawan.php';</script>";
        exit;
    }

    mysqli_query($conn, "INSERT INTO users 
        (nama, username, password, role, status, tanggal_daftar)
        VALUES
        ('$nama', '$username', '$password', '$role', '$status', '$tanggal_daftar')
    ");

    header("Location: akun_karyawan.php");
    exit;
}

if (isset($_GET['nonaktif'])) {
    $id = $_GET['nonaktif'];
    mysqli_query($conn, "UPDATE users SET status='Nonaktif' WHERE id='$id' AND role='karyawan'");
    header("Location: akun_karyawan.php");
    exit;
}

if (isset($_GET['aktif'])) {
    $id = $_GET['aktif'];
    mysqli_query($conn, "UPDATE users SET status='Aktif' WHERE id='$id' AND role='karyawan'");
    header("Location: akun_karyawan.php");
    exit;
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id='$id' AND role='karyawan'");
    header("Location: akun_karyawan.php");
    exit;
}

$search = isset($_GET['search']) ? $_GET['search'] : "";
$filter = isset($_GET['status']) ? $_GET['status'] : "";

$where = "WHERE role='karyawan'";

if ($search != "") {
    $where .= " AND nama LIKE '%$search%'";
}

if ($filter != "") {
    $where .= " AND status='$filter'";
}

$data = mysqli_query($conn, "SELECT * FROM users $where ORDER BY id DESC");

$total_karyawan = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role='karyawan'"));
$total_aktif = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role='karyawan' AND status='Aktif'"));
$total_nonaktif = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role='karyawan' AND status='Nonaktif'"));
$total_bulan_ini = mysqli_num_rows(mysqli_query($conn, "
    SELECT * FROM users 
    WHERE role='karyawan'
    AND MONTH(tanggal_daftar)=MONTH(CURRENT_DATE())
    AND YEAR(tanggal_daftar)=YEAR(CURRENT_DATE())
"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Akun Karyawan - Drink Point</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff7f7;
            color: #222;
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
            overflow-x: auto;
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
            margin: 0;
        }

        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .btn {
            background: #d6001c;
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(4,1fr);
            gap: 22px;
            margin-bottom: 28px;
        }

        .summary-card {
            background: white;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.07);
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
            margin: 0;
            font-weight: bold;
            color: #333;
        }

        .summary-card h3 {
            margin: 10px 0 0;
            font-size: 34px;
            color: #d6001c;
        }

        .summary-card span {
            color: #777;
            font-size: 14px;
            margin-left: 6px;
            font-weight: normal;
        }

        .form-card,
        .table-card {
            background: white;
            border-radius: 18px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.07);
            margin-bottom: 28px;
        }

        .form-card {
            display: none;
        }

        input,
        select {
            padding: 13px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
        }

        .form-card input {
            width: 27%;
            margin-right: 10px;
        }

        .tools {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            gap: 20px;
        }

        .search-box {
            display: flex;
            gap: 10px;
        }

        .search-box input {
            width: 300px;
        }

        .filter-box select {
            width: 190px;
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
            padding: 16px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            vertical-align: middle;
        }

        .user-icon {
            width: 38px;
            height: 38px;
            background: #d6001c;
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }

        .badge-aktif {
            background: #d1fae5;
            color: #087f3f;
            padding: 7px 14px;
            border-radius: 20px;
            font-weight: bold;
        }

        .badge-nonaktif {
            background: #ffe5e5;
            color: #d6001c;
            padding: 7px 14px;
            border-radius: 20px;
            font-weight: bold;
        }

        .aksi {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 5px;
            background: white;
            border: 1px solid #ddd;
        }

        .hapus {
            border: 1px solid #ff9b9b;
            color: #d6001c;
        }

        .legend-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .legend {
            display: flex;
            gap: 30px;
        }

        .dot-green,
        .dot-red {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .dot-green {
            background: #22c55e;
        }

        .dot-red {
            background: #ef4444;
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

        @media(max-width:1100px){
            .summary{
                grid-template-columns:repeat(2,1fr);
            }
        }
    </style>

    <script>
        function tampilForm() {
            var form = document.getElementById("formTambah");

            if(form.style.display=="block"){
                form.style.display="none";
            }else{
                form.style.display="block";
            }
        }
    </script>
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
            <a href="validasi.php">✅ Validasi Laporan</a>
            <a href="akun_karyawan.php" class="active">👥 Akun Karyawan</a>
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

        <div class="page-head">
            <h1>Akun Karyawan</h1>
            <button class="btn" onclick="tampilForm()">+ Tambah Karyawan</button>
        </div>

        <div id="formTambah" class="form-card">
            <h3>Tambah Akun Karyawan</h3>

            <form method="POST">
                <input type="text" name="nama" placeholder="Nama karyawan" required>
                <input type="text" name="username" placeholder="Username" required>
                <input type="text" name="password" placeholder="Password awal" required>
                <button type="submit" name="tambah" class="btn">Simpan</button>
            </form>
        </div>

        <div class="summary">
            <div class="summary-card">
                <div class="circle">👥</div>
                <p>Total Karyawan</p>
                <h3><?php echo $total_karyawan; ?><span>Orang</span></h3>
            </div>

            <div class="summary-card">
                <div class="circle">👤</div>
                <p>Karyawan Aktif</p>
                <h3><?php echo $total_aktif; ?><span>Orang</span></h3>
            </div>

            <div class="summary-card">
                <div class="circle">👤</div>
                <p>Karyawan Nonaktif</p>
                <h3><?php echo $total_nonaktif; ?><span>Orang</span></h3>
            </div>

            <div class="summary-card">
                <div class="circle">📅</div>
                <p>Terdaftar Bulan Ini</p>
                <h3><?php echo $total_bulan_ini; ?><span>Orang</span></h3>
            </div>
        </div>

        <div class="tools">
            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Cari nama karyawan..." value="<?php echo $search; ?>">
                <button type="submit" class="btn">Cari</button>
            </form>

            <form method="GET" class="filter-box">
                <select name="status" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="Aktif" <?php if($filter == 'Aktif') echo 'selected'; ?>>Aktif</option>
                    <option value="Nonaktif" <?php if($filter == 'Nonaktif') echo 'selected'; ?>>Nonaktif</option>
                </select>
            </form>
        </div>

        <div class="table-card">
            <table>
                <tr>
                    <th>No</th>
                    <th>Nama Karyawan</th>
                    <th>Username</th>
                    <th>Jabatan</th>
                    <th>Status</th>
                    <th>Terdaftar</th>
                    <th>Aksi</th>
                </tr>

                <?php
                $no = 1;
                while ($row = mysqli_fetch_assoc($data)) {
                ?>

                <tr>
                    <td><?php echo str_pad($no++, 2, '0', STR_PAD_LEFT); ?></td>

                    <td>
                        <span class="user-icon">👤</span>
                        <?php echo $row['nama']; ?>
                    </td>

                    <td><?php echo $row['username']; ?></td>

                    <td>Kasir</td>

                    <td>
                        <?php if ($row['status'] == 'Aktif') { ?>
                            <span class="badge-aktif">Aktif</span>
                        <?php } else { ?>
                            <span class="badge-nonaktif">Nonaktif</span>
                        <?php } ?>
                    </td>

                    <td><?php echo date('d F Y', strtotime($row['tanggal_daftar'])); ?></td>

                    <td>
                        <?php if ($row['status'] == 'Aktif') { ?>
                            <a href="akun_karyawan.php?nonaktif=<?php echo $row['id']; ?>" 
                               onclick="return confirm('Nonaktifkan akun ini?')" 
                               class="aksi" 
                               title="Nonaktifkan">✎</a>
                        <?php } else { ?>
                            <a href="akun_karyawan.php?aktif=<?php echo $row['id']; ?>" 
                               onclick="return confirm('Aktifkan akun ini?')" 
                               class="aksi" 
                               title="Aktifkan">✓</a>
                        <?php } ?>

                        <a href="edit_password_karyawan.php?id=<?php echo $row['id']; ?>" 
                           class="aksi" 
                           title="Edit Password">🔑</a>

                        <a href="akun_karyawan.php?hapus=<?php echo $row['id']; ?>" 
                           onclick="return confirm('Yakin hapus akun karyawan ini?')" 
                           class="aksi hapus" 
                           title="Hapus">🗑</a>
                    </td>
                </tr>

                <?php } ?>
            </table>

            <div class="legend-wrap">
                <div class="legend">
                    <div>
                        <span class="dot-green"></span>
                        <b>Aktif</b><br>
                        Karyawan dapat login dan mengakses sistem
                    </div>

                    <div>
                        <span class="dot-red"></span>
                        <b>Nonaktif</b><br>
                        Karyawan tidak dapat login
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            © 2026 <span>Drink Point</span>. Semua hak dilindungi.
        </div>

    </div>

</div>

</body>
</html>