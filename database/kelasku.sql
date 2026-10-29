-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20260412.9edf12e957
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 29, 2026 at 04:57 AM
-- Server version: 9.1.0
-- PHP Version: 8.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kelasku`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `room_id` bigint UNSIGNED NOT NULL,
  `purpose` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `participant_count` int UNSIGNED NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reviewed_by` bigint UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `lecture_schedule_id` bigint UNSIGNED DEFAULT NULL,
  `lecture_date` date DEFAULT NULL,
  `is_lecture_exception` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `created_at`, `updated_at`, `user_id`, `room_id`, `purpose`, `participant_count`, `starts_at`, `ends_at`, `status`, `reviewed_by`, `reviewed_at`, `rejection_reason`, `lecture_schedule_id`, `lecture_date`, `is_lecture_exception`) VALUES
(1, '2026-10-29 04:55:36', '2026-10-29 04:56:17', 999, 1, 'acara', 200, '2026-10-31 08:00:00', '2026-10-31 10:00:00', 'rejected', 999, '2026-10-29 11:56:17', 'butuh surat izin', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `created_at`, `updated_at`, `name`) VALUES
(1, '2026-10-29 03:52:05', '2026-10-29 03:52:05', 'Proyektor'),
(2, '2026-10-29 03:52:26', '2026-10-29 03:52:26', 'TV'),
(3, '2026-10-29 03:52:51', '2026-10-29 03:52:51', 'Colokan Tanam'),
(4, '2026-10-29 03:53:07', '2026-10-29 03:53:07', 'AC'),
(5, '2026-10-29 03:54:38', '2026-10-29 03:54:38', 'Kursi Tablet'),
(6, '2026-10-29 03:54:58', '2026-10-29 03:54:58', 'Kursi Biasa dan Meja Panjang');

-- --------------------------------------------------------

--
-- Table structure for table `facility_room`
--

