-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 06, 2025 at 11:15 AM
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
-- Table structure for table `event_registration`
--

CREATE TABLE `event_registration` (
  `id` varchar(225) NOT NULL,
  `Event_name` varchar(225) NOT NULL,
  `Time` varchar(225) NOT NULL,
  `Date` varchar(20) NOT NULL,
  `Venue` varchar(60) NOT NULL,
  `Department` varchar(255) NOT NULL,
  `Poster_name` varchar(255) NOT NULL,
  `Event_links` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_registration`
--

INSERT INTO `event_registration` (`id`, `Event_name`, `Time`, `Date`, `Venue`, `Department`, `Poster_name`, `Event_links`) VALUES
('e001', 'Club_spark', '2:00pm', '06/07/2025', 'HT3 lab', 'IT', 'Club_spark.jpg', 'https://Club_spark_google_form'),
('e002', 'Aiml Arena\'25 coding', '', '03.09.2025', 'HT Labs1&2', '', 'Aiml_Arena\'25_coding_contest.j', ''),
('e003', 'Amaravati quantunm valley hackathon 2025', '11:00am', '28.08.2025', '', 'computer science & business system', 'Amaravati_quantunm_valley_hack', ''),
('e004', 'Colorido', '', '15-16 February', '', '', 'Colorido.jpg', ''),
('e005', 'Engineers\'day celebration Quiz', '10:00AM', '26.09.2025', '', '', 'Engineer\'sday_celebration.jpg', ''),
('e006', 'International yoga day', '11:00 AM', '21.06.2025', '', '', '', ''),
('e007', 'Medhanvesh', '', '06.07.2025', '', '', 'Medhanvesh.jpg', ''),
('e008', 'Photography talk', '11:00 AM', '23.04.2025', '', '', 'Photography_club.jpg', ''),
('e009', 'Renewaloegy elergy (Poster Persontantion)', '2:00 PM', '30.06.2025', '', '', 'Renewaloegy_elergy.jpg', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `event_registration`
--
ALTER TABLE `event_registration`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
