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

$error = "";

if (isset($_POST['simpan'])) {

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
            header("Location: edit_profil_pemilik.php?error=upload");
            exit;
        }
    }

    mysqli_query($conn, "
        UPDATE users SET
        nama='$nama',
        username='$username',
        foto='$foto'
        WHERE id='$id'
    ");

    $_SESSION['nama'] = $nama;

    header("Location: profil_pemilik.php?success=profil");
    exit;
}

if (isset($_POST['ubah_password'])) {

    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];
    $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

    if ($password_baru != $konfirmasi) {
        header("Location: edit_profil_pemilik.php?error=password");
        exit;
    }

    mysqli_query($conn, "
        UPDATE users
        SET password='$password_hash'
        WHERE id='$id'
    ");

    header("Location: profil_pemilik.php?success=password");
    exit;
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

.error-box{
    background:#fee2e2;
    color:#991b1b;
    padding:14px;
    border-radius:12px;
    margin-bottom:20px;
    font-weight:bold;
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
</style>
</head>

<body>

<div class="card">

    <?php if(isset($_GET['error']) && $_GET['error'] == 'upload'){ ?>
        <div class="error-box">
            ⚠ Upload foto gagal. Pastikan folder uploads sudah ada.
        </div>
    <?php } ?>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'password'){ ?>
        <div class="error-box">
            ⚠ Konfirmasi password tidak sama.
        </div>
    <?php } ?>

    <div class="avatar">
        <?php if(!empty($user['foto'])){ ?>
            <img src="./uploads/<?php echo $user['foto']; ?>">
        <?php } else { ?>
            P
        <?php } ?>
    </div>

    <h1>Edit Profil</h1>

    <form method="POST" enctype="multipart/form-data">
        <label>Foto Profil</label>
        <input type="file" name="foto">

        <label>Nama</label>
        <input type="text" name="nama" value="<?php echo $user['nama']; ?>" required>

        <label>Username</label>
        <input type="text" name="username" value="<?php echo $user['username']; ?>" required>

        <button type="submit" name="simpan" class="btn">
            Simpan Profil
        </button>
    </form>

    <div class="line"></div>

    <h1>Ubah Password</h1>

    <form method="POST" id="passwordForm">
        <label>Password Baru</label>
            <div class="password-wrapper">
                <input type="password" id="password_baru" name="password_baru" required>
                <span class="toggle-password" onclick="togglePassword('password_baru')">👁️</span>
            </div>

            <label>Konfirmasi Password</label>
            <div class="password-wrapper">
                <input type="password" id="konfirmasi" name="konfirmasi" required>
                <span class="toggle-password" onclick="togglePassword('konfirmasi')">👁️</span>
            </div>

        <button type="button" onclick="openConfirmModal()" class="btn">
            Simpan Password
        </button>
    </form>

    <br>

    <a href="profil_pemilik.php" class="back">
        ← Kembali ke Profil
    </a>

</div>

<div id="confirmModal" class="modal">
    <div class="modal-box">
        <h3>Konfirmasi Password</h3>
        <p>Yakin ingin mengubah password akun pemilik?</p>

        <div class="modal-actions">
            <button type="button" onclick="closeConfirmModal()" class="btn-cancel">Batal</button>
            <button type="submit" form="passwordForm" name="ubah_password" class="btn-confirm">Simpan</button>
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