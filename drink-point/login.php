<?php
session_start();
include "koneksi.php";

$error = "";

$saved_username = isset($_COOKIE['remember_username']) ? $_COOKIE['remember_username'] : "";
$checked = $saved_username != "" ? "checked" : "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($conn,"
    SELECT * FROM users
    WHERE username='$username'
    ");

    if(mysqli_num_rows($query) > 0){

    $data = mysqli_fetch_assoc($query);

    if(password_verify($password, $data['password'])){

        if ($data['status'] == 'Nonaktif') {
            $error = "Akun Anda sudah dinonaktifkan. Hubungi pemilik.";
        } else {

            $_SESSION['id'] = $data['id'];
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['role'] = $data['role'];

            if (isset($_POST['remember'])) {
                setcookie("remember_username", $username, time() + (86400 * 30), "/");
            } else {
                setcookie("remember_username", "", time() - 3600, "/");
            }

            if ($data['role'] == 'pemilik') {
                header("Location: dashboard.php");
            } else {
                header("Location: dashboard_karyawan.php");
            }
            exit;
        }

    }else{
        $error = "Username atau password salah!";
    }

}else{
    $error = "Username atau password salah!";
}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Drink Point</title>

    <style>
        * {
            box-sizing: border-box;
        }

.row{
    margin:18px 0 25px;
    display:flex;
    justify-content:flex-start;
    align-items:center;
}

.remember{
    display:flex;
    align-items:center;
    gap:8px;
    cursor:pointer;
    color:#d6001c;
    font-weight:bold;
    user-select:none;
}

