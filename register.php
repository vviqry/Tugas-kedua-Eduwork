<?php
// Koneksi database
$conn = new mysqli("localhost", "root", "", "ecommerce");

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = isset($_POST['role']) ? $_POST['role'] : 'buyer'; // Default ke buyer
    $phone_number = trim($_POST['phone_number'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    // Validasi sederhana
    if (empty($nama) || empty($email) || empty($password) || empty($phone_number) || empty($alamat)) {
        $error = "Semua field wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error = "Password harus minimal 6 karakter.";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone_number)) {
        $error = "Nomor telepon harus berupa angka dan antara 10-15 digit.";
    } else {
        // Proses upload foto profil jika ada
        $foto_profil = null;
        if (!empty($_FILES['foto_profil']['name'])) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $foto_profil = time() . "_" . basename($_FILES["foto_profil"]["name"]);
            $target_file = $target_dir . $foto_profil;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($imageFileType, $allowed_types)) {
                $error = "Hanya file JPG, JPEG, PNG, atau GIF yang diperbolehkan!";
            } elseif ($_FILES["foto_profil"]["size"] > 5000000) {
                $error = "Ukuran file terlalu besar, maksimum 5MB!";
            } elseif (!move_uploaded_file($_FILES["foto_profil"]["tmp_name"], $target_file)) {
                $error = "Gagal mengunggah foto profil!";
                $foto_profil = null;
            }
        }

        if (empty($error)) {
            // Cek apakah email sudah terdaftar
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Email sudah terdaftar!";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert ke database
                $insert = $conn->prepare("INSERT INTO users (nama, email, password, foto_profil, role, phone_number, alamat) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insert->bind_param("sssssss", $nama, $email, $hashed_password, $foto_profil, $role, $phone_number, $alamat);
                if ($insert->execute()) {
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Gagal mendaftar. Silakan coba lagi.";
                }
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun</title>
    <style>

@media (max-width: 767px) {
    .form-container {
        width: 90%;
        margin-right: 5%;
        margin-left: 5%;
        padding: 20px;
    }
    input[type="text"], input[type="password"], input[type="file"], select, textarea {
        padding: 8px;
        margin: 5px 0 10px 0;
    }
}
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 100px 0;
            height: 100vh;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            background-image: url('https://images.unsplash.com/photo-1631592058858-a8c4b556df5b?q=80&w=2072&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.3);
            padding: 25px; /* Dikurangi dari 30px untuk mengurangi tinggi */
            border-radius: 10px;
            box-shadow: 0 0px 30px rgba(0, 0, 0, 0.5);
            width: 450px; /* Diperlebar dari 350px menjadi 450px */
            text-align: center;
            
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        h2 {
            color: #2E8B57;
            margin-bottom: 10px; /* Dikurangi dari 15px untuk mengurangi tinggi */
            font-size: 1.8em;
            font-weight: 600;
            text-shadow: 0 3px 4px rgba(0, 0, 0, 1);
        }
        p {
            color: #555;
            margin-bottom: 20px; /* Dikurangi dari 25px */
            font-size: 0.95em;
            text-shadow: 2px 3px 5px rgb(255, 255, 255);
        }
        label {
            display: block;
            text-align: left;
            color: black;
            font-size: 0.9em;
            margin-bottom: 5px;
            font-weight: 500;
        }
        input[type="text"], input[type="password"], input[type="file"], select, textarea {
            width: 100%;
            padding: 10px; /* Dikurangi dari 12px untuk mengurangi tinggi */
            margin: 6px 0 12px 0; /* Dikurangi dari 8px 0 15px 0 */
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1em;
            color: #333;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        input[type="text"]::placeholder, input[type="password"]::placeholder, textarea::placeholder {
            color: #999; /* Warna placeholder abu-abu muda seperti di login.php */
            font-style: italic;
        }
        input[type="text"]:focus, input[type="password"]:focus, input[type="file"]:focus, select:focus, textarea:focus {
            border-color: #2E8B57;
            box-shadow: 0 0 8px rgba(46, 139, 87, 0.7);
            outline: none;
        }
        textarea {
            resize: vertical;
            height: 60px; /* Dikurangi dari 80px untuk mengurangi tinggi */
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #2E8B57;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.1s ease;
        }
        button:hover {
            background-color: #256C43;
            transform: translateY(-2px);
        }
        button:active {
            transform: translateY(0);
        }
        .error {
            font-size: 0.9em;
            margin-top: 10px;
            color: #e74c3c;
            background-color: rgba(231, 76, 60, 0.1);
            padding: 8px;
            border-radius: 5px;
        }
        a {
            color: #2E8B57;
            text-decoration: none;
            font-weight: 500;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Daftar Akun Baru</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <label for="nama">Nama</label>
            <input type="text" id="nama" name="nama" placeholder="nama" required>

            <label for="email">Email</label>
            <input type="text" id="email" name="email" placeholder="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="password" required>

            <label for="foto_profil">Foto Profil (opsional)</label>
            <input type="file" id="foto_profil" name="foto_profil" accept="image/*">

            <label for="role">Pilih Peran</label>
            <select id="role" name="role">
                <option value="buyer">Buyer</option>
                <option value="seller">Seller</option>
            </select>

            <label for="phone_number">Nomor Telepon</label>
            <input type="text" id="phone_number" name="phone_number" placeholder="nomor telepon" pattern="[0-9]{10,15}" required>

            <label for="alamat">Alamat</label>
            <textarea id="alamat" name="alamat" placeholder="alamat" required></textarea>

            <button type="submit">Daftar</button>
        </form>
        <p style="background-color: orange; padding: 10px; border-radius: 5px;">Sudah punya akun? <a href="login.php" style="color: white; text-decoration: underline;">Login di sini</a></p>

    </div>
</body>
</html>