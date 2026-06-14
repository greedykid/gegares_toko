-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2026 at 08:20 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gegares_v2`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `attribute_changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attribute_changes`)),
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `subject_id`, `event`, `causer_type`, `causer_id`, `attribute_changes`, `properties`, `created_at`, `updated_at`) VALUES
(1, 'user', 'created', 'App\\Models\\User', 1, 'created', NULL, NULL, '{\"attributes\":{\"name\":\"Admin Gegares\",\"email\":\"admin@gegares.com\",\"password\":\"$2y$12$0DKpcfa4Ge6oplOEEEY3VOdRH7.7eYSVOd.85QTJPF08yUWpT43LC\",\"role\":\"admin\",\"avatar\":null,\"google_id\":null,\"google_avatar\":null,\"phone\":null}}', '[]', '2026-05-30 09:23:21', '2026-05-30 09:23:21'),
(2, 'category', 'created', 'App\\Models\\Category', 1, 'created', NULL, NULL, '{\"attributes\":{\"name\":\"Kue Basah\",\"slug\":\"kue-basah\",\"image\":null,\"description\":\"Kue tradisional dengan tekstur lembut dan basah\",\"is_active\":true}}', '[]', '2026-05-30 09:23:21', '2026-05-30 09:23:21'),
(3, 'category', 'created', 'App\\Models\\Category', 2, 'created', NULL, NULL, '{\"attributes\":{\"name\":\"Kue Kering\",\"slug\":\"kue-kering\",\"image\":null,\"description\":\"Kue renyah dan tahan lama\",\"is_active\":true}}', '[]', '2026-05-30 09:23:21', '2026-05-30 09:23:21'),
(4, 'category', 'created', 'App\\Models\\Category', 3, 'created', NULL, NULL, '{\"attributes\":{\"name\":\"Gorengan\",\"slug\":\"gorengan\",\"image\":null,\"description\":\"Jajanan goreng yang renyah dan gurih\",\"is_active\":true}}', '[]', '2026-05-30 09:23:21', '2026-05-30 09:23:21'),
(5, 'category', 'created', 'App\\Models\\Category', 4, 'created', NULL, NULL, '{\"attributes\":{\"name\":\"Jajanan Kukus\",\"slug\":\"jajanan-kukus\",\"image\":null,\"description\":\"Jajanan sehat yang dikukus sempurna\",\"is_active\":true}}', '[]', '2026-05-30 09:23:21', '2026-05-30 09:23:21'),
(6, 'product', 'created', 'App\\Models\\Product', 1, 'created', NULL, NULL, '{\"attributes\":{\"id\":1,\"category_id\":1,\"name\":\"Klepon\",\"slug\":\"klepon\",\"description\":\"Bola-bola ketan hijau berisi gula merah cair, dibalut kelapa parut segar. Sensasi ledakan manis di setiap gigitan.\",\"price\":\"15000.00\",\"stock\":50,\"image\":null,\"is_featured\":true,\"rating_avg\":0,\"rating_count\":0,\"created_at\":\"2026-05-30T09:23:22.000000Z\",\"updated_at\":\"2026-05-30T09:23:22.000000Z\",\"deleted_at\":null}}', '[]', '2026-05-30 09:23:22', '2026-05-30 09:23:22'),
(7, 'product', 'created', 'App\\Models\\Product', 2, 'created', NULL, NULL, '{\"attributes\":{\"id\":2,\"category_id\":1,\"name\":\"Kue Lapis\",\"slug\":\"kue-lapis\",\"description\":\"Kue berlapis warna-warni dengan cita rasa manis legit. Dibuat dari tepung beras dan santan pilihan.\",\"price\":\"25000.00\",\"stock\":30,\"image\":null,\"is_featured\":true,\"rating_avg\":0,\"rating_count\":0,\"created_at\":\"2026-05-30T09:23:22.000000Z\",\"updated_at\":\"2026-05-30T09:23:22.000000Z\",\"deleted_at\":null}}', '[]', '2026-05-30 09:23:22', '2026-05-30 09:23:22'),
(8, 'product', 'created', 'App\\Models\\Product', 3, 'created', NULL, NULL, '{\"attributes\":{\"id\":3,\"category_id\":1,\"name\":\"Onde-Onde\",\"slug\":\"onde-onde\",\"description\":\"Bola ketan isi kacang hijau manis, dibalut wijen dan digoreng hingga keemasan. Renyah di luar, lembut di dalam.\",\"price\":\"18000.00\",\"stock\":40,\"image\":null,\"is_featured\":false,\"rating_avg\":0,\"rating_count\":0,\"created_at\":\"2026-05-30T09:23:22.000000Z\",\"updated_at\":\"2026-05-30T09:23:22.000000Z\",\"deleted_at\":null}}', '[]', '2026-05-30 09:23:22', '2026-05-30 09:23:22'),
(9, 'product', 'created', 'App\\Models\\Product', 4, 'created', NULL, NULL, '{\"attributes\":{\"id\":4,\"category_id\":1,\"name\":\"Getuk Lindri\",\"slug\":\"getuk-lindri\",\"description\":\"Singkong kukus yang dihaluskan dengan gula dan kelapa parut, dicetak cantik berwarna-warni.\",\"price\":\"12000.00\",\"stock\":35,\"image\":null,\"is_featured\":false,\"rating_avg\":0,\"rating_count\":0,\"created_at\":\"2026-05-30T09:23:22.000000Z\",\"updated_at\":\"2026-05-30T09:23:22.000000Z\",\"deleted_at\":null}}', '[]', '2026-05-30 09:23:22', '2026-05-30 09:23:22'),
(10, 'product', 'created', 'App\\Models\\Product', 5, 'created', NULL, NULL, '{\"attributes\":{\"id\":5,\"category_id\":2,\"name\":\"Kue Semprit\",\"slug\":\"kue-semprit\",\"description\":\"Kue kering klasik berbentuk bunga dengan tekstur renyah yang lumer di mulut. Cocok untuk camilan teman teh.\",\"price\":\"35000.00\",\"stock\":25,\"image\":null,\"is_featured\":false,\"rating_avg\":0,\"rating_count\":0,\"created_at\":\"2026-05-30T09:23:22.000000Z\",\"updated_at\":\"2026-05-30T09:23:22.000000Z\",\"deleted_at\":null}}', '[]', '2026-05-30 09:23:22', '2026-05-30 09:23:22'),
(11, 'product', 'created', 'App\\Models\\Product', 6, 'created', NULL, NULL, '{\"attributes\":{\"id\":6,\"category_id\":2,\"name\":\"Kastengel\",\"slug\":\"kastengel\",\"description\":\"Kue keju premium dengan rasa gurih yang kaya. Menggunakan keju Edam asli untuk cita rasa terbaik.\",\"price\":\"45000.00\",\"stock\":20,\"image\":null,\"is_featured\":true,\"rating_avg\":0,\"rating_count\":0,\"created_at\":\"2026-05-30T09:23:22.000000Z\",\"updated_at\":\"2026-05-30T09:23:22.000000Z\",\"deleted_at\":null}}', '[]', '2026-05-30 09:23:22', '2026-05-30 09:23:22'),
(12, 'product', 'created', 'App\\Models\\Product', 7, 'created', NULL, NULL, '{\"attributes\":{\"id\":7,\"category_id\":3,\"name\":\"Risoles Mayo\",\"slug\":\"risoles-mayo\",\"description\":\"Kulit crepe renyah berisi ayam, mayones, dan sayuran segar. Digoreng dengan tepung panir hingga keemasan.\",\"price\":\"20000.00\",\"stock\":45,\"image\":null,\"is_featured\":true,\"rating_avg\":0,\"rating_count\":0,\"created_at\":\"2026-05-30T09:23:22.000000Z\",\"updated_at\":\"2026-05-30T09:23:22.000000Z\",\"deleted_at\":null}}', '[]', '2026-05-30 09:23:22', '2026-05-30 09:23:22'),
(13, 'product', 'created', 'App\\Models\\Product', 8, 'created', NULL, NULL, '{\"attributes\":{\"id\":8,\"category_id\":3,\"name\":\"Pastel Isi Ragout\",\"slug\":\"pastel-isi-ragout\",\"description\":\"Kulit pastri renyah berlapis-lapis dengan isian ragout ayam wortel yang creamy dan gurih.\",\"price\":\"22000.00\",\"stock\":35,\"image\":null,\"is_featured\":false,\"rating_avg\":0,\"rating_count\":0,\"created_at\":\"2026-05-30T09:23:22.000000Z\",\"updated_at\":\"2026-05-30T09:23:22.000000Z\",\"deleted_at\":null}}', '[]', '2026-05-30 09:23:22', '2026-05-30 09:23:22'),
(14, 'product', 'created', 'App\\Models\\Product', 9, 'created', NULL, NULL, '{\"attributes\":{\"id\":9,\"category_id\":3,\"name\":\"Lumpia Semarang\",\"slug\":\"lumpia-semarang\",\"description\":\"Lumpia goreng khas Semarang dengan isian rebung dan udang. Renyah dan beraroma harum.\",\"price\":\"25000.00\",\"stock\":30,\"image\":null,\"is_featured\":false,\"rating_avg\":0,\"rating_count\":0,\"created_at\":\"2026-05-30T09:23:22.000000Z\",\"updated_at\":\"2026-05-30T09:23:22.000000Z\",\"deleted_at\":null}}', '[]', '2026-05-30 09:23:22', '2026-05-30 09:23:22'),
(15, 'product', 'created', 'App\\Models\\Product', 10, 'created', NULL, NULL, '{\"attributes\":{\"id\":10,\"category_id\":3,\"name\":\"Combro\",\"slug\":\"combro\",\"description\":\"Jajanan Sunda dari singkong parut berisi oncom pedas. Digoreng hingga kecokelatan dan renyah.\",\"price\":\"10000.00\",\"stock\":0,\"image\":null,\"is_featured\":false,\"rating_avg\":0,\"rating_count\":0,\"created_at\":\"2026-05-30T09:23:22.000000Z\",\"updated_at\":\"2026-05-30T09:23:22.000000Z\",\"deleted_at\":null}}', '[]', '2026-05-30 09:23:22', '2026-05-30 09:23:22'),
(16, 'product', 'created', 'App\\Models\\Product', 11, 'created', NULL, NULL, '{\"attributes\":{\"id\":11,\"category_id\":4,\"name\":\"Nagasari\",\"slug\":\"nagasari\",\"description\":\"Kue kukus dari tepung beras dan santan, dibungkus daun pisang dengan potongan pisang raja di dalamnya.\",\"price\":\"15000.00\",\"stock\":40,\"image\":null,\"is_featured\":true,\"rating_avg\":0,\"rating_count\":0,\"created_at\":\"2026-05-30T09:23:22.000000Z\",\"updated_at\":\"2026-05-30T09:23:22.000000Z\",\"deleted_at\":null}}', '[]', '2026-05-30 09:23:22', '2026-05-30 09:23:22'),
(17, 'product', 'created', 'App\\Models\\Product', 12, 'created', NULL, NULL, '{\"attributes\":{\"id\":12,\"category_id\":4,\"name\":\"Putu Bambu\",\"slug\":\"putu-bambu\",\"description\":\"Kue putu kukus di dalam bambu, berisi gula merah dan ditaburi kelapa parut. Aroma daun pandan yang harum.\",\"price\":\"12000.00\",\"stock\":3,\"image\":null,\"is_featured\":false,\"rating_avg\":0,\"rating_count\":0,\"created_at\":\"2026-05-30T09:23:22.000000Z\",\"updated_at\":\"2026-05-30T09:23:22.000000Z\",\"deleted_at\":null}}', '[]', '2026-05-30 09:23:22', '2026-05-30 09:23:22'),
(18, 'product', 'created', 'App\\Models\\Product', 13, 'created', NULL, NULL, '{\"attributes\":{\"id\":13,\"category_id\":4,\"name\":\"Dadar Gulung\",\"slug\":\"dadar-gulung\",\"description\":\"Crepe hijau pandan lembut yang digulung berisi kelapa parut dan gula merah. Manis dan harum.\",\"price\":\"18000.00\",\"stock\":30,\"image\":null,\"is_featured\":false,\"rating_avg\":0,\"rating_count\":0,\"created_at\":\"2026-05-30T09:23:22.000000Z\",\"updated_at\":\"2026-05-30T09:23:22.000000Z\",\"deleted_at\":null}}', '[]', '2026-05-30 09:23:22', '2026-05-30 09:23:22'),
(19, 'product', 'created', 'App\\Models\\Product', 14, 'created', NULL, NULL, '{\"attributes\":{\"id\":14,\"category_id\":4,\"name\":\"Serabi Solo\",\"slug\":\"serabi-solo\",\"description\":\"Kue tradisional Solo dari tepung beras dan santan, disajikan dengan kuah kinca gula merah yang kental.\",\"price\":\"16000.00\",\"stock\":25,\"image\":null,\"is_featured\":true,\"rating_avg\":0,\"rating_count\":0,\"created_at\":\"2026-05-30T09:23:22.000000Z\",\"updated_at\":\"2026-05-30T09:23:22.000000Z\",\"deleted_at\":null}}', '[]', '2026-05-30 09:23:22', '2026-05-30 09:23:22'),
(20, 'product', 'created', 'App\\Models\\Product', 15, 'created', NULL, NULL, '{\"attributes\":{\"id\":15,\"category_id\":4,\"name\":\"Lemper Ayam\",\"slug\":\"lemper-ayam\",\"description\":\"Ketan pulen berisi ayam suwir berbumbu, dibungkus daun pisang dan dikukus hingga harum.\",\"price\":\"20000.00\",\"stock\":35,\"image\":null,\"is_featured\":false,\"rating_avg\":0,\"rating_count\":0,\"created_at\":\"2026-05-30T09:23:22.000000Z\",\"updated_at\":\"2026-05-30T09:23:22.000000Z\",\"deleted_at\":null}}', '[]', '2026-05-30 09:23:22', '2026-05-30 09:23:22'),
(21, 'user', 'created', 'App\\Models\\User', 2, 'created', NULL, NULL, '{\"attributes\":{\"name\":\"Rizki Arbiansyah\",\"email\":\"rizki@gmail.com\",\"password\":\"$2y$12$3LnkU6bRmn.4abWdKzMkCOnLalKNyoJhQyzXc2sLAE5nHnOgHdQHm\",\"role\":\"user\",\"avatar\":null,\"google_id\":null,\"google_avatar\":null,\"phone\":\"08211261991\"}}', '[]', '2026-05-30 10:11:50', '2026-05-30 10:11:50'),
(22, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"phone\":\"82112619691\"},\"old\":{\"phone\":null}}', '[]', '2026-06-02 04:15:20', '2026-06-02 04:15:20'),
(23, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":false,\"promos\":true,\"newsletter\":false}},\"old\":{\"notification_settings\":null}}', '[]', '2026-06-02 04:18:47', '2026-06-02 04:18:47'),
(24, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false}},\"old\":{\"notification_settings\":{\"order_updates\":false,\"promos\":true,\"newsletter\":false}}}', '[]', '2026-06-02 04:18:52', '2026-06-02 04:18:52'),
(25, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":false,\"promos\":true,\"newsletter\":false}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false}}}', '[]', '2026-06-02 04:19:19', '2026-06-02 04:19:19'),
(26, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false}},\"old\":{\"notification_settings\":{\"order_updates\":false,\"promos\":true,\"newsletter\":false}}}', '[]', '2026-06-02 04:20:17', '2026-06-02 04:20:17'),
(27, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false}}}', '[]', '2026-06-02 04:20:24', '2026-06-02 04:20:24'),
(28, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true}}}', '[]', '2026-06-02 04:21:07', '2026-06-02 04:21:07'),
(29, 'user', 'created', 'App\\Models\\User', 3, 'created', NULL, NULL, '{\"attributes\":{\"name\":\"Test SpacingSpacing Test\",\"email\":\"spacingtest@example.com\",\"password\":\"$2y$12$f6rmP9N3uuBsQy3lLM6N\\/OQWTknoAbQQjW3ZMxCckyZmG.OaEV6Ue\",\"role\":\"user\",\"avatar\":null,\"google_id\":null,\"google_avatar\":null,\"phone\":\"628123456788\",\"notification_settings\":null}}', '[]', '2026-06-02 04:38:42', '2026-06-02 04:38:42'),
(30, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T11:45:48+07:00\"}},\"old\":{\"notification_settings\":null}}', '[]', '2026-06-02 04:45:48', '2026-06-02 04:45:48'),
(31, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T11:46:02+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T11:45:48+07:00\"}}}', '[]', '2026-06-02 04:46:02', '2026-06-02 04:46:02'),
(32, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T11:46:34+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T11:46:02+07:00\"}}}', '[]', '2026-06-02 04:46:34', '2026-06-02 04:46:34'),
(33, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T11:46:47+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false}}}', '[]', '2026-06-02 04:46:47', '2026-06-02 04:46:47'),
(34, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T11:46:51+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T11:46:47+07:00\"}}}', '[]', '2026-06-02 04:46:51', '2026-06-02 04:46:51'),
(35, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T11:46:54+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T11:46:51+07:00\"}}}', '[]', '2026-06-02 04:46:54', '2026-06-02 04:46:54'),
(36, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T11:47:02+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T11:46:54+07:00\"}}}', '[]', '2026-06-02 04:47:02', '2026-06-02 04:47:02'),
(37, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T11:47:04+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T11:47:02+07:00\"}}}', '[]', '2026-06-02 04:47:04', '2026-06-02 04:47:04'),
(38, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T11:55:30+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T11:47:04+07:00\"}}}', '[]', '2026-06-02 04:55:30', '2026-06-02 04:55:30'),
(39, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T11:59:59+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T11:55:30+07:00\"}}}', '[]', '2026-06-02 04:59:59', '2026-06-02 04:59:59'),
(40, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:00:00+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T11:59:59+07:00\"}}}', '[]', '2026-06-02 05:00:00', '2026-06-02 05:00:00'),
(41, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:00:09+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:00:00+07:00\"}}}', '[]', '2026-06-02 05:00:09', '2026-06-02 05:00:09'),
(42, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:05:31+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:00:09+07:00\"}}}', '[]', '2026-06-02 05:05:31', '2026-06-02 05:05:31'),
(43, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:05:34+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:05:31+07:00\"}}}', '[]', '2026-06-02 05:05:34', '2026-06-02 05:05:34'),
(44, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"avatar\":\"avatars\\/fwSotprEXaIh4f0y5CvF8DjIVCA42fQFWF59WQic.jpg\"},\"old\":{\"avatar\":null}}', '[]', '2026-06-02 05:06:16', '2026-06-02 05:06:16'),
(45, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"avatar\":null},\"old\":{\"avatar\":\"avatars\\/fwSotprEXaIh4f0y5CvF8DjIVCA42fQFWF59WQic.jpg\"}}', '[]', '2026-06-02 05:11:25', '2026-06-02 05:11:25'),
(46, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:19:24+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:05:34+07:00\"}}}', '[]', '2026-06-02 05:19:24', '2026-06-02 05:19:24'),
(47, 'coupon', 'created', 'App\\Models\\Coupon', 1, 'created', 'App\\Models\\User', 1, '{\"attributes\":{\"id\":1,\"code\":\"FREEONGKIR\",\"type\":\"fixed\",\"value\":\"40000.00\",\"min_purchase\":\"20000.00\",\"start_date\":null,\"end_date\":null,\"usage_limit\":null,\"used_count\":0,\"is_active\":true,\"created_at\":\"2026-06-02T05:21:09.000000Z\",\"updated_at\":\"2026-06-02T05:21:09.000000Z\",\"deleted_at\":null}}', '[]', '2026-06-02 05:21:09', '2026-06-02 05:21:09'),
(48, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:21:30+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T11:46:34+07:00\"}}}', '[]', '2026-06-02 05:21:30', '2026-06-02 05:21:30'),
(49, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:21:37+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:21:30+07:00\"}}}', '[]', '2026-06-02 05:21:37', '2026-06-02 05:21:37'),
(50, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:21:45+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:21:37+07:00\"}}}', '[]', '2026-06-02 05:21:45', '2026-06-02 05:21:45'),
(51, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:21:52+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:21:45+07:00\"}}}', '[]', '2026-06-02 05:21:52', '2026-06-02 05:21:52'),
(52, 'order', 'created', 'App\\Models\\Order', 1, 'created', 'App\\Models\\User', 3, '{\"attributes\":{\"id\":1,\"user_id\":3,\"order_number\":\"GGR-20260602-E71C82\",\"biteship_order_id\":null,\"courier_tracking_id\":null,\"address_id\":1,\"subtotal\":\"40000.00\",\"shipping_cost\":\"28500.00\",\"discount_amount\":\"0.00\",\"total\":\"68500.00\",\"status\":\"pending\",\"coupon_id\":null,\"payment_status\":\"unpaid\",\"payment_method\":\"midtrans\",\"snap_token\":null,\"midtrans_order_id\":null,\"shipping_courier\":\"gojek\",\"shipping_service\":\"instant\",\"tracking_number\":null,\"notes\":null,\"paid_at\":null,\"created_at\":\"2026-06-02T05:23:10.000000Z\",\"updated_at\":\"2026-06-02T05:23:10.000000Z\",\"deleted_at\":null}}', '[]', '2026-06-02 05:23:10', '2026-06-02 05:23:10'),
(53, 'order', 'created', 'App\\Models\\Order', 2, 'created', 'App\\Models\\User', 3, '{\"attributes\":{\"id\":2,\"user_id\":3,\"order_number\":\"GGR-20260602-B797CF\",\"biteship_order_id\":null,\"courier_tracking_id\":null,\"address_id\":1,\"subtotal\":\"40000.00\",\"shipping_cost\":\"28500.00\",\"discount_amount\":\"0.00\",\"total\":\"68500.00\",\"status\":\"pending\",\"coupon_id\":null,\"payment_status\":\"unpaid\",\"payment_method\":\"midtrans\",\"snap_token\":null,\"midtrans_order_id\":null,\"shipping_courier\":\"gojek\",\"shipping_service\":\"instant\",\"tracking_number\":null,\"notes\":null,\"paid_at\":null,\"created_at\":\"2026-06-02T05:26:03.000000Z\",\"updated_at\":\"2026-06-02T05:26:03.000000Z\",\"deleted_at\":null}}', '[]', '2026-06-02 05:26:03', '2026-06-02 05:26:03'),
(54, 'order', 'updated', 'App\\Models\\Order', 2, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"snap_token\":\"3b2ba11f-a87f-4a88-8251-ef1046398a93\",\"midtrans_order_id\":\"GGR-20260602-B797CF\",\"updated_at\":\"2026-06-02T05:26:04.000000Z\"},\"old\":{\"snap_token\":null,\"midtrans_order_id\":null,\"updated_at\":\"2026-06-02T05:26:03.000000Z\"}}', '[]', '2026-06-02 05:26:04', '2026-06-02 05:26:04'),
(55, 'order', 'updated', 'App\\Models\\Order', 2, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"status\":\"paid\",\"payment_status\":\"paid\",\"payment_method\":\"bank_transfer\",\"paid_at\":\"2026-06-02T05:27:18.000000Z\",\"updated_at\":\"2026-06-02T05:27:18.000000Z\"},\"old\":{\"status\":\"pending\",\"payment_status\":\"unpaid\",\"payment_method\":\"midtrans\",\"paid_at\":null,\"updated_at\":\"2026-06-02T05:26:04.000000Z\"}}', '[]', '2026-06-02 05:27:18', '2026-06-02 05:27:18'),
(56, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:28:33+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:21:52+07:00\"}}}', '[]', '2026-06-02 05:28:33', '2026-06-02 05:28:33'),
(57, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:28:43+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:28:33+07:00\"}}}', '[]', '2026-06-02 05:28:43', '2026-06-02 05:28:43'),
(58, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:29:50+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:28:43+07:00\"}}}', '[]', '2026-06-02 05:29:50', '2026-06-02 05:29:50'),
(59, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:30:03+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:29:50+07:00\"}}}', '[]', '2026-06-02 05:30:03', '2026-06-02 05:30:03'),
(60, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:32:33+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:30:03+07:00\"}}}', '[]', '2026-06-02 05:32:33', '2026-06-02 05:32:33'),
(61, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:34:12+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:32:33+07:00\"}}}', '[]', '2026-06-02 05:34:12', '2026-06-02 05:34:12'),
(62, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:35:08+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:34:12+07:00\"}}}', '[]', '2026-06-02 05:35:08', '2026-06-02 05:35:08'),
(63, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:37:02+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:35:08+07:00\"}}}', '[]', '2026-06-02 05:37:02', '2026-06-02 05:37:02'),
(64, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:37:29+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:37:02+07:00\"}}}', '[]', '2026-06-02 05:37:30', '2026-06-02 05:37:30'),
(65, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:37:32+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:37:29+07:00\"}}}', '[]', '2026-06-02 05:37:32', '2026-06-02 05:37:32'),
(66, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:37:37+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:37:32+07:00\"}}}', '[]', '2026-06-02 05:37:37', '2026-06-02 05:37:37'),
(67, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:37:39+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:37:37+07:00\"}}}', '[]', '2026-06-02 05:37:39', '2026-06-02 05:37:39'),
(68, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:37:42+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:37:39+07:00\"}}}', '[]', '2026-06-02 05:37:43', '2026-06-02 05:37:43'),
(69, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:37:55+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:37:42+07:00\"}}}', '[]', '2026-06-02 05:37:55', '2026-06-02 05:37:55'),
(70, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:38:00+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:37:55+07:00\"}}}', '[]', '2026-06-02 05:38:00', '2026-06-02 05:38:00'),
(71, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:38:08+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:38:00+07:00\"}}}', '[]', '2026-06-02 05:38:08', '2026-06-02 05:38:08'),
(72, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:38:10+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:38:08+07:00\"}}}', '[]', '2026-06-02 05:38:10', '2026-06-02 05:38:10'),
(73, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:38:19+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:38:10+07:00\"}}}', '[]', '2026-06-02 05:38:19', '2026-06-02 05:38:19'),
(74, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:38:25+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:38:19+07:00\"}}}', '[]', '2026-06-02 05:38:25', '2026-06-02 05:38:25'),
(75, 'order', 'updated', 'App\\Models\\Order', 2, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"biteship_order_id\":\"6a1e6c5d8d3ef61c0558b5d7\",\"updated_at\":\"2026-06-02T05:38:34.000000Z\"},\"old\":{\"biteship_order_id\":null,\"updated_at\":\"2026-06-02T05:27:18.000000Z\"}}', '[]', '2026-06-02 05:38:34', '2026-06-02 05:38:34'),
(76, 'order', 'updated', 'App\\Models\\Order', 2, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"status\":\"processing\",\"tracking_number\":\"WYB-1780378717153\"},\"old\":{\"status\":\"paid\",\"tracking_number\":null}}', '[]', '2026-06-02 05:38:34', '2026-06-02 05:38:34'),
(77, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:38:47+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:38:25+07:00\"}}}', '[]', '2026-06-02 05:38:47', '2026-06-02 05:38:47'),
(78, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:39:37+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:38:47+07:00\"}}}', '[]', '2026-06-02 05:39:37', '2026-06-02 05:39:37'),
(79, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:41:17+07:00\"}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:39:37+07:00\"}}}', '[]', '2026-06-02 05:41:17', '2026-06-02 05:41:17'),
(80, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":false,\"promos\":false,\"newsletter\":false}},\"old\":{\"notification_settings\":{\"last_read_at\":\"2026-06-02T12:41:17+07:00\"}}}', '[]', '2026-06-02 05:41:27', '2026-06-02 05:41:27'),
(81, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":false,\"promos\":false,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:41:36+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":false,\"promos\":false,\"newsletter\":false}}}', '[]', '2026-06-02 05:41:36', '2026-06-02 05:41:36'),
(82, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":false,\"promos\":false,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:42:41+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":false,\"promos\":false,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:41:36+07:00\"}}}', '[]', '2026-06-02 05:42:41', '2026-06-02 05:42:41'),
(83, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false}},\"old\":{\"notification_settings\":{\"order_updates\":false,\"promos\":false,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:42:41+07:00\"}}}', '[]', '2026-06-02 05:42:47', '2026-06-02 05:42:47'),
(84, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:42:49+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false}}}', '[]', '2026-06-02 05:42:50', '2026-06-02 05:42:50'),
(85, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:42:55+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:42:49+07:00\"}}}', '[]', '2026-06-02 05:42:55', '2026-06-02 05:42:55'),
(86, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:42:55+07:00\"}}}', '[]', '2026-06-02 05:42:59', '2026-06-02 05:42:59'),
(87, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T12:43:02+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true}}}', '[]', '2026-06-02 05:43:02', '2026-06-02 05:43:02'),
(88, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T12:43:04+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T12:43:02+07:00\"}}}', '[]', '2026-06-02 05:43:05', '2026-06-02 05:43:05'),
(89, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:48:11+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:19:24+07:00\"}}}', '[]', '2026-06-02 05:48:11', '2026-06-02 05:48:11'),
(90, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:48:13+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:48:11+07:00\"}}}', '[]', '2026-06-02 05:48:13', '2026-06-02 05:48:13'),
(91, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:48:14+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:48:13+07:00\"}}}', '[]', '2026-06-02 05:48:14', '2026-06-02 05:48:14'),
(92, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:48:16+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:48:14+07:00\"}}}', '[]', '2026-06-02 05:48:16', '2026-06-02 05:48:16'),
(93, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:48:20+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:48:16+07:00\"}}}', '[]', '2026-06-02 05:48:20', '2026-06-02 05:48:20'),
(94, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:48:52+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:48:20+07:00\"}}}', '[]', '2026-06-02 05:48:52', '2026-06-02 05:48:52'),
(95, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:48:55+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:48:52+07:00\"}}}', '[]', '2026-06-02 05:48:55', '2026-06-02 05:48:55'),
(96, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:05:44+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T12:43:04+07:00\"}}}', '[]', '2026-06-02 06:05:44', '2026-06-02 06:05:44'),
(97, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:05:47+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:05:44+07:00\"}}}', '[]', '2026-06-02 06:05:47', '2026-06-02 06:05:47'),
(98, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:06:16+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:05:47+07:00\"}}}', '[]', '2026-06-02 06:06:16', '2026-06-02 06:06:16'),
(99, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:06:51+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:06:16+07:00\"}}}', '[]', '2026-06-02 06:06:51', '2026-06-02 06:06:51'),
(100, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:06:52+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:06:51+07:00\"}}}', '[]', '2026-06-02 06:06:52', '2026-06-02 06:06:52'),
(101, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:06:58+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:06:52+07:00\"}}}', '[]', '2026-06-02 06:06:58', '2026-06-02 06:06:58'),
(102, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:07:05+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:06:58+07:00\"}}}', '[]', '2026-06-02 06:07:05', '2026-06-02 06:07:05'),
(103, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:07:07+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:07:05+07:00\"}}}', '[]', '2026-06-02 06:07:07', '2026-06-02 06:07:07'),
(104, 'coupon', 'created', 'App\\Models\\Coupon', 2, 'created', 'App\\Models\\User', 1, '{\"attributes\":{\"id\":2,\"code\":\"FREEONGKIR2\",\"type\":\"fixed\",\"value\":\"15000.00\",\"min_purchase\":\"0.00\",\"start_date\":null,\"end_date\":null,\"usage_limit\":null,\"used_count\":0,\"is_active\":true,\"created_at\":\"2026-06-02T06:09:51.000000Z\",\"updated_at\":\"2026-06-02T06:09:51.000000Z\",\"deleted_at\":null}}', '[]', '2026-06-02 06:09:51', '2026-06-02 06:09:51'),
(105, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T13:12:40+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T12:48:55+07:00\"}}}', '[]', '2026-06-02 06:12:40', '2026-06-02 06:12:40'),
(106, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T13:12:52+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T13:12:40+07:00\"}}}', '[]', '2026-06-02 06:12:52', '2026-06-02 06:12:52'),
(107, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T14:49:31+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T13:07:07+07:00\"}}}', '[]', '2026-06-02 07:49:31', '2026-06-02 07:49:31'),
(108, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T14:51:04+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T14:49:31+07:00\"}}}', '[]', '2026-06-02 07:51:04', '2026-06-02 07:51:04'),
(109, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T14:51:06+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T14:51:04+07:00\"}}}', '[]', '2026-06-02 07:51:06', '2026-06-02 07:51:06'),
(110, 'user', 'updated', 'App\\Models\\User', 3, 'updated', 'App\\Models\\User', 3, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T14:51:12+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T14:51:06+07:00\"}}}', '[]', '2026-06-02 07:51:12', '2026-06-02 07:51:12'),
(111, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-08T16:53:33+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-02T13:12:52+07:00\"}}}', '[]', '2026-06-08 09:53:34', '2026-06-08 09:53:34'),
(112, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-08T16:56:51+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-08T16:53:33+07:00\"}}}', '[]', '2026-06-08 09:56:51', '2026-06-08 09:56:51'),
(113, 'user', 'updated', 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"attributes\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-08T16:56:53+07:00\"}},\"old\":{\"notification_settings\":{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-08T16:56:51+07:00\"}}}', '[]', '2026-06-08 09:56:53', '2026-06-08 09:56:53');

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `address_line` text NOT NULL,
  `area_id` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `province` varchar(255) NOT NULL,
  `postal_code` varchar(255) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `biteship_location_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `label`, `recipient_name`, `phone`, `address_line`, `area_id`, `city`, `province`, `postal_code`, `latitude`, `longitude`, `is_primary`, `biteship_location_id`, `created_at`, `updated_at`) VALUES
