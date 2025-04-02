-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2025 at 04:11 PM
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
-- Database: `signup`
--

-- --------------------------------------------------------

--
-- Table structure for table `allocations`
--

CREATE TABLE `allocations` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `allocated_to_name` varchar(255) NOT NULL,
  `allocated_to_username` varchar(255) NOT NULL,
  `campus` varchar(50) NOT NULL,
  `building` varchar(50) NOT NULL,
  `floor` int(11) NOT NULL,
  `room_number` int(11) NOT NULL,
  `allocation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `resource_type` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `allocation_requests`
--

CREATE TABLE `allocation_requests` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `campus` varchar(50) NOT NULL,
  `prefered_type` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `name` varchar(255) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `academic_rank` varchar(50) DEFAULT NULL,
  `work_range` varchar(50) DEFAULT NULL,
  `marital_status` varchar(50) DEFAULT NULL,
  `disability` varchar(50) DEFAULT NULL,
  `soamu` varchar(50) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allocation_requests`
--

INSERT INTO `allocation_requests` (`id`, `username`, `date`, `campus`, `prefered_type`, `status`, `name`, `gender`, `academic_rank`, `work_range`, `marital_status`, `disability`, `soamu`, `score`, `rank`) VALUES
(41, 'elsa', '0000-00-00', 'Main', 'two_bedroom', 'Approved', 'elsabet sewale mulualem', 'female', 'professor', '>8', 'unmarried', 'no', '1-4', 88, 2),
(42, 'kalkidan', '0000-00-00', 'Main', 'three_bedroom', 'Approved', 'kalkidan sewale mulualem', 'female', 'phd', '>8', 'married', 'no', '1-4', 86, 3),
(43, 'admasu', '0000-00-00', 'Main', 'two_bedroom', 'Approved', 'admasu sewale mulualem', 'male', 'researcher', '>8', 'unmarried', 'no', '>4', 83, 4),
(44, 'aye', '0000-00-00', 'Main', 'one_bedroom', 'Pending', 'aye tamiru chekol', 'female', 'professor', '>8', 'married', 'no', '1-4', 93, 1),
(45, 'sew', '0000-00-00', 'Main', 'three_bedroom', 'Approved', ' sewale mulualem yitbarek', 'male', 'msc', '5-8', 'married', 'no', '1-4', 73, 5);

-- --------------------------------------------------------

--
-- Table structure for table `appeals`
--

