<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "ecommerce");
if (!$conn) {
    die(json_encode(['success' => false, 'message' => "Koneksi gagal: " . mysqli_connect_error()]));
}

$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id == 0) {
    die(json_encode(['success' => false, 'message' => "User tidak login"]));
}

// Ambil product_id dari parameter GET
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id == 0) {
    die(json_encode(['success' => false, 'message' => "Produk tidak valid"]));
}

// Ambil data produk untuk mendapatkan seller_id
$product_query = "SELECT seller_id FROM products WHERE id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result();
$product = $product_result->fetch_assoc();
$stmt->close();

if (!$product) {
    die(json_encode(['success' => false, 'message' => "Produk tidak ditemukan"]));
}

$seller_id = $product['seller_id'];

// Cek apakah produk sudah ada di keranjang user
$check_query = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$check_result = $stmt->get_result();
$cart_item = $check_result->fetch_assoc();

if ($cart_item) {
    // Jika produk sudah ada di keranjang, tambah quantity
    $new_quantity = $cart_item['quantity'] + 1;
    $update_query = "UPDATE cart SET quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
    $stmt->execute();
} else {
    // Jika produk belum ada di keranjang, tambahkan baru
    $insert_query = "INSERT INTO cart (user_id, product_id, quantity, seller_id) VALUES (?, ?, 1, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iii", $user_id, $product_id, $seller_id);
    $stmt->execute();
}

$stmt->close();

// Hitung ulang total item di keranjang
$cart_query = "SELECT SUM(quantity) AS total_items FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();
$row = $cart_result->fetch_assoc();
$total_items = $row['total_items'] ?? 0;
$stmt->close();

mysqli_close($conn);

// Kirim response JSON
header('Content-Type: application/json');
echo json_encode(['success' => true, 'total_items' => $total_items]);
exit();
?>