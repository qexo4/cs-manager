-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Cze 05, 2026 at 06:17 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cs2`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `end_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `profit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `result` enum('win','lose') NOT NULL DEFAULT 'win',
  `ban_days` int(11) NOT NULL DEFAULT 0,
  `ban_start_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `name`, `amount`, `end_amount`, `profit`, `result`, `ban_days`, `ban_start_at`) VALUES
(4, 'qaudunix', 100.00, 600.00, 500.00, 'win', 8, '2026-06-01 17:02:02'),
(6, 'wodnerMan', 0.00, 0.00, 0.00, 'lose', 8, '2026-06-01 21:35:11'),
(7, 'freemoney', 20.00, 0.00, -20.00, 'win', 8, '2026-05-24 12:39:24'),
(8, 'qeXo', 0.00, 0.00, 0.00, 'lose', 30, '2026-05-29 16:02:07'),
(10, 'pawciqu', 0.00, 0.00, 0.00, 'win', 8, '2026-06-01 18:47:37'),
(12, 'goldenalex23', 0.00, 0.00, 0.00, 'lose', 15, '2026-06-02 23:24:11'),
(13, 'skieer2137', 0.00, 0.00, 0.00, 'lose', 15, '2026-06-02 23:24:35'),
(14, 'tripletmammon', 0.00, 0.00, 0.00, 'win', 15, '2026-06-02 23:24:51'),
(15, 'retuuk200', 0.00, 0.00, 0.00, 'win', 15, '2026-06-02 23:25:04'),
(16, 'Grompenji', 0.00, 0.00, 0.00, 'lose', 15, '2026-06-02 23:25:24'),
(17, 'Lodgek221', 0.00, 0.00, 0.00, 'lose', 15, '2026-06-02 23:25:38'),
(18, 'NeonWolf', 0.00, 0.00, 0.00, 'lose', 15, '2026-06-03 23:07:41'),
(19, 'ShadowVex', 0.00, 0.00, 0.00, 'win', 15, '2026-06-03 23:14:34'),
(20, 'FrostByte', 0.00, 0.00, 0.00, 'win', 15, '2026-06-04 11:06:54'),
(21, 'GGidran', 0.00, 0.00, 0.00, 'win', 4, '2026-06-04 14:41:27'),
(22, 'GrapessT', 0.00, 0.00, 0.00, 'lose', 6, '2026-06-04 14:42:19'),
(23, 'herecomesmoney', 0.00, 0.00, 0.00, 'win', 6, '2026-06-04 14:43:56'),
(24, 'olobla07', 0.00, 0.00, 0.00, 'lose', 16, '2026-06-04 14:45:04'),
(25, 'pomidor', 0.00, 0.00, 0.00, 'lose', 5, '2026-06-04 14:45:55'),
(26, 'WildBeast4422', 0.00, 0.00, 0.00, 'win', 16, '2026-06-04 14:59:06');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
