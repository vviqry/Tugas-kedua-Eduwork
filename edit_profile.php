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

// Ambil data user
$user_query = "SELECT nama, foto_profil, phone_number, alamat FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $phone_number = $_POST['phone_number'];
    $alamat = $_POST['alamat'];
    $foto_profil = $user['foto_profil'];

    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        // Validasi jenis file
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_type = mime_content_type($_FILES['foto_profil']['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            die("Error: Hanya file gambar (JPG, JPEG, PNG) yang diizinkan!");
        }

        $target_dir = "uploads/";
        $foto_profil = $target_dir . uniqid() . "-" . basename($_FILES["foto_profil"]["name"]);
        move_uploaded_file($_FILES["foto_profil"]["tmp_name"], $foto_profil);
    }

    $query = "UPDATE users SET nama = ?, foto_profil = ?, phone_number = ?, alamat = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $nama, $foto_profil, $phone_number, $alamat, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: url('https://images.unsplash.com/photo-1638774264622-6d573c90ea8b?q=80&w=2001&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
            background-size: cover;
            background-attachment: fixed;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        .form-container h2 {
            margin-bottom: 20px;
            color: #2E8B57;
        }
        .form-container label {
            display: block;
            text-align: left;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        .form-container input,
        .form-container textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-container input[type="file"] {
            padding: 3px;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .form-container button,
        .form-container a.button {
            width: 48%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            display: inline-block;
            text-align: center;
        }
        .form-container button {
            background: #2E8B57;
        }
        .form-container button:hover {
            background: #256f44;
        }
        .form-container a.button {
            background: #666;
        }
        .form-container a.button:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Profil</h2>
        <form method="POST" enctype="multipart/form-data">
            <label for="nama">Nama</label>
            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama'] ?? ''); ?>" required>

            <label for="phone_number">Nomor Telepon (contoh: 6281234567890)</label>
            <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" placeholder="Nomor Telepon (contoh: 6281234567890)">

            <label for="alamat">Alamat</label>
            <textarea id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat Anda"><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>

            <label for="foto_profil">Foto Profil</label>
            <input type="file" id="foto_profil" name="foto_profil">

            <div class="button-container">
                
                <a href="index.php" class="button">Kembali</a>
                <button type="submit">Simpan</button>
            </div>
        </form>
    </div>
</body>
</html>