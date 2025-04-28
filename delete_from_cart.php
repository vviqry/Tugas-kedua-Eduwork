<?php
session_start();

// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "ecommerce");
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil user_id dari sesi
$user_id = $_SESSION['user_id'] ?? 0;

// Validasi user_id
if ($user_id == 0) {
    die("Silakan login terlebih dahulu.");
}

// Periksa apakah id produk ada di URL
if (!isset($_GET['id'])) {
    die("Produk tidak ditemukan.");
}
$product_id = (int)$_GET['id'];

// Tentukan halaman asal (dari mana request berasal)
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'cart.php';
$redirect_page = (strpos($referer, 'checkout.php') !== false) ? 'checkout.php' : 'cart.php';

// Hapus seluruh jenis produk dari keranjang
$delete_query = "DELETE FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id'";
mysqli_query($conn, $delete_query);

// Tutup koneksi
mysqli_close($conn);

// Redirect kembali ke halaman asal
header("Location: $redirect_page");
exit();
?>