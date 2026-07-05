<?php
include "koneksi.php";

$pesan = "";

if (isset($_POST['reset'])) {
    $username = $_POST['username'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    if ($password_baru != $konfirmasi) {
        $pesan = "Konfirmasi password tidak sama.";
    } else {
        $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");

        if (mysqli_num_rows($cek) > 0) {
            mysqli_query($conn, "
                UPDATE users 
                SET password='$password_baru'
                WHERE username='$username'
            ");

            echo "<script>
                alert('Password berhasil diubah. Silakan login.');
                window.location='login.php';
            </script>";
            exit;
        } else {
            $pesan = "Username tidak ditemukan.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lupa Password - Drink Point</title>
    <style>
        body {
            margin:0;
            font-family:Arial;
            background:#fff7f7;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
        }

        .card {
            width:420px;
            background:white;
            padding:35px;
            border-radius:20px;
            box-shadow:0 8px 25px rgba(0,0,0,0.08);
        }

        h2 {
            color:#d6001c;
            text-align:center;
        }

        input {
            width:100%;
            padding:13px;
            margin-top:10px;
            margin-bottom:15px;
            border:1px solid #ddd;
            border-radius:10px;
            box-sizing:border-box;
        }

        button {
            width:100%;
            padding:14px;
            background:#d6001c;
            color:white;
            border:none;
            border-radius:10px;
            font-weight:bold;
        }

        .error {
            background:#fee2e2;
            color:#991b1b;
            padding:12px;
            border-radius:10px;
            margin-bottom:15px;
            text-align:center;
        }

        a {
            display:block;
            text-align:center;
            margin-top:18px;
            color:#d6001c;
            text-decoration:none;
            font-weight:bold;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Lupa Password</h2>

    <?php if ($pesan != "") { ?>
        <div class="error"><?php echo $pesan; ?></div>
    <?php } ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Masukkan username" required>
        <input type="password" name="password_baru" placeholder="Password baru" required>
        <input type="password" name="konfirmasi" placeholder="Konfirmasi password" required>

        <button type="submit" name="reset">Ubah Password</button>
    </form>

    <a href="login.php">← Kembali ke Login</a>
</div>

</body>
</html>