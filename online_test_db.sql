-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 29 Haz 2026, 13:07:38
-- Sunucu sürümü: 5.7.36
-- PHP Sürümü: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `online_test_db`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(5, 'TYT', '2025-12-27 16:20:22'),
(6, 'AYT', '2025-12-27 16:20:23'),
(7, 'YDT', '2025-12-27 16:20:23'),
(8, 'LGS', '2025-12-27 16:20:23'),
(9, 'KPSS', '2025-12-27 16:20:23'),
(10, 'Genel', '2025-12-27 16:20:23');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `duration` int(11) NOT NULL DEFAULT '30',
  `is_active` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `category` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Genel'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `exams`
--

INSERT INTO `exams` (`id`, `category_id`, `title`, `slug`, `description`, `duration`, `is_active`, `created_at`, `category`) VALUES
(7, 5, 'tyt mat', '', NULL, 40, 1, '2025-12-27 16:21:02', 'Genel'),
(8, 6, ' türkçe', '', NULL, 40, 1, '2025-12-31 06:33:16', 'Genel'),
(9, 6, 'kimya', '', NULL, 40, 1, '2025-12-31 06:33:31', 'Genel'),
(10, 5, 'tarih', '', NULL, 40, 1, '2025-12-31 06:33:50', 'Genel'),
(11, 6, 'fizik', '', NULL, 40, 1, '2025-12-31 06:35:11', 'Genel'),
(12, 6, 'matematik', '', NULL, 50, 1, '2025-12-31 06:35:25', 'Genel');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_reply` text COLLATE utf8mb4_unicode_ci,
  `reply_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `message`, `image_path`, `admin_reply`, `reply_date`, `created_at`) VALUES
(1, 10, 'hocam buradaki hata nedir', 'uploads/feedback_1766398553_638.png', 'bu soruna daha sonra döneceğim', '2025-12-22 10:16:43', '2025-12-22 10:15:53'),
(2, 14, 'hocam ne zaman müsaitsiniz soru sormak istiyorum', NULL, 'Merhaba selin, şuan mesai saatleri dışında olmamdan sebebiyetle sorunu yarın alabilirim', '2025-12-27 15:07:33', '2025-12-24 06:23:20'),
(3, 15, 'bu cok ıyı bır sıte olmus', NULL, NULL, NULL, '2025-12-25 07:29:28');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `feedbacks`
--

INSERT INTO `feedbacks` (`id`, `user_id`, `message`, `created_at`) VALUES
(1, 10, 'bu sistemi yapandan Allah razı olsun eline emeğine sağlık.', '2025-12-20 15:21:08'),
(2, 12, 'bencede harika bir sistem bayıldımmm.', '2025-12-20 15:23:06');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `files`
--

INSERT INTO `files` (`id`, `title`, `file_path`, `file_type`, `file_size`, `uploaded_at`) VALUES
(1, '1.hafta ders notu', 'uploads/doc_69469cbdd758c.pdf', 'pdf', '80.31 KB', '2025-12-20 12:55:25'),
(2, '2.hafta ders notu', 'uploads/doc_6949168cf405b.pdf', 'pdf', '4736.68 KB', '2025-12-22 09:59:41');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `question_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_a` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_b` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_c` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_d` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correct_answer` enum('a','b','c','d') COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int(11) DEFAULT '10',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `questions`
--

INSERT INTO `questions` (`id`, `exam_id`, `question_text`, `image_path`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `points`, `created_at`) VALUES
(16, 7, '$\\sqrt{48} + \\sqrt{75} - \\sqrt{12}$ işleminin sonucu kaçtır?', NULL, '$7\\sqrt{3}$', '$6\\sqrt{3}$', '$8\\sqrt{3}$', '$9\\sqrt{3}$', 'a', 10, '2025-12-27 16:21:53'),
(17, 7, '12 + 3 * (8 - 2) / 2 işleminin sonucu kaçtır?', NULL, '21', '45', '15', '35', 'a', 10, '2025-12-27 16:22:41');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `results`
--

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `correct_count` int(11) DEFAULT '0',
  `wrong_count` int(11) DEFAULT '0',
  `completed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `results`
--

INSERT INTO `results` (`id`, `user_id`, `exam_id`, `score`, `correct_count`, `wrong_count`, `completed_at`, `created_at`) VALUES
(28, 10, 7, 0, 0, 2, '2025-12-27 16:23:42', '2025-12-27 16:23:42'),
(29, 10, 7, 100, 2, 0, '2025-12-27 16:30:47', '2025-12-27 16:30:47'),
(30, 10, 7, 0, 0, 2, '2026-01-02 14:12:26', '2026-01-02 14:12:26'),
(31, 16, 7, 0, 0, 2, '2026-01-02 14:18:16', '2026-01-02 14:18:16'),
(32, 17, 7, 0, 0, 2, '2026-04-08 17:40:06', '2026-04-08 17:40:06'),
(33, 18, 7, 0, 0, 2, '2026-06-27 15:06:27', '2026-06-27 15:06:27');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','student') COLLATE utf8mb4_unicode_ci DEFAULT 'student',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `school` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Belirtilmedi',
  `class_level` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Belirtilmedi',
  `profile_img` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default',
  `reset_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `avatar`, `created_at`, `school`, `class_level`, `profile_img`, `reset_code`, `avatar_path`) VALUES
