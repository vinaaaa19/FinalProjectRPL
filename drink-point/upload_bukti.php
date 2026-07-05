<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: laporan.php");
    exit;
}

$id = $_GET['id'];

$transaksi = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM transaksi 
    WHERE id_transaksi='$id'
"));

if (!$transaksi) {
    echo "<script>alert('Transaksi tidak ditemukan'); window.location='laporan.php';</script>";
    exit;
}

if (isset($_POST['upload'])) {
    if (!empty($_FILES['bukti_pembayaran']['name'])) {

        if (!is_dir("uploads/bukti_pembayaran")) {
            mkdir("uploads/bukti_pembayaran", 0777, true);
        }

        $nama_file = time() . "_" . basename($_FILES['bukti_pembayaran']['name']);
        $tujuan = "uploads/bukti_pembayaran/" . $nama_file;

        $tipe = strtolower(pathinfo($tujuan, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (!in_array($tipe, $allowed)) {
            echo "<script>alert('File harus JPG, JPEG, atau PNG');</script>";
        } else {
            move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $tujuan);

            mysqli_query($conn, "
                UPDATE transaksi 
                SET bukti_pembayaran='$nama_file'
                WHERE id_transaksi='$id'
            ");

            header("Location: laporan.php?upload=success");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Bukti Pembayaran</title>
    <style>

        .upload-box{
    display:block;
    background:#fff1f1;
    color:#d6001c;
    border:2px dashed #d6001c;
    padding:25px;
    border-radius:15px;
    text-align:center;
    font-weight:bold;
    cursor:pointer;
    margin-top:12px;
}

.upload-box input{
    display:none;
}

.file-name{
    color:#555;
    font-size:14px;
    text-align:center;
}

.preview-img{
    width:100%;
    max-height:260px;
    object-fit:contain;
    border-radius:14px;
    margin-top:15px;
    border:1px solid #eee;
}

        body {
            font-family: Arial;
            background: #fff7f7;
        }

        .card {
            width: 450px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        h2 {
            color: #d6001c;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-top: 10px;
        }

        button {
            width: 100%;
            margin-top: 20px;
            padding: 14px;
            background: #d6001c;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #d6001c;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Upload Bukti Pembayaran</h2>

    <p>Metode: <b><?php echo $transaksi['metode_pembayaran']; ?></b></p>
    <p>Total: <b>Rp <?php echo number_format($transaksi['total'],0,',','.'); ?></b></p>

    <form method="POST" enctype="multipart/form-data">
        <label>Pilih Bukti Pembayaran</label>
        <label class="upload-box">
    📷 Pilih Bukti Pembayaran
    <input type="file" name="bukti_pembayaran" accept="image/*" required onchange="previewFile(this)">
        </label>

        <p id="fileName" class="file-name">Belum ada file dipilih</p>

        <img id="preview" class="preview-img" style="display:none;">

                <button type="submit" name="upload">Upload Bukti</button>
            </form>

            <a href="laporan.php">← Kembali ke Laporan</a>
        </div>

        <script>
function previewFile(input){
    const file = input.files[0];

    if(file){
        document.getElementById("fileName").innerText = "✔ " + file.name;

        const reader = new FileReader();
        reader.onload = function(e){
            const preview = document.getElementById("preview");
            preview.src = e.target.result;
            preview.style.display = "block";
        }
        reader.readAsDataURL(file);
    }
}
</script>

<?php if(isset($_GET['upload']) && $_GET['upload']=="success"){ ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
Swal.fire({
    icon: 'success',
    title: 'Upload Berhasil',
    text: 'Bukti pembayaran berhasil diunggah.',
    confirmButtonColor: '#dc2626',
    backdrop: false
});
</script>
<?php } ?>

</body>
</html>