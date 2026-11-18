-- ============================================================
-- DATABASE FOODFINDER (foodfinder_db)
-- Proyek Rekayasa Perangkat Lunak - Pencarian & Rekomendasi Tempat Makan
-- ============================================================

CREATE DATABASE IF NOT EXISTS `foodfinder_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `foodfinder_db`;

-- Drop tables if exists in correct foreign key order
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `menus`;
DROP TABLE IF EXISTS `restaurants`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- 1. Table structure for `users`
-- ------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. Table structure for `categories`
-- ------------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `icon` VARCHAR(100) NOT NULL DEFAULT 'fa-utensils',
  `image` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. Table structure for `restaurants`
-- ------------------------------------------------------------
CREATE TABLE `restaurants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `food_type` VARCHAR(100) NOT NULL,
  `price_range` ENUM('$', '$$', '$$$', '$$$$') NOT NULL DEFAULT '$$',
  `price_min` INT NOT NULL DEFAULT 15000,
  `price_max` INT NOT NULL DEFAULT 50000,
  `rating` DECIMAL(2,1) NOT NULL DEFAULT 0.0,
  `total_reviews` INT NOT NULL DEFAULT 0,
  `address` TEXT NOT NULL,
  `city` VARCHAR(100) NOT NULL DEFAULT 'Jakarta Selatan',
  `gmaps_url` TEXT DEFAULT NULL,
  `opening_hours` VARCHAR(100) NOT NULL DEFAULT '10:00 - 22:00 WIB',
  `description` TEXT NOT NULL,
  `facilities` TEXT DEFAULT NULL,
  `atmosphere` VARCHAR(100) DEFAULT 'Santai',
  `image` VARCHAR(255) NOT NULL,
  `is_popular` TINYINT(1) NOT NULL DEFAULT 0,
  `is_recommended` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_restaurant_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. Table structure for `menus`