(1, 3, 'Rumah', 'TEST', '081291291233', 'Tebet, Jalan Tebet Timur Dalam III M, RW 01, Tebet Timur, Tebet, Jakarta Selatan, Daerah Khusus Ibukota Jakarta, Jawa, 12840, Indonesia', 'IDNP6IDNC148IDND845IDZ12810', 'Jakarta Selatan', 'DKI Jakarta', '12810', -6.2263977, 106.8584389, 1, '6a1e68b2ae9675758d89972b', '2026-06-02 05:22:56', '2026-06-02 05:22:56');

-- --------------------------------------------------------

--
-- Table structure for table `banned_ips`
--

CREATE TABLE `banned_ips` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `banned_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('gegares-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:2;', 1780932394),
('gegares-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1780932394;', 1780932394),
('gegares-cache-biteship_rates_5f96fcf9228e6a6fd32dac335fc3208e', 'a:6:{i:0;a:25:{s:27:\"available_collection_method\";a:1:{i:0;s:6:\"pickup\";}s:30:\"available_for_cash_on_delivery\";b:0;s:31:\"available_for_proof_of_delivery\";b:0;s:32:\"available_for_instant_waybill_id\";b:1;s:23:\"available_for_insurance\";b:1;s:7:\"company\";s:5:\"gojek\";s:12:\"courier_name\";s:5:\"GOJEK\";s:12:\"courier_code\";s:5:\"gojek\";s:20:\"courier_service_name\";s:7:\"Instant\";s:20:\"courier_service_code\";s:7:\"instant\";s:8:\"currency\";s:3:\"IDR\";s:11:\"description\";s:36:\"Instant service for on demand needs.\";s:8:\"duration\";s:11:\"1 - 2 Hours\";s:23:\"shipment_duration_range\";s:5:\"1 - 2\";s:22:\"shipment_duration_unit\";s:5:\"hours\";s:12:\"service_type\";s:8:\"same_day\";s:13:\"shipping_type\";s:6:\"parcel\";s:5:\"price\";i:28500;s:12:\"shipping_fee\";i:28500;s:21:\"shipping_fee_discount\";i:0;s:22:\"shipping_fee_surcharge\";i:0;s:13:\"insurance_fee\";i:0;s:20:\"cash_on_delivery_fee\";i:0;s:9:\"tax_lines\";a:0:{}s:4:\"type\";s:7:\"instant\";}i:1;a:25:{s:27:\"available_collection_method\";a:1:{i:0;s:6:\"pickup\";}s:30:\"available_for_cash_on_delivery\";b:0;s:31:\"available_for_proof_of_delivery\";b:0;s:32:\"available_for_instant_waybill_id\";b:1;s:23:\"available_for_insurance\";b:1;s:7:\"company\";s:5:\"gojek\";s:12:\"courier_name\";s:5:\"GOJEK\";s:12:\"courier_code\";s:5:\"gojek\";s:20:\"courier_service_name\";s:8:\"Same Day\";s:20:\"courier_service_code\";s:8:\"same_day\";s:8:\"currency\";s:3:\"IDR\";s:11:\"description\";s:48:\"Same day service. Available from 08:00 to 15:00.\";s:8:\"duration\";s:11:\"6 - 8 Hours\";s:23:\"shipment_duration_range\";s:5:\"6 - 8\";s:22:\"shipment_duration_unit\";s:5:\"hours\";s:12:\"service_type\";s:8:\"same_day\";s:13:\"shipping_type\";s:6:\"parcel\";s:5:\"price\";i:25000;s:12:\"shipping_fee\";i:25000;s:21:\"shipping_fee_discount\";i:0;s:22:\"shipping_fee_surcharge\";i:0;s:13:\"insurance_fee\";i:0;s:20:\"cash_on_delivery_fee\";i:0;s:9:\"tax_lines\";a:0:{}s:4:\"type\";s:8:\"same_day\";}i:2;a:25:{s:27:\"available_collection_method\";a:1:{i:0;s:6:\"pickup\";}s:30:\"available_for_cash_on_delivery\";b:0;s:31:\"available_for_proof_of_delivery\";b:0;s:32:\"available_for_instant_waybill_id\";b:1;s:23:\"available_for_insurance\";b:1;s:7:\"company\";s:4:\"grab\";s:12:\"courier_name\";s:4:\"GRAB\";s:12:\"courier_code\";s:4:\"grab\";s:20:\"courier_service_name\";s:7:\"Instant\";s:20:\"courier_service_code\";s:7:\"instant\";s:8:\"currency\";s:3:\"IDR\";s:11:\"description\";s:36:\"Instant service for on demand needs.\";s:8:\"duration\";s:11:\"1 - 3 Hours\";s:23:\"shipment_duration_range\";s:5:\"1 - 3\";s:22:\"shipment_duration_unit\";s:5:\"hours\";s:12:\"service_type\";s:8:\"same_day\";s:13:\"shipping_type\";s:6:\"parcel\";s:5:\"price\";i:27000;s:12:\"shipping_fee\";i:27000;s:21:\"shipping_fee_discount\";i:0;s:22:\"shipping_fee_surcharge\";i:0;s:13:\"insurance_fee\";i:0;s:20:\"cash_on_delivery_fee\";i:0;s:9:\"tax_lines\";a:0:{}s:4:\"type\";s:7:\"instant\";}i:3;a:25:{s:27:\"available_collection_method\";a:1:{i:0;s:6:\"pickup\";}s:30:\"available_for_cash_on_delivery\";b:0;s:31:\"available_for_proof_of_delivery\";b:0;s:32:\"available_for_instant_waybill_id\";b:1;s:23:\"available_for_insurance\";b:1;s:7:\"company\";s:4:\"grab\";s:12:\"courier_name\";s:4:\"GRAB\";s:12:\"courier_code\";s:4:\"grab\";s:20:\"courier_service_name\";s:8:\"Same Day\";s:20:\"courier_service_code\";s:8:\"same_day\";s:8:\"currency\";s:3:\"IDR\";s:11:\"description\";s:48:\"Same day service. Available from 08:00 to 15:00.\";s:8:\"duration\";s:11:\"4 - 8 Hours\";s:23:\"shipment_duration_range\";s:5:\"4 - 8\";s:22:\"shipment_duration_unit\";s:5:\"hours\";s:12:\"service_type\";s:8:\"same_day\";s:13:\"shipping_type\";s:6:\"parcel\";s:5:\"price\";i:19000;s:12:\"shipping_fee\";i:19000;s:21:\"shipping_fee_discount\";i:0;s:22:\"shipping_fee_surcharge\";i:0;s:13:\"insurance_fee\";i:0;s:20:\"cash_on_delivery_fee\";i:0;s:9:\"tax_lines\";a:0:{}s:4:\"type\";s:8:\"same_day\";}i:4;a:25:{s:27:\"available_collection_method\";a:1:{i:0;s:6:\"pickup\";}s:30:\"available_for_cash_on_delivery\";b:1;s:31:\"available_for_proof_of_delivery\";b:0;s:32:\"available_for_instant_waybill_id\";b:1;s:23:\"available_for_insurance\";b:1;s:7:\"company\";s:8:\"anteraja\";s:12:\"courier_name\";s:8:\"AnterAja\";s:12:\"courier_code\";s:8:\"anteraja\";s:20:\"courier_service_name\";s:8:\"Same Day\";s:20:\"courier_service_code\";s:8:\"same_day\";s:8:\"currency\";s:3:\"IDR\";s:11:\"description\";s:33:\"Same day service for Jakarta Area\";s:8:\"duration\";s:12:\"8 - 12 hours\";s:23:\"shipment_duration_range\";s:6:\"8 - 12\";s:22:\"shipment_duration_unit\";s:5:\"hours\";s:12:\"service_type\";s:8:\"same_day\";s:13:\"shipping_type\";s:6:\"parcel\";s:5:\"price\";i:22500;s:12:\"shipping_fee\";i:22500;s:21:\"shipping_fee_discount\";i:0;s:22:\"shipping_fee_surcharge\";i:0;s:13:\"insurance_fee\";i:0;s:20:\"cash_on_delivery_fee\";i:0;s:9:\"tax_lines\";a:0:{}s:4:\"type\";s:8:\"same_day\";}i:5;a:25:{s:27:\"available_collection_method\";a:1:{i:0;s:6:\"pickup\";}s:30:\"available_for_cash_on_delivery\";b:0;s:31:\"available_for_proof_of_delivery\";b:0;s:32:\"available_for_instant_waybill_id\";b:1;s:23:\"available_for_insurance\";b:0;s:7:\"company\";s:12:\"dash_express\";s:12:\"courier_name\";s:12:\"Dash Express\";s:12:\"courier_code\";s:12:\"dash_express\";s:20:\"courier_service_name\";s:8:\"Same Day\";s:20:\"courier_service_code\";s:8:\"SAME_DAY\";s:8:\"currency\";s:3:\"IDR\";s:11:\"description\";s:13:\"Dash Same Day\";s:8:\"duration\";s:10:\"35 minutes\";s:23:\"shipment_duration_range\";s:1:\"8\";s:22:\"shipment_duration_unit\";s:5:\"hours\";s:12:\"service_type\";s:8:\"same_day\";s:13:\"shipping_type\";s:6:\"parcel\";s:5:\"price\";i:18000;s:12:\"shipping_fee\";i:18000;s:21:\"shipping_fee_discount\";i:0;s:22:\"shipping_fee_surcharge\";i:0;s:13:\"insurance_fee\";i:0;s:20:\"cash_on_delivery_fee\";i:0;s:9:\"tax_lines\";a:0:{}s:4:\"type\";s:8:\"SAME_DAY\";}}', 1780378078),
('gegares-cache-chatbot-tSI2hNrUvdlVjmApl3gsceN1JRmoccBvONRYG0Kv', 'i:1;', 1780389682),
('gegares-cache-chatbot-tSI2hNrUvdlVjmApl3gsceN1JRmoccBvONRYG0Kv:timer', 'i:1780389682;', 1780389682),
('gegares-cache-chatbot-ymW7ex2vlHcWsoyvfaCVha8YGmY8I83oBTUEX0Xd', 'i:1;', 1780372346),
('gegares-cache-chatbot-ymW7ex2vlHcWsoyvfaCVha8YGmY8I83oBTUEX0Xd:timer', 'i:1780372346;', 1780372346);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `image`, `description`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Kue Basah', 'kue-basah', NULL, 'Kue tradisional dengan tekstur lembut dan basah', 1, '2026-05-30 09:23:21', '2026-05-30 09:23:21', NULL),
(2, 'Kue Kering', 'kue-kering', NULL, 'Kue renyah dan tahan lama', 1, '2026-05-30 09:23:21', '2026-05-30 09:23:21', NULL),
(3, 'Gorengan', 'gorengan', NULL, 'Jajanan goreng yang renyah dan gurih', 1, '2026-05-30 09:23:21', '2026-05-30 09:23:21', NULL),
(4, 'Jajanan Kukus', 'jajanan-kukus', NULL, 'Jajanan sehat yang dikukus sempurna', 1, '2026-05-30 09:23:21', '2026-05-30 09:23:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `type` enum('fixed','percent') NOT NULL,
  `value` decimal(12,2) NOT NULL,
  `min_purchase` decimal(12,2) DEFAULT 0.00,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `min_purchase`, `start_date`, `end_date`, `usage_limit`, `used_count`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'FREEONGKIR', 'fixed', 40000.00, 20000.00, NULL, NULL, NULL, 0, 1, '2026-06-02 05:21:09', '2026-06-02 05:21:09', NULL),
(2, 'FREEONGKIR2', 'fixed', 15000.00, 0.00, NULL, NULL, NULL, 0, 1, '2026-06-02 06:09:51', '2026-06-02 06:09:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_07_000001_add_role_to_users_table', 1),
(5, '2026_04_07_000002_create_categories_table', 1),
(6, '2026_04_07_000003_create_products_table', 1),
(7, '2026_04_07_000004_create_product_images_table', 1),
(8, '2026_04_07_000005_create_addresses_table', 1),
(9, '2026_04_07_000006_create_wishlists_table', 1),
(10, '2026_04_07_000007_create_orders_table', 1),
(11, '2026_04_07_000008_create_order_items_table', 1),
(12, '2026_04_07_000009_create_reviews_table', 1),
(13, '2026_04_07_104826_add_biteship_location_id_to_addresses_table', 1),
(14, '2026_04_07_161446_add_is_active_to_categories_table', 1),
(15, '2026_04_07_171900_create_store_settings_table', 1),
(16, '2026_04_07_190534_add_biteship_order_id_to_orders_table', 1),
(17, '2026_04_07_190927_add_courier_tracking_id_to_orders_table', 1),
(18, '2026_04_07_233819_add_image_to_reviews_table', 1),
(19, '2026_04_08_141456_add_notification_settings_to_users_table', 1),
(20, '2026_04_09_194119_add_google_id_to_users_table', 1),
(21, '2026_04_10_195553_create_security_events_table', 1),
(22, '2026_04_10_203014_create_banned_ips_table', 1),
(23, '2026_04_14_164645_create_coupons_table', 1),
(24, '2026_04_14_164645_create_product_variants_table', 1),
(25, '2026_04_14_164646_add_deleted_at_to_critical_tables', 1),
(26, '2026_04_14_164646_update_orders_and_items_table', 1),
(27, '2026_04_14_164902_create_activity_log_table', 1),
(28, '2026_04_14_172506_make_min_purchase_nullable_on_coupons_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(255) NOT NULL,
  `biteship_order_id` varchar(255) DEFAULT NULL,
  `courier_tracking_id` varchar(255) DEFAULT NULL,
  `address_id` bigint(20) UNSIGNED NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `shipping_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `status` enum('pending','awaiting_payment','paid','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
  `coupon_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_status` enum('unpaid','pending','paid','failed','expired') NOT NULL DEFAULT 'unpaid',
  `payment_method` varchar(255) DEFAULT NULL,
  `snap_token` varchar(255) DEFAULT NULL,
  `midtrans_order_id` varchar(255) DEFAULT NULL,
  `shipping_courier` varchar(255) DEFAULT NULL,
  `shipping_service` varchar(255) DEFAULT NULL,
  `tracking_number` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `biteship_order_id`, `courier_tracking_id`, `address_id`, `subtotal`, `shipping_cost`, `discount_amount`, `total`, `status`, `coupon_id`, `payment_status`, `payment_method`, `snap_token`, `midtrans_order_id`, `shipping_courier`, `shipping_service`, `tracking_number`, `notes`, `paid_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 'GGR-20260602-E71C82', NULL, NULL, 1, 40000.00, 28500.00, 0.00, 68500.00, 'pending', NULL, 'unpaid', 'midtrans', NULL, NULL, 'gojek', 'instant', NULL, NULL, NULL, '2026-06-02 05:23:10', '2026-06-02 05:23:10', NULL),
(2, 3, 'GGR-20260602-B797CF', '6a1e6c5d8d3ef61c0558b5d7', NULL, 1, 40000.00, 28500.00, 0.00, 68500.00, 'processing', NULL, 'paid', 'bank_transfer', '3b2ba11f-a87f-4a88-8251-ef1046398a93', 'GGR-20260602-B797CF', 'gojek', 'instant', 'WYB-1780378717153', NULL, '2026-06-02 05:27:18', '2026-06-02 05:26:03', '2026-06-02 05:38:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `variant_name` varchar(255) DEFAULT NULL,
  `product_price` decimal(12,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_variant_id`, `product_name`, `variant_name`, `product_price`, `quantity`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 2, 1, NULL, 'Klepon', NULL, 15000.00, 1, 15000.00, '2026-06-02 05:26:03', '2026-06-02 05:26:03'),
