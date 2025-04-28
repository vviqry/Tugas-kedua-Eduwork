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

// Periksa parameter
if (!isset($_GET['action']) || !isset($_GET['product_id'])) {
    die("Aksi atau produk tidak ditemukan.");
}

$action = $_GET['action'];
$product_id = (int)$_GET['product_id'];

// Tentukan halaman asal
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'cart.php';
$redirect_page = (strpos($referer, 'checkout.php') !== false) ? 'checkout.php' : 'cart.php';

// Ambil quantity saat ini
$check_query = "SELECT quantity FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    $row = mysqli_fetch_assoc($check_result);
    $current_quantity = $row['quantity'];

    if ($action == 'increase') {
        // Tambah quantity
        $new_quantity = $current_quantity + 1;
        $update_query = "UPDATE cart SET quantity = '$new_quantity' WHERE user_id = '$user_id' AND product_id = '$product_id'";
        mysqli_query($conn, $update_query);
    } elseif ($action == 'decrease') {
        // Kurangi quantity
        if ($current_quantity > 1) {
            $new_quantity = $current_quantity - 1;
            $update_query = "UPDATE cart SET quantity = '$new_quantity' WHERE user_id = '$user_id' AND product_id = '$product_id'";
            mysqli_query($conn, $update_query);
        } else {
            // Jika quantity = 1, hapus item
            $delete_query = "DELETE FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id'";
            mysqli_query($conn, $delete_query);
        }
    }
}

// Tutup koneksi
mysqli_close($conn);

// Redirect kembali ke halaman asal
header("Location: $redirect_page");
exit();
?>