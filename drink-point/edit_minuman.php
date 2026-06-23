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

if (!isset($_GET['id'])) {
    header("Location: data_minuman.php");
    exit;
}

$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM minuman WHERE id_minuman='$id'");
$row = mysqli_fetch_assoc($data);

if (!$row) {
    echo "<script>alert('Data minuman tidak ditemukan'); window.location='data_minuman.php';</script>";
    exit;
}

if (isset($_POST['simpan'])) {
    $nama = $_POST['nama_minuman'];
    $harga = $_POST['harga'] * 1000;
    $stok = $_POST['stok'];

    if ($stok <= 0) {
        $status = "Habis";
    } else {
        $status = "Aktif";
    }

    mysqli_query($conn, "
        UPDATE minuman SET
        nama_minuman='$nama',
        harga='$harga',
        stok='$stok',
        status='$status'
        WHERE id_minuman='$id'
    ");

    echo "<script>alert('Data minuman berhasil diperbarui'); window.location='data_minuman.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Minuman - Drink Point</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff7f7;
        }

        .card {
            width: 520px;
            margin: 70px auto;
            background: white;
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        h1 {
            margin-top: 0;
            color: #d6001c;
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 18px;
        }

        input {
            width: 100%;
            padding: 13px;
            margin-top: 8px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-sizing: border-box;
        }

        .info {
            background: #fff5f5;
            padding: 13px;
            border-radius: 10px;
            margin-top: 15px;
            color: #555;
            font-size: 14px;
        }

        button {
            width: 100%;
            margin-top: 25px;
            padding: 14px;
            background: #d6001c;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #d6001c;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="card">
    <h1>Edit Minuman</h1>

    <form method="POST">
        <label>Nama Minuman</label>
        <input type="text" name="nama_minuman" value="<?php echo $row['nama_minuman']; ?>" required>

        <label>Harga</label>
        <input type="text" name="harga"
            value="<?php echo $row['harga'] / 1000; ?>"
            required>

        <div class="info">
            Isi harga seperti <b>8</b> untuk Rp 8.000
        </div>

        <label>Stok</label>
        <input type="number" name="stok" value="<?php echo $row['stok']; ?>" required>

        <button type="submit" name="simpan">Simpan Perubahan</button>
    </form>

    <a href="data_minuman.php">← Kembali ke Data Minuman</a>
</div>

</body>
</html>