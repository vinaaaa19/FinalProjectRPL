<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Karyawan</title>
</head>
<body>

<h1>Dashboard Karyawan</h1>

<h3>Selamat Datang,
<?php echo $_SESSION['nama']; ?>
</h3>

<ul>
    <li>Input Transaksi Penjualan</li>
    <li>Lihat Stok Minuman</li>
    <li>Riwayat Transaksi Pribadi</li>
    <li>Logout</li>
</ul>

<a href="logout.php">Logout</a>

</body>
</html>