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

$user = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT * FROM users 
        WHERE id='$id' AND role='karyawan'
    ")
);

if (!$user) {
    header("Location: akun_karyawan.php?error=notfound");
    exit;
}

if (isset($_POST['simpan_profil'])) {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $foto = $user['foto'];

    if (!empty($_FILES['foto']['name'])) {

        if (!is_dir("uploads")) {
            mkdir("uploads", 0777, true);
        }

        $nama_file = time() . "_" . basename($_FILES['foto']['name']);
        $tujuan = "uploads/" . $nama_file;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
            $foto = $nama_file;
        } else {
            header("Location: edit_profil_karyawan.php?id=$id&error=upload");
            exit;
        }
    }

    mysqli_query($conn, "
        UPDATE users SET
        nama='$nama',
        username='$username',
        foto='$foto'
        WHERE id='$id' AND role='karyawan'
    ");

    header("Location: akun_karyawan.php?success=edit");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profil Karyawan - Drink Point</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff7f7;
        }

        .card {
            width: 620px;
            margin: 50px auto;
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        h1 {
            color: #d6001c;
            margin-top: 0;
        }

        h3 {
            margin-top: 30px;
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
            font-size: 42px;
            font-weight: bold;
            margin: auto;
            overflow: hidden;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        label {
            font-weight: bold;
            margin-top: 15px;
            display: block;
        }

        input {
            width: 100%;
            padding: 13px;
            margin-top: 8px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-sizing: border-box;
        }

        .readonly {
            background: #f3f4f6;
            color: #555;
        }

        button {
            width: 100%;
            margin-top: 22px;
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

        .error-box {
            background:#fee2e2;
            color:#991b1b;
            padding:14px;
            border-radius:12px;
            margin-bottom:20px;
            font-weight:bold;
        }
    </style>
</head>

<body>

<div class="card">

    <?php if(isset($_GET['error']) && $_GET['error'] == 'upload'){ ?>
        <div class="error-box">
            ⚠ Upload foto gagal. Pastikan folder uploads sudah ada.
        </div>
    <?php } ?>

    <div class="avatar">
        <?php if (!empty($user['foto'])) { ?>
            <img src="./uploads/<?php echo $user['foto']; ?>">
        <?php } else { ?>
            <?php echo strtoupper(substr($user['nama'], 0, 1)); ?>
        <?php } ?>
    </div>

    <h1>Edit Profil Karyawan</h1>

    <form method="POST" enctype="multipart/form-data">
        <h3>Informasi Profil</h3>

        <label>Foto Profil</label>
        <input type="file" name="foto" accept="image/*">

        <label>Nama</label>
        <input type="text" name="nama" value="<?php echo $user['nama']; ?>" required>

        <label>Username</label>
        <input type="text" name="username" value="<?php echo $user['username']; ?>" required>

        <label>Role</label>
        <input type="text" value="Karyawan" class="readonly" readonly>

        <button type="submit" name="simpan_profil">
            Simpan Profil Karyawan
        </button>
    </form>

    <a href="akun_karyawan.php">← Kembali ke Akun Karyawan</a>

</div>

</body>
</html>