(4, 'admin', 'admin@test.com', '$2y$10$ZtW6kNjzuHqTBxoKSBMPIu.HF.jFghVsObtSgqAcjaRKENX9S5SiK', 'admin', 'default.png', '2025-12-18 13:45:19', 'Belirtilmedi', 'Belirtilmedi', 'default', NULL, NULL),
(6, 'selin', 'selin@gmail.com', '$2y$10$W3eySLqz2SWmeZxSTs5sAeTsG0IoeLCRUeyft/7nOnP6.Zt./5GXe', 'student', 'default.png', '2025-12-19 12:44:37', 'Belirtilmedi', 'Belirtilmedi', 'default', NULL, NULL),
(7, 'ayşe nur', 'aysenur@gmail.com', 'aysenur', 'student', 'default.png', '2025-12-19 13:09:45', 'yunus emre lortaokulu', '7B', 'default', NULL, NULL),
(8, 'fatma nur', 'fatman@gmail.com', 'fatma', 'student', 'default.png', '2025-12-19 13:11:13', 'yunus emre ortaokulu', '8A', 'default', NULL, NULL),
(9, 'aslı gül', 'asligul@gmail.com', '$2y$10$tvDpApKwLVyb11wfsTf0ZuGWJd2U8GbqsC5tHKDJTe7tGylbcavuy', 'student', 'default.png', '2025-12-19 13:15:58', 'yunus emre ortaokulu', '8A', 'default', NULL, NULL),
(10, 'yeni admin', 'yeni_admin@gmail.com', '$2y$10$EieYcl3n18BZ3YYYU0goquzmWOi0lrPJ4mpO.nbugBjwc5Fp8goL2', 'admin', 'default.png', '2025-12-19 13:28:29', 'yunus emre ortaokulu', '8A', 'default', NULL, 'uploads/avatar_10_1767423767.png'),
(11, 'ceren öksüz', 'ceren@gmail.com', '$2y$10$20IycpkVoX72VaNYdDtsBePEHMJjA/gJwhyILDsJmB29wTBaznA.2', 'student', 'default.png', '2025-12-19 13:32:17', 'yunus emre ortaokulu', '6C', 'default', NULL, NULL),
(12, 'irem şaşmaz', 'iremm@gmail.com', '$2y$10$70fy82wwEa0XOiB7zsVl0uLGSSELPx2tcxo992TPTbcijV5C.vY6.', 'student', 'default.png', '2025-12-20 12:18:12', 'yunus emre lisesi', '12D', 'default', NULL, 'uploads/avatar_12_6946c39e9e6c1.jpg'),
(13, 'hüsna', 'husna05@gmail.com', '$2y$10$7dqVsNJWmCPPilChJYl2Pu3lWLOfBnTzk5QCXYDt0yRDKmSRmTm2O', 'student', 'default.png', '2025-12-21 20:08:29', 'banü', '12. Sınıf', 'default', NULL, NULL),
(14, 'selin nur', 'selinn@gmail.com', '$2y$10$7CLysgJueO40S4MerKzHPeYon7RwjgMD/tA7vQZ4bkrlzb.0eKhKK', 'student', 'default.png', '2025-12-24 06:20:24', 'bandırma üniversitesi', '12. Sınıf', 'default', NULL, 'uploads/avatar_14_1766557438.png'),
(15, 'Ayse gül', 'aysegul@gmail.com', '$2y$10$tsgw70ZcL9ajN3gIL0s9WuEnrQdMu5H5J7qNkkLwDr3f9jUsBctg2', 'student', 'default.png', '2025-12-25 07:26:53', 'Anadolu lisesi', '11. Sınıf', 'default', NULL, NULL),
(16, 'nesibe göksu', 'nesibe@gmail.com', '$2y$10$Sgph7m1MR9pm5Xoj1N0/DePOjbzbuTtIIyEC.cBrOQ4DGAWM/7F.u', 'student', 'default.png', '2026-01-02 14:17:37', 'bandırma üniversitesi', '11. Sınıf', 'default', NULL, NULL),
(17, 'asya', 'asya@gmail.com', '$2y$10$.Mm7uDfGxGmEcyKnz47s8OtOnxwfDTye3.zZhiAq01/itNFk33T/W', 'student', 'default.png', '2026-04-08 17:39:18', 'gönen meslek lisesi', '12. Sınıf', 'default', NULL, NULL),
(18, 'asude', 'asude@gmail.com', '$2y$10$BWjZsPOgyIz4g8X54xEKq.5Dfy5VRjO/JXwzp/CO9EgKZ3yUP9gru', 'student', 'default.png', '2026-06-27 15:04:28', 'yunus emre lisesi', '9. Sınıf', 'default', NULL, NULL);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_exam_slug` (`slug`),
  ADD KEY `idx_category` (`category_id`);

--
-- Tablo için indeksler `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_exam` (`exam_id`);

--
-- Tablo için indeksler `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `idx_results_user` (`user_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_role` (`role`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Tablo için AUTO_INCREMENT değeri `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Tablo için AUTO_INCREMENT değeri `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Tablo için AUTO_INCREMENT değeri `results`
--
ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
