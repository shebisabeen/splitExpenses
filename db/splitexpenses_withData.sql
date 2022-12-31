-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 31, 2022 at 07:59 AM
-- Server version: 10.4.20-MariaDB
-- PHP Version: 7.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `splitexpenses`
--
CREATE DATABASE IF NOT EXISTS `splitexpenses` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `splitexpenses`;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE IF NOT EXISTS `expenses` (
  `expense_id` varchar(50) NOT NULL,
  `fk_group_id` varchar(50) NOT NULL,
  `description` varchar(500) NOT NULL,
  `amount` float NOT NULL DEFAULT 0,
  `payer` varchar(50) NOT NULL,
  `split` longtext NOT NULL,
  PRIMARY KEY (`expense_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`expense_id`, `fk_group_id`, `description`, `amount`, `payer`, `split`) VALUES
('63ad0269af811', '63abef8faad9c', 'test  expense 1', 250, '63abf5a590d16', '[{\"memberId\":\"63abf5a590d16\",\"amount\":\"120\"},{\"memberId\":\"63abf8c739760\",\"amount\":\"130\"}]'),
('63ad035e51444', '63abef8faad9c', 'test  expense 2', 200, '63abf8c739760', '[{\"memberId\":\"63abf5a590d16\",\"amount\":\"100\"},{\"memberId\":\"63abf8c739760\",\"amount\":\"100\"}]'),
('63ad045377aa2', '63abef8faad9c', 'test  expense 3', 300, '63abf8c739760', '[{\"memberId\":\"63abf5a590d16\",\"amount\":120},{\"memberId\":\"63abf8c739760\",\"amount\":180}]'),
('63ad04c09c63c', '63abef8faad9c', 'test  expense 4', 300, '63abf5a590d16', '[{\"memberId\":\"63abf5a590d16\",\"amount\":\"100\"},{\"memberId\":\"63abf8c739760\",\"amount\":\"200\"}]'),
('63ae401899632', '63abef8faad9c', 'test  expense 5', 600, '63abf5a590d16', '[{\"memberId\":\"63ae3f2b27ca0\",\"amount\":\"100\"},{\"memberId\":\"63abf8c739760\",\"amount\":\"150\"},{\"memberId\":\"63ae3eef042ec\",\"amount\":\"100\"},{\"memberId\":\"63ae3f0d0416d\",\"amount\":\"100\"},{\"memberId\":\"63ae3f1b4089b\",\"amount\":\"150\"}]'),
('63ae6a134f5db', '63abef8faad9c', 'test  expense 1', 650, '63abf8c739760', '[{\"memberId\":\"63ae3f2b27ca0\",\"amount\":\"100\"},{\"memberId\":\"63abf5a590d16\",\"amount\":\"100\"},{\"memberId\":\"63abf8c739760\",\"amount\":\"100\"},{\"memberId\":\"63ae3eef042ec\",\"amount\":\"100\"},{\"memberId\":\"63ae3f0d0416d\",\"amount\":\"100\"},{\"memberId\":\"63ae3f1b4089b\",\"amount\":\"150\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `group_id` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` varchar(200) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'active',
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`group_id`, `name`, `description`, `created_by`, `created_at`, `status`) VALUES
('63abef8faad9c', 'Group 1', 'test Group expenses 1', '63abe9bcec25c', '2022-12-28 02:56:07', 'active'),
('63abef99ab8fa', 'Group 2', 'test Group expenses 2', '63abe9bcec25c', '2022-12-28 02:56:17', 'active'),
('63abefaaf0a5b', 'Group 3', 'test Group expenses 3', '63abea290b083', '2022-12-28 02:56:34', 'active'),
('63ac0f2930f21', 'Group 4', 'test Group expenses 4', '63ac0ed9102a6', '2022-12-28 05:10:57', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

CREATE TABLE IF NOT EXISTS `group_members` (
  `member_id` varchar(50) NOT NULL,
  `fk_user_id` varchar(50) NOT NULL,
  `fk_group_id` varchar(50) NOT NULL,
  `total_paid` float NOT NULL DEFAULT 0,
  `total_share` float NOT NULL DEFAULT 0,
  PRIMARY KEY (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `group_members`
--

INSERT INTO `group_members` (`member_id`, `fk_user_id`, `fk_group_id`, `total_paid`, `total_share`) VALUES
('63abf5a590d16', '63abe9bcec25c', '63abef8faad9c', 1150, 540),
('63abf8c739760', '63abea290b083', '63abef8faad9c', 1150, 860),
('63ac0f2930f23', '63ac0ed9102a6', '63ac0f2930f21', 0, 0),
('63ae3eef042ec', '63abea9ddb761', '63abef8faad9c', 0, 200),
('63ae3f0d0416d', '63abeaace82c1', '63abef8faad9c', 0, 200),
('63ae3f1b4089b', '63abead6ae69a', '63abef8faad9c', 0, 300),
('63ae3f2b27ca0', '63abeae617db7', '63abef8faad9c', 0, 200),
('63ae50372b891', '63abe9bcec25c', '63ac0f2930f21', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `username` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `token` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` smallint(6) NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `username`, `email`, `password`, `token`, `created_at`, `is_active`) VALUES
('63abe9bcec25c', 'Sabeen CS', 'sabeencs', 'shebi.456@gmail.com', 'password', 'asefUDYcUyyDzv82iL5fcABpSorxat35', '2022-12-28 02:31:17', 1),
('63abea290b083', 'Vishnu R', 'vishnur', 'vishnurnair@gmail.com', 'password', 'yjHpr0QVNdr1VnnttpcIj3o3OeUaBcTp', '2022-12-28 02:33:05', 1),
('63abea9ddb761', 'Arjun S', 'arjuns', 'arjuns@gmail.com', 'password', '5cCVyXgEbTSulsR7h2jnoDV1c4WjLnK8', '2022-12-28 02:35:01', 1),
('63abeaace82c1', 'Rishi', 'rishi', 'rishi@gmail.com', 'password', 'O9w5L9RY4eJw5MBwqHN2yO6zMpjjf89p', '2022-12-28 02:35:16', 1),
('63abeac729c18', 'Arjun M', 'arjunm', 'arjunm@gmail.com', 'password', 'fJJR4tXYHC07duO9fMuWJfYt0NTyB6J2', '2022-12-28 02:35:43', 1),
('63abead6ae69a', 'Irfan M', 'irfanm', 'irfanm@gmail.com', 'password', 'pHncjWEAob075ltb2pDNcKzs47Vxh4tx', '2022-12-28 02:35:58', 1),
('63abeae617db7', 'Praveen', 'praveen', 'praveen@gmail.com', 'password', 'UP7plXG2tiUUfeOKdn0HX3ucZzKVGZLg', '2022-12-28 02:36:14', 1),
('63abeafac10ca', 'Naseem F', 'naseemf', 'naseemf@gmail.com', 'password', 'ZPjXBvhcLr9111dCLrsUtKzbVC5TgKMb', '2022-12-28 02:36:34', 1),
('63abeb1184a62', 'Fahad P', 'fahadp', 'fahadp@gmail.com', 'password', 'tbf1CoHbKiv8S9JTKhku0zOiPaCnj1CT', '2022-12-28 02:36:57', 1),
('63ac089c01281', 'Rifas Ali', 'rifasa', 'rifasa@gmail.com', 'password', 'sKD6GkMjiBunGMrrbXDiFSck5RCTqBAc', '2022-12-28 04:43:00', 1),
('63ac0ed9102a6', 'Jishnu M', 'jishnum', 'jishnum@gmail.com', 'password', 'EzCv2MCXxHbRy8zWPRB3e6vPMFiHhWS0', '2022-12-28 05:09:37', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