CREATE TABLE `appeals` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `appeal_reason` text NOT NULL,
  `allocation_type` enum('Residence','Office') NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolution_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appeals`
--

INSERT INTO `appeals` (`id`, `username`, `appeal_reason`, `allocation_type`, `status`, `resolved_at`, `resolution_message`, `created_at`) VALUES
(3, 'admasu', 'not fair', 'Residence', 'Rejected', '2025-03-29 10:55:21', 'its fair', '2025-03-29 10:54:39');

-- --------------------------------------------------------

--
-- Table structure for table `critical_allocation_requests`
--

CREATE TABLE `critical_allocation_requests` (
  `id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `resource_type` enum('residence','office') NOT NULL,
  `preferred_residence` varchar(255) DEFAULT NULL,
  `preferred_office` varchar(255) DEFAULT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `resource_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `form_permissions`
--

CREATE TABLE `form_permissions` (
  `id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance`
--

CREATE TABLE `maintenance` (
  `id` int(11) NOT NULL,
  `bfno` varchar(50) NOT NULL,
  `request_by` varchar(100) NOT NULL,
  `work_required` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `work_type` varchar(50) NOT NULL,
  `material_list` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `username`, `message`, `is_read`, `created_at`) VALUES
(26, 'admasu', 'Your residence allocation request has been successfully processed.', 1, '2025-03-13 15:27:04'),
(27, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-13 15:31:30'),
(28, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-14 11:54:23'),
(29, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-14 11:54:30'),
(30, 'admin', 'The allocation committee added a new resource.', 1, '2025-03-14 12:26:06'),
(31, 'aye', 'Your residence allocation request has been successfully processed.', 1, '2025-03-14 14:26:09'),
(32, 'elsa', 'Your residence allocation request has been successfully processed.', 1, '2025-03-14 14:26:26'),
(33, 'kalkidan', 'Your residence allocation request has been successfully processed.', 1, '2025-03-14 14:26:29'),
(34, 'admasu', 'Your residence allocation request has been successfully processed.', 1, '2025-03-14 14:26:29'),
(35, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-14 14:26:32'),
(36, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-14 14:26:36'),
(37, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-14 14:27:02'),
(38, 'kalkidan', 'Your residence allocation request has been successfully processed.', 1, '2025-03-14 15:01:32'),
(39, 'admin', 'The allocation committee added a new resource.', 1, '2025-03-14 19:46:46'),
(40, 'admin', 'The allocation committee added a new resource.', 1, '2025-03-14 19:46:50'),
(41, 'admasu', 'The allocation committee added a new resource.', 1, '2025-03-16 11:22:17'),
(42, 'director', 'The allocation committee added a new resource.', 0, '2025-03-16 11:45:40'),
(43, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 11:50:57'),
(44, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:32:35'),
(45, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:32:36'),
(46, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:32:39'),
(47, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:33:15'),
(48, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:33:36'),
(49, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:37:38'),
(50, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:37:50'),
(51, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:38:57'),
(52, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:45:01'),
(53, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:45:15'),
(54, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:46:05'),
(55, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:46:10'),
(56, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:48:13'),
(57, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:48:18'),
(58, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:48:18'),
(59, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:48:24'),
(60, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:48:59'),
(61, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:49:00'),
(62, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:49:00'),
(63, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:49:00'),
(64, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:49:10'),
(65, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:50:32'),
(66, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:50:33'),
(67, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:50:33'),
(68, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:50:37'),
(69, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:51:52'),
(70, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:51:52'),
(71, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:51:53'),
(72, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:51:53'),
(73, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:57:12'),
(74, 'committee', 'The allocation committee added a new resource.', 1, '2025-03-17 12:57:23'),
(75, 'committee', 'Your residence allocation request has been successfully processed.', 0, '2025-03-18 12:43:44'),
(76, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-22 19:49:10'),
(77, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-22 20:32:44'),
(78, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-22 20:54:04'),
(79, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-22 20:54:26'),
(80, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-22 20:59:10'),
(81, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-22 21:00:19'),
(82, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-22 21:01:40'),
(83, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-23 09:26:23'),
(84, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-23 09:26:35'),
(85, 'admasu', 'Your residence allocation request has been successfully processed.', 1, '2025-03-23 09:26:43'),
(86, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-23 15:54:55'),
(87, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-23 19:19:53'),
(88, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-23 19:19:58'),
(89, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-23 19:20:07'),
(90, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-23 19:20:15'),
(91, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-23 19:20:21'),
(92, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-23 19:20:27'),
(93, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-23 19:20:31'),
(94, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-23 19:20:52'),
(95, 'committee', 'Your residence allocation has been deallocated.', 0, '2025-03-24 19:49:13'),
(96, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-24 20:36:47'),
(97, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-24 20:37:24'),
(98, 'admasu', 'Your appeal for Office has been rejected. because the allocation result correctly so the appeal is rejected', 1, '2025-03-26 14:54:00'),
(99, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-27 18:01:23'),
(100, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-27 18:02:28'),
(101, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-27 18:27:13'),
(102, 'elsa', 'Your office allocation request has been rejected.', 1, '2025-03-27 19:03:28'),
(103, 'admasu', 'Your office allocation request has been rejected.', 1, '2025-03-27 19:10:26'),
(104, 'admasu', 'Your office allocation request has been successfully processed.', 1, '2025-03-27 19:43:27'),
(105, 'kalkidan', 'Your office allocation request has been successfully processed.', 0, '2025-03-27 19:43:27'),
(106, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-27 19:44:22'),
(107, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-27 19:44:50'),
(108, 'kalkidan', 'Your office allocation has been deallocated.', 0, '2025-03-27 19:51:54'),
(109, 'kalkidan', 'Your office allocation request has been successfully processed.', 0, '2025-03-27 19:52:19'),
(110, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-27 19:53:30'),
(111, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-27 19:53:51'),
(112, 'sew', 'Your office allocation request has been successfully processed.', 1, '2025-03-27 19:53:59'),
(113, 'aye', 'Your office allocation request has been successfully processed.', 0, '2025-03-27 19:53:59'),
(114, 'admasu', 'Your office allocation has been deallocated.', 1, '2025-03-28 17:30:41'),
(115, 'kalkidan', 'Your office allocation has been deallocated.', 0, '2025-03-28 17:30:41'),
(116, 'sew', 'Your office allocation has been deallocated.', 1, '2025-03-28 17:30:41'),
(117, 'aye', 'Your office allocation has been deallocated.', 0, '2025-03-28 17:30:41'),
(118, 'aye', 'Your residence allocation request has been successfully processed.', 0, '2025-03-28 17:42:54'),
(119, 'admasu', 'Your residence allocation request has been successfully processed.', 1, '2025-03-28 17:42:54'),
(120, 'kalkidan', 'Your residence allocation request has been successfully processed.', 0, '2025-03-28 17:42:54'),
(121, 'admasu', 'Your residence allocation has been deallocated.', 1, '2025-03-28 17:43:03'),
(122, 'aye', 'Your residence allocation has been deallocated.', 0, '2025-03-28 17:43:03'),
(123, 'kalkidan', 'Your residence allocation has been deallocated.', 0, '2025-03-28 17:43:03'),
(124, 'elsa', 'Your residence allocation request has been rejected.', 0, '2025-03-28 19:08:34'),
(125, 'elsa', 'Your residence allocation request has been successfully processed.', 0, '2025-03-28 21:07:16'),
(126, 'kalkidan', 'Your residence allocation request has been successfully processed.', 0, '2025-03-28 21:07:16'),
(127, 'admasu', 'Your residence allocation request has been successfully processed.', 1, '2025-03-28 21:07:16'),
(128, 'sew', 'Your residence allocation request has been successfully processed.', 1, '2025-03-28 21:07:16'),
(129, 'elsa', 'Your residence allocation has been deallocated.', 0, '2025-03-28 21:11:08'),
(130, 'kalkidan', 'Your residence allocation has been deallocated.', 0, '2025-03-28 21:11:09'),
(131, 'admasu', 'Your residence allocation has been deallocated.', 1, '2025-03-28 21:11:09'),
(132, 'sew', 'Your residence allocation has been deallocated.', 1, '2025-03-28 21:11:09'),
(133, 'elsa', 'Your residence allocation request has been successfully processed.', 0, '2025-03-28 21:11:55'),
(134, 'kalkidan', 'Your residence allocation request has been successfully processed.', 0, '2025-03-28 21:11:55'),
(135, 'admasu', 'Your residence allocation request has been successfully processed.', 1, '2025-03-28 21:11:55'),
(136, 'sew', 'Your residence allocation request has been successfully processed.', 1, '2025-03-28 21:11:55'),
(137, 'elsa', 'Your residence allocation has been deallocated.', 0, '2025-03-28 21:15:10'),
(138, 'kalkidan', 'Your residence allocation has been deallocated.', 0, '2025-03-28 21:15:10'),
(139, 'admasu', 'Your residence allocation has been deallocated.', 1, '2025-03-28 21:15:10'),
(140, 'sew', 'Your residence allocation has been deallocated.', 1, '2025-03-28 21:15:10'),
(141, 'elsa', 'Your residence allocation request has been successfully processed.', 0, '2025-03-28 21:15:44'),
(142, 'kalkidan', 'Your residence allocation request has been successfully processed.', 0, '2025-03-28 21:16:48'),
(143, 'admasu', 'Your residence allocation request has been successfully processed.', 1, '2025-03-28 21:16:48'),
(144, 'sew', 'Your residence allocation request has been successfully processed.', 1, '2025-03-28 21:16:48'),
(145, 'admasu', 'Your appeal for Residence has been rejected. its fair', 1, '2025-03-29 10:55:21'),
(146, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 10:58:39'),
(147, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:12:19'),
(148, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:12:50'),
(149, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:16:08'),
(150, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:16:56'),
(151, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:17:16'),
(152, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:18:00'),
(153, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:18:12'),
(154, 'elsa', 'Your residence allocation has been deallocated.', 0, '2025-03-29 11:23:11'),
(155, 'kalkidan', 'Your residence allocation has been deallocated.', 0, '2025-03-29 11:23:11'),
(156, 'admasu', 'Your residence allocation has been deallocated.', 1, '2025-03-29 11:23:11'),
(157, 'sew', 'Your residence allocation has been deallocated.', 0, '2025-03-29 11:23:11'),
(158, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:29:26'),
(159, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:30:47'),
(160, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:31:28'),
(161, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:42:53'),
(162, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:46:23'),
(163, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:46:53'),
(164, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:47:00'),
(165, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:47:20'),
(166, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:47:21'),
(167, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:47:21'),
(168, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:47:21'),
(169, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:47:21'),
(170, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-29 11:54:02'),
(171, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-30 12:09:10'),
(172, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-30 12:29:40'),
(173, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-30 12:30:15'),
(174, 'committee', 'The allocation committee added a new resource.', 0, '2025-03-30 18:36:23');

-- --------------------------------------------------------

--
-- Table structure for table `office_allocation`
--

CREATE TABLE `office_allocation` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `allocated_to_name` varchar(255) NOT NULL,
  `allocated_to_username` varchar(255) NOT NULL,
  `campus` varchar(50) NOT NULL,
  `building` varchar(50) NOT NULL,
  `floor` int(11) NOT NULL,
  `room_number` int(11) NOT NULL,
  `allocation_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `office_allocation_requests`
