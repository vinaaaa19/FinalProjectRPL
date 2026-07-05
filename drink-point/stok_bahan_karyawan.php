<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'karyawan') {
    header("Location: dashboard.php");
    exit;
}

$user = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM users WHERE id='".$_SESSION['id']."'
"));

$notif_stok = mysqli_query($conn, "SELECT * FROM bahan WHERE status='Menipis'");
$jumlah_notif = mysqli_num_rows($notif_stok);

$notif_minuman_habis = mysqli_query($conn, "
    SELECT * FROM minuman 
    WHERE stok <= 0
");

$jumlah_minuman_habis = mysqli_num_rows($notif_minuman_habis);

$jumlah_notif = $jumlah_notif + $jumlah_minuman_habis;

$notif_bukti = mysqli_query($conn, "
    SELECT * FROM transaksi 
    WHERE metode_pembayaran != 'Tunai'
    AND (bukti_pembayaran IS NULL OR bukti_pembayaran = '')
");

$jumlah_bukti = mysqli_num_rows($notif_bukti);
$jumlah_notif = $jumlah_notif + $jumlah_bukti;

$cari = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';

$data = mysqli_query($conn,"
SELECT *
FROM bahan
WHERE nama_bahan LIKE '%$cari%'
ORDER BY nama_bahan ASC
");

if (isset($_POST['update_stok'])) {
    $id_bahan = $_POST['id_bahan'];
    $stok_baru = $_POST['stok_baru'];

    $status = ($stok_baru <= 5) ? "Menipis" : "Aman";

    mysqli_query($conn, "
        UPDATE bahan 
        SET stok='$stok_baru', status='$status'
        WHERE id_bahan='$id_bahan'
    ");

    header("Location: stok_bahan_karyawan.php");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Stok Bahan - Drink Point</title>
    <style>
        body{margin:0;font-family:Arial,sans-serif;background:#fff7f7;}
        .container{display:flex;min-height:100vh;}
        .sidebar{
            width:300px;background:linear-gradient(180deg,#e6001f,#b40018);
            color:white;padding:30px 25px;position:fixed;left:0;top:0;
            height:100vh;overflow-y:auto;
        }
        .content{
            margin-left:330px;
    width:calc(100% - 300px);
            padding:45px;background:white;height:100vh;overflow-y:auto;
        }
        .logo{font-size:30px;font-weight:bold;margin-bottom:40px;}
        .menu-title{font-size:13px;font-weight:bold;margin:25px 0 12px;}
        .menu a{
            display:block;color:white;text-decoration:none;padding:15px 18px;
            border-radius:12px;margin-bottom:10px;font-size:17px;
        }
        .menu a.active,.menu a:hover{background:rgba(255,255,255,0.25);}
        .menu{padding-bottom:160px;}
        .logout-box{
            position:fixed;left:25px;bottom:25px;width:240px;
            background:rgba(255,255,255,0.08);
            border:1px solid rgba(255,255,255,0.35);
            border-radius:15px;padding:18px;color:white;text-decoration:none;
        }
        .logout-box div{font-size:22px;font-weight:bold;margin-bottom:6px;}

        .top{display:flex;justify-content:flex-end;align-items:center;margin-bottom:45px;}
        .profile{display:flex;align-items:center;gap:15px;}
        .profile-info{text-align:left;line-height:1.3;}
        .profile-info span{color:#333;font-size:14px;}
        .avatar-mini{
            width:45px;height:45px;background:#d6001c;color:white;border-radius:50%;
            display:flex;align-items:center;justify-content:center;font-weight:bold;overflow:hidden;
        }
        .avatar-mini img{width:100%;height:100%;object-fit:cover;border-radius:50%;}

        .notif{position:relative;font-size:28px;cursor:pointer;display:inline-block;margin-right:10px;}
        .notif-badge{
            position:absolute;top:-8px;right:-10px;background:#d6001c;color:white;
            border-radius:50%;font-size:11px;width:20px;height:20px;
            display:flex;align-items:center;justify-content:center;font-weight:bold;
        }
        .notif-dropdown{
            display:none;position:absolute;right:0;top:38px;width:270px;background:white;
            color:#333;border-radius:12px;box-shadow:0 8px 25px rgba(0,0,0,0.12);
            padding:15px;z-index:99;font-size:14px;text-align:left;
        }
        .notif-dropdown p{margin:10px 0;padding:10px;background:#fff5f5;border-radius:8px;}
        .notif:hover .notif-dropdown{display:block;}

        h1{font-size:38px;margin-bottom:8px;}
        .subtitle{color:#666;margin-bottom:35px;}
        .table-card{
            background:white;border-radius:18px;padding:28px;
            box-shadow:0 8px 25px rgba(0,0,0,0.07);margin-bottom:28px;
        }
        table{width:100%;border-collapse:collapse;}
        th{background:#ffe5e5;padding:16px;text-align:left;}
        td{padding:16px;border-bottom:1px solid #eee;}
        .badge{padding:7px 14px;border-radius:20px;font-weight:bold;font-size:14px;}
        .aman{background:#d1fae5;color:#087f3f;}
        .menipis{background:#ffedd5;color:#c2410c;}
        .footer{text-align:center;margin-top:50px;color:#777;font-size:13px;}
        .footer span{color:#d6001c;font-weight:bold;}

        .modal{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.35);
    justify-content:center;
    align-items:center;
    z-index:9999;
}

        .modal-box{
            background:white;width:360px;padding:28px;border-radius:18px;text-align:center;
            box-shadow:0 10px 30px rgba(0,0,0,0.18);
        }

        .modal-box input[type="number"]{
    width:90%;
    padding:12px;
    margin-top:15px;
    border:1px solid #ddd;
    border-radius:10px;
    font-size:15px;
}

        .btn-cancel,.btn-logout{
            flex:1;padding:12px;border-radius:10px;text-decoration:none;font-weight:bold;
            border:none;cursor:pointer;
        }
        .btn-cancel{background:#f3f4f6;color:#333;}
        .btn-logout{background:#d6001c;color:white;}
        .modal-actions{display:flex;gap:12px;margin-top:25px;}

        .search-box{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:20px;
}

.search-box input{
    width:350px;
    padding:12px 18px;
    border:1px solid #ddd;
    border-radius:12px;
    font-size:15px;
}

.search-box button,
.btn-search{
    background:#d6001c;
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:12px;
    font-weight:bold;
    cursor:pointer;
}
    </style>
</head>
<body>

<div class="container">

    <div class="sidebar">
        <div class="logo">🥤 Drink Point</div>

        <div class="menu">
            <a href="dashboard_karyawan.php">🏠 Dashboard</a>

            <div class="menu-title">MENU KARYAWAN</div>

            <a href="stok_minuman_karyawan.php">🧋 Data Minuman</a>
    <a href="stok_bahan_karyawan.php">📦 Stok Bahan</a>
    <a href="transaksi.php">🛒 Transaksi Penjualan</a>
    <a href="laporan.php">📊 Laporan Penjualan</a>
    <a href="profil_karyawan.php">👤 Profil Saya</a>
        </div>

        <a href="#" onclick="openLogoutModal()" class="logout-box">
            <div>🚪 Logout</div>
            <small>Keluar dari sistem</small>
        </a>
    </div>

    <div class="content">

        <div class="top">
            <div class="profile">

                <div class="notif">
                    🔔
                    <?php if ($jumlah_notif > 0) { ?>
                        <span class="notif-badge"><?php echo $jumlah_notif; ?></span>
                    <?php } ?>

                    <div class="notif-dropdown">
                        <b>Notifikasi</b>

                        <?php if ($jumlah_notif > 0) { ?>

<?php while ($n = mysqli_fetch_assoc($notif_stok)) { ?>
    <p>
        <a href="stok_bahan_karyawan.php"
           style="color:#333;text-decoration:none;display:block;">
            ⚠ Stok <b><?php echo $n['nama_bahan']; ?></b> menipis
            <br>
            <small style="color:#777;">Klik untuk cek stok bahan</small>
        </a>
    </p>
<?php } ?>

<?php while ($m = mysqli_fetch_assoc($notif_minuman_habis)) { ?>
    <p>
        <a href="stok_minuman_karyawan.php"
           style="color:#333;text-decoration:none;display:block;">
            🥤 Minuman <b><?php echo $m['nama_minuman']; ?></b> habis
            <br>
            <small style="color:#777;">Klik untuk melihat stok minuman</small>
        </a>
    </p>
<?php } ?>

<?php while ($b = mysqli_fetch_assoc($notif_bukti)) { ?>
    <p>
        <a href="laporan.php"
           style="color:#333;text-decoration:none;display:block;">
            📷 Bukti pembayaran transaksi
            #<?php echo $b['id_transaksi']; ?> belum diupload
            <br>
            <small style="color:#777;">Klik untuk membuka laporan penjualan</small>
        </a>
    </p>
<?php } ?>
                            <p>Tidak ada notifikasi</p>
                        <?php } ?>
                    </div>
                </div>

                <a href="profil_karyawan.php" class="avatar-link">
                    <div class="avatar-mini">
                        <?php if (!empty($user['foto'])) { ?>
                            <img src="./uploads/<?php echo $user['foto']; ?>">
                        <?php } else { ?>
                            <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                        <?php } ?>
                    </div>
                </a>

                <div class="profile-info">
                    <b><?php echo $_SESSION['nama']; ?></b><br>
                    <span>Karyawan</span>
                </div>

            </div>
        </div>

        <h1>Stok Bahan</h1>
        <p class="subtitle">Stok bahan yang tersedia</p>

        <form method="GET" class="search-box">
    <input type="text" name="cari" placeholder="🔍 Cari nama bahan..."
           value="<?php echo htmlspecialchars($cari); ?>">

    <button type="submit" class="btn-search">Cari</button>
</form>

        <div class="table-card">
            <table>
                <tr>
                    <th>No</th>
                    <th>Nama Bahan</th>
                    <th>Satuan</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

                <?php $no = 1; while ($row = mysqli_fetch_assoc($data)) { ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $row['nama_bahan']; ?></td>
                        <td><?php echo $row['satuan']; ?></td>
                        <td><?php echo $row['stok']; ?></td>
                        <td>
                            <?php if ($row['status'] == "Aman") { ?>
                                <span class="badge aman">🟢 Aman</span>
                            <?php } else { ?>
                                <span class="badge menipis">🟠 Menipis</span>
                            <?php } ?>
                        </td>

                        <td>
                            <button
                                type="button"
                                class="btn-search"
                                onclick="openStokModal(
                                    '<?php echo $row['id_bahan']; ?>',
                                    '<?php echo htmlspecialchars($row['nama_bahan'], ENT_QUOTES); ?>',
                                    '<?php echo $row['stok']; ?>'
                                )">
                                Update
                            </button>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <div class="footer">
            © 2026 <span>Drink Point</span>. Semua hak dilindungi.
        </div>

    </div>
</div>

<div id="logoutModal" class="modal">
    <div class="modal-box">
        <h3>Konfirmasi Logout</h3>
        <p>Yakin ingin keluar dari sistem?</p>

        <div class="modal-actions">
            <button onclick="closeLogoutModal()" class="btn-cancel">Batal</button>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
</div>

<script>
function openLogoutModal() {
    document.getElementById("logoutModal").style.display = "flex";
}

function closeLogoutModal() {
    document.getElementById("logoutModal").style.display = "none";
}
</script>

<div id="stokModal" class="modal">
    <div class="modal-box">
        <h3>Update Stok Bahan</h3>

        <form method="POST">
            <input type="hidden" name="id_bahan" id="id_bahan">

            <p id="nama_bahan_text"></p>

            <input type="number" name="stok_baru" id="stok_baru" required>

            <div class="modal-actions">
                <button type="button" onclick="closeStokModal()" class="btn-cancel">Batal</button>
                <button type="submit" name="update_stok" class="btn-logout">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStokModal(id, nama, stok){
    document.getElementById("id_bahan").value = id;
    document.getElementById("nama_bahan_text").innerText = "Update stok: " + nama;
    document.getElementById("stok_baru").value = stok;
    document.getElementById("stokModal").style.display = "flex";
}

function closeStokModal(){
    document.getElementById("stokModal").style.display = "none";
}
</script>

</body>
</html>