(2, 2, 2, NULL, 'Kue Lapis', NULL, 25000.00, 1, 25000.00, '2026-06-02 05:26:03', '2026-06-02 05:26:03');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `rating_avg` decimal(3,2) NOT NULL DEFAULT 0.00,
  `rating_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `stock`, `image`, `is_featured`, `rating_avg`, `rating_count`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Klepon', 'klepon', 'Bola-bola ketan hijau berisi gula merah cair, dibalut kelapa parut segar. Sensasi ledakan manis di setiap gigitan.', 15000.00, 49, NULL, 1, 3.70, 50, '2026-05-30 09:23:22', '2026-06-02 05:27:18', NULL),
(2, 1, 'Kue Lapis', 'kue-lapis', 'Kue berlapis warna-warni dengan cita rasa manis legit. Dibuat dari tepung beras dan santan pilihan.', 25000.00, 29, NULL, 1, 4.30, 110, '2026-05-30 09:23:22', '2026-06-02 05:27:18', NULL),
(3, 1, 'Onde-Onde', 'onde-onde', 'Bola ketan isi kacang hijau manis, dibalut wijen dan digoreng hingga keemasan. Renyah di luar, lembut di dalam.', 18000.00, 40, NULL, 0, 4.00, 111, '2026-05-30 09:23:22', '2026-05-30 09:23:22', NULL),
(4, 1, 'Getuk Lindri', 'getuk-lindri', 'Singkong kukus yang dihaluskan dengan gula dan kelapa parut, dicetak cantik berwarna-warni.', 12000.00, 35, NULL, 0, 3.50, 33, '2026-05-30 09:23:22', '2026-05-30 09:23:22', NULL),
(5, 2, 'Kue Semprit', 'kue-semprit', 'Kue kering klasik berbentuk bunga dengan tekstur renyah yang lumer di mulut. Cocok untuk camilan teman teh.', 35000.00, 25, NULL, 0, 4.50, 8, '2026-05-30 09:23:22', '2026-05-30 09:23:22', NULL),
(6, 2, 'Kastengel', 'kastengel', 'Kue keju premium dengan rasa gurih yang kaya. Menggunakan keju Edam asli untuk cita rasa terbaik.', 45000.00, 20, NULL, 1, 3.50, 120, '2026-05-30 09:23:22', '2026-05-30 09:23:22', NULL),
(7, 3, 'Risoles Mayo', 'risoles-mayo', 'Kulit crepe renyah berisi ayam, mayones, dan sayuran segar. Digoreng dengan tepung panir hingga keemasan.', 20000.00, 45, NULL, 1, 3.70, 27, '2026-05-30 09:23:22', '2026-05-30 09:23:22', NULL),
(8, 3, 'Pastel Isi Ragout', 'pastel-isi-ragout', 'Kulit pastri renyah berlapis-lapis dengan isian ragout ayam wortel yang creamy dan gurih.', 22000.00, 35, NULL, 0, 3.70, 80, '2026-05-30 09:23:22', '2026-05-30 09:23:22', NULL),
(9, 3, 'Lumpia Semarang', 'lumpia-semarang', 'Lumpia goreng khas Semarang dengan isian rebung dan udang. Renyah dan beraroma harum.', 25000.00, 30, NULL, 0, 3.80, 109, '2026-05-30 09:23:22', '2026-05-30 09:23:22', NULL),
(10, 3, 'Combro', 'combro', 'Jajanan Sunda dari singkong parut berisi oncom pedas. Digoreng hingga kecokelatan dan renyah.', 10000.00, 0, NULL, 0, 3.60, 94, '2026-05-30 09:23:22', '2026-05-30 09:23:22', NULL),
(11, 4, 'Nagasari', 'nagasari', 'Kue kukus dari tepung beras dan santan, dibungkus daun pisang dengan potongan pisang raja di dalamnya.', 15000.00, 40, NULL, 1, 4.70, 94, '2026-05-30 09:23:22', '2026-05-30 09:23:22', NULL),
(12, 4, 'Putu Bambu', 'putu-bambu', 'Kue putu kukus di dalam bambu, berisi gula merah dan ditaburi kelapa parut. Aroma daun pandan yang harum.', 12000.00, 3, NULL, 0, 4.10, 106, '2026-05-30 09:23:22', '2026-05-30 09:23:22', NULL),
(13, 4, 'Dadar Gulung', 'dadar-gulung', 'Crepe hijau pandan lembut yang digulung berisi kelapa parut dan gula merah. Manis dan harum.', 18000.00, 30, NULL, 0, 3.50, 96, '2026-05-30 09:23:22', '2026-05-30 09:23:22', NULL),
(14, 4, 'Serabi Solo', 'serabi-solo', 'Kue tradisional Solo dari tepung beras dan santan, disajikan dengan kuah kinca gula merah yang kental.', 16000.00, 25, NULL, 1, 4.20, 27, '2026-05-30 09:23:22', '2026-05-30 09:23:22', NULL),
(15, 4, 'Lemper Ayam', 'lemper-ayam', 'Ketan pulen berisi ayam suwir berbumbu, dibungkus daun pisang dan dikukus hingga harum.', 20000.00, 35, NULL, 0, 3.80, 14, '2026-05-30 09:23:22', '2026-05-30 09:23:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_events`
--