--

CREATE TABLE `office_allocation_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `date` date NOT NULL,
  `campus` varchar(50) NOT NULL,
  `office_type` enum('private','shared','open_space') NOT NULL,
  `academic_rank` varchar(50) NOT NULL,
  `work_range` varchar(50) NOT NULL,
  `disability` enum('no','yes') NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `score` int(11) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `office_allocation_requests`
--

INSERT INTO `office_allocation_requests` (`id`, `name`, `username`, `gender`, `date`, `campus`, `office_type`, `academic_rank`, `work_range`, `disability`, `status`, `score`, `rank`, `created_at`) VALUES
(9, 'Admasu sewale mulualem', 'admasu', 'male', '0000-00-00', 'Main', 'private', 'professor', '>8', 'no', 'Pending', 90, 1, '2025-03-27 18:14:38'),
(10, 'elsabet sewale mulualem', 'elsa', 'female', '0000-00-00', 'Chamo', 'shared', 'professor', '>8', 'no', 'Pending', 95, 1, '2025-03-27 18:15:18'),
(11, 'kalkidan sewale mulualem', 'kalkidan', 'female', '0000-00-00', 'Main', 'private', 'phd', '5-8', 'no', 'Pending', 86, 2, '2025-03-27 18:16:02'),
(12, ' sewale mulualem yitbarek', 'sew', 'male', '0000-00-00', 'Chamo', 'private', 'researcher', '3-5', 'no', 'Pending', 83, 3, '2025-03-27 18:50:12'),
(13, 'aye tamiru chekol', 'aye', 'female', '0000-00-00', 'Chamo', 'private', 'msc', '5-8', 'no', 'Pending', 80, 4, '2025-03-27 18:51:42');

-- --------------------------------------------------------

--
-- Table structure for table `office_reports`
--

CREATE TABLE `office_reports` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_to_director` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `office_resource`
--

