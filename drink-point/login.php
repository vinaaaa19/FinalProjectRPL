<?php
session_start();
include "koneksi.php";

$error = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users 
                                  WHERE username='$username' 
                                  AND password='$password'");

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);

        $_SESSION['id'] = $data['id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['role'] = $data['role'];

        if ($data['role'] == 'pemilik') {
            header("Location: dashboard.php");
        } else {
            header("Location: dashboard_karyawan.php");
        }
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Drink Point</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff5f5;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .left {
            width: 55%;
            padding: 70px;
            background: linear-gradient(135deg, #fff, #ffe5e5);
        }

        .logo {
            font-size: 42px;
            font-weight: bold;
        }

        .logo span {
            color: #d6001c;
        }

        .left h1 {
            font-size: 38px;
            margin-top: 80px;
            line-height: 1.4;
        }

        .left h1 span {
            color: #d6001c;
        }

        .left p {
            color: #555;
            font-size: 18px;
            line-height: 1.6;
        }

        .right {
            width: 45%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fffafa;
        }

        .card {
            width: 420px;
            background: white;
            padding: 45px;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .card h2 {
            text-align: center;
            color: #d6001c;
            font-size: 32px;
        }

        .card p {
            text-align: center;
            color: #666;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 25px;
        }

        input {
            width: 100%;
            padding: 14px;
            margin-top: 8px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        .row {
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
            color: #d6001c;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 15px;
            background: #d6001c;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 16px;
        }

        .error {
            background: #ffd6d6;
            color: #a40000;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
        }

        .note {
            margin-top: 25px;
            text-align: center;
            color: #777;
            line-height: 1.5;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="left">
        <div class="logo">🥤 Drink <span>Point</span></div>
        <p>Sistem Manajemen Stok dan Penjualan Minuman</p>

        <h1>
            Kelola stok minuman,<br>
            catat penjualan,<br>
            dan pantau laporan<br>
            <span>dengan mudah.</span>
        </h1>

        <p>
            Drink Point membantu Anda mengelola usaha minuman
            dengan lebih efisien, praktis, dan terorganisir.
        </p>
    </div>

    <div class="right">
        <div class="card">
            <h2>Login</h2>
            <p>Masuk untuk mengakses sistem Drink Point</p>

            <?php if ($error != "") { ?>
                <div class="error"><?php echo $error; ?></div>
            <?php } ?>

            <form method="POST">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required>

                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>

                <div class="row">
                    <span>☑ Ingat saya</span>
                    <span>Lupa password?</span>
                </div>

                <button type="submit" name="login">Login</button>

                <div class="note">
                    Akun dibuat oleh pemilik usaha.<br>
                    Hubungi pemilik jika belum memiliki akun.
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>