-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2025 at 08:02 AM
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
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `id` int(11) NOT NULL,
  `Event_name` varchar(225) NOT NULL,
  `Time` varchar(225) NOT NULL,
  `Date` varchar(40) NOT NULL,
  `Venue` varchar(60) NOT NULL,
  `Department` varchar(255) NOT NULL,
  `Poster_name` varchar(255) NOT NULL,
  `Event_links` varchar(225) NOT NULL,
  `First_prizes` varchar(50) NOT NULL,
  `Second_prizes` varchar(50) NOT NULL,
  `Third_prizes` varchar(50) NOT NULL,
  `Created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`id`, `Event_name`, `Time`, `Date`, `Venue`, `Department`, `Poster_name`, `Event_links`, `First_prizes`, `Second_prizes`, `Third_prizes`, `Created_at`) VALUES
(2, 'Innovation, design and Entrepreneurship (ide) bootcamp', '2:00pm', '17.03.25', '', 'CSE', 'Innovation,design_and_Entrepreneurship_(ide)_bootcamp.jpg', 'https://docs.google.com/Innovation,design_and_Entrepreneurship_(ide)_bootcamp', '4000', '3000', '1500', '2025-10-04 08:59:33.637951'),
(3, 'Club spark', '2:00pm', '06/07/2025', 'HT3 lab', 'Information Technology', 'Club_spark.jpg', 'https://docs.google.com/Clubspark/googleform', '5000', '2000', '1000', '2025-09-26 15:55:03.340576'),
(4, 'Aiml Arena\'25 coding', '8:00 AM', '03.09.2025', 'HT Labs1&2', 'CSE', 'Aiml_Arena\'25_coding_contest.jpg', 'https://docs.google.com/AimlArena\'25coding_contest/googleform', '4000', '2000', '1500', '2025-10-02 03:06:07.672735'),
(5, 'Amaravati quantunm valley hackathon 2025', '11:00am', '28.08.2025', 'HT Labs1&2', 'CSBS', 'Amaravati_quantunm_valley_hackathon_2025.jpg', 'https://docs.google.com/Amaravatiquantunm_valley_hack', '4000', '3500', '2500', '2025-10-02 03:06:14.875070'),
(6, 'Colorido', '10:00', '2025-03-15', 'OAT', 'Civil Engineering', 'Colorido.jpg', 'https://docs.google.com/Colorido', '5000', '2000', '1000', '2025-10-09 15:36:34.895268'),
(7, 'Engineers\'day celebration Quiz', '10:00AM', '26.09.2025', 'HT3 lab', 'CSE', 'Engineer\'sday_celebration.jpg', 'https://docs.google.com/Engineer\'sday_celebration', '4500', '3000', '1000', '2025-10-02 03:06:19.778746'),
(8, 'International yoga day', '11:00 AM', '21.06.2025', 'OAT', 'Mechanical Engineering', 'International_yoga_day.jpg', 'https://docs.google.com/International_yoga_day', '2500', '1500', '1000', '2025-09-26 15:55:03.340576'),
(9, 'Medhanvesh', '8:00 AM', '06.07.2025', 'HT Lab4&5', 'ISTE', 'Medhanvesh.jpg', 'https://docs.google.com/Medhanvesh', '4500', '4000', '2500', '2025-09-26 15:55:03.340576'),
(10, 'Photography talk', '11:00 AM', '23.04.2025', 'Digital Block (seminar hall)', 'CSM', 'Photography_club.jpg', 'https://docs.google.com/Photography_club', '3000', '2000', '1000', '2025-09-26 15:55:03.340576'),
(11, 'Renewaloegy elergy (Poster Presentation)', '2:00 PM', '30.06.2025', 'OAT', 'CSBS', 'Renewaloegy_elergy.jpg', 'https://docs.google.com/Renewaloegy_elergy', '4500', '2000', '1000', '2025-10-02 03:06:27.306020'),
(12, 'Club Waltz Event Poster', '2:00 PM', '25.08.2025', 'OAT', 'Mechanical Engineering', 'Club_Waltz_Event_Poster.jpg', 'https://docs.google.com/Club_Waltz_Event_Poster', '3000', '2000', '1500', '2025-10-04 09:03:45.310814'),
(13, '40th Annual day', '8:00am', '23.04.2025', 'OAT', 'Information Technology', '40th_Annual_day.jpeg', 'https://docs.google.com/40th_Annual_day', '3000', '2000', '1000', '2025-09-26 15:55:03.340576'),
(14, 'Club Waltz', '2:00pm', '27.09.2025', 'OAT', 'Mechanical Engineering', 'Club_Waltz.jpeg', 'https://docs.google.com/Club_Waltz', '4000', '2000', '1500', '2025-10-04 09:02:19.637966'),
(15, 'Anime Music Video', '2:00 to 4:00pm ', '20.06.2025', 'SJB (seminar hall)', 'CSE', 'Anime_Music_Video.jpeg', 'https://docs.google.com/Anime_Music_Video', '3000', '2000', '1500', '2025-10-04 09:02:37.693165'),
(16, 'Epistemicon-2025', '2:00pm', '20.09.2025', 'OAT', 'Intra Events ', 'Epistemicon-2025.jpeg', '	\r\nhttps://docs.google.com/Epistemicon-2025', 'NIL', 'NIL', 'NIL', '2025-10-04 09:02:46.816832'),
(17, 'Fix & Run(code on c)', '8:00 Am Onwards', '18.03.2025', 'HT LABS,HI-TECH BLOCK', 'IEEE', 'Fix&Run.jpeg', '	\r\nhttps://docs.google.com/Fix&Run', '4000', '3000', '1500', '2025-10-04 09:03:00.986260'),
(18, 'IPR Awareness Programme', '3:00-4:00 pm', '20.05.2025', 'ONLINE', 'Intra Events ', 'IPR_Awareness_Programme.jpeg', '	\r\nhttps://docs.google.com/IPR_Awareness_Programme', 'NIL', 'NIL', 'NIL', '2025-10-04 09:03:13.787270'),
(19, 'Thursday Thoughts', '3:00 PM', '06.02.2025', 'OAT', 'Intra Events ', 'Thursday_Thoughts.jpeg', '	\r\nhttps://docs.google.com/Thursday_Thoughts', '4000', '3000', '2000', '2025-10-04 09:03:56.888128');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
