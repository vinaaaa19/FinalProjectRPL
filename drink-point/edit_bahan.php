<?php
session_start();
include "koneksi.php";

date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'pemilik' && $_SESSION['role'] != 'karyawan') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: stok_bahan.php");
    exit;
}

$id = $_GET['id'];

$data = mysqli_query($conn, "SELECT * FROM bahan WHERE id_bahan='$id'");
$row = mysqli_fetch_assoc($data);

if (!$row) {
    header("Location: stok_bahan.php?error=notfound");
    exit;
}

if (isset($_POST['simpan'])) {
    $nama = $_POST['nama_bahan'];
    $satuan = $_POST['satuan'];
    $stok = $_POST['stok'];

    if ($stok <= 5) {
        $status = "Menipis";
    } else {
        $status = "Aman";
    }

    $stok_lama = $row['stok'];
    $id_user = $_SESSION['id'];
    $tanggal_log = date('Y-m-d H:i:s');

    mysqli_query($conn, "
        UPDATE bahan SET
        nama_bahan='$nama',
        satuan='$satuan',
        stok='$stok',
        status='$status'
        WHERE id_bahan='$id'
    ");

    mysqli_query($conn, "
        INSERT INTO log_stok
        (id_bahan, id_user, stok_lama, stok_baru, tanggal, keterangan)
        VALUES
        ('$id', '$id_user', '$stok_lama', '$stok', '$tanggal_log', 'Perubahan stok bahan')
    ");

    header("Location: stok_bahan.php?success=edit");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Bahan - Drink Point</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff7f7;
        }

        .card {
            width: 520px;
            margin: 70px auto;
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
            display: block;
            font-weight: bold;
            margin-top: 18px;
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
            width: 100%;
            margin-top: 25px;
            padding: 14px;
            background: #d6001c;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #d6001c;
            text-decoration: none;
            font-weight: bold;
        }

        .info {
            background: #fff5f5;
            padding: 13px;
            border-radius: 10px;
            margin-top: 15px;
            color: #555;
            font-size: 14px;
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
    <h1>Edit Bahan</h1>

    <form method="POST" id="editBahanForm">
        <label>Nama Bahan</label>
        <input type="text" name="nama_bahan" value="<?php echo $row['nama_bahan']; ?>" required>

        <label>Satuan</label>
        <input type="text" name="satuan" value="<?php echo $row['satuan']; ?>" required>

        <label>Stok</label>
        <input type="number" name="stok" min="0" step="1" value="<?php echo $row['stok']; ?>" required>

        <div class="info">
            Jika stok 5 atau kurang, status otomatis menjadi <b>Menipis</b>.
        </div>

        <button type="button" onclick="openConfirmModal()" class="btn-submit">
            Simpan Perubahan
        </button>
    </form>

    <a href="stok_bahan.php" class="back">← Kembali ke Stok Bahan</a>
</div>

<div id="confirmModal" class="modal">
    <div class="modal-box">
        <h3>Konfirmasi Perubahan</h3>
        <p>Yakin ingin menyimpan perubahan stok bahan ini?</p>

        <div class="modal-actions">
            <button type="button" onclick="closeConfirmModal()" class="btn-cancel">Batal</button>
            <button type="submit" form="editBahanForm" name="simpan" class="btn-confirm">Simpan</button>
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

</body>
</html>