CREATE TABLE `security_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `event_type` varchar(255) NOT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'low',
  `payload` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('ak5YNciC8EJbHPeantj1NqSX7LDoVRnM9jV6RnCi', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiIzR3FTUG9sS2JHS1pBUUpXTE5LcGNqbFUwSTZGN1BBSFB6WkJlc1pTIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3Byb2R1Y3RzIiwicm91dGUiOiJwcm9kdWN0cy5pbmRleCJ9LCJnZWdhcmVzX2NoYXRfaGlzdG9yeSI6W3sicm9sZSI6ImFzc2lzdGFudCIsImNvbnRlbnQiOiJIYWxvISBTYXlhIGFzaXN0ZW4gR2VnYXJlcy4gQWRhIHlhbmcgYmlzYSBzYXlhIGJhbnR1PyBLYW11IGp1Z2EgYmlzYSBraXJpbSBmb3RvIGphamFuYW4gcGFzYXIgdW50dWsgdGFueWEgbmFtYW55YSBsaG8hIiwidGltZSI6IjIzOjMxIiwic3VnZ2VzdGlvbnMiOlsiUmVrb21lbmRhc2kgamFqYW5hbiB0ZXJsYXJpcyIsIkNlayBzdGF0dXMgcGVzYW5hbiBzYXlhIiwiSmFtIG9wZXJhc2lvbmFsICYgbG9rYXNpIHRva28iLCJDYXJhIHBlc2FuICYgbWV0b2RlIGJheWFyIl19XSwiZ2VnYXJlc19jaGF0X2hhc2giOiJkNWQwMmQwMDM2ZmVlMDY0OTc1MGFmNmQyZTU4NWFmMWI5MThmMWMzYzE1ZTUyMmRkYzkyNGM5NWVkNDgyNDY0In0=', 1780938361);

-- --------------------------------------------------------

--
-- Table structure for table `store_settings`
--

CREATE TABLE `store_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_name` varchar(255) NOT NULL DEFAULT 'Gegares Ecommerce',
  `contact_phone` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `address_line` varchar(255) DEFAULT NULL,
  `area_id` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `biteship_location_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `google_avatar` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `notification_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_settings`)),
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `google_id`, `google_avatar`, `email_verified_at`, `password`, `role`, `avatar`, `phone`, `notification_settings`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Admin Gegares', 'admin@gegares.com', NULL, NULL, '2026-05-30 09:23:21', '$2y$12$0DKpcfa4Ge6oplOEEEY3VOdRH7.7eYSVOd.85QTJPF08yUWpT43LC', 'admin', NULL, '82112619691', '{\"order_updates\":true,\"promos\":true,\"newsletter\":false,\"last_read_at\":\"2026-06-08T16:56:53+07:00\"}', NULL, '2026-05-30 09:23:21', '2026-06-08 09:56:53', NULL),
(2, 'Rizki Arbiansyah', 'rizki@gmail.com', NULL, NULL, NULL, '$2y$12$3LnkU6bRmn.4abWdKzMkCOnLalKNyoJhQyzXc2sLAE5nHnOgHdQHm', 'user', NULL, '08211261991', NULL, NULL, '2026-05-30 10:11:50', '2026-05-30 10:11:50', NULL),
(3, 'Test SpacingSpacing Test', 'spacingtest@example.com', NULL, NULL, NULL, '$2y$12$f6rmP9N3uuBsQy3lLM6N/OQWTknoAbQQjW3ZMxCckyZmG.OaEV6Ue', 'user', NULL, '628123456788', '{\"order_updates\":true,\"promos\":true,\"newsletter\":true,\"last_read_at\":\"2026-06-02T14:51:12+07:00\"}', NULL, '2026-06-02 04:38:42', '2026-06-02 07:51:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `product_id`, `created_at`, `updated_at`) VALUES
(3, 3, 1, '2026-06-02 06:12:05', '2026-06-02 06:12:05'),
(4, 1, 1, '2026-06-08 09:58:04', '2026-06-08 09:58:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addresses_user_id_foreign` (`user_id`);

--
-- Indexes for table `banned_ips`
--
ALTER TABLE `banned_ips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `banned_ips_ip_address_unique` (`ip_address`),
  ADD KEY `banned_ips_banned_until_index` (`banned_until`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_user_id_foreign` (`user_id`),
  ADD KEY `orders_address_id_foreign` (`address_id`),
  ADD KEY `orders_biteship_order_id_index` (`biteship_order_id`),
  ADD KEY `orders_courier_tracking_id_index` (`courier_tracking_id`),
  ADD KEY `orders_coupon_id_foreign` (`coupon_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`),
  ADD KEY `order_items_product_variant_id_foreign` (`product_variant_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD KEY `products_category_id_foreign` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_foreign` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_variants_sku_unique` (`sku`),
  ADD KEY `product_variants_product_id_foreign` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviews_user_id_foreign` (`user_id`),
  ADD KEY `reviews_product_id_foreign` (`product_id`),
  ADD KEY `reviews_order_id_foreign` (`order_id`);

--
-- Indexes for table `security_events`
--
ALTER TABLE `security_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `security_events_user_id_foreign` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `store_settings`
--
ALTER TABLE `store_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_google_id_unique` (`google_id`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wishlists_user_id_product_id_unique` (`user_id`,`product_id`),
  ADD KEY `wishlists_product_id_foreign` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `banned_ips`
--
ALTER TABLE `banned_ips`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_events`
--
ALTER TABLE `security_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `store_settings`
--
ALTER TABLE `store_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`),
  ADD CONSTRAINT `orders_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `security_events`
--
ALTER TABLE `security_events`
  ADD CONSTRAINT `security_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
