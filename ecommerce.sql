-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2025 at 09:52 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `user_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `product_id`, `quantity`, `user_id`, `seller_id`, `created_at`) VALUES
(52, 43, 1, 8, 1, '2025-04-27 03:58:57'),
(53, 30, 1, 8, 1, '2025-04-27 03:58:57'),
(54, 29, 1, 8, 1, '2025-04-27 03:58:57'),
(111, 45, 2, 1, 17, '2025-04-28 07:00:13'),
(112, 34, 1, 1, 1, '2025-04-28 07:13:06'),
(113, 32, 1, 1, 16, '2025-04-28 07:13:08');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `order_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total`, `order_date`) VALUES
(1, 6, 281000.00, '2025-04-26 05:06:54'),
(2, 12, 52000.00, '2025-04-26 05:12:05'),
(3, 12, 55000.00, '2025-04-26 12:33:47'),
(4, 12, 93000.00, '2025-04-26 17:14:58'),
(5, 12, 77000.00, '2025-04-26 18:22:06'),
(6, 12, 55000.00, '2025-04-26 18:35:55'),
(7, 13, 95000.00, '2025-04-27 02:23:01'),
(8, 8, 128000.00, '2025-04-27 04:10:30'),
(9, 6, 122000.00, '2025-04-28 04:11:51'),
(10, 6, 374000.00, '2025-04-28 05:45:16'),
(11, 6, 30000.00, '2025-04-28 05:46:21'),
(12, 6, 67000.00, '2025-04-28 05:57:02'),
(13, 6, 12000.00, '2025-04-28 05:57:27'),
(14, 1, 188000.00, '2025-04-28 06:02:51'),
(15, 1, 193000.00, '2025-04-28 08:29:20'),
(16, 1, 172000.00, '2025-04-28 08:38:39');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 29, 2, 18000.00),
(2, 1, 45, 5, 4000.00),
(3, 1, 31, 3, 55000.00),
(4, 1, 35, 1, 35000.00),
(5, 1, 30, 1, 12000.00),
(6, 1, 37, 1, 13000.00),
(7, 2, 30, 1, 12000.00),
(8, 2, 34, 2, 20000.00),
(9, 3, 31, 1, 55000.00),
(10, 4, 31, 1, 55000.00),
(11, 4, 29, 1, 18000.00),
(12, 4, 34, 1, 20000.00),
(13, 5, 36, 1, 10000.00),
(14, 5, 30, 1, 12000.00),
(15, 5, 34, 1, 20000.00),
(16, 5, 35, 1, 35000.00),
(17, 6, 30, 1, 12000.00),
(18, 6, 29, 1, 18000.00),
(19, 6, 34, 1, 20000.00),
(20, 6, 41, 1, 5000.00),
(21, 7, 31, 1, 55000.00),
(22, 7, 29, 1, 18000.00),
(23, 7, 38, 1, 18000.00),
(24, 7, 45, 1, 4000.00),
(25, 8, 30, 1, 12000.00),
(26, 8, 36, 1, 10000.00),
(27, 8, 32, 1, 28000.00),
(28, 8, 34, 3, 20000.00),
(29, 8, 29, 1, 18000.00),
(30, 9, 41, 2, 5000.00),
(31, 9, 44, 2, 7000.00),
(32, 9, 28, 1, 25000.00),
(33, 9, 31, 1, 55000.00),
(34, 9, 38, 1, 18000.00),
(35, 10, 31, 3, 55000.00),
(36, 10, 34, 4, 20000.00),
(37, 10, 38, 1, 18000.00),
(38, 10, 29, 4, 18000.00),
(39, 10, 44, 2, 7000.00),
(40, 10, 28, 1, 25000.00),
(41, 11, 29, 1, 18000.00),
(42, 11, 30, 1, 12000.00),
(43, 12, 41, 2, 5000.00),
(44, 12, 44, 3, 7000.00),
(45, 12, 38, 2, 18000.00),
(46, 13, 30, 1, 12000.00),
(47, 14, 44, 1, 7000.00),
(48, 14, 28, 1, 25000.00),
(49, 14, 30, 1, 12000.00),
(50, 14, 41, 2, 5000.00),
(51, 14, 31, 1, 55000.00),
(52, 14, 36, 1, 10000.00),
(53, 14, 38, 2, 18000.00),
(54, 14, 46, 1, 6000.00),
(55, 14, 37, 1, 13000.00),
(56, 14, 45, 1, 4000.00),
(57, 14, 43, 1, 10000.00),
(58, 15, 31, 1, 55000.00),
(59, 15, 38, 1, 18000.00),
(60, 15, 35, 3, 35000.00),
(61, 15, 47, 1, 15000.00),
(62, 16, 41, 1, 9000.00),
(63, 16, 31, 1, 55000.00),
(64, 16, 34, 1, 20000.00),
(65, 16, 30, 1, 12000.00),
(66, 16, 35, 1, 35000.00),
(67, 16, 32, 1, 28000.00),
(68, 16, 37, 1, 13000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `nama_produk` varchar(100) DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `kategori` enum('buah','sayur','minuman','bumbu') NOT NULL DEFAULT 'buah',
  `deskripsi` text DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `foto_produk` varchar(255) DEFAULT NULL,
  `seller_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `nama_produk`, `harga`, `kategori`, `deskripsi`, `stok`, `foto_produk`, `seller_id`) VALUES
