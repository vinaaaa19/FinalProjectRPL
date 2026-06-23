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

$id = $_GET['id'];

$data = mysqli_query($conn, "SELECT * FROM users WHERE id='$id' AND role='karyawan'");
$row = mysqli_fetch_assoc($data);

if (!$row) {
    echo "<script>alert('Data karyawan tidak ditemukan'); window.location='akun_karyawan.php';</script>";
    exit;
}

if (isset($_POST['simpan'])) {
    $password_baru = $_POST['password_baru'];

    mysqli_query($conn, "UPDATE users SET password='$password_baru' WHERE id='$id' AND role='karyawan'");

    echo "<script>alert('Password karyawan berhasil diubah'); window.location='akun_karyawan.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Password Karyawan</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff7f7;
        }

        .card {
            width: 500px;
            margin: 80px auto;
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
            font-weight: bold;
            display: block;
            margin-top: 20px;
        }

        input {
            width: 100%;
            padding: 13px;
            margin-top: 8px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        button {
            margin-top: 25px;
            padding: 13px 22px;
            background: #d6001c;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
        }

        a {
            margin-left: 10px;
            color: #d6001c;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="card">
    <h1>Edit Password Karyawan</h1>

    <p>Nama: <b><?php echo $row['nama']; ?></b></p>
    <p>Username: <b><?php echo $row['username']; ?></b></p>

    <form method="POST">
        <label>Password Baru</label>
        <input type="text" name="password_baru" placeholder="Masukkan password baru" required>

        <button type="submit" name="simpan">Simpan Password</button>
        <a href="akun_karyawan.php">Kembali</a>
    </form>
</div>

</body>
</html>