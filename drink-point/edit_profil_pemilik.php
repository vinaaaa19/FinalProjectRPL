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

$user = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM users WHERE id='$id'")
);

if (isset($_POST['simpan'])) {

    $nama = $_POST['nama'];
    $username = $_POST['username'];

    $foto = $user['foto'];

    if ($_FILES['foto']['name'] != "") {

        $nama_file = time() . "_" . $_FILES['foto']['name'];

        move_uploaded_file(
            $_FILES['foto']['tmp_name'],
            "uploads/" . $nama_file
        );

        $foto = $nama_file;
    }

    mysqli_query($conn, "
        UPDATE users SET
        nama='$nama',
        username='$username',
        foto='$foto'
        WHERE id='$id'
    ");

    $_SESSION['nama'] = $nama;

    echo "
    <script>
        alert('Profil berhasil diperbarui');
        window.location='profil_pemilik.php';
    </script>
    ";
    exit;
}

if (isset($_POST['ubah_password'])) {

    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    if ($password_baru != $konfirmasi) {

        echo "
        <script>
            alert('Konfirmasi password tidak sama');
        </script>
        ";
    } else {

        mysqli_query($conn, "
            UPDATE users
            SET password='$password_baru'
            WHERE id='$id'
        ");

        echo "
        <script>
            alert('Password berhasil diubah');
            window.location='profil_pemilik.php';
        </script>
        ";
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Profil Pemilik</title>

<style>

body{
    margin:0;
    font-family:Arial;
    background:#fff7f7;
}

.card{
    width:650px;
    margin:40px auto;
    background:white;
    padding:35px;
    border-radius:20px;
    box-shadow:0 8px 25px rgba(0,0,0,.08);
}

h1{
    color:#d6001c;
}

label{
    display:block;
    margin-top:15px;
    font-weight:bold;
}

input{
    width:100%;
    padding:12px;
    margin-top:8px;
    border:1px solid #ddd;
    border-radius:10px;
    box-sizing:border-box;
}

.avatar{
    width:120px;
    height:120px;
    border-radius:50%;
    overflow:hidden;
    background:#d6001c;
    color:white;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:40px;
    font-weight:bold;
    margin:auto;
}

.avatar img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.btn{
    width:100%;
    margin-top:20px;
    padding:14px;
    background:#d6001c;
    color:white;
    border:none;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
}

.line{
    margin:30px 0;
    border-top:1px solid #eee;
}

.back{
    text-decoration:none;
    color:#d6001c;
    font-weight:bold;
}

</style>
</head>
<body>

<div class="card">

<div class="avatar">

<?php if(!empty($user['foto'])){ ?>

<img src="uploads/<?php echo $user['foto']; ?>">

<?php } else { ?>

P

<?php } ?>

</div>

<h1>Edit Profil</h1>

<form method="POST" enctype="multipart/form-data">

<label>Foto Profil</label>
<input type="file" name="foto">

<label>Nama</label>
<input type="text"
name="nama"
value="<?php echo $user['nama']; ?>"
required>

<label>Username</label>
<input type="text"
name="username"
value="<?php echo $user['username']; ?>"
required>

<button type="submit"
name="simpan"
class="btn">
Simpan Profil
</button>

</form>

<div class="line"></div>

<h1>Ubah Password</h1>

<form method="POST">

<label>Password Baru</label>
<input type="password"
name="password_baru"
required>

<label>Konfirmasi Password</label>
<input type="password"
name="konfirmasi"
required>

<button type="submit"
name="ubah_password"
class="btn">
Simpan Password
</button>

</form>

<br>

<a href="profil_pemilik.php"
class="back">
← Kembali ke Profil
</a>

</div>

</body>
</html>