-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 31 Bulan Mei 2025 pada 17.34
-- Versi server: 10.4.17-MariaDB
-- Versi PHP: 8.0.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `login`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `artikel`
--

CREATE TABLE `artikel` (
  `ID_artikel` int(11) NOT NULL,
  `ID_user` int(11) DEFAULT NULL,
  `ID_komen` int(11) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `isi_artikel` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `tanggal_posting` datetime DEFAULT current_timestamp(),
  `status` enum('published','draft') DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `artikel`
--

INSERT INTO `artikel` (`ID_artikel`, `ID_user`, `ID_komen`, `judul`, `slug`, `isi_artikel`, `gambar`, `tanggal_posting`, `status`) VALUES
(1, NULL, NULL, 'Pentingnya Pola Makan Sehat', 'pentingnya-pola-makan-sehat', '<p>Pola makan sehat adalah kunci utama untuk menjaga kesehatan tubuh secara keseluruhan. Berikut adalah beberapa tips untuk menerapkan pola makan sehat:</p>\r\n\r\n<h3>1. Konsumsi Beragam Makanan</h3>\r\n<p>Pastikan menu makanan Anda beragam dan mencakup semua kelompok makanan seperti karbohidrat, protein, lemak sehat, serat, vitamin, dan mineral. Variasi makanan akan memastikan tubuh mendapatkan semua nutrisi yang dibutuhkan.</p>\r\n\r\n<h3>2. Perbanyak Konsumsi Sayur dan Buah</h3>\r\n<p>Sayuran dan buah-buahan kaya akan serat, vitamin, mineral, dan antioksidan yang penting untuk kesehatan. Usahakan untuk mengonsumsi minimal 5 porsi sayur dan buah setiap hari.</p>\r\n\r\n<h3>3. Pilih Karbohidrat Kompleks</h3>\r\n<p>Ganti karbohidrat olahan dengan karbohidrat kompleks seperti beras merah, roti gandum utuh, dan pasta gandum utuh. Karbohidrat kompleks mengandung lebih banyak serat dan nutrisi penting.</p>\r\n\r\n<h3>4. Batasi Konsumsi Gula dan Garam</h3>\r\n<p>Konsumsi gula dan garam berlebihan dapat meningkatkan risiko berbagai penyakit seperti diabetes, hipertensi, dan penyakit jantung. Kurangi makanan olahan yang biasanya tinggi gula dan garam.</p>\r\n\r\n<h3>5. Minum Air yang Cukup</h3>\r\n<p>Air membantu menjaga keseimbangan cairan dalam tubuh, mendukung fungsi organ, dan membantu mengeluarkan racun. Usahakan minum minimal 8 gelas air setiap hari.</p>\r\n\r\n<p>Dengan menerapkan pola makan sehat secara konsisten, Anda dapat meningkatkan energi, menjaga berat badan ideal, dan mengurangi risiko berbagai penyakit kronis seperti diabetes, hipertensi, dan penyakit jantung.</p>', 'pola-makan-sehat.jpg', '2025-05-30 10:09:29', 'published'),
(2, NULL, NULL, 'Tips Olahraga di Rumah', 'tips-olahraga-di-rumah', '<p>Olahraga di rumah bisa menjadi alternatif yang efektif untuk menjaga kebugaran tanpa perlu pergi ke gym. Berikut beberapa tips olahraga di rumah yang efektif:</p>\r\n\r\n<h3>1. Tentukan Jadwal Tetap</h3>\r\n<p>Buat jadwal olahraga yang tetap seperti Anda akan pergi ke gym. Konsistensi adalah kunci keberhasilan dalam program kebugaran apa pun.</p>\r\n\r\n<h3>2. Manfaatkan Peralatan Sederhana</h3>\r\n<p>Anda tidak memerlukan peralatan mahal untuk berolahraga di rumah. Matras yoga, resistance band, atau bahkan botol air sebagai pengganti dumbbell bisa menjadi alternatif yang baik.</p>\r\n\r\n<h3>3. Coba Latihan HIIT</h3>\r\n<p>High-Intensity Interval Training (HIIT) adalah latihan intensitas tinggi dalam waktu singkat yang efektif untuk membakar kalori dan meningkatkan kebugaran kardiovaskular.</p>\r\n\r\n<h3>4. Gunakan Video Tutorial</h3>\r\n<p>Manfaatkan tutorial olahraga online yang banyak tersedia secara gratis. Pilih jenis olahraga yang Anda sukai seperti yoga, pilates, atau latihan kekuatan.</p>\r\n\r\n<h3>5. Libatkan Seluruh Keluarga</h3>\r\n<p>Ajak anggota keluarga untuk berolahraga bersama. Selain menyehatkan, ini juga bisa menjadi kegiatan bonding yang menyenangkan.</p>\r\n\r\n<p>Olahraga di rumah tidak hanya menghemat waktu dan uang, tetapi juga memberikan fleksibilitas untuk beradaptasi dengan jadwal harian Anda. Yang terpenting adalah konsistensi dan progresivitas dalam latihan untuk mendapatkan hasil yang optimal.</p>', 'olahraga-rumah.jpg', '2025-05-30 10:09:29', 'published'),
(3, NULL, NULL, 'Mengelola Stres dengan Efektif', 'mengelola-stres-dengan-efektif', '<p>Stres adalah bagian normal dari kehidupan, tetapi jika tidak dikelola dengan baik, dapat berdampak negatif pada kesehatan fisik dan mental. Berikut adalah beberapa cara efektif untuk mengelola stres:</p>\r\n\r\n<h3>1. Praktek Mindfulness dan Meditasi</h3>\r\n<p>Meditasi dan mindfulness membantu menenangkan pikiran dan mengurangi kecemasan. Luangkan 10-15 menit setiap hari untuk duduk tenang dan fokus pada pernapasan Anda.</p>\r\n\r\n<h3>2. Olahraga Teratur</h3>\r\n<p>Aktivitas fisik melepaskan endorfin, hormon yang membantu meningkatkan suasana hati. Cukup 30 menit olahraga sedang setiap hari dapat membantu mengurangi stres secara signifikan.</p>\r\n\r\n<h3>3. Tidur yang Cukup</h3>\r\n<p>Kurang tidur dapat meningkatkan kadar stres. Usahakan tidur 7-8 jam setiap malam untuk membantu tubuh dan pikiran pulih dari aktivitas harian.</p>\r\n\r\n<h3>4. Menjaga Keseimbangan Kerja-Kehidupan</h3>\r\n<p>Tetapkan batas antara pekerjaan dan kehidupan pribadi. Luangkan waktu untuk hobi dan aktivitas yang Anda nikmati di luar pekerjaan.</p>\r\n\r\n<h3>5. Kembangkan Jaringan Dukungan</h3>\r\n<p>Berbicara dengan teman, keluarga, atau profesional kesehatan mental dapat membantu mengurangi beban stres. Jangan ragu untuk mencari dukungan ketika Anda membutuhkannya.</p>\r\n\r\n<p>Mengelola stres dengan efektif bukan hanya tentang menghilangkan stressor, tetapi juga tentang mengembangkan ketahanan dan strategi coping yang sehat. Dengan pendekatan proaktif terhadap manajemen stres, Anda dapat meningkatkan kesejahteraan secara keseluruhan dan mencegah dampak negatif stres pada kesehatan Anda.</p>', 'manajemen-stres.jpg', '2025-05-30 10:09:29', 'published'),
(4, NULL, NULL, 'Manfaat Tidur yang Cukup bagi Kesehatan', 'manfaat-tidur-yang-cukup-bagi-kesehatan', '<p>Tidur yang cukup dan berkualitas sangat penting untuk kesehatan secara keseluruhan. Berikut adalah beberapa manfaat tidur yang baik:</p>\r\n\r\n<h3>1. Meningkatkan Fungsi Otak</h3>\r\n<p>Tidur yang cukup membantu meningkatkan konsentrasi, produktivitas, dan kinerja kognitif. Sebaliknya, kurang tidur dapat mengganggu fungsi otak, menurunkan konsentrasi dan kemampuan berpikir.</p>\r\n\r\n<h3>2. Menjaga Kesehatan Jantung</h3>\r\n<p>Tidur yang cukup membantu menjaga kesehatan jantung dengan menurunkan tekanan darah dan memberikan waktu bagi jantung untuk beristirahat. Kurang tidur kronis dikaitkan dengan peningkatan risiko penyakit jantung.</p>\r\n\r\n<h3>3. Memperkuat Sistem Kekebalan Tubuh</h3>\r\n<p>Selama tidur, tubuh memproduksi dan melepaskan sitokin, sejenis protein yang membantu melawan infeksi dan peradangan. Tidur yang cukup memperkuat pertahanan tubuh terhadap penyakit.</p>\r\n\r\n<h3>4. Mengendalikan Berat Badan</h3>\r\n<p>Kurang tidur dapat mengganggu keseimbangan hormon yang mengatur nafsu makan, menyebabkan peningkatan rasa lapar dan keinginan untuk mengonsumsi makanan tinggi kalori dan karbohidrat.</p>\r\n\r\n<h3>5. Meningkatkan Kesehatan Mental</h3>\r\n<p>Tidur yang baik sangat penting untuk kesehatan mental. Kurang tidur dikaitkan dengan depresi, kecemasan, dan gangguan mood lainnya.</p>\r\n\r\n<p>Para ahli kesehatan merekomendasikan tidur 7-9 jam per malam untuk orang dewasa. Membangun rutinitas tidur yang sehat, seperti tidur dan bangun pada waktu yang sama setiap hari, menghindari kafein dan alkohol sebelum tidur, dan menciptakan lingkungan tidur yang nyaman, dapat membantu meningkatkan kualitas tidur Anda.</p>', 'tidur-sehat.jpg', '2025-05-30 10:09:29', 'published'),
(5, NULL, NULL, 'Pentingnya Konsumsi Air Putih Setiap Hari', 'pentingnya-konsumsi-air-putih-setiap-hari', '<p>Air putih adalah komponen penting untuk menjaga kesehatan tubuh. Berikut adalah beberapa alasan mengapa Anda perlu memastikan konsumsi air putih yang cukup setiap hari:</p>\r\n\r\n<h3>1. Menjaga Keseimbangan Cairan Tubuh</h3>\r\n<p>Tubuh manusia terdiri dari sekitar 60% air. Air berperan dalam berbagai fungsi tubuh seperti pencernaan, penyerapan nutrisi, sirkulasi, dan pengaturan suhu tubuh.</p>\r\n\r\n<h3>2. Membantu Fungsi Ginjal</h3>\r\n<p>Air membantu ginjal membuang racun dari tubuh melalui urin. Konsumsi air yang cukup membantu mencegah pembentukan batu ginjal dan infeksi saluran kemih.</p>\r\n\r\n<h3>3. Meningkatkan Kinerja Fisik</h3>\r\n<p>Dehidrasi dapat menurunkan kinerja fisik dengan menyebabkan kelelahan, penurunan motivasi, dan peningkatan rasa lelah. Minum air yang cukup membantu menjaga performa optimal saat berolahraga.</p>\r\n\r\n<h3>4. Menjaga Kesehatan Kulit</h3>\r\n<p>Air membantu menjaga kelembaban kulit dan elastisitasnya. Konsumsi air yang cukup dapat membantu kulit terlihat lebih segar dan menurunkan risiko keriput.</p>\r\n\r\n<h3>5. Membantu Penurunan Berat Badan</h3>\r\n<p>Minum air sebelum makan dapat membantu mengurangi jumlah kalori yang dikonsumsi karena memberikan rasa kenyang. Air juga membantu metabolisme lemak.</p>\r\n\r\n<p>Kebutuhan air setiap orang berbeda-beda, tergantung pada berbagai faktor seperti aktivitas fisik, iklim, dan kondisi kesehatan. Namun, panduan umum adalah mengonsumsi sekitar 8 gelas (2 liter) air setiap hari. Perhatikan warna urin Anda sebagai indikator hidrasi - urin yang jernih atau kuning pucat menunjukkan hidrasi yang baik.</p>', 'air-putih.jpg', '2025-05-30 10:09:29', 'published'),
(6, NULL, NULL, 'Pentingnya Pola Makan Sehat', 'pentingnya-pola-makan-sehat', '<p>Pola makan sehat adalah kunci utama untuk menjaga kesehatan tubuh secara keseluruhan. Berikut adalah beberapa tips untuk menerapkan pola makan sehat:</p>\r\n\r\n<h3>1. Konsumsi Beragam Makanan</h3>\r\n<p>Pastikan menu makanan Anda beragam dan mencakup semua kelompok makanan seperti karbohidrat, protein, lemak sehat, serat, vitamin, dan mineral. Variasi makanan akan memastikan tubuh mendapatkan semua nutrisi yang dibutuhkan.</p>\r\n\r\n<h3>2. Perbanyak Konsumsi Sayur dan Buah</h3>\r\n<p>Sayuran dan buah-buahan kaya akan serat, vitamin, mineral, dan antioksidan yang penting untuk kesehatan. Usahakan untuk mengonsumsi minimal 5 porsi sayur dan buah setiap hari.</p>\r\n\r\n<h3>3. Pilih Karbohidrat Kompleks</h3>\r\n<p>Ganti karbohidrat olahan dengan karbohidrat kompleks seperti beras merah, roti gandum utuh, dan pasta gandum utuh. Karbohidrat kompleks mengandung lebih banyak serat dan nutrisi penting.</p>\r\n\r\n<h3>4. Batasi Konsumsi Gula dan Garam</h3>\r\n<p>Konsumsi gula dan garam berlebihan dapat meningkatkan risiko berbagai penyakit seperti diabetes, hipertensi, dan penyakit jantung. Kurangi makanan olahan yang biasanya tinggi gula dan garam.</p>\r\n\r\n<h3>5. Minum Air yang Cukup</h3>\r\n<p>Air membantu menjaga keseimbangan cairan dalam tubuh, mendukung fungsi organ, dan membantu mengeluarkan racun. Usahakan minum minimal 8 gelas air setiap hari.</p>\r\n\r\n<p>Dengan menerapkan pola makan sehat secara konsisten, Anda dapat meningkatkan energi, menjaga berat badan ideal, dan mengurangi risiko berbagai penyakit kronis seperti diabetes, hipertensi, dan penyakit jantung.</p>', 'pola-makan-sehat.jpg', '2025-05-30 10:14:25', 'published'),
(7, NULL, NULL, 'Tips Olahraga di Rumah', 'tips-olahraga-di-rumah', '<p>Olahraga di rumah bisa menjadi alternatif yang efektif untuk menjaga kebugaran tanpa perlu pergi ke gym. Berikut beberapa tips olahraga di rumah yang efektif:</p>\r\n\r\n<h3>1. Tentukan Jadwal Tetap</h3>\r\n<p>Buat jadwal olahraga yang tetap seperti Anda akan pergi ke gym. Konsistensi adalah kunci keberhasilan dalam program kebugaran apa pun.</p>\r\n\r\n<h3>2. Manfaatkan Peralatan Sederhana</h3>\r\n<p>Anda tidak memerlukan peralatan mahal untuk berolahraga di rumah. Matras yoga, resistance band, atau bahkan botol air sebagai pengganti dumbbell bisa menjadi alternatif yang baik.</p>\r\n\r\n<h3>3. Coba Latihan HIIT</h3>\r\n<p>High-Intensity Interval Training (HIIT) adalah latihan intensitas tinggi dalam waktu singkat yang efektif untuk membakar kalori dan meningkatkan kebugaran kardiovaskular.</p>\r\n\r\n<h3>4. Gunakan Video Tutorial</h3>\r\n<p>Manfaatkan tutorial olahraga online yang banyak tersedia secara gratis. Pilih jenis olahraga yang Anda sukai seperti yoga, pilates, atau latihan kekuatan.</p>\r\n\r\n<h3>5. Libatkan Seluruh Keluarga</h3>\r\n<p>Ajak anggota keluarga untuk berolahraga bersama. Selain menyehatkan, ini juga bisa menjadi kegiatan bonding yang menyenangkan.</p>\r\n\r\n<p>Olahraga di rumah tidak hanya menghemat waktu dan uang, tetapi juga memberikan fleksibilitas untuk beradaptasi dengan jadwal harian Anda. Yang terpenting adalah konsistensi dan progresivitas dalam latihan untuk mendapatkan hasil yang optimal.</p>', 'olahraga-rumah.jpg', '2025-05-30 10:14:25', 'published'),
(8, NULL, NULL, 'Mengelola Stres dengan Efektif', 'mengelola-stres-dengan-efektif', '<p>Stres adalah bagian normal dari kehidupan, tetapi jika tidak dikelola dengan baik, dapat berdampak negatif pada kesehatan fisik dan mental. Berikut adalah beberapa cara efektif untuk mengelola stres:</p>\r\n\r\n<h3>1. Praktek Mindfulness dan Meditasi</h3>\r\n<p>Meditasi dan mindfulness membantu menenangkan pikiran dan mengurangi kecemasan. Luangkan 10-15 menit setiap hari untuk duduk tenang dan fokus pada pernapasan Anda.</p>\r\n\r\n<h3>2. Olahraga Teratur</h3>\r\n<p>Aktivitas fisik melepaskan endorfin, hormon yang membantu meningkatkan suasana hati. Cukup 30 menit olahraga sedang setiap hari dapat membantu mengurangi stres secara signifikan.</p>\r\n\r\n<h3>3. Tidur yang Cukup</h3>\r\n<p>Kurang tidur dapat meningkatkan kadar stres. Usahakan tidur 7-8 jam setiap malam untuk membantu tubuh dan pikiran pulih dari aktivitas harian.</p>\r\n\r\n<h3>4. Menjaga Keseimbangan Kerja-Kehidupan</h3>\r\n<p>Tetapkan batas antara pekerjaan dan kehidupan pribadi. Luangkan waktu untuk hobi dan aktivitas yang Anda nikmati di luar pekerjaan.</p>\r\n\r\n<h3>5. Kembangkan Jaringan Dukungan</h3>\r\n<p>Berbicara dengan teman, keluarga, atau profesional kesehatan mental dapat membantu mengurangi beban stres. Jangan ragu untuk mencari dukungan ketika Anda membutuhkannya.</p>\r\n\r\n<p>Mengelola stres dengan efektif bukan hanya tentang menghilangkan stressor, tetapi juga tentang mengembangkan ketahanan dan strategi coping yang sehat. Dengan pendekatan proaktif terhadap manajemen stres, Anda dapat meningkatkan kesejahteraan secara keseluruhan dan mencegah dampak negatif stres pada kesehatan Anda.</p>', 'manajemen-stres.jpg', '2025-05-30 10:14:25', 'published'),
(9, NULL, NULL, 'Manfaat Tidur yang Cukup bagi Kesehatan', 'manfaat-tidur-yang-cukup-bagi-kesehatan', '<p>Tidur yang cukup dan berkualitas sangat penting untuk kesehatan secara keseluruhan. Berikut adalah beberapa manfaat tidur yang baik:</p>\r\n\r\n<h3>1. Meningkatkan Fungsi Otak</h3>\r\n<p>Tidur yang cukup membantu meningkatkan konsentrasi, produktivitas, dan kinerja kognitif. Sebaliknya, kurang tidur dapat mengganggu fungsi otak, menurunkan konsentrasi dan kemampuan berpikir.</p>\r\n\r\n<h3>2. Menjaga Kesehatan Jantung</h3>\r\n<p>Tidur yang cukup membantu menjaga kesehatan jantung dengan menurunkan tekanan darah dan memberikan waktu bagi jantung untuk beristirahat. Kurang tidur kronis dikaitkan dengan peningkatan risiko penyakit jantung.</p>\r\n\r\n<h3>3. Memperkuat Sistem Kekebalan Tubuh</h3>\r\n<p>Selama tidur, tubuh memproduksi dan melepaskan sitokin, sejenis protein yang membantu melawan infeksi dan peradangan. Tidur yang cukup memperkuat pertahanan tubuh terhadap penyakit.</p>\r\n\r\n<h3>4. Mengendalikan Berat Badan</h3>\r\n<p>Kurang tidur dapat mengganggu keseimbangan hormon yang mengatur nafsu makan, menyebabkan peningkatan rasa lapar dan keinginan untuk mengonsumsi makanan tinggi kalori dan karbohidrat.</p>\r\n\r\n<h3>5. Meningkatkan Kesehatan Mental</h3>\r\n<p>Tidur yang baik sangat penting untuk kesehatan mental. Kurang tidur dikaitkan dengan depresi, kecemasan, dan gangguan mood lainnya.</p>\r\n\r\n<p>Para ahli kesehatan merekomendasikan tidur 7-9 jam per malam untuk orang dewasa. Membangun rutinitas tidur yang sehat, seperti tidur dan bangun pada waktu yang sama setiap hari, menghindari kafein dan alkohol sebelum tidur, dan menciptakan lingkungan tidur yang nyaman, dapat membantu meningkatkan kualitas tidur Anda.</p>', 'tidur-sehat.jpg', '2025-05-30 10:14:25', 'published'),
(10, NULL, NULL, 'Pentingnya Konsumsi Air Putih Setiap Hari', 'pentingnya-konsumsi-air-putih-setiap-hari', '<p>Air putih adalah komponen penting untuk menjaga kesehatan tubuh. Berikut adalah beberapa alasan mengapa Anda perlu memastikan konsumsi air putih yang cukup setiap hari:</p>\r\n\r\n<h3>1. Menjaga Keseimbangan Cairan Tubuh</h3>\r\n<p>Tubuh manusia terdiri dari sekitar 60% air. Air berperan dalam berbagai fungsi tubuh seperti pencernaan, penyerapan nutrisi, sirkulasi, dan pengaturan suhu tubuh.</p>\r\n\r\n<h3>2. Membantu Fungsi Ginjal</h3>\r\n<p>Air membantu ginjal membuang racun dari tubuh melalui urin. Konsumsi air yang cukup membantu mencegah pembentukan batu ginjal dan infeksi saluran kemih.</p>\r\n\r\n<h3>3. Meningkatkan Kinerja Fisik</h3>\r\n<p>Dehidrasi dapat menurunkan kinerja fisik dengan menyebabkan kelelahan, penurunan motivasi, dan peningkatan rasa lelah. Minum air yang cukup membantu menjaga performa optimal saat berolahraga.</p>\r\n\r\n<h3>4. Menjaga Kesehatan Kulit</h3>\r\n<p>Air membantu menjaga kelembaban kulit dan elastisitasnya. Konsumsi air yang cukup dapat membantu kulit terlihat lebih segar dan menurunkan risiko keriput.</p>\r\n\r\n<h3>5. Membantu Penurunan Berat Badan</h3>\r\n<p>Minum air sebelum makan dapat membantu mengurangi jumlah kalori yang dikonsumsi karena memberikan rasa kenyang. Air juga membantu metabolisme lemak.</p>\r\n\r\n<p>Kebutuhan air setiap orang berbeda-beda, tergantung pada berbagai faktor seperti aktivitas fisik, iklim, dan kondisi kesehatan. Namun, panduan umum adalah mengonsumsi sekitar 8 gelas (2 liter) air setiap hari. Perhatikan warna urin Anda sebagai indikator hidrasi - urin yang jernih atau kuning pucat menunjukkan hidrasi yang baik.</p>', 'air-putih.jpg', '2025-05-30 10:14:25', 'published'),
(11, NULL, NULL, 'Pentingnya', 'pentingnya', '<p>qwertyuiobd</p>', '', '2025-05-30 17:08:44', 'draft');

-- --------------------------------------------------------

--
-- Struktur dari tabel `catalog`
--

CREATE TABLE `catalog` (
  `ID_katalog` int(11) NOT NULL,
  `Nama_barang` varchar(100) DEFAULT NULL,
  `Harga_barang` decimal(10,2) DEFAULT NULL,
  `Jenis_barang` varchar(50) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `ID_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `catalog`
--

INSERT INTO `catalog` (`ID_katalog`, `Nama_barang`, `Harga_barang`, `Jenis_barang`, `gambar`, `ID_user`) VALUES
(1, 'Paracetamol', '6000.00', 'Obat demam', NULL, 1),
(2, 'Ibuprofen', '40000.00', 'Obat pereda nyeri', NULL, 1),
(3, 'Vitamin C', '54000.00', 'Suplemen', NULL, 1),
(4, 'Amoxicillin', '35000.00', 'Antibiotik', NULL, 1),
(5, 'Asam Mefenamat', '8000.00', 'Obat pereda nyeri', NULL, 1),
(6, 'cinta', '122.00', '122', NULL, 1),
(7, 'cinta', '122.00', '122', NULL, 1),
(8, 'Amoxicillin', '10000.00', 'kapsul', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokter`
--

CREATE TABLE `dokter` (
  `ID_dokter` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Pengguna_role` varchar(20) DEFAULT NULL,
  `Spesialisasi` varchar(100) DEFAULT NULL,
  `Nomor_telepon` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `dokter`
--

INSERT INTO `dokter` (`ID_dokter`, `Username`, `Pengguna_role`, `Spesialisasi`, `Nomor_telepon`) VALUES
(1, 'nayla', 'dokter', 'sehat', '66666666'),
(2, 'lita', 'dokter', 'makan', '666666'),
(3, 'yia', 'dokter', 'as', 'sss'),
(4, 'kentang', 'dokter', 'menanam', '12345678');

-- --------------------------------------------------------

--
-- Struktur dari tabel `janji_temu`
--

CREATE TABLE `janji_temu` (
  `ID_janji_temu` int(11) NOT NULL,
  `ID_user` int(11) DEFAULT NULL,
  `ID_dokter` int(11) DEFAULT NULL,
  `Waktu` time DEFAULT NULL,
  `Tanggal` date DEFAULT NULL,
  `Status` enum('pending','dikonfirmasi','selesai','dibatalkan') DEFAULT 'pending',
  `keluhan` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `biaya` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `obat`
--

CREATE TABLE `obat` (
  `ID_obat` int(11) NOT NULL,
  `nama_obat` varchar(100) NOT NULL,
  `jenis_obat` varchar(50) NOT NULL,
  `harga_obat` decimal(10,2) NOT NULL,
  `stok_obat` int(11) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_kadaluarsa` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `obat`
--

INSERT INTO `obat` (`ID_obat`, `nama_obat`, `jenis_obat`, `harga_obat`, `stok_obat`, `deskripsi`, `tanggal_kadaluarsa`, `created_at`, `updated_at`) VALUES
(1, 'Paracetamol', 'Tablet', '6000.00', 100, 'Obat untuk mengurangi demam dan nyeri ringan', '2025-12-31', '2025-05-30 03:23:03', '2025-05-30 03:23:03'),
(2, 'Amoxicillin', 'Kapsul', '15000.00', 50, 'Antibiotik untuk infeksi bakteri', '2025-10-15', '2025-05-30 03:23:03', '2025-05-30 03:23:03'),
(3, 'OBH Combi', 'Sirup', '25000.00', 30, 'Obat batuk dan flu', '2025-08-20', '2025-05-30 03:23:03', '2025-05-30 03:23:03'),
(4, 'Antasida', 'Tablet', '8000.00', 75, 'Obat untuk mengatasi sakit maag', '2025-11-30', '2025-05-30 03:23:03', '2025-05-30 03:23:03'),
(5, 'Vitamin C', 'Tablet', '12000.00', 80, 'Suplemen vitamin C untuk daya tahan tubuh', '2026-01-15', '2025-05-30 03:23:03', '2025-05-30 03:23:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemesanan`
--

CREATE TABLE `pemesanan` (
  `ID_pemesanan` int(11) NOT NULL,
  `ID_user` int(11) NOT NULL,
  `nomor_pemesanan` varchar(50) NOT NULL,
  `tanggal_pemesanan` datetime DEFAULT current_timestamp(),
  `total_harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status_pemesanan` enum('pending','dikonfirmasi','diproses','dikirim','selesai','dibatalkan') DEFAULT 'pending',
  `metode_pembayaran` enum('tunai','transfer','e-wallet','kartu_kredit') DEFAULT NULL,
  `status_pembayaran` enum('belum_bayar','sudah_bayar','gagal') DEFAULT 'belum_bayar',
  `alamat_pengiriman` text NOT NULL,
  `nomor_telepon` varchar(15) NOT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rekam_medis`
--

CREATE TABLE `rekam_medis` (
  `ID_rkmmed` int(11) NOT NULL,
  `ID_user` int(11) NOT NULL,
  `ID_dokter` int(11) NOT NULL,
  `Diagnosa` text NOT NULL,
  `Tanggal` date NOT NULL,
  `Pengobatan` text NOT NULL,
  `keluhan` text NOT NULL,
  `tekanan_darah` varchar(20) DEFAULT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `tinggi_badan` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `rekam_medis`
--

INSERT INTO `rekam_medis` (`ID_rkmmed`, `ID_user`, `ID_dokter`, `Diagnosa`, `Tanggal`, `Pengobatan`, `keluhan`, `tekanan_darah`, `berat_badan`, `tinggi_badan`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Demam dan flu', '2025-05-30', 'Paracetamol 3x1 tablet, istirahat cukup', 'Demam tinggi, batuk, pilek, sakit kepala', '120/80', '65.50', '170.00', '2025-05-30 03:40:18', '2025-05-30 03:40:18'),
(2, 2, 2, 'Gastritis akut', '2025-05-29', 'Antasida 3x1 tablet sebelum makan, diet rendah asam', 'Nyeri ulu hati, mual, muntah setelah makan', '110/70', '58.00', '165.00', '2025-05-30 03:40:18', '2025-05-30 03:40:18'),
(3, 38, 2, '11', '2025-05-30', '11', '11', '120', '111.00', '111.00', '2025-05-30 04:02:36', '2025-05-30 04:02:36'),
(4, 38, 2, '11', '2025-05-30', '11', '11', '120', '111.00', '111.00', '2025-05-30 04:03:24', '2025-05-30 04:03:24'),
(5, 38, 2, '11', '2025-05-30', '11', '11', '120', '111.00', '111.00', '2025-05-30 04:04:28', '2025-05-30 04:04:28'),
(6, 13, 2, '11', '2025-05-30', '11', '11', '120', '111.00', '111.00', '2025-05-30 04:04:39', '2025-05-30 04:05:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `resep_obat`
--

CREATE TABLE `resep_obat` (
  `ID_resep` int(11) NOT NULL,
  `ID_rkmmed` int(11) NOT NULL,
  `ID_obat` int(11) NOT NULL,
  `jumlah_obat` int(11) NOT NULL,
  `dosis` varchar(100) NOT NULL,
  `instruksi` text NOT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_resep` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `resep_obat`
--

INSERT INTO `resep_obat` (`ID_resep`, `ID_rkmmed`, `ID_obat`, `jumlah_obat`, `dosis`, `instruksi`, `catatan`, `created_at`, `tanggal_resep`) VALUES
(1, 3, 2, 12, '12', '12', NULL, '2025-05-30 04:10:08', '2025-05-30 11:10:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_login`
--

CREATE TABLE `tb_login` (
  `ID_user` int(11) NOT NULL,
  `username` varchar(10) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(10) NOT NULL,
  `pengguna` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `tb_login`
--

INSERT INTO `tb_login` (`ID_user`, `username`, `email`, `password`, `pengguna`) VALUES
(1, 'admin', 'admin@gmail.com', '123456', 'admin'),
(13, 'yut', 'tre', 'tyre', 'user'),
(14, 'actuallyty', 'xyz.dam@gmail.com', '==========', 'admin'),
(15, 'actuallyty', 'tyara.angel22@smk.belajar.id', '=o', 'admin'),
(16, 'sdf', 'sadf', 'asdf', 'admin'),
(17, 'actuallyty', 'Tyara_Charlisa_ts7_24@student.smktelkom-sda.sch.id', 'uyj', 'admin'),
(18, 'w', 'jghff@', 'nb', 'user'),
(19, 'eds', '123@', '123', 'user'),
(20, 'root', 'xyz.dam@gmail.com', '', 'user'),
(21, 'root', 'xyz.dam@gmail.com', '', 'user'),
(22, 'root', 'xyz.dam@gmail.com', '', 'admin'),
(23, 'root', 'puriekitchen@gmail.com', '', 'admin'),
(24, 'root', 'tyara.angel22@smk.belajar.id', '', 'admin'),
(25, 'ASWER', 'tyara.angel22@smk.belajar.id', 'ASDFG', 'admin'),
(26, 'ASWER', 'tyara.angel22@smk.belajar.id', '$2y$10$zwY', 'admin'),
(27, 'moli', 'tyara.angel22@smk.belajar.id', '$2y$10$wKo', 'admin'),
(29, 'adi', 'tyara.angel22@smk.belajar.id', 'hai123', 'admin'),
(30, 'w', 'puriekitchen@gmail.com', 'kjn', 'user'),
(31, 'actuallyty', 'bnjk', 'bjkl', ''),
(32, 'actuallyty', '1234@email', '123456789', 'user'),
(33, 'huio', 'ghatax123@gmail.com', '1234567', 'user'),
(34, 'w', 'Tyara_Charlisa_ts7_24@student.smktelkom-sda.sch.id', 'kj', 'user'),
(35, 'w', 'Tyara_Charlisa_ts7_24@student.smktelkom-sda.sch.id', 'kj', 'user'),
(36, 'w', 'Tyara_Charlisa_ts7_24@student.smktelkom-sda.sch.id', 'kj', 'user'),
(37, 'w', 'Tyara_Charlisa_ts7_24@student.smktelkom-sda.sch.id', 'kj', 'user'),
(38, 'actuallyty', 'we', 'sdds', ''),
(39, 'actuallyty', 'qwwq', 'qwwq', 'admin'),
(40, '12345', 'qwer@gmail.com', '1234', 'user');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `artikel`
--
ALTER TABLE `artikel`
  ADD PRIMARY KEY (`ID_artikel`),
  ADD KEY `ID_user` (`ID_user`);

--
-- Indeks untuk tabel `catalog`
--
ALTER TABLE `catalog`
  ADD PRIMARY KEY (`ID_katalog`),
  ADD KEY `fk_catalog_user` (`ID_user`);

--
-- Indeks untuk tabel `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`ID_dokter`),
  ADD KEY `idx_spesialisasi` (`Spesialisasi`);

--
-- Indeks untuk tabel `janji_temu`
--
ALTER TABLE `janji_temu`
  ADD PRIMARY KEY (`ID_janji_temu`),
  ADD KEY `ID_dokter` (`ID_dokter`),
  ADD KEY `fk_janji_user` (`ID_user`);

--
-- Indeks untuk tabel `obat`
--
ALTER TABLE `obat`
  ADD PRIMARY KEY (`ID_obat`),
  ADD KEY `idx_nama_obat` (`nama_obat`),
  ADD KEY `idx_jenis_obat` (`jenis_obat`),
  ADD KEY `idx_stok` (`stok_obat`);

--
-- Indeks untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`ID_pemesanan`),
  ADD UNIQUE KEY `nomor_pemesanan` (`nomor_pemesanan`),
  ADD KEY `idx_user_pemesanan` (`ID_user`),
  ADD KEY `idx_nomor_pemesanan` (`nomor_pemesanan`),
  ADD KEY `idx_status` (`status_pemesanan`);

--
-- Indeks untuk tabel `rekam_medis`
--
ALTER TABLE `rekam_medis`
  ADD PRIMARY KEY (`ID_rkmmed`),
  ADD KEY `FK_rekam_medis_user` (`ID_user`),
  ADD KEY `FK_rekam_medis_dokter` (`ID_dokter`);

--
-- Indeks untuk tabel `resep_obat`
--
ALTER TABLE `resep_obat`
  ADD PRIMARY KEY (`ID_resep`),
  ADD KEY `ID_rkmmed` (`ID_rkmmed`),
  ADD KEY `fk_obat` (`ID_obat`);

--
-- Indeks untuk tabel `tb_login`
--
ALTER TABLE `tb_login`
  ADD PRIMARY KEY (`ID_user`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_pengguna` (`pengguna`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `artikel`
--
ALTER TABLE `artikel`
  MODIFY `ID_artikel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `catalog`
--
ALTER TABLE `catalog`
  MODIFY `ID_katalog` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `dokter`
--
ALTER TABLE `dokter`
  MODIFY `ID_dokter` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `obat`
--
ALTER TABLE `obat`
  MODIFY `ID_obat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `ID_pemesanan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `rekam_medis`
--
ALTER TABLE `rekam_medis`
  MODIFY `ID_rkmmed` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `resep_obat`
--
ALTER TABLE `resep_obat`
  MODIFY `ID_resep` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tb_login`
--
ALTER TABLE `tb_login`
  MODIFY `ID_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `artikel`
--
ALTER TABLE `artikel`
  ADD CONSTRAINT `fk_artikel_user` FOREIGN KEY (`ID_user`) REFERENCES `tb_login` (`ID_user`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `catalog`
--
ALTER TABLE `catalog`
  ADD CONSTRAINT `fk_catalog_user` FOREIGN KEY (`ID_user`) REFERENCES `tb_login` (`ID_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `janji_temu`
--
ALTER TABLE `janji_temu`
  ADD CONSTRAINT `fk_janji_dokter` FOREIGN KEY (`ID_dokter`) REFERENCES `dokter` (`ID_dokter`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_janji_user` FOREIGN KEY (`ID_user`) REFERENCES `tb_login` (`ID_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD CONSTRAINT `fk_pemesanan_user` FOREIGN KEY (`ID_user`) REFERENCES `tb_login` (`ID_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `rekam_medis`
--
ALTER TABLE `rekam_medis`
  ADD CONSTRAINT `fk_rekam_dokter` FOREIGN KEY (`ID_dokter`) REFERENCES `dokter` (`ID_dokter`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `resep_obat`
--
ALTER TABLE `resep_obat`
  ADD CONSTRAINT `fk_obat_resep` FOREIGN KEY (`ID_obat`) REFERENCES `obat` (`ID_obat`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rkmmed_resep` FOREIGN KEY (`ID_rkmmed`) REFERENCES `rekam_medis` (`ID_rkmmed`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
