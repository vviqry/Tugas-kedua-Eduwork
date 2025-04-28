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

// Ambil data keranjang dengan JOIN ke tabel products dan users untuk mendapatkan info seller
$cart_query = "
    SELECT c.*, p.nama_produk, p.harga, p.seller_id, u.nama AS seller_name, u.phone_number AS seller_phone
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    JOIN users u ON p.seller_id = u.id
    WHERE c.user_id = ?
    ORDER BY p.seller_id
";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();

// Kelompokkan item berdasarkan seller
$cart_items_by_seller = [];
$total_price = 0;
if ($cart_result->num_rows > 0) {
    while ($row = $cart_result->fetch_assoc()) {
        $seller_id = $row['seller_id'];
        if (!isset($cart_items_by_seller[$seller_id])) {
            $cart_items_by_seller[$seller_id] = [
                'seller_name' => $row['seller_name'],
                'seller_phone' => $row['seller_phone'],
                'items' => []
            ];
        }
        $cart_items_by_seller[$seller_id]['items'][] = $row;
        $subtotal = $row['harga'] * $row['quantity'];
        $total_price += $subtotal;
    }
} else {
    die("Keranjang Anda kosong.");
}
$stmt->close();

// Proses checkout jika tombol "Konfirmasi Pembayaran" diklik
$show_wa_buttons = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    // Mulai transaksi untuk memastikan data tersimpan dengan benar
    mysqli_begin_transaction($conn);

    try {
        // Simpan pesanan ke tabel orders menggunakan prepared statement
        $order_date = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total, order_date) VALUES (?, ?, ?)");
        $stmt->bind_param("ids", $user_id, $total_price, $order_date);
        $stmt->execute();
        $order_id = $conn->insert_id;
        $stmt->close();

        // Simpan detail pesanan ke tabel order_details
        $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($cart_items_by_seller as $seller_data) {
            foreach ($seller_data['items'] as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                $price = $item['harga'];
                $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
                $stmt->execute();
            }
        }
        $stmt->close();

        // Kosongkan keranjang
        $delete_cart_query = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($delete_cart_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // Commit transaksi
        mysqli_commit($conn);

        // Set flag untuk menampilkan tombol WhatsApp
        $show_wa_buttons = true;
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($conn);
        die("Gagal memproses pesanan: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Pasa Danguang-danguang</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            margin: 0;
            padding: 20px;
            background-image: url('https://images.unsplash.com/photo-1638774264622-6d573c90ea8b?q=80&w=2001&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
            background-size: cover;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .receipt-container {
            max-width: 500px;
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            border: 1px solid #ccc;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .receipt-header h1 {
            font-size: 24px;
            margin: 0;
            color: #2E8B57;
        }

        .receipt-header p {
            margin: 5px 0;
            font-size: 14px;
        }

        .receipt-body {
            margin: 10px 0;
        }

        .receipt-body table {
            width: 100%;
            border-collapse: collapse;
        }

        .receipt-body th, .receipt-body td {
            padding: 5px;
            text-align: left;
            font-size: 14px;
        }

        .receipt-body th {
            background: #4682B4;
            color: white;
        }

        .receipt-footer {
            border-top: 2px dashed #000;
            padding-top: 10px;
            text-align: right;
        }

        .receipt-footer .total {
            font-weight: bold;
            font-size: 16px;
        }

        .checkout-btn, .wa-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #2E8B57;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 20px;
            font-family: Arial, sans-serif;
            text-align: center;
            text-decoration: none;
        }

        .checkout-btn:hover, .wa-btn:hover {
            background: #256f44;
        }

        .back-btn {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #4682B4;
            text-decoration: none;
            font-family: Arial, sans-serif;
        }

        .back-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>Pasa Danguang-danguang</h1>
            <p>Struk Pembelian</p>
            <p>Tanggal: <?php echo date('d-m-Y H:i:s'); ?></p>
            <p>Kasir: User ID <?php echo $user_id; ?></p>
        </div>

        <div class="receipt-body">
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($cart_items_by_seller as $seller_data) {
                        foreach ($seller_data['items'] as $item) {
                            $subtotal = $item['harga'] * $item['quantity'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                                <td>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="receipt-footer">
            <p class="total">Grand Total: Rp <?php echo number_format($total_price, 0, ',', '.'); ?></p>
        </div>

        <?php if ($show_wa_buttons): ?>
            <?php foreach ($cart_items_by_seller as $seller_id => $seller_data): ?>
                <?php
                $order_details = "Hai " . htmlspecialchars($seller_data['seller_name']) . ",\nSaya ingin memesan:\n";
                $seller_total = 0;
                foreach ($seller_data['items'] as $item) {
                    $subtotal = $item['harga'] * $item['quantity'];
                    $seller_total += $subtotal;
                    $order_details .= "- " . htmlspecialchars($item['nama_produk']) . " (" . $item['quantity'] . "x) = Rp " . number_format($subtotal, 0, ',', '.') . "\n";
                }
                $order_details .= "Total: Rp " . number_format($seller_total, 0, ',', '.') . "\nTerima kasih!";
                $wa_link = "https://wa.me/" . htmlspecialchars($seller_data['seller_phone']) . "?text=" . urlencode($order_details);
                ?>
                <a href="<?php echo $wa_link; ?>" class="wa-btn" target="_blank">Konfirmasi Pembayaran ke <?php echo htmlspecialchars($seller_data['seller_name']); ?> via WhatsApp</a>
            <?php endforeach; ?>
            <a href="index.php" class="back-btn">Kembali ke Beranda</a>
        <?php else: ?>
            <form method="POST">
                <input type="hidden" name="grand_total" value="<?php echo $total_price; ?>">
                <button type="submit" name="checkout" class="checkout-btn">Konfirmasi Pembayaran</button>
            </form>
            <a href="cart.php" class="back-btn">Kembali ke Keranjang</a>
        <?php endif; ?>
    </div>
    <?php mysqli_close($conn); ?>
</body>
</html>