(28, 'Mangga Harum Manis', 25000.00, 'buah', 'Mangga lokal dengan aroma harum yang menggoda dan rasa manis alami. Cocok untuk dimakan langsung atau dibuat jus segar.', NULL, 'mangga.jpg', 1),
(29, 'Jeruk Medan', 18000.00, 'buah', 'Jeruk berkulit tebal dengan rasa manis asam yang segar. Mengandung vitamin C tinggi untuk daya tahan tubuh.', NULL, 'jeruk.jpg', 16),
(30, 'Semangka Tanpa Biji', 12000.00, 'buah', 'Daging merah segar, manis, dan tanpa biji—lebih praktis dan nikmat disantap kapan saja. Ideal untuk cuaca panas.', NULL, 'semangka.jpg', 1),
(31, 'Anggur Merah Impor', 55000.00, 'buah', 'Buah anggur premium dari luar negeri dengan rasa manis dan kulit tipis. Cocok untuk camilan sehat atau hiasan dessert.', NULL, 'anggur.jpg', 1),
(32, 'Alpukat Mentega', 28000.00, 'buah', 'Daging buah kuning lembut seperti mentega. Cocok untuk jus, salad, atau dimakan dengan susu kental manis.', NULL, 'alpukat.jpg', 16),
(34, 'Pisang Cavendish', 20000.00, 'buah', 'Pisang impor dengan kulit kuning mulus dan rasa manis legit. Cocok untuk sarapan, camilan sehat, atau bahan smoothies.', NULL, 'pisang.jpg', 1),
(35, 'Apel Fuji', 35000.00, 'buah', 'Apel merah cerah dari Jepang dengan tekstur renyah dan rasa manis segar. Kaya serat dan cocok untuk diet sehat.', NULL, 'apel.jpg', 15),
(36, 'Pepaya California', 10000.00, 'buah', 'Pepaya berukuran sedang dengan daging buah merah oranye yang lembut dan manis. Baik untuk pencernaan dan anak-anak.', NULL, 'pepaya.jpg', 1),
(37, 'Nanas Madu Palembang', 13000.00, 'buah', 'Nanas manis tanpa rasa asam yang menyengat. Cocok untuk dibuat rujak, jus, atau campuran sambal.', NULL, 'nanas.jpg', 17),
(38, 'Melon Hijau', 18000.00, 'buah', 'Melon segar dengan daging buah hijau muda, manis dan juicy. Pas untuk disajikan dingin saat cuaca panas.', NULL, 'melon.jpg', 1),
(41, 'Bayam Hijau (Naik)', 9000.00, 'buah', 'Bayam segar dengan daun hijau cerah, kaya zat besi dan sangat baik untuk kesehatan darah. Cocok untuk sayur bening atau tumisan. Mak nyus', NULL, '1745335074_bayam.jpg', 1),
(42, 'Wortel Lokal', 8000.00, 'sayur', 'Wortel segar berwarna oranye cerah, kaya beta-karoten untuk kesehatan mata. Cocok untuk sup, jus, atau campuran sayur.', NULL, '1745382290_wortel.jpg', 18),
(43, 'Buncis Segar', 10000.00, 'sayur', 'Buncis hijau muda dengan tekstur renyah. Cocok untuk tumisan atau pelengkap capcay.', NULL, '1745382336_buncis.jpg', 18),
(44, 'Sawi putih', 7000.00, 'sayur', 'Sawi putih segar dan lembut, cocok untuk sup, tumisan, atau sayur lodeh. Tinggi serat dan vitamin A.', NULL, '1745382374_sawi.jpg', 1),
(45, 'Kangkung Air', 4000.00, 'sayur', 'Kangkung segar dengan batang panjang dan daun lebar. Favorit untuk ditumis pedas atau dijadikan lalapan.', NULL, '1745382410_kangkung.jpg', 17),
(46, 'Tomat Merah', 6000.00, 'sayur', 'Tomat lokal dengan warna merah cerah, kaya antioksidan dan vitamin C. Cocok untuk sambal, jus, atau masakan harian.', NULL, '1745382440_tomat.jpg', 1),
(47, 'Mangga Kuini (/kg)', 15000.00, 'buah', 'Mangga lokal beraroma harum khas, dengan rasa manis sedikit asam. Cocok untuk dimakan langsung atau dijadikan jus segar.', NULL, '1745719675_mango-4971095_1280.jpg', 19);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `role` enum('seller','buyer') NOT NULL DEFAULT 'buyer',
  `phone_number` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `foto_profil`, `role`, `phone_number`, `alamat`) VALUES
