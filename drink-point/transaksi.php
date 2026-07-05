<?php
session_start();
include "koneksi.php";

date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

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

$user = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT * FROM users
        WHERE id='".$_SESSION['id']."'
    ")
);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* tambah ke keranjang */
if (isset($_POST['tambah_cart'])) {
    $id_minuman = $_POST['id_minuman'];
    $qty = $_POST['qty'];

    if (isset($_SESSION['cart'][$id_minuman])) {
        $_SESSION['cart'][$id_minuman] += $qty;
    } else {
        $_SESSION['cart'][$id_minuman] = $qty;
    }

    header("Location: transaksi.php");
    exit;
}

/* hapus item keranjang */
if (isset($_GET['hapus_cart'])) {
    $id = $_GET['hapus_cart'];
    unset($_SESSION['cart'][$id]);

    header("Location: transaksi.php");
    exit;
}

/* simpan transaksi */
if (isset($_POST['simpan_transaksi'])) {

    $id_user = $_SESSION['id'];
    $metode = $_POST['metode_pembayaran'];

    $uang_diterima = 0;
    $kembalian = 0;
    $bukti_pembayaran = null;
    $total = 0;

    if ($metode == "Tunai") {
        $uang_diterima = $_POST['uang_diterima'];
    }

    foreach ($_SESSION['cart'] as $id_minuman => $qty) {
        $minuman = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT * FROM minuman 
            WHERE id_minuman='$id_minuman'
        "));

        $subtotal = $minuman['harga'] * $qty;
        $total += $subtotal;
    }

    if ($metode == "Tunai") {
    $kembalian = $uang_diterima - $total;

    if ($uang_diterima < $total) {
        header("Location: transaksi.php?error=uang_kurang");
        exit;
    }
}

if (!empty($_FILES['bukti_pembayaran']['name'])) {
        if (!is_dir("uploads/bukti_pembayaran")) {
            mkdir("uploads/bukti_pembayaran", 0777, true);
        }

        $nama_file = time() . "_" . basename($_FILES['bukti_pembayaran']['name']);
        $tujuan = "uploads/bukti_pembayaran/" . $nama_file;

        if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $tujuan)) {
            $bukti_pembayaran = $nama_file;
        }
    }

    $tanggal_sekarang = date('Y-m-d H:i:s');

    mysqli_query($conn, "
        INSERT INTO transaksi 
        (tanggal, total, id_user, metode_pembayaran, bukti_pembayaran, uang_diterima, kembalian)
        VALUES
        ('$tanggal_sekarang', '$total', '$id_user', '$metode', '$bukti_pembayaran', '$uang_diterima', '$kembalian')
    ");

    $id_transaksi = mysqli_insert_id($conn);

    foreach ($_SESSION['cart'] as $id_minuman => $qty) {
        $minuman = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT * FROM minuman 
            WHERE id_minuman='$id_minuman'
        "));

        $subtotal = $minuman['harga'] * $qty;

        mysqli_query($conn, "
            INSERT INTO detail_transaksi
            (id_transaksi, id_minuman, qty, subtotal)
            VALUES
            ('$id_transaksi', '$id_minuman', '$qty', '$subtotal')
        ");

        mysqli_query($conn, "
            UPDATE minuman 
            SET stok = stok - $qty
            WHERE id_minuman='$id_minuman'
        ");
    }

    $_SESSION['cart'] = [];

    header("Location: transaksi.php?success=1");
    exit;
}

$cari = isset($_GET['cari']) ? $_GET['cari'] : '';

$minuman = mysqli_query($conn, "
    SELECT * FROM minuman
    WHERE status='Aktif'
    AND nama_minuman LIKE '%$cari%'
");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaksi Penjualan - Drink Point</title>
    <style>

        .modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.35);
    justify-content: center;
    align-items: center;
    z-index: 999;
}

.success-box{
    background:#d1fae5;
    color:#065f46;
    padding:15px;
    border-radius:12px;
    margin-bottom:20px;
    font-weight:bold;
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
.btn-logout {
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

.btn-logout {
    background: #d6001c;
    color: white;
}

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff7f7;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar{
            width:300px;
            background:linear-gradient(180deg,#e6001f,#b40018);
            color:white;
            padding:30px 25px;

            position:fixed;
            left:0;
            top:0;

            height:100vh;
            overflow-y:auto;
        }

        .content{
             margin-left:330px;
    width:calc(100% - 300px);

            padding:45px;
            background:white;

            height:100vh;
            overflow-y:auto;
        }

        .logo {
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 40px;
        }

        .menu-title {
            font-size: 13px;
            font-weight: bold;
            margin: 25px 0 12px;
        }

        .menu a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px 18px;
            border-radius: 12px;
            margin-bottom: 10px;
            font-size: 17px;
        }

        .menu a.active,
        .menu a:hover {
            background: rgba(255,255,255,0.25);
        }

        .menu {
            padding-bottom: 160px;
        }

        .logout-box {
            position: fixed;
            left: 25px;
            bottom: 25px;
            width: 240px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 15px;
            padding: 18px;
            color: white;
            text-decoration: none;
            z-index: 1000;
        }

        .logout-box div {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .logout-box small {
            opacity: 0.85;
        }

        .logout-box:hover {
            background: rgba(255,255,255,0.18);
        }

        .top {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 45px;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .profile-info {
            text-align: left;
            line-height: 1.3;
        }

        .profile-info b {
            font-size: 16px;
        }

        .profile-info span {
            color: #333;
            font-size: 14px;
        }

        .avatar-link {
            text-decoration: none;
        }

        .avatar-mini {
            width: 45px;
            height: 45px;
            background: #d6001c;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            overflow: hidden;
        }

        .notif {
            position: relative;
            font-size: 28px;
            cursor: pointer;
            display: inline-block;
            margin-right: 10px;
        }

        .notif-badge {
            position: absolute;
            top: -8px;
            right: -10px;
            background: #d6001c;
            color: white;
            border-radius: 50%;
            font-size: 11px;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .notif-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 38px;
            width: 270px;
            background: white;
            color: #333;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            padding: 15px;
            z-index: 99;
            font-size: 14px;
            text-align: left;
        }

        .notif-dropdown p {
            margin: 10px 0;
            padding: 10px;
            background: #fff5f5;
            border-radius: 8px;
        }

        .notif:hover .notif-dropdown {
            display: block;
        }

        h1 {
            font-size: 36px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }

        .layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
        }

        .card {
            background: white;
            border-radius: 18px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.07);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #ffe5e5;
            padding: 14px;
            text-align: left;
            font-size: 14px;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        input, select {
            padding: 11px;
            border: 1px solid #ddd;
            border-radius: 9px;
            width: 100%;
            box-sizing: border-box;
        }

        .qty {
            width: 60px;
            text-align: center;
        }

        .btn {
            background: #d6001c;
            color: white;
            border: none;
            padding: 11px 15px;
            border-radius: 9px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-small {
            padding: 8px 12px;
            background: #d6001c;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .hapus {
            color: #d6001c;
            border: 1px solid #d6001c;
            padding: 7px 10px;
            border-radius: 8px;
            text-decoration: none;
        }

        .total-box {
            margin-top: 20px;
            font-size: 18px;
        }

        .total-bayar {
            font-size: 28px;
            color: #d6001c;
            font-weight: bold;
            text-align: right;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            color: #777;
            font-size: 13px;
        }

        .footer span {
            color: #d6001c;
            font-weight: bold;
        }

        .search-box{
    display:flex;
    align-items:center;
    gap:12px;
    margin:20px 0;
}

.search-box input{
    width:350px;
    padding:12px 18px;
    border:1px solid #ddd;
    border-radius:12px;
    font-size:15px;
    outline:none;
    transition:.3s;
}

.search-box input:focus{
    border-color:#e60023;
    box-shadow:0 0 8px rgba(230,0,35,.15);
}

.btn-search{
    background:#e60023;
    color:white;
    border:none;
    padding:12px 18px;
    border-radius:12px;
    cursor:pointer;
    font-weight:bold;
    transition:.3s;
}

.btn-search:hover{
    background:#c8001d;
}

.btn-reset{
    text-decoration:none;
    color:#e60023;
    font-weight:bold;
}

    </style>
</head>
<body>

<div class="container">

    <div class="sidebar">
        <div class="logo">🥤 Drink Point</div>

        <div class="menu">

    <?php if ($_SESSION['role'] == 'pemilik') { ?>

        <a href="dashboard.php">🏠 Dashboard</a>

        <div class="menu-title">MENU UTAMA</div>

        <a href="data_minuman.php">🧋 Data Minuman</a>
        <a href="stok_bahan.php">📦 Stok Bahan</a>
       <a href="transaksi.php" class="active">🛒 Transaksi Penjualan</a>
        <a href="laporan.php">📊 Laporan Penjualan</a>
        <a href="validasi.php">✅ Validasi Laporan</a>
        <a href="akun_karyawan.php">👥 Akun Karyawan</a>
        <a href="profil_pemilik.php">👤 Profil Saya</a>

    <?php } else { ?>

    <a href="dashboard_karyawan.php">🏠 Dashboard</a>

    <div class="menu-title">MENU KARYAWAN</div>

    <a href="stok_minuman_karyawan.php">🧋 Data Minuman</a>
    <a href="stok_bahan_karyawan.php">📦 Stok Bahan</a>
    <a href="transaksi.php">🛒 Transaksi Penjualan</a>
    <a href="laporan.php">📊 Laporan Penjualan</a>
    <a href="profil_karyawan.php">👤 Profil Saya</a>

<?php } ?>

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
    <a href="<?php echo ($_SESSION['role']=='pemilik') ? 'stok_bahan.php' : 'stok_bahan_karyawan.php'; ?>"
       style="color:#333;text-decoration:none;">
        ⚠ Stok <?php echo $n['nama_bahan']; ?> menipis
        <br>
        <small>Klik untuk cek stok bahan</small>
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
    <a href="laporan.php" style="color:#333;text-decoration:none;">
        📷 Bukti pembayaran transaksi 
        #<?php echo $b['id_transaksi']; ?> belum diupload
        <br>
        <small>Klik untuk upload bukti pembayaran</small>
    </a>
</p>
<?php } ?>

<?php } else { ?>

    <p>Tidak ada notifikasi</p>

<?php } ?>
                    </div>
                </div>

              <a href="<?php echo ($_SESSION['role'] == 'karyawan') ? 'profil_karyawan.php' : 'profil_pemilik.php'; ?>" class="avatar-link">
                <div class="avatar-mini">
                    <?php if (!empty($user['foto'])) { ?>
                        <img src="./uploads/<?php echo $user['foto']; ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    <?php } else { ?>
                        <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                    <?php } ?>
                </div>
            </a>
                

                <div class="profile-info">
                    <b><?php echo $_SESSION['nama']; ?></b><br>
                    <span><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>

            </div>
        </div>

        <?php if(isset($_GET['success'])){ ?> 
        <div class="success-box">
            ✅ Transaksi berhasil disimpan
        </div>
        <?php } ?>

        <h1>Transaksi Penjualan</h1>
        <p class="subtitle">Catat transaksi penjualan minuman</p>

<form method="GET" class="search-box">

    <input type="text"
           name="cari"
           placeholder="Cari nama minuman..."
           value="<?php echo $cari; ?>">

    <button type="submit" class="btn-search">
        🔍 Cari
    </button>

</form>

        <div class="layout">

            <div class="card">
                <h3>Daftar Minuman</h3>

                <table>
                    <tr>
                        <th>Minuman</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>

                    <?php while ($row = mysqli_fetch_assoc($minuman)) { ?>
                    <tr>
                        <td><?php echo $row['nama_minuman']; ?></td>
                        <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo $row['stok']; ?> gelas</td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="id_minuman" value="<?php echo $row['id_minuman']; ?>">
                                <input class="qty" type="number" name="qty" value="1" min="1" max="<?php echo $row['stok']; ?>">
                        </td>
                        <td>
                                <button class="btn-small" type="submit" name="tambah_cart">+</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            </div>

            <div class="card">
                <h3>Rincian Transaksi</h3>

                <table>
                    <tr>
                        <th>Minuman</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>

                    <?php
                    $total_bayar = 0;
                    $total_item = 0;

                    foreach ($_SESSION['cart'] as $id_minuman => $qty) {
                        $m = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM minuman WHERE id_minuman='$id_minuman'"));
                        $subtotal = $m['harga'] * $qty;
                        $total_bayar += $subtotal;
                        $total_item += $qty;
                    ?>

                    <tr>
                        <td><?php echo $m['nama_minuman']; ?></td>
                        <td>Rp <?php echo number_format($m['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo $qty; ?> gelas</td>
                        <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                        <td>
                            <a class="hapus" href="transaksi.php?hapus_cart=<?php echo $id_minuman; ?>">Hapus</a>
                        </td>
                    </tr>

                    <?php } ?>
                </table>

                <div class="total-box">
                    <p>Subtotal (<?php echo $total_item; ?> item)</p>
                    <div class="total-bayar">
                        Rp <?php echo number_format($total_bayar, 0, ',', '.'); ?>
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <p>Metode Pembayaran</p>
                    <select name="metode_pembayaran" required>
                        <option value="Tunai">Tunai</option>
                        <option value="QRIS">QRIS</option>
                        <option value="Transfer">Transfer</option>
                    </select>

                    <div id="buktiBox" style="display:none; margin-top:15px;">
                        <p>Bukti Pembayaran</p>
                        <input type="file" name="bukti_pembayaran" accept="image/*">
                        <small style="color:#777;">Bukti QRIS/Transfer boleh diupload sekarang atau belakangan.</small>
                    </div>

                    <div id="uangBox">
                        <p>Uang Diterima</p>
                        <input type="number" name="uang_diterima" placeholder="Contoh: 50000" required>
                    </div>
                    <br><br>
                    <button class="btn" style="width:100%;" type="submit" name="simpan_transaksi">
                        💾 Simpan Transaksi
                    </button>
                </form>
            </div>

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

<script>
const metodeBayar = document.querySelector('select[name="metode_pembayaran"]');
const buktiBox = document.getElementById('buktiBox');
const uangBox = document.getElementById('uangBox');
const uangInput = document.querySelector('input[name="uang_diterima"]');

function aturPembayaran(){

    if(metodeBayar.value=="Tunai"){

        uangBox.style.display="block";
        buktiBox.style.display="none";

        uangInput.required=true;

    }else{

        uangBox.style.display="none";
        buktiBox.style.display="block";

        uangInput.required=false;
        uangInput.value="";
    }

}

metodeBayar.addEventListener("change",aturPembayaran);

aturPembayaran();
</script>

<?php if(isset($_GET['error']) && $_GET['error'] == 'uang_kurang'){ ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'error',
    title: 'Transaksi Gagal',
    text: 'Uang yang diterima kurang dari total pembayaran.',
    confirmButtonColor: '#dc2626',
    backdrop: false
});
</script>
<?php } ?>

</body>
</html>