.remember input {
    width: auto;
    margin: 0;
}

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff4f6;
        }

       .container{
            width:1300px;
            max-width:95%;
            height:90vh;

            margin:auto;
            margin-top:35px;

            display:flex;

            background:white;

            border-radius:30px;

            overflow:hidden;

            box-shadow:0 20px 50px rgba(0,0,0,.12);
        }

        .left {
            flex: 1.7;
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            background:
                radial-gradient(circle at 15% 10%, #ffd6df 0, #ffd6df 18%, transparent 19%),
                radial-gradient(circle at 85% 85%, #ffb3c1 0, #ffb3c1 22%, transparent 23%),
                linear-gradient(135deg, #fff, #fff2f4);
            padding: 60px 70px;
        }

        .left::before {
            content: "";
            position: absolute;
            left: -80px;
            top: -90px;
            width: 300px;
            height: 220px;
            background: #ffb6c6;
            border-radius: 50%;
            opacity: 0.7;
        }

        .left::after {
            content: "";
            position: absolute;
            right: -120px;
            bottom: -120px;
            width: 380px;
            height: 280px;
            background: #ff5b78;
            border-radius: 50%;
            opacity: 0.25;
        }

        .logo-wrap {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: #d6001c;
            color: white;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            transform: rotate(-6deg);
        }

        .logo {
            font-size: 42px;
            font-weight: bold;
            color: #111;
        }

        .logo span {
            color: #d6001c;
        }

        .tagline {
            position: relative;
            z-index: 2;
            color: #555;
            margin: 5px 0 45px 90px;
            line-height: 1.5;
        }

        .hero {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 35px;
            align-items: center;
        }

        .headline {
            font-size: 38px;
            line-height: 1.25;
            font-weight: bold;
            color: #111;
            margin-bottom: 20px;
        }

        .headline span {
            color: #d6001c;
        }

        .desc {
            color: #555;
            line-height: 1.6;
            font-size: 15px;
        }

        .drinks {
            margin-top: 30px;
            display: flex;
            align-items: flex-end;
            gap: 18px;
        }

        .cup {
            width: 95px;
            height: 150px;
            border-radius: 22px 22px 35px 35px;
            background: linear-gradient(#fff, #ffb5c5);
            box-shadow: 0 12px 25px rgba(214,0,28,.18);
            position: relative;
        }

        .cup:nth-child(2) {
            height: 175px;
            background: linear-gradient(#fff, #ff6c86);
        }

        .cup:nth-child(3) {
            height: 160px;
            background: linear-gradient(#fff, #ffc34d);
        }

        .cup::before {
            content: "";
            position: absolute;
            width: 10px;
            height: 95px;
            background: #d6001c;
            top: -45px;
            left: 50%;
            transform: rotate(12deg);
            border-radius: 8px;
        }

        .cup::after {
            content: "Drink\A Point";
            white-space: pre;
            position: absolute;
            left: 50%;
            top: 55%;
            transform: translate(-50%, -50%);
            background: #d6001c;
            color: white;
            font-size: 12px;
            font-weight: bold;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            line-height: 1.1;
        }

        .features {
            display: grid;
            gap: 28px;
        }

        .feature {
            display: flex;
            gap: 18px;
            align-items: flex-start;
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #ffe2e7;
            color: #d6001c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
            flex-shrink: 0;
        }

        .feature h4 {
            margin: 0 0 6px;
            color: #d6001c;
        }

        .feature p {
            margin: 0;
            color: #555;
            font-size: 13px;
            line-height: 1.45;
        }

       .right{
            flex:1;

            display:flex;
            justify-content:center;
            align-items:center;

            background:white;
        }

        .card{
            width:420px;

            background:transparent;

            box-shadow:none;

            padding:20px;
        }

        .card h2 {
            text-align: center;
            color: #d6001c;
            font-size: 34px;
            margin: 0 0 8px;
        }

        .card p {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 13px;
            border-radius: 12px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 18px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 20px;
        }

        input {
            width: 100%;
            padding: 15px;
            margin-top: 8px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 15px;
            outline: none;
        }

        .password-wrapper{
    position:relative;
}

.password-wrapper input{
    padding-right:50px;
}

.toggle-password{
    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    color:#777;
    font-size:18px;
    user-select:none;
}

        input:focus {
            border-color: #d6001c;
            box-shadow: 0 0 0 3px rgba(214,0,28,0.12);
        }

        button {
            width: 100%;
            padding: 15px;
            background: #d6001c;
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #b90018;
        }

        .note {
            margin-top: 25px;
            text-align: center;
            color: #777;
            line-height: 1.5;
            font-size: 14px;
        }

        @media(max-width: 1000px) {
            .container {
                flex-direction: column;
            }

            .left,
            .right {
                width: 100%;
                border-left:1px solid #eee;
            }


            .hero {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="left">
        <div class="logo-wrap">
            <div class="logo-icon">🥤</div>
            <div>
                <div class="logo">Drink <span>Point</span></div>
            </div>
        </div>

        <div class="tagline">
            Sistem Manajemen Stok dan<br>
            Penjualan Minuman
        </div>

        <div class="hero">
            <div>
                <div class="headline">
                    Kelola stok minuman,<br>
                    catat penjualan,<br>
                    dan pantau laporan<br>
                    <span>dengan mudah.</span>
                </div>

                <p class="desc">
                    Drink Point membantu Anda mengelola usaha minuman
                    dengan lebih efisien, praktis, dan terorganisir.
                </p>

                <div class="drinks">
                    <div class="cup"></div>
                    <div class="cup"></div>
                    <div class="cup"></div>
                </div>
            </div>

            <div class="features">
                <div class="feature">
                    <div class="feature-icon">📦</div>
                    <div>
                        <h4>Kelola Stok</h4>
                        <p>Pantau stok minuman dan bahan secara real time dan akurat.</p>
                    </div>
                </div>

                <div class="feature">
                    <div class="feature-icon">🛒</div>
                    <div>
                        <h4>Transaksi Cepat</h4>
                        <p>Catat penjualan dengan mudah, stok otomatis berkurang.</p>
                    </div>
                </div>

                <div class="feature">
                    <div class="feature-icon">📊</div>
                    <div>
                        <h4>Laporan Lengkap</h4>
                        <p>Dapatkan laporan penjualan harian dengan cepat.</p>
                    </div>
                </div>

                <div class="feature">
                    <div class="feature-icon">🛡️</div>
                    <div>
                        <h4>Aman & Terpercaya</h4>
                        <p>Data usaha aman dengan hak akses sesuai pengguna.</p>
                    </div>
                </div>
            </div>
        </div>
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
                <input type="text" name="username" placeholder="Masukkan username" value="<?php echo $saved_username; ?>" required>

                <?php if($saved_username != ""){ ?>
                    <div style="
                        color:#16a34a;
                        font-size:13px;
                        margin-top:8px;
                    ">
                    ✓ Username tersimpan
                    </div>
                    <?php } ?>

                <label>Password</label>

                <div class="password-wrapper">
                    <input type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        required>

                    <span class="toggle-password"
                        onclick="togglePassword()">
                        👁️
                    </span>
                </div>

                <div class="row">
                    <label class="remember">
                        <input type="checkbox" name="remember" <?php echo $checked; ?>>
                        Ingat Saya
                    </label>
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

<script>
function togglePassword() {

    const password =
        document.getElementById("password");

    if(password.type === "password"){
        password.type = "text";
    }else{
        password.type = "password";
    }

}
</script>

</body>
</html>