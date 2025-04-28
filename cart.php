<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "ecommerce");
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id == 0) {
    header("Location: login.php");
    exit();
}

// Ambil role user
$user_query = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$user_role = $user['role'] ?? 'buyer';
$stmt->close();

// Ambil data keranjang, kelompokkan berdasarkan seller
$query = "
    SELECT c.product_id, c.quantity, p.nama_produk, p.harga, p.seller_id, u.nama AS seller_name, u.phone_number AS seller_phone
    FROM cart c
    JOIN products p ON c.product_id = p.id
    JOIN users u ON p.seller_id = u.id
    WHERE c.user_id = ?
    ORDER BY p.seller_id
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items_by_seller = [];
while ($row = $result->fetch_assoc()) {
    $seller_id = $row['seller_id'];
    if (!isset($cart_items_by_seller[$seller_id])) {
        $cart_items_by_seller[$seller_id] = [
            'seller_name' => $row['seller_name'],
            'seller_phone' => $row['seller_phone'],
            'items' => []
        ];
    }
    $cart_items_by_seller[$seller_id]['items'][] = $row;
}
$stmt->close();

// Proses hapus item dari keranjang
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $delete_query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $stmt->close();
    header("Location: cart.php");
    exit();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Pasa Danguang-danguang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            padding-top: 90px;
            background-image: url('https://images.unsplash.com/photo-1638774264622-6d573c90ea8b?q=80&w=2001&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
            background-size: cover;
            background-attachment: fixed;
        }
        .navbar-custom {
            background-color: rgba(255, 255, 255, 1);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            padding: 10px 0;
        }
        .navbar-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .page-title {
            text-align: center;
            color: #2E8B57;
            margin-bottom: 40px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
        }
        .seller-section {
            margin-bottom: 40px;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .seller-section h2 {
            color: #2E8B57;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #666; /* Tambahkan border tabel */
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #666; /* Border lebih gelap */
            color: #333; /* Warna teks lebih gelap */
        }
        th {
            background: #e0e0e0; /* Latar belakang header lebih kontras */
            color: #2E8B57; /* Warna teks header */
            font-weight: bold;
        }
        td {
            background: rgba(255, 255, 255, 0.95); /* Latar belakang sel lebih solid */
        }
        .quantity-controls a {
            display: inline-block;
            text-decoration: none;
            color: white;
            background: #2E8B57;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
        }
        .quantity-controls a:hover {
            background: #256f44;
        }
        .delete-btn {
            display: inline-block;
            text-decoration: none;
            color: white;
            background: #ff0000;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .delete-btn:hover {
            background: #cc0000;
        }
        .checkout-btn {
            display: block;
            text-align: center;
            margin-top: 20px;
            background: #2E8B57;
            color: white;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        .checkout-btn:hover {
            background: #256f44;
        }
        .back-btn {
            display: inline-block;
            text-decoration: none;
            color: white;
            background: #4682B4;
            padding: 8px 15px;
            border-radius: 5px;
        }
        .back-btn:hover {
            background: #3a6a94;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar-custom">
        <div class="navbar-content">
            <a href="index.php" style="color: #2E8B57; font-weight: bold; text-decoration: none; font-size: 24px;">Pasa Danguang-danguang</a>
            <a href="index.php" class="back-btn">Kembali ke Beranda</a>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">Keranjang Belanja</h1>

        <?php if (empty($cart_items_by_seller)): ?>
            <p style="text-align: center; background-color: black; color: white;">Keranjang Anda kosong.</p>
        <?php else: ?>
            <?php foreach ($cart_items_by_seller as $seller_id => $seller_data): ?>
                <div class="seller-section">
                    <h2>Penjual: <?php echo htmlspecialchars($seller_data['seller_name']); ?></h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            foreach ($seller_data['items'] as $item):
                                $subtotal = $item['harga'] * $item['quantity'];
                                $total += $subtotal;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                                    <td>
                                        <span class="quantity-controls">
                                            <a href="update_cart.php?action=decrease&product_id=<?php echo $item['product_id']; ?>">-</a>
                                            <?php echo $item['quantity']; ?>
                                            <a href="update_cart.php?action=increase&product_id=<?php echo $item['product_id']; ?>">+</a>
                                        </span>
                                    </td>
                                    <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                    <td>
                                        <a href="cart.php?action=remove&product_id=<?php echo $item['product_id']; ?>" class="delete-btn">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p style="font-weight: bold; margin-top: 10px;">Total: Rp <?php echo number_format($total, 0, ',', '.'); ?></p>
                </div>
            <?php endforeach; ?>
            <a href="checkout.php" class="checkout-btn">Konfirmasi Pembayaran</a>
        <?php endif; ?>
    </div>
</body>
</html>