CREATE TABLE `office_resource` (
  `id` int(11) NOT NULL,
  `campus` varchar(50) NOT NULL,
  `building` varchar(50) NOT NULL,
  `floor` int(11) NOT NULL,
  `room_number` int(11) NOT NULL,
  `resource_type` enum('private','shared') NOT NULL,
  `status` enum('Available','Allocated') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `office_resource`
--

INSERT INTO `office_resource` (`id`, `campus`, `building`, `floor`, `room_number`, `resource_type`, `status`) VALUES
(1, 'Main', 'registral', 1, 1, 'private', 'Allocated'),
(2, 'Main', 'registral', 1, 2, 'private', 'Allocated'),
(3, 'Main', 'registral', 1, 3, 'private', 'Allocated'),
(4, 'Main', 'registral', 1, 4, 'private', 'Allocated'),
(5, 'Main', 'registral', 1, 5, 'private', 'Allocated'),
(6, 'Main', 'registral', 1, 6, 'private', 'Available'),
(7, 'Main', 'registral', 1, 7, 'private', 'Available'),
(8, 'Main', 'registral', 1, 8, 'private', 'Available'),
(9, 'Main', 'registral', 1, 9, 'private', 'Available'),
(10, 'Main', 'registral', 1, 10, 'private', 'Available'),
(11, 'Main', 'registral', 1, 11, 'private', 'Available'),
(12, 'Main', 'registral', 1, 12, 'private', 'Available'),
(13, 'Main', 'registral', 2, 13, 'shared', 'Available'),
(14, 'Main', 'registral', 2, 14, 'shared', 'Available'),
(15, 'Main', 'registral', 2, 15, 'shared', 'Available'),
(16, 'Main', 'registral', 2, 16, 'shared', 'Available'),
(17, 'Main', 'registral', 2, 17, 'shared', 'Available'),
(18, 'Main', 'registral', 2, 18, 'shared', 'Available'),
(19, 'Main', 'registral', 2, 19, 'shared', 'Available'),
(20, 'Main', 'registral', 2, 20, 'shared', 'Available'),
(21, 'Chamo', '12', 1, 1, 'private', 'Available'),
(22, 'Chamo', '12', 1, 2, 'private', 'Available'),
(23, 'Chamo', '12', 1, 3, 'private', 'Available'),
(24, 'Chamo', '12', 1, 4, 'private', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `sent_to_director` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `title`, `data`, `sent_to_director`, `created_at`) VALUES
(1, 'Track Resource Report', '[{\"campus\":\"Abaya\",\"building\":\"buildings 001 abaya\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"three_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Chamo\",\"building\":\"buildings 001 chamo\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"one_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Chamo\",\"building\":\"chamo buildings 001\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"three_bedroom\",\"status\":\"Available\"},{\"campus\":\"Chamo\",\"building\":\"chamo buildings 001\",\"floor\":\"2\",\"room_number\":\"2\",\"resource_type\":\"three_bedroom\",\"status\":\"Available\"},{\"campus\":\"Chamo\",\"building\":\"chamo buildings 001\",\"floor\":\"2\",\"room_number\":\"3\",\"resource_type\":\"three_bedroom\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"buildings 001\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"three_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Main\",\"building\":\"buildings 001\",\"floor\":\"2\",\"room_number\":\"2\",\"resource_type\":\"three_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Main\",\"building\":\"buildings 003\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"two_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Main\",\"building\":\"buildings 003\",\"floor\":\"1\",\"room_number\":\"2\",\"resource_type\":\"two_bedroom\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"buildings 003\",\"floor\":\"1\",\"room_number\":\"3\",\"resource_type\":\"two_bedroom\",\"status\":\"Available\"},{\"campus\":\"Sawula\",\"building\":\"sawula building 001\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"studio\",\"status\":\"Available\"},{\"campus\":\"Sawula\",\"building\":\"sawula building 001\",\"floor\":\"1\",\"room_number\":\"2\",\"resource_type\":\"studio\",\"status\":\"Available\"},{\"campus\":\"Sawula\",\"building\":\"sawula building 001\",\"floor\":\"1\",\"room_number\":\"3\",\"resource_type\":\"studio\",\"status\":\"Available\"}]', 1, '2025-03-18 16:01:02'),
(2, 'About Allocation Report', '[{\"allocated_to_name\":\"Admasu sewale muluale\",\"campus\":\"Main\",\"building\":\"buildings 001\",\"floor\":\"2\",\"room_number\":\"2\",\"resource_type\":\"three_bedroom\",\"status\":\"Allocated\"}]', 1, '2025-03-18 16:08:16'),
(3, 'Maintenance Issue Report', '[{\"bfno\":\"BA\",\"request_by\":\"adma\",\"work_required\":\"SDAF\",\"location\":\"AVV\",\"date\":\"2025-03-08\",\"work_type\":\"Plumbing\",\"material_list\":\"DDFFFD\",\"status\":\"Completed\"}]', 1, '2025-03-18 16:16:41'),
(6, 'Maintenance Issue Report', '[{\"bfno\":\"BA\",\"request_by\":\"adma\",\"work_required\":\"SDAF\",\"location\":\"AVV\",\"date\":\"2025-03-08\",\"work_type\":\"Plumbing\",\"material_list\":\"DDFFFD\",\"status\":\"Completed\"}]', 0, '2025-03-18 19:23:51'),
(7, 'Maintenance Issue Report', '[{\"bfno\":\"BA\",\"request_by\":\"adma\",\"work_required\":\"SDAF\",\"location\":\"AVV\",\"date\":\"2025-03-08\",\"work_type\":\"Plumbing\",\"material_list\":\"DDFFFD\",\"status\":\"Completed\"}]', 0, '2025-03-18 19:23:52'),
(8, 'Maintenance Issue Report', '[{\"bfno\":\"BA\",\"request_by\":\"adma\",\"work_required\":\"SDAF\",\"location\":\"AVV\",\"date\":\"2025-03-08\",\"work_type\":\"Plumbing\",\"material_list\":\"DDFFFD\",\"status\":\"Completed\"}]', 0, '2025-03-18 19:23:53'),
(9, 'Maintenance Issue Report', '[{\"bfno\":\"BA\",\"request_by\":\"adma\",\"work_required\":\"SDAF\",\"location\":\"AVV\",\"date\":\"2025-03-08\",\"work_type\":\"Plumbing\",\"material_list\":\"DDFFFD\",\"status\":\"Completed\"}]', 0, '2025-03-18 19:24:11'),
(10, 'Track Resource Report', '[{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"private\",\"status\":\"Allocated\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"2\",\"resource_type\":\"private\",\"status\":\"Allocated\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"3\",\"resource_type\":\"private\",\"status\":\"Allocated\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"4\",\"resource_type\":\"private\",\"status\":\"Allocated\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"5\",\"resource_type\":\"private\",\"status\":\"Allocated\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"6\",\"resource_type\":\"private\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"7\",\"resource_type\":\"private\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"8\",\"resource_type\":\"private\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"9\",\"resource_type\":\"private\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"10\",\"resource_type\":\"private\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"11\",\"resource_type\":\"private\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"12\",\"resource_type\":\"private\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"2\",\"room_number\":\"13\",\"resource_type\":\"shared\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"2\",\"room_number\":\"14\",\"resource_type\":\"shared\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"2\",\"room_number\":\"15\",\"resource_type\":\"shared\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"2\",\"room_number\":\"16\",\"resource_type\":\"shared\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"2\",\"room_number\":\"17\",\"resource_type\":\"shared\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"2\",\"room_number\":\"18\",\"resource_type\":\"shared\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"2\",\"room_number\":\"19\",\"resource_type\":\"shared\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"2\",\"room_number\":\"20\",\"resource_type\":\"shared\",\"status\":\"Available\"}]', 0, '2025-03-18 20:54:57'),
(11, 'About Allocation Report', '[{\"allocated_to_name\":\"aye tamiru chekol\",\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"2\",\"resource_type\":\"private\",\"status\":\"\"},{\"allocated_to_name\":\"elsabet sewale mulualem\",\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"3\",\"resource_type\":\"private\",\"status\":\"\"},{\"allocated_to_name\":\"kalkidan sewale mulualem\",\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"4\",\"resource_type\":\"private\",\"status\":\"\"},{\"allocated_to_name\":\"Admasu sewale muluale\",\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"5\",\"resource_type\":\"private\",\"status\":\"\"}]', 0, '2025-03-18 20:55:18'),
(12, 'About Allocation Report', '[{\"allocated_to_name\":\"aye tamiru chekol\",\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"2\",\"resource_type\":\"private\",\"status\":\"\"},{\"allocated_to_name\":\"elsabet sewale mulualem\",\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"3\",\"resource_type\":\"private\",\"status\":\"\"},{\"allocated_to_name\":\"kalkidan sewale mulualem\",\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"4\",\"resource_type\":\"private\",\"status\":\"\"},{\"allocated_to_name\":\"Admasu sewale muluale\",\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"5\",\"resource_type\":\"private\",\"status\":\"\"}]', 0, '2025-03-18 20:58:36'),
(13, 'About Allocation Report', '[{\"allocated_to_name\":\"aye tamiru chekol\",\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"2\",\"resource_type\":\"private\",\"status\":\"\"},{\"allocated_to_name\":\"elsabet sewale mulualem\",\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"3\",\"resource_type\":\"private\",\"status\":\"\"},{\"allocated_to_name\":\"kalkidan sewale mulualem\",\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"4\",\"resource_type\":\"private\",\"status\":\"\"},{\"allocated_to_name\":\"Admasu sewale muluale\",\"campus\":\"Main\",\"building\":\"registral\",\"floor\":\"1\",\"room_number\":\"5\",\"resource_type\":\"private\",\"status\":\"\"}]', 1, '2025-03-18 20:58:59'),
(14, 'Maintenance Issue Report', '[{\"bfno\":\"BA\",\"request_by\":\"adma\",\"work_required\":\"SDAF\",\"location\":\"AVV\",\"date\":\"2025-03-08\",\"work_type\":\"Plumbing\",\"material_list\":\"DDFFFD\",\"status\":\"Completed\"}]', 0, '2025-03-20 18:18:44'),
(15, 'Track Resource Report', '[{\"campus\":\"Abaya\",\"building\":\"buildings 001 abaya\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"three_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Chamo\",\"building\":\"buildings 001 chamo\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"one_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Chamo\",\"building\":\"chamo buildings 001\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"three_bedroom\",\"status\":\"Available\"},{\"campus\":\"Chamo\",\"building\":\"chamo buildings 001\",\"floor\":\"2\",\"room_number\":\"2\",\"resource_type\":\"three_bedroom\",\"status\":\"Available\"},{\"campus\":\"Chamo\",\"building\":\"chamo buildings 001\",\"floor\":\"2\",\"room_number\":\"3\",\"resource_type\":\"three_bedroom\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"34\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"three_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Main\",\"building\":\"34\",\"floor\":\"1\",\"room_number\":\"2\",\"resource_type\":\"three_bedroom\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"buildings 001\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"three_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Main\",\"building\":\"buildings 001\",\"floor\":\"2\",\"room_number\":\"2\",\"resource_type\":\"three_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Main\",\"building\":\"buildings 003\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"two_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Main\",\"building\":\"buildings 003\",\"floor\":\"1\",\"room_number\":\"2\",\"resource_type\":\"two_bedroom\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"buildings 003\",\"floor\":\"1\",\"room_number\":\"3\",\"resource_type\":\"two_bedroom\",\"status\":\"Available\"},{\"campus\":\"Sawula\",\"building\":\"sawula building 001\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"studio\",\"status\":\"Available\"},{\"campus\":\"Sawula\",\"building\":\"sawula building 001\",\"floor\":\"1\",\"room_number\":\"2\",\"resource_type\":\"studio\",\"status\":\"Available\"},{\"campus\":\"Sawula\",\"building\":\"sawula building 001\",\"floor\":\"1\",\"room_number\":\"3\",\"resource_type\":\"studio\",\"status\":\"Available\"}]', 0, '2025-03-23 13:50:01'),
(16, 'Track Resource Report', '[{\"campus\":\"Abaya\",\"building\":\"buildings 001 abaya\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"three_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Chamo\",\"building\":\"buildings 001 chamo\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"one_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Chamo\",\"building\":\"chamo buildings 001\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"three_bedroom\",\"status\":\"Available\"},{\"campus\":\"Chamo\",\"building\":\"chamo buildings 001\",\"floor\":\"2\",\"room_number\":\"2\",\"resource_type\":\"three_bedroom\",\"status\":\"Available\"},{\"campus\":\"Chamo\",\"building\":\"chamo buildings 001\",\"floor\":\"2\",\"room_number\":\"3\",\"resource_type\":\"three_bedroom\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"34\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"three_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Main\",\"building\":\"34\",\"floor\":\"1\",\"room_number\":\"2\",\"resource_type\":\"three_bedroom\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"buildings 001\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"three_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Main\",\"building\":\"buildings 001\",\"floor\":\"2\",\"room_number\":\"2\",\"resource_type\":\"three_bedroom\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"buildings 003\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"two_bedroom\",\"status\":\"Allocated\"},{\"campus\":\"Main\",\"building\":\"buildings 003\",\"floor\":\"1\",\"room_number\":\"2\",\"resource_type\":\"two_bedroom\",\"status\":\"Available\"},{\"campus\":\"Main\",\"building\":\"buildings 003\",\"floor\":\"1\",\"room_number\":\"3\",\"resource_type\":\"two_bedroom\",\"status\":\"Available\"},{\"campus\":\"Sawula\",\"building\":\"sawula building 001\",\"floor\":\"1\",\"room_number\":\"1\",\"resource_type\":\"studio\",\"status\":\"Available\"},{\"campus\":\"Sawula\",\"building\":\"sawula building 001\",\"floor\":\"1\",\"room_number\":\"2\",\"resource_type\":\"studio\",\"status\":\"Available\"},{\"campus\":\"Sawula\",\"building\":\"sawula building 001\",\"floor\":\"1\",\"room_number\":\"3\",\"resource_type\":\"studio\",\"status\":\"Available\"}]', 1, '2025-03-27 18:02:06');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `campus` varchar(50) NOT NULL,
  `building` varchar(50) NOT NULL,
  `floor` int(11) NOT NULL,
  `room_number` int(11) NOT NULL,
  `resource_type` enum('three_bedroom','two_bedroom','one_bedroom','studio','service') NOT NULL,
  `status` enum('Available','Allocated') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `campus`, `building`, `floor`, `room_number`, `resource_type`, `status`) VALUES
