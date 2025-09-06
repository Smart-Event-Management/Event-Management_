-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 06, 2025 at 11:48 AM
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
('e001', 'Club_spark', '2:00pm', '06/07/2025', 'HT3 lab', 'IT', 'Club_spark.jpg', 'https://docs.google.com/Clubspark/googleform'),
('e002', 'Aiml Arena\'25 coding', '8:00 AM', '03.09.2025', 'HT Labs1&2', 'Computer Science Department', 'Aiml_Arena\'25_coding_contest.jpg', 'https://docs.google.com/AimlArena\'25coding_contest/googleform'),
('e003', 'Amaravati quantunm valley hackathon 2025', '11:00am', '28.08.2025', 'HT Labs1&2', 'computer science & business system', 'Amaravati_quantunm_valley_hack', 'https://docs.google.com/Amaravatiquantunm_valley_hack'),
('e004', 'Colorido', '10:00 AM', '15-16 February', '', '', 'Colorido.jpg', 'https://docs.google.com/Colorido'),
('e005', 'Engineers\'day celebration Quiz', '10:00AM', '26.09.2025', 'HT3 lab', 'Computer Science Department', 'Engineer\'sday_celebration.jpg', 'https://docs.google.com/Engineer\'sday_celebration'),
('e006', 'International yoga day', '11:00 AM', '21.06.2025', 'OAT', '', 'International_yoga_day.jpg', 'https://docs.google.com/International_yoga_day'),
('e007', 'Medhanvesh', '8:00 AM', '06.07.2025', 'HT Lab4&5', 'ISTE', 'Medhanvesh.jpg', 'https://docs.google.com/Medhanvesh'),
('e008', 'Photography talk', '11:00 AM', '23.04.2025', 'Digital Block (seminar hall)', 'CSM', 'Photography_club.jpg', 'https://docs.google.com/Photography_club'),
('e009', 'Renewaloegy elergy (Poster Presentation)', '2:00 PM', '30.06.2025', 'OAT', '', 'Renewaloegy_elergy.jpg', 'https://docs.google.com/Renewaloegy_elergy');

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
