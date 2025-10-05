-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 05, 2025 at 03:53 PM
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
-- Database: `alumnidb`
--

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `roll_no` varchar(50) NOT NULL,
  `department` varchar(100) NOT NULL,
  `year_of_graduation` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_seen` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `username`, `roll_no`, `department`, `year_of_graduation`, `password`, `created_at`, `last_seen`) VALUES
(1, 'kushal', 'l24it134', 'it', 2027, '$2y$10$NQBFI4m5W7C4am5dMIilRuDR5zCnYudWIxVggZKeirTyrr0tYlk.W', '2025-08-07 01:19:03', NULL),
(2, 'mukesh', 'y23it', 'it', 2027, '$2y$10$gxIlTA.xd/BTbXPM21yvGuqwQZvbIat7LnbEJqorUQWqPcYzMQbQ2', '2025-08-07 01:42:09', NULL),
(6, 'bharath', 'y23cs03', 'csd', 2027, '$2y$10$MlK/mQTB1xtPagwWyiCTZ.3Had2zqKXZPAdlUtI/6W7DY3oMpwpC2', '2025-09-08 17:17:24', NULL),
(8, 'mukesh', 'y23it004', 'csm', 2027, '$2y$10$K9wz.AWtO4TOs9VtDTQ1z.mx1YpD5peM4X42JYGELah9usdqQLEzW', '2025-09-30 09:54:49', NULL),
(9, 'siva', 'y23it067', 'it', 2027, '$2y$10$i4Mv4F.wb56rKYO2ADfyseQWBEVYHUHQgtUhBPM4KaHTu6EDr0Tzy', '2025-09-30 09:58:30', NULL),
(10, 'mukesh1', 'y23it033', 'it', 2027, '$2y$10$Jy1mH0fiq5kSxQoqUSnC7.JfWtouTMwPQvKPixhGO.EzyIAg8bxFG', '2025-10-05 12:46:52', '2025-10-05 19:03:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roll_no` (`roll_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