(18, 'Main', 'buildings 001', 1, 1, 'three_bedroom', 'Allocated'),
(19, 'Chamo', 'chamo buildings 001', 1, 1, 'three_bedroom', 'Available'),
(20, 'Main', 'buildings 001', 2, 2, 'three_bedroom', 'Available'),
(21, 'Main', 'buildings 003', 1, 1, 'two_bedroom', 'Allocated'),
(22, 'Main', 'buildings 003', 1, 2, 'two_bedroom', 'Available'),
(23, 'Main', 'buildings 003', 1, 3, 'two_bedroom', 'Available'),
(24, 'Abaya', 'buildings 001 abaya', 1, 1, 'three_bedroom', 'Allocated'),
(25, 'Chamo', 'buildings 001 chamo', 1, 1, 'one_bedroom', 'Allocated'),
(26, 'Chamo', 'chamo buildings 001', 2, 2, 'three_bedroom', 'Available'),
(27, 'Chamo', 'chamo buildings 001', 2, 3, 'three_bedroom', 'Available'),
(28, 'Sawula', 'sawula building 001', 1, 1, 'studio', 'Available'),
(29, 'Sawula', 'sawula building 001', 1, 2, 'studio', 'Available'),
(30, 'Sawula', 'sawula building 001', 1, 3, 'studio', 'Available'),
(31, 'Main', '34', 1, 1, 'three_bedroom', 'Allocated'),
(32, 'Main', '34', 1, 2, 'three_bedroom', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','allocation_committee','staff_member','managing_director') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'nsr.480.14', 'AMU/AD003', 'admin'),
(2, 'committee', 'committee123', 'allocation_committee'),
(4, 'director', 'director123', 'managing_director'),
(8, 'admasu', 'admasu', 'staff_member'),
(9, 'kalkidan', 'kalkidan', 'staff_member'),
(10, 'elsa', 'elsa', 'staff_member'),
(24, 'committee1', 'AMU/A003', 'allocation_committee'),
(25, 'debe1', '1234', 'managing_director'),
(27, 'esey', 'AMU/D005', 'managing_director'),
(29, 'admin', 'admin', 'admin'),
(30, 'aye', 'aye', 'staff_member'),
(31, 'sew', 'sew', 'staff_member');

-- --------------------------------------------------------

--
-- Table structure for table `user_detail`
--

CREATE TABLE `user_detail` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `college` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `employment_date` date NOT NULL,
  `academic_rank` enum('Professor','Researcher','PhD','MSc','BSc') NOT NULL,
  `work_range` enum('>8','5-8','3-5','1-3') NOT NULL,
  `marital_status` enum('married','unmarried','divorced') NOT NULL,
  `children` int(11) DEFAULT 0,
  `spouse` enum('yes','no') NOT NULL,
  `spouse_name` varchar(255) DEFAULT NULL,
  `disability` enum('yes','no') NOT NULL,
  `soamu` enum('1-4','>4') NOT NULL,
  `current_address` enum('private','university') NOT NULL,
  `unit_type` enum('three_bedroom','two_bedroom','one_bedroom','studio','service') NOT NULL,
  `email` varchar(255) NOT NULL,
  `alt_email` varchar(255) DEFAULT NULL,
  `phone_home` varchar(20) DEFAULT NULL,
  `phone_mobile` varchar(20) NOT NULL,
  `status` enum('Pending','Approved','Rejected','Deferred') DEFAULT 'Pending',
  `score` int(11) DEFAULT 0,
  `rank` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_approved` tinyint(1) DEFAULT 0,
  `is_rejected` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_detail`
--

INSERT INTO `user_detail` (`id`, `user_id`, `name`, `gender`, `college`, `department`, `employment_date`, `academic_rank`, `work_range`, `marital_status`, `children`, `spouse`, `spouse_name`, `disability`, `soamu`, `current_address`, `unit_type`, `email`, `alt_email`, `phone_home`, `phone_mobile`, `status`, `score`, `rank`, `created_at`, `updated_at`, `is_approved`, `is_rejected`) VALUES
(7, 8, 'Admasu sewale mulualem', 'male', 'AMIT', 'CS', '2025-03-14', 'Professor', '', 'unmarried', 0, 'no', '', 'no', '', 'private', 'three_bedroom', 'admasusewale66@gmail.com', 'admasusewale66@gmail.com', '123456', '1234', 'Pending', 0, 0, '2025-03-14 12:15:01', '2025-03-30 07:32:01', 1, 0),
(8, 9, 'kalkidan sewale mulualem', 'female', 'amit', 'it', '2025-03-14', 'Professor', '', 'unmarried', 0, 'no', '', 'no', '1-4', 'private', 'three_bedroom', 'kalkidansewale@gmail.com', 'kalkidansewale@gmail.com', '12345678', '12345678', 'Pending', 0, 0, '2025-03-14 18:21:32', '2025-03-30 07:31:55', 1, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allocation_requests`
--
ALTER TABLE `allocation_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appeals`
--
ALTER TABLE `appeals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `critical_allocation_requests`
--
ALTER TABLE `critical_allocation_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `form_permissions`
--
ALTER TABLE `form_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `office_allocation`
--
ALTER TABLE `office_allocation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `office_allocation_requests`
--
ALTER TABLE `office_allocation_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `office_reports`
--
ALTER TABLE `office_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `office_resource`
--
ALTER TABLE `office_resource`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_room` (`campus`,`building`,`floor`,`room_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_detail`
--
ALTER TABLE `user_detail`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allocation_requests`
--
ALTER TABLE `allocation_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `appeals`
--
ALTER TABLE `appeals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `critical_allocation_requests`
--
ALTER TABLE `critical_allocation_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `form_permissions`
--
ALTER TABLE `form_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT for table `office_allocation`
--
ALTER TABLE `office_allocation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `office_allocation_requests`
--
ALTER TABLE `office_allocation_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `office_reports`
--
ALTER TABLE `office_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `office_resource`
--
ALTER TABLE `office_resource`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `user_detail`
--
ALTER TABLE `user_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
