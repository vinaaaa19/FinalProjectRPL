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
    header("Location: akun_karyawan.php");
    exit;
}

$id = $_GET['id'];

$data = mysqli_query($conn, "SELECT * FROM users WHERE id='$id' AND role='karyawan'");
$row = mysqli_fetch_assoc($data);

if (!$row) {
    header("Location: akun_karyawan.php?error=notfound");
    exit;
}

if (isset($_POST['simpan'])) {
    $password_baru = $_POST['password_baru'];
    $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

    mysqli_query($conn, "
        UPDATE users
        SET password='$password_hash'
        WHERE id='$id' AND role='karyawan'
    ");

    header("Location: akun_karyawan.php?success=password");
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
            box-sizing: border-box;
        }

        .btn-submit {
            margin-top: 25px;
            padding: 13px 22px;
            background: #d6001c;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
        }

        .back {
            margin-left: 10px;
            color: #d6001c;
            text-decoration: none;
            font-weight: bold;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.35);
            justify-content: center;
            align-items: center;
            z-index: 999;
        }

        .modal-box {
            background: white;
            width: 360px;
            padding: 28px;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.18);
        }

        .modal-box h3 {
            margin-top: 0;
            color: #d6001c;
        }

        .modal-box p {
            color: #555;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .btn-cancel,
        .btn-confirm {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .btn-cancel {
            background: #f3f4f6;
            color: #333;
        }

        .btn-confirm {
            background: #d6001c;
            color: white;
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

    </style>
</head>

<body>

<div class="card">
    <h1>Edit Password Karyawan</h1>

    <p>Nama: <b><?php echo $row['nama']; ?></b></p>
    <p>Username: <b><?php echo $row['username']; ?></b></p>

    <form method="POST" id="passwordForm">
        <label>Password Baru</label>

<div class="password-wrapper">
    <input type="password"
           id="password_baru"
           name="password_baru"
           placeholder="Masukkan password baru"
           required>

    <span class="toggle-password"
          onclick="togglePassword('password_baru')">
        👁️
    </span>
</div>
        <button type="button" onclick="openConfirmModal()" class="btn-submit">
            Simpan Password
        </button>

        <a href="akun_karyawan.php" class="back">Kembali</a>
    </form>
</div>

<div id="confirmModal" class="modal">
    <div class="modal-box">
        <h3>Konfirmasi Password</h3>
        <p>Yakin ingin mengubah password karyawan ini?</p>

        <div class="modal-actions">
            <button type="button" onclick="closeConfirmModal()" class="btn-cancel">Batal</button>
            <button type="submit" form="passwordForm" name="simpan" class="btn-confirm">Simpan</button>
        </div>
    </div>
</div>

<script>
function openConfirmModal() {
    document.getElementById("confirmModal").style.display = "flex";
}

function closeConfirmModal() {
    document.getElementById("confirmModal").style.display = "none";
}
</script>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);

    if(input.type === "password"){
        input.type = "text";
    }else{
        input.type = "password";
    }
}
</script>

</body>
</html>