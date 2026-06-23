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

$id = $_SESSION['id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$id'"));

$total_transaksi = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM transaksi"));
$total_karyawan = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role='karyawan'"));
$total_minuman = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM minuman"));
$total_bahan = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM bahan"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil Pemilik - Drink Point</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff7f7;
        }

        .wrapper {
            min-height: 100vh;
            padding: 50px;
        }

        .card {
            max-width: 850px;
            margin: auto;
            background: white;
            border-radius: 22px;
            padding: 35px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .header {
            display: flex;
            align-items: center;
            gap: 25px;
            border-bottom: 1px solid #eee;
            padding-bottom: 25px;
        }

        .avatar {
            width: 120px;
            height: 120px;
            background: #d6001c;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 45px;
            font-weight: bold;
            overflow: hidden;
            flex-shrink: 0;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        h1 {
            color: #d6001c;
            margin: 0 0 8px;
        }

        .role {
            color: #777;
            font-size: 16px;
        }

        .info {
            margin-top: 28px;
            background: #fff5f5;
            padding: 20px;
            border-radius: 16px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .row:last-child {
            border-bottom: none;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-top: 28px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.07);
            text-align: center;
        }

        .stat-card h3 {
            color: #d6001c;
            font-size: 30px;
            margin: 8px 0 0;
        }

        .stat-card p {
            color: #666;
            margin: 0;
            font-weight: bold;
        }

        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            text-align: center;
            padding: 14px;
            background: #d6001c;
            color: white;
            border-radius: 12px;
            text-decoration: none;
            font-weight: bold;
        }

        .back {
            background: white;
            color: #d6001c;
            border: 1px solid #d6001c;
        }
    </style>
</head>
<body>

<div class="wrapper">

    <div class="card">

        <div class="header">
            <div class="avatar">
                <?php if (!empty($user['foto'])) { ?>
                    <img src="uploads/<?php echo $user['foto']; ?>">
                <?php } else { ?>
                    P
                <?php } ?>
            </div>

            <div>
                <h1><?php echo $user['nama']; ?></h1>
                <div class="role"><?php echo ucfirst($user['role']); ?> Drink Point</div>
            </div>
        </div>

        <div class="info">
            <div class="row">
                <b>Nama</b>
                <span><?php echo $user['nama']; ?></span>
            </div>

            <div class="row">
                <b>Username</b>
                <span><?php echo $user['username']; ?></span>
            </div>

            <div class="row">
                <b>Role</b>
                <span><?php echo ucfirst($user['role']); ?></span>
            </div>

            <div class="row">
                <b>Status Akun</b>
                <span>Aktif</span>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <p>Transaksi</p>
                <h3><?php echo $total_transaksi; ?></h3>
            </div>

            <div class="stat-card">
                <p>Karyawan</p>
                <h3><?php echo $total_karyawan; ?></h3>
            </div>

            <div class="stat-card">
                <p>Minuman</p>
                <h3><?php echo $total_minuman; ?></h3>
            </div>

            <div class="stat-card">
                <p>Bahan</p>
                <h3><?php echo $total_bahan; ?></h3>
            </div>
        </div>

        <div class="actions">
            <a href="edit_profil_pemilik.php" class="btn">Edit Profil</a>
            <a href="dashboard.php" class="btn back">Kembali ke Dashboard</a>
        </div>

    </div>

</div>

</body>
</html>