(1, 'Pak Ujang', 'pak ujang', '$2y$10$q67bUUZZRSKVZI0XM3XzA.jQXUYzcB6hfq9ythzKCCwcXjyLsW9sC', 'uploads/680edc340b993-1.jpg', 'seller', '6281390403669', ''),
(6, 'Admin', 'admin@contoh.com', '$2y$10$4Ndv7Vnd6LGvDdKWcue4segtdsPpfVQ0wo4Xw71fiQ8tDW6JRXN56', 'uploads/sawi.jpg', 'buyer', NULL, NULL),
(7, 'Vviqry', 'vviqry02@gmail.com', '$2y$10$DjYrf1bUrmcFn7XN9GAzjOqzpimdRlbu.O8pWIIxyyD.F0TvNoahK', NULL, 'buyer', NULL, NULL),
(8, 'Hkvkjvaj', 'hkvkjvaj', '$2y$10$m56wfEAe111s/E2YnuHSsObTX5wFx6HgufLJOh9rja1gLZqw7L/mi', NULL, 'buyer', NULL, NULL),
(9, 'Hkvkvyjagjgxhjvj', 'hkvkjvajgghxjhvj', '$2y$10$KQHRKxBXAQ01e1llrnQ1FOOlheY.kBqsFtsYNeQNbIsEBIupoTvK6', NULL, 'buyer', NULL, NULL),
(10, 'Vviqry', 'vviqry', '$2y$10$jwjfFdr/qThZYDTeGAxHHeQ8iLW52yi92PD1G2PAqD2.1B.O2Dspi', NULL, 'buyer', NULL, NULL),
(11, 'Walawe', 'walawe', '$2y$10$gAAUYh3M05hniA4AXEbBCO7w0AnMQsGOc/2ahSMsb/xvwapCnCXma', NULL, 'buyer', NULL, NULL),
(12, 'Kalikali', 'Kalikali', '$2y$10$yO0P2O2TDre1atkMjdbYS.vjjZcaWcemKvPS.T4bIfF4empHynaCa', 'uploads/680edb6ce807a-mango-4971095_1280.jpg', 'buyer', '628696971607', 'RHQ7+Q5P, Guguak VIII Koto, Kec. Guguak, Kabupaten Lima Puluh Kota, Sumatera Barat 26253'),
(13, 'Momok', 'momok', '$2y$10$/uYJ2eXcnLfBjhqiYd.2OepEMJGB4hrimC0TlBAPq632mkGHfjPpG', NULL, 'buyer', NULL, NULL),
(15, 'Ibu Siti', 'sitimart@gmail.com', '$2y$10$J1k2L3m4N5o6P7q8R9s0T1u2V3w4X5y6Z7a8B9c0D1e2F3g4H5i6J7k', NULL, 'seller', '6281234567890', NULL),
(16, 'Pak Budi', 'budistore@gmail.com', '$2y$10$K1l2M3n4O5p6Q7r8S9t0U1v2W3x4Y5z6A7b8C9d0E1f2G3h4I5j6K7l', NULL, 'seller', '6282345678901', NULL),
(17, 'Mbak Ani', 'anishop@gmail.com', '$2y$10$L1m2N3o4P5q6R7s8T9u0V1w2X3y4Z5a6B7c8D9e0F1g2H3i4J5k6L7m', NULL, 'seller', '6283456789012', NULL),
(18, 'Pak Dedi', 'dedifarm@gmail.com', '$2y$10$M1n2O3p4Q5r6S7t8U9v0W1x2Y3z4A5b6C7d8E9f0G1h2I3j4K5l6M7n', NULL, 'seller', '6284567890123', NULL),
(19, 'Ibu Rina', 'rinamarket@gmail.com', '$2y$10$N1o2P3q4R5s6T7u8V9w0X1y2Z3a4B5c6D7e8F9g0H1i2J3k4L5m6N7o', NULL, 'seller', '6285678901234', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
