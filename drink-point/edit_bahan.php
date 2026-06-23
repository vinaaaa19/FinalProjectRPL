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
    header("Location: stok_bahan.php");
    exit;
}

$id = $_GET['id'];

$data = mysqli_query($conn, "SELECT * FROM bahan WHERE id_bahan='$id'");
$row = mysqli_fetch_assoc($data);

if (!$row) {
    echo "<script>alert('Data bahan tidak ditemukan'); window.location='stok_bahan.php';</script>";
    exit;
}

if (isset($_POST['simpan'])) {
    $nama = $_POST['nama_bahan'];
    $satuan = $_POST['satuan'];
    $stok = $_POST['stok'];

    if ($stok <= 5) {
        $status = "Menipis";
    } else {
        $status = "Aman";
    }

    mysqli_query($conn, "
        UPDATE bahan SET
        nama_bahan='$nama',
        satuan='$satuan',
        stok='$stok',
        status='$status'
        WHERE id_bahan='$id'
    ");

    echo "<script>alert('Data bahan berhasil diperbarui'); window.location='stok_bahan.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Bahan - Drink Point</title>
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

        .info {
            background: #fff5f5;
            padding: 13px;
            border-radius: 10px;
            margin-top: 15px;
            color: #555;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="card">
    <h1>Edit Bahan</h1>

    <form method="POST">
        <label>Nama Bahan</label>
        <input type="text" name="nama_bahan" value="<?php echo $row['nama_bahan']; ?>" required>

        <label>Satuan</label>
        <input type="text" name="satuan" value="<?php echo $row['satuan']; ?>" required>

        <label>Stok</label>
        <input type="number" name="stok" min="0" step="1" value="<?php echo $row['stok']; ?>" required>

        <div class="info">
            Jika stok 5 atau kurang, status otomatis menjadi <b>Menipis</b>.
        </div>

        <button type="submit" name="simpan">Simpan Perubahan</button>
    </form>

    <a href="stok_bahan.php">← Kembali ke Stok Bahan</a>
</div>

</body>
</html>