-- ------------------------------------------------------------
CREATE TABLE `menus` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `restaurant_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `price` INT NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_menu_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. Table structure for `reviews`
-- ------------------------------------------------------------
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `restaurant_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `rating` TINYINT(1) NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `comment` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_review_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. Table structure for `favorites`
-- ------------------------------------------------------------
CREATE TABLE `favorites` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `restaurant_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_restaurant_unique` (`user_id`, `restaurant_id`),
  CONSTRAINT `fk_favorite_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_favorite_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DUMMY / SEED DATA
-- Catatan Demo: Data contoh untuk pengujian dan evaluasi aplikasi FoodFinder
-- ============================================================

-- Data Pengguna (Password: 'admin123' untuk admin, 'user123' untuk user biasa)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `avatar`) VALUES
(1, 'Administrator FoodFinder', 'admin@foodfinder.com', '$2y$10$Uvp1.Y9ddgW.TABn6WlRM.tmxXDMvFl4be2g.paS.C9SVQM2dGGOK', 'admin', 'admin-avatar.png'),
(2, 'Anisa Rahmawati', 'user@foodfinder.com', '$2y$10$dobjQDJRyMwRPO/bo/nxLOZaCN/L2I5K.yLzU1ENEv56NljkS68n.', 'user', 'user-avatar.png'),
(3, 'Budi Santoso', 'budi@gmail.com', '$2y$10$dobjQDJRyMwRPO/bo/nxLOZaCN/L2I5K.yLzU1ENEv56NljkS68n.', 'user', 'user-avatar-2.png');

-- Data Kategori Makanan
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `image`, `description`) VALUES
(1, 'Makanan Berat', 'makanan-berat', 'fa-utensils', 'cat-berat.jpg', 'Pilihan hidangan utama yang mengenyangkan untuk makan siang dan malam.'),
(2, 'Kafe & Kopi', 'kafe-dan-kopi', 'fa-mug-hot', 'cat-kafe.jpg', 'Tempat ngopi santai dengan aneka racikan kopi, teh, dan suasana nyaman untuk WFC.'),
(3, 'Makanan Tradisional', 'makanan-tradisional', 'fa-bowl-rice', 'cat-tradisional.jpg', 'Kuliner khas nusantara kaya rempah dan cita rasa otentik warisan leluhur.'),
(4, 'Makanan Cepat Saji', 'makanan-cepat-saji', 'fa-burger', 'cat-fastfood.jpg', 'Menu cepat saji lezat seperti burger, fried chicken, dan kentang goreng krispi.'),
(5, 'Camilan & Snack', 'camilan-dan-snack', 'fa-cookie-bite', 'cat-snack.jpg', 'Gorengan, martabak, pastry dan kudapan ringan peneman aktivitas santai.'),
(6, 'Dessert & Manisan', 'dessert-dan-manisan', 'fa-ice-cream', 'cat-dessert.jpg', 'Es krim, gelato, cake lembut, dan makanan penutup manis pelepas dahaga.'),
(7, 'Minuman Segar', 'minuman-segar', 'fa-wine-glass', 'cat-minuman.jpg', 'Jus buah asli, boba drink, smoothies dingin segar beraneka rasa.'),
(8, 'Makanan Asia & Jepang', 'makanan-asia-dan-jepang', 'fa-bowl-food', 'cat-asia.jpg', 'Ramen hangat, sushi segar, dimsum, dan masakan khas Asia timur.');

-- Data Restoran Demo
INSERT INTO `restaurants` (`id`, `category_id`, `name`, `slug`, `food_type`, `price_range`, `price_min`, `price_max`, `rating`, `total_reviews`, `address`, `city`, `gmaps_url`, `opening_hours`, `description`, `facilities`, `atmosphere`, `image`, `is_popular`, `is_recommended`, `status`) VALUES
(1, 3, 'Bebek Goreng H. Slamet Asli', 'bebek-goreng-h-slamet-asli', 'Bebek & Ayam Tradisional', '$$', 25000, 50000, 4.8, 3, 'Jl. Radio Dalam Raya No. 12, Kebayoran Baru', 'Jakarta Selatan', 'https://maps.google.com/?q=Bebek+Goreng+H+Slamet+Radio+Dalam', '10:00 - 21:30 WIB', 'Menyajikan bebek goreng empuk dengan bumbu meresap hingga ke tulang dan sambal korek pedas gurih yang legendaris.', 'WiFi, AC, Parkir Mobil, Parkir Motor, Mushola, Area Merokok', 'Keluarga', 'resto-bebek.jpg', 1, 1, 'published'),

(2, 2, 'Senja Kopi & Creative Space', 'senja-kopi-creative-space', 'Kopi Spesialti & Pastry', '$$', 20000, 55000, 4.7, 3, 'Jl. Tebet Timur Dalam Raya No. 45', 'Jakarta Selatan', 'https://maps.google.com/?q=Senja+Kopi+Tebet', '08:00 - 23:00 WIB', 'Kafe berkonsep industrial minimalis dengan pencahayaan hangat, playlist indie yang menenangkan, serta stopkontak melimpah untuk mahasiswa dan pekerja remote.', 'WiFi Kencang, Stopkontak Tiap Meja, AC Dingin, Area Outdoor, Ruang Rapat', 'Cocok Tugas / WFC', 'resto-kafe.jpg', 1, 1, 'published'),

(3, 8, 'Ramen Master & Izakaya Bar', 'ramen-master-izakaya-bar', 'Ramen Kuah Kental & Gyoza', '$$$', 38000, 85000, 4.9, 2, 'Jl. Senopati No. 88, Senayan', 'Jakarta Selatan', 'https://maps.google.com/?q=Ramen+Master+Senopati', '11:00 - 22:00 WIB', 'Menyajikan ramen kuah kaldu kaya rasa dengan mie kenyal buatan sendiri, telur tamago lembut, dan chashu yang lumer di mulut.', 'AC, WiFi, Free Ocha Refill, Pembayaran QRIS/Kartu, Kursi Bayi', 'Santai', 'resto-ramen.jpg', 1, 1, 'published'),

(4, 1, 'Ayam Geprek Sambal Korek Juara', 'ayam-geprek-sambal-korek-juara', 'Ayam Geprek & Nasi Kulit', '$', 15000, 30000, 4.6, 2, 'Jl. Margonda Raya No. 180 (Dekat Kampus)', 'Depok', 'https://maps.google.com/?q=Ayam+Geprek+Margonda', '09:00 - 22:00 WIB', 'Pilihan utama mahasiswa! Ayam crispy digeprek dengan cabai rawit segar level 1 sampai 10. Paket nasi dan es teh manis porsi jumbo sangat ramah kantong.', 'Parkir Motor Luas, Es Teh Jumbo, Takeaway Cepat, Sambal Ambil Sendiri', 'Santai', 'resto-geprek.jpg', 1, 0, 'published'),

(5, 6, 'Sweet Berry Gelato & Waffle', 'sweet-berry-gelato-waffle', 'Gelato Artisan & Crepes', '$$', 22000, 48000, 4.9, 2, 'Jl. Gandaria I No. 20', 'Jakarta Selatan', 'https://maps.google.com/?q=Sweet+Berry+Gelato+Gandaria', '10:00 - 22:00 WIB', 'Gelato artisan Italia dibuat segar tiap pagi dengan bahan alami premium, buah segar, dan pilihan cone renyah dengan suasana interior pink aesthetic.', 'AC Estetik, Spot Foto Instagrammable, WiFi, Musik Santai, Parkir', 'Romantis', 'resto-gelato.jpg', 1, 1, 'published'),

(6, 5, 'Martabak & Terang Bulan Legit 88', 'martabak-terang-bulan-legit-88', 'Martabak Manis & Telur Spesial', '$$', 25000, 65000, 4.7, 1, 'Jl. Fatmawati Raya No. 102', 'Jakarta Selatan', 'https://maps.google.com/?q=Martabak+Legit+Fatmawati', '16:00 - 24:00 WIB', 'Martabak manis tebal bersarang dengan butter Wijsman melimpah dan martabak telur renyah isi daging sapi cincang pilihan.', 'Takeaway Cepat, Antrean Tertib, Parkir, Pembayaran Cashless', 'Santai', 'resto-martabak.jpg', 0, 1, 'published'),

(7, 2, 'Ruang Temu Kopi & Eatery', 'ruang-temu-kopi-eatery', 'Manual Brew & Rice Bowl', '$', 15000, 40000, 4.5, 1, 'Jl. Kemang Timur No. 34', 'Jakarta Selatan', 'https://maps.google.com/?q=Ruang+Temu+Kopi+Kemang', '07:30 - 22:30 WIB', 'Kafe cozy bernuansa hijau alami dengan spot duduk lesehan dan beanbag yang nyaman untuk nongkrong bersama teman kuliah.', 'Outdoor Garden, WiFi, Bean Bag, Mushola, Ramah Hewan Peliharaan', 'Santai', 'resto-kafe2.jpg', 0, 0, 'published'),

(8, 4, 'Burger Barn & Smokehouse', 'burger-barn-smokehouse', 'Smash Burger & Crispy Fries', '$$$', 35000, 75000, 4.8, 1, 'Jl. Cipete Raya No. 50', 'Jakarta Selatan', 'https://maps.google.com/?q=Burger+Barn+Cipete', '11:00 - 22:00 WIB', 'Burger daging sapi Australia 100% dengan keju leleh cheddar, saus rahasia homemade, dan bun brioche lembut yang dipanggang mentega gurih.', 'AC, WiFi, Area Outdoor, Parkir Mobil, Pembayaran Digital', 'Keluarga', 'resto-burger.jpg', 1, 0, 'published'),

(9, 3, 'Soto Betawi H. Husein Manggarai', 'soto-betawi-h-husein-manggarai', 'Soto Daging Kuah Santan Susu', '$$', 30000, 50000, 4.8, 1, 'Jl. Padang Panjang No. 6B, Pasar Manggis', 'Jakarta Selatan', 'https://maps.google.com/?q=Soto+Betawi+Husein+Manggarai', '07:00 - 15:00 WIB', 'Legenda kuliner Jakarta dengan kuah soto perpaduan santan dan susu yang gurih creamy, potongan daging empuk, serta emping melinjo renyah.', 'Kipas Angin, Parkir Terbatas, Pelayanan Kilat, Meja Panjang Tradisional', 'Keluarga', 'resto-soto.jpg', 1, 1, 'published'),

(10, 7, 'Fresh Splash Juice & Smoothie Bar', 'fresh-splash-juice-smoothie-bar', 'Cold Pressed Juice & Fruit Bowls', '$', 12000, 28000, 4.6, 1, 'Jl. Panglima Polim V No. 14', 'Jakarta Selatan', 'https://maps.google.com/?q=Fresh+Splash+Panglima+Polim', '08:00 - 21:00 WIB', 'Pilihan sehat menyegarkan tanpa pemanis buatan! Menghidangkan jus cold-pressed buah segar, smoothie bowl dengan topping chia seed dan granola.', 'Grab and Go, Pembayaran QRIS, Tempat Duduk Bar, Ramah Lingkungan', 'Santai', 'resto-juice.jpg', 0, 0, 'published');

-- Data Menu Andalan Restoran
INSERT INTO `menus` (`restaurant_id`, `name`, `price`, `description`, `is_featured`) VALUES
(1, 'Bebek Goreng Paha Dada + Sambal Korek', 38000, 'Potongan bebek goreng empuk renyah disajikan dengan lalapan segar dan sambal korek khas Solo.', 1),
(1, 'Ayam Kampung Goreng Gurih', 32000, 'Ayam kampung berbumbu kuning gurih meresap dengan taburan kremesan renyah.', 1),
(1, 'Sayur Asem Segar Khas Jawa', 10000, 'Kuah asam manis segar dengan kacang tanah, jagung manis, dan labu siam.', 0),

(2, 'Senja Aren Latte', 24000, 'Espresso ganda dipadu susu creamy dan sirup gula aren organik premium.', 1),
(2, 'Croissant Almond Panggang', 28000, 'Pastry berlapis renyah dengan isian krim almond manis dan taburan almond panggang.', 1),
(2, 'Chicken Karaage Rice Bowl Sambal Matah', 35000, 'Nasi hangat dengan potongan ayam renyah berbumbu dan sambal matah Bali segar.', 1),

(3, 'Tori Paitan Ramen Spesial', 55000, 'Ramen kaldu ayam kental yang direbus selama 10 jam, disajikan dengan telur ajitsuke dan rebung.', 1),
(3, 'Spicy Miso Ramen Beef', 62000, 'Ramen berkuah pasta miso pedas beraroma minyak wijen dan irisan daging sapi empuk.', 1),
(3, 'Pan-fried Gyoza (5 pcs)', 28000, 'Dumpling isi ayam cincang dan sayuran dengan saus celup asam gurih khas jepang.', 1),

(4, 'Paket Ayam Geprek Juara + Nasi', 18000, 'Ayam crispy digeprek sambal korek pedas nampol, disajikan dengan nasi hangat dan timun.', 1),
(4, 'Kulit Ayam Krispi Pedas', 12000, 'Kulit ayam goreng ekstra renyah bertabur bumbu pedas gurih nagih.', 1),
(4, 'Es Teh Manis Jumbo', 5000, 'Es teh manis segar ukuran jumbo pelepas dahaga dari sambal pedas.', 0),

(5, 'Double Scoop Gelato Pistachio & Strawberry', 35000, 'Perpaduan gelato kacang pistachio panggang gurih dan sorbet stroberi asam segar.', 1),
(5, 'Belgian Waffle with Vanilla Gelato', 38000, 'Waffle Belgia renyah di luar lembut di dalam dengan siraman sirup maple dan scoop gelato.', 1),

(6, 'Martabak Manis Cokelat Keju Wijsman', 55000, 'Adonan tebal lembut bertabur cokelat butir dan keju cheddar tebal dengan aroma butter khas.', 1),
(6, 'Martabak Telur Spesial Daging Sapi (4 Telur)', 60000, 'Kulit martabak renyah dengan isian telur bebek, daun bawang, dan daging sapi bumbu kari.', 1),

(7, 'Japanese Iced Drip Coffee', 22000, 'Kopi seduh manual beans Gayo diseduh langsung di atas es batu untuk rasa fruity yang bersih.', 1),
(7, 'Toast Roti Panggang Srikaya Butter', 18000, 'Roti gandum bakar dengan selai srikaya buatan rumahan dan potongan mentega dingin.', 1),

(8, 'The Classic Double Cheeseburger', 52000, 'Dua lembar patty sapi smashed, keju Amerika dobel, acar timun, dan saus burger spesial.', 1),
(8, 'Truffle Parmesan Fries', 28000, 'Kentang goreng renyah dengan sentuhan minyak truffle wangi dan parutan keju parmesan.', 1),

(9, 'Soto Daging Betawi Campur', 45000, 'Irisan daging sapi empuk dan babat dalam kuah santan susu berbumbu rempah harum.', 1),
(9, 'Perkedel Kentang Gurih', 6000, 'Perkedel kentang tebal dengan paduan daun seledri dan daging cincang.', 0),

(10, 'Green Detox Cold Pressed Juice', 22000, 'Kombinasi bayam horenso, apel hijau, nanas madu, timun, dan perasan jeruk nipis.', 1),
(10, 'Dragon Fruit Acai Smoothie Bowl', 28000, 'Smoothie buah naga merah kental dengan topping irisan pisang, chia seeds, dan granola madu.', 1);

-- Data Ulasan Pengguna
INSERT INTO `reviews` (`restaurant_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 2, 5, 'Bebek gorengnya benar-benar juara! Dagingnya empuk banget dan sambal koreknya sangat nampol. Tempatnya juga bersih dan luas untuk keluarga.', '2026-09-15 12:30:00'),
(1, 3, 5, 'Pelayanan cepat dan ramah. Sambalnya bikin keringetan tapi nagih abis. Parkirannya luas banget di Radio Dalam.', '2026-09-20 18:45:00'),
(2, 2, 5, 'Tempat favorit buat ngerjain tugas kampus atau skripsi! WiFi kencang, banyak colokan, kopi susunya pas tidak terlalu manis.', '2026-09-25 14:10:00'),
(2, 3, 4, 'Suasananya tenang dan estetik. Croissant almondnya enak banget pas masih hangat. Sangat direkomendasikan buat WFC.', '2026-10-02 16:20:00'),
(3, 2, 5, 'Kuah ramennya creamy dan gurih banget, tidak bikin eneg. Tamagonya lumer di mulut. Pasti bakal balik lagi ke sini!', '2026-10-05 19:15:00'),
(4, 2, 4, 'Harga sangat bersahabat buat mahasiswa. Sambalnya pedas pol dan es tehnya benar-benar ukuran jumbo. Mantap!', '2026-10-08 13:00:00'),
(5, 2, 5, 'Gelatonya authentic banget! Rasa pistachionya nendang. Tempatnya juga gemas serba pastel, cocok buat foto-foto.', '2026-10-10 15:40:00'),
(6, 3, 5, 'Martabak manisnya legit tidak pelit topping. Kejunya tebal dan rotinya empuk bersarang sampai besok paginya.', '2026-10-11 20:10:00'),
(8, 2, 5, 'Smash burgernya juicy banget, pinggirannya crispy gurih! Bun-nya lembut wangi butter. Salah satu burger terbaik di Jaksel.', '2026-10-12 18:30:00'),
(9, 3, 5, 'Kuah sotonya wangi rempah dan tidak eneg berkat perpaduan santan susu. Dagingnya empuk sekali, porsi mengenyangkan.', '2026-10-13 11:20:00');

-- Data Favorit Demo
INSERT INTO `favorites` (`user_id`, `restaurant_id`) VALUES
(2, 1),
(2, 2),
(2, 5);