CREATE TABLE `facility_room` (
  `id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `room_id` bigint UNSIGNED NOT NULL,
  `facility_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `facility_room`
--

INSERT INTO `facility_room` (`id`, `created_at`, `updated_at`, `room_id`, `facility_id`) VALUES
(1, '2026-10-29 03:55:53', '2026-10-29 03:55:53', 4, 4),
(2, '2026-10-29 03:55:53', '2026-10-29 03:55:53', 4, 5),
(3, '2026-10-29 03:55:53', '2026-10-29 03:55:53', 4, 2),
(8, '2026-10-29 04:05:39', '2026-10-29 04:05:39', 20, 4),
(9, '2026-10-29 04:05:39', '2026-10-29 04:05:39', 20, 3),
(10, '2026-10-29 04:05:39', '2026-10-29 04:05:39', 20, 5),
(11, '2026-10-29 04:05:39', '2026-10-29 04:05:39', 20, 1),
(12, '2026-10-29 04:06:21', '2026-10-29 04:06:21', 21, 4),
(13, '2026-10-29 04:06:21', '2026-10-29 04:06:21', 21, 3),
(14, '2026-10-29 04:06:21', '2026-10-29 04:06:21', 21, 5),
(15, '2026-10-29 04:06:21', '2026-10-29 04:06:21', 21, 1),
(16, '2026-10-29 04:07:38', '2026-10-29 04:07:38', 22, 4),
(17, '2026-10-29 04:07:38', '2026-10-29 04:07:38', 22, 5),
(18, '2026-10-29 04:07:38', '2026-10-29 04:07:38', 22, 1),
(19, '2026-10-29 04:08:26', '2026-10-29 04:08:26', 23, 4),
(20, '2026-10-29 04:08:26', '2026-10-29 04:08:26', 23, 5),
(21, '2026-10-29 04:08:26', '2026-10-29 04:08:26', 23, 1),
(22, '2026-10-29 04:11:17', '2026-10-29 04:11:17', 24, 4),
(23, '2026-10-29 04:11:17', '2026-10-29 04:11:17', 24, 5),
(24, '2026-10-29 04:11:17', '2026-10-29 04:11:17', 24, 1),
(25, '2026-10-29 04:12:02', '2026-10-29 04:12:02', 25, 4),
(26, '2026-10-29 04:12:02', '2026-10-29 04:12:02', 25, 5),
(27, '2026-10-29 04:12:02', '2026-10-29 04:12:02', 25, 1),
(28, '2026-10-29 04:12:57', '2026-10-29 04:12:57', 26, 4),
(29, '2026-10-29 04:12:57', '2026-10-29 04:12:57', 26, 3),
(30, '2026-10-29 04:12:57', '2026-10-29 04:12:57', 26, 5),
(31, '2026-10-29 04:12:57', '2026-10-29 04:12:57', 26, 1),
(32, '2026-10-29 04:13:47', '2026-10-29 04:13:47', 27, 4),
(33, '2026-10-29 04:13:47', '2026-10-29 04:13:47', 27, 5),
(34, '2026-10-29 04:13:47', '2026-10-29 04:13:47', 27, 1),
(35, '2026-10-29 04:14:28', '2026-10-29 04:14:28', 28, 4),
(36, '2026-10-29 04:14:28', '2026-10-29 04:14:28', 28, 5),
(37, '2026-10-29 04:14:28', '2026-10-29 04:14:28', 28, 1),
(38, '2026-10-29 04:15:10', '2026-10-29 04:15:10', 29, 4),
(39, '2026-10-29 04:15:10', '2026-10-29 04:15:10', 29, 5),
(40, '2026-10-29 04:15:10', '2026-10-29 04:15:10', 29, 1),
(41, '2026-10-29 04:15:40', '2026-10-29 04:15:40', 16, 4),
(42, '2026-10-29 04:15:40', '2026-10-29 04:15:40', 16, 5),
(43, '2026-10-29 04:15:40', '2026-10-29 04:15:40', 16, 1),
(44, '2026-10-29 04:16:09', '2026-10-29 04:16:09', 17, 4),
(45, '2026-10-29 04:16:09', '2026-10-29 04:16:09', 17, 3),
(46, '2026-10-29 04:16:09', '2026-10-29 04:16:09', 17, 6),
(47, '2026-10-29 04:16:09', '2026-10-29 04:16:09', 17, 1);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lecture_schedules`
--

CREATE TABLE `lecture_schedules` (
  `id` bigint UNSIGNED NOT NULL,
  `room_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `semester` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `participant_count` int UNSIGNED NOT NULL,
  `weekday` tinyint UNSIGNED NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `starts_on` date NOT NULL,
  `ends_on` date NOT NULL,
  `cancelled_from` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_10_26_101808_create_rooms_table', 2),
(5, '2026_10_26_101829_create_facilities_table', 2),
(6, '2026_10_26_101932_create_faciity_room_table', 2),
(7, '2026_10_26_102203_add_identity_and_role_to_users_table', 2),
(8, '2026_10_26_102346_create_bookings_table', 2),
(9, '2026_10_26_105130_add_identity_and_role_to_users_table', 2),
(10, '2026_10_26_105131_add_details_to_rooms_table', 3),
(11, '2026_10_26_105132_add_name_to_facilities_table', 3),
(12, '2026_10_26_105133_fix_facility_room_table', 3),
(13, '2026_10_26_105134_add_details_to_bookings_table', 3),
(14, '2026_10_26_105135_ensure_identity_and_role_on_users_table', 3),
(15, '2026_10_26_172908_update_roles_on_users_table', 4),
(16, '2026_10_29_000000_insert_unique_rooms', 5),
(17, '2026_10_29_000001_create_lecture_schedules_table', 6);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int UNSIGNED NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `created_at`, `updated_at`, `code`, `name`, `capacity`, `location`, `description`, `photo_path`, `is_active`) VALUES
(1, '2026-10-29 03:41:27', '2026-10-29 03:44:14', 'FMIPA-AUD-DADANG', 'AUDITORIUM GEDUNG PROF. DR. DADANG ISKANDAR', 200, 'Lantai 1', 'Memerlukan surat Izin untuk meminjam', NULL, 1),
(4, '2026-10-29 03:41:27', '2026-10-29 03:55:53', 'FMIPA-101', 'RUANG 101', 50, 'Lantai 1', 'Ruangan kelas', NULL, 1),
(5, '2026-10-29 03:41:27', '2026-10-29 03:45:22', 'FMIPA-102', 'RUANG 102', 50, 'Lantai 1', 'Ruangan kelas', NULL, 1),
(6, '2026-10-29 03:41:27', '2026-10-29 03:45:54', 'FMIPA-103', 'RUANG 103', 50, 'Lantai 1', 'Ruangan kelas', NULL, 1),
(7, '2026-10-29 03:41:27', '2026-10-29 03:47:34', 'FMIPA-106', 'RUANG 106', 50, 'Lantai 1', 'Ruangan kelas', NULL, 1),
(8, '2026-10-29 03:41:27', '2026-10-29 03:48:11', 'FMIPA-107', 'RUANG 107', 50, 'Lantai 1', 'Ruangan kelas', NULL, 1),
(9, '2026-10-29 03:41:27', '2026-10-29 03:48:33', 'FMIPA-201', 'RUANG 201', 50, 'Lantai 2', 'Ruang kelas lantai 2', NULL, 1),
(10, '2026-10-29 03:41:27', '2026-10-29 03:49:21', 'FMIPA-202', 'RUANG 202', 50, 'Lantai 2', 'Ruangan kelas lantai 2', NULL, 1),
(11, '2026-10-29 03:41:27', '2026-10-29 03:49:46', 'FMIPA-203', 'RUANG 203', 50, 'Lantai 2', 'Ruangan kelas lantai 2', NULL, 1),
(12, '2026-10-29 03:41:27', '2026-10-29 03:50:31', 'FMIPA-205', 'RUANG 205', 50, 'Lantai 2', 'Ruangan kelas lantai 2', NULL, 1),
(13, '2026-10-29 03:41:27', '2026-10-29 03:50:59', 'FMIPA-206', 'RUANG 206', 50, 'Lantai 2', 'Ruangan kelas lantai 2', NULL, 1),
(14, '2026-10-29 03:41:27', '2026-10-29 03:51:25', 'FMIPA-302', 'RUANG 302', 50, 'Lantai 3', 'Ruangan kelas lantai 3', NULL, 1),
(15, '2026-10-29 03:41:27', '2026-10-29 03:51:45', 'FMIPA-304', 'RUANG 304', 50, 'Lantai 3', 'Ruangan kelas lantai 3', NULL, 1),
(16, '2026-10-29 03:41:27', '2026-10-29 04:15:40', 'FMIPA-308', 'RUANG 308', 50, 'Lantai 3', 'Ruangan kelas lantai 3', NULL, 1),
(17, '2026-10-29 03:41:27', '2026-10-29 04:16:09', 'FMIPA-309', 'RUANG 309', 50, 'Lantai 3', 'Ruang kelas lantai 3', NULL, 1),
(18, '2026-10-29 03:46:38', '2026-10-29 03:46:38', 'FMIPA-104', 'RUANG 104', 50, 'Lantai 1', 'Ruangan kelas', NULL, 1),
(19, '2026-10-29 03:47:09', '2026-10-29 03:47:09', 'FMIPA-105', 'RUANG 105', 50, 'Lantai 1', 'Ruang kelas', NULL, 1),
(20, '2026-10-29 04:04:58', '2026-10-29 04:05:39', 'FMIPA-301-A', 'RUANG 301 A', 50, 'Lantai 3', 'Ruang kelas lantai 3', NULL, 1),
(21, '2026-10-29 04:06:21', '2026-10-29 04:06:21', 'FMIPA-301-B', 'RUANG 301 B', 50, 'Lantai 3', 'Ruang kelas lantai 3', NULL, 1),
(22, '2026-10-29 04:07:38', '2026-10-29 04:07:38', 'FMIPA-204', 'RUANG 204', 50, 'Lantai 2', 'Ruang kelas lantai 2', NULL, 1),
(23, '2026-10-29 04:08:26', '2026-10-29 04:08:26', 'FMIPA-207', 'RUANG 207', 50, 'Lantai 2', 'Ruangan kelas lantai 2', NULL, 1),
(24, '2026-10-29 04:11:17', '2026-10-29 04:11:17', 'FMIPA-208', 'RUANG 208', 50, 'Lantai 2', 'Ruang kelas lantai 2', NULL, 1),
(25, '2026-10-29 04:12:02', '2026-10-29 04:12:02', 'FMIPA-209', 'RUANG 209', 50, 'Lantai 2', 'Ruang kelas lantai 2', NULL, 1),
(26, '2026-10-29 04:12:57', '2026-10-29 04:12:57', 'FMIPA-303', 'RUANG 303', 50, 'Lantai 3', 'Ruang kelas lantai 3', NULL, 1),
(27, '2026-10-29 04:13:47', '2026-10-29 04:13:47', 'FMIPA-305', 'RUANG 305', 50, 'Lantai 3', 'Ruang kelas lantai 3', NULL, 1),
(28, '2026-10-29 04:14:28', '2026-10-29 04:14:28', 'FMIPA-306', 'RUANG 306', 50, 'Lantai 3', 'Ruang kelas lantai 3', NULL, 1),
(29, '2026-10-29 04:15:10', '2026-10-29 04:15:10', 'FMIPA-307', 'RUANG 307', 50, 'Lantai 3', 'Ruang kelas lantai 3', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('P3Py7AeC2MFRMNP9nNWB1VqHT0gwnoCGSIxMR661', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.144.0 Chrome/150.0.7871.250 Electron/43.8.3 Safari/537.36', 'eyJfdG9rZW4iOiJwd2p6TTBvbkI4Vmw0S00yUVJhNmRCcEFxQlQzZXlwMXpuME91RmdqIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1793244882),
('WoKhiRUSrY2lkhMnbMbRd7B5AhNU4ZOGqnICcyyW', 999, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJBWXpDVzdzaVBIWW94eHBZdWo0WjAzZDJFczJmNFdKT0kzUGo2WjY0IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjo5OTksInBhc3N3b3JkX2hhc2hfd2ViIjoiN2U1ODhmNjExM2E4MTA1YzFkMTBmOTcyNjE2OTA3ZGIzMDIwMDdhYzE3YTFlNmU0YTMxZGM0ZGQwNzE0YjY3MCJ9', 1793249822);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `identity_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','dosen','mahasiswa') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mahasiswa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `identity_number`, `role`) VALUES
(1, 'Test User', 'test@example.com', '2026-10-26 04:00:55', '$2y$12$c5cOlAUJtmItsNAMCnrTU.01LlhWU2F8LwXmi1JoQxdRNdIYZ8oE.', 'DHCGjmX5wz', '2026-10-26 04:00:55', '2026-10-26 04:00:55', NULL, 'mahasiswa'),
(2, 'Rasyid', 'mercuryus214@gmail.com', NULL, '$2y$12$/Vgtx.4dSflH5Qwj1MXYxeEKfx1krsyvF2N7yNhF6mxx9AxItWtiO', NULL, '2026-10-26 04:24:53', '2026-10-26 04:24:53', NULL, 'mahasiswa'),
(3, 'Rasyid Saputra', 'rasyid.saputra7126@student.unri.ac.id', NULL, '$2y$12$1CMu7h3ahSkDpe69Z11S8ugNPt0fcpWpXxMaQtMNK7G5i282u0MAm', NULL, '2026-10-26 09:06:33', '2026-10-26 09:06:33', '2403127126', 'mahasiswa'),
(999, 'Yue', 'yue@gmail.com', '2026-10-26 10:11:15', '$2y$12$L86.UxJ28TRnH7xVT7Aoe.kqf8kh4W7FuYAL10vfT7ACIvsuNcac.', NULL, '2026-10-26 10:11:15', '2026-10-26 10:38:18', '1', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_user_id_foreign` (`user_id`),
  ADD KEY `bookings_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `bookings_room_schedule_index` (`room_id`,`status`,`starts_at`,`ends_at`),
  ADD KEY `bookings_lecture_schedule_id_foreign` (`lecture_schedule_id`);

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
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `facilities_name_unique` (`name`);

--
-- Indexes for table `facility_room`
--
ALTER TABLE `facility_room`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `facility_room_room_id_facility_id_unique` (`room_id`,`facility_id`),
  ADD KEY `facility_room_facility_id_foreign` (`facility_id`);

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
-- Indexes for table `lecture_schedules`
--
ALTER TABLE `lecture_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecture_schedules_room_id_foreign` (`room_id`),
  ADD KEY `lecture_schedules_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rooms_code_unique` (`code`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_identity_number_unique` (`identity_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `facility_room`
--
ALTER TABLE `facility_room`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lecture_schedules`
--
ALTER TABLE `lecture_schedules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_lecture_schedule_id_foreign` FOREIGN KEY (`lecture_schedule_id`) REFERENCES `lecture_schedules` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `bookings_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `bookings_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `facility_room`
--
ALTER TABLE `facility_room`
  ADD CONSTRAINT `facility_room_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `facility_room_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lecture_schedules`
--
ALTER TABLE `lecture_schedules`
  ADD CONSTRAINT `lecture_schedules_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `lecture_schedules_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
