-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 26, 2025 at 05:56 PM
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
-- Dumping data for table `event_registration`
--

INSERT INTO `event_registration` (`id`, `Event_name`, `Time`, `Date`, `Venue`, `Department`, `Poster_name`, `Event_links`, `First_prizes`, `Second_prizes`, `Third_prizes`, `Created_at`) VALUES
('e001', 'Club spark', '2:00pm', '06/07/2025', 'HT3 lab', 'Information Technology', 'Club_spark.jpg', 'https://docs.google.com/Clubspark/googleform', '5000', '2000', '1000', '2025-09-26 15:55:03.340576'),
('e002', 'Aiml Arena\'25 coding', '8:00 AM', '03.09.2025', 'HT Labs1&2', 'Computer Science & Engineering', 'Aiml_Arena\'25_coding_contest.jpg', 'https://docs.google.com/AimlArena\'25coding_contest/googleform', '4000', '2000', '1500', '2025-09-26 15:55:03.340576'),
('e003', 'Amaravati quantunm valley hackathon 2025', '11:00am', '28.08.2025', 'HT Labs1&2', 'Computer Science & Business System', 'Amaravati_quantunm_valley_hackathon_2025.jpg', 'https://docs.google.com/Amaravatiquantunm_valley_hack', '4000', '3500', '2500', '2025-09-26 15:55:03.340576'),
('e004', 'Colorido', '10:00 AM', '15-16 February', 'OAT', 'Civil Engineering', 'Colorido.jpg', 'https://docs.google.com/Colorido', '5000', '2000', '1000', '2025-09-26 15:55:03.340576'),
('e005', 'Engineers\'day celebration Quiz', '10:00AM', '26.09.2025', 'HT3 lab', 'Computer Science & Engineering', 'Engineer\'sday_celebration.jpg', 'https://docs.google.com/Engineer\'sday_celebration', '4500', '3000', '1000', '2025-09-26 15:55:03.340576'),
('e006', 'International yoga day', '11:00 AM', '21.06.2025', 'OAT', 'Mechanical Engineering', 'International_yoga_day.jpg', 'https://docs.google.com/International_yoga_day', '2500', '1500', '1000', '2025-09-26 15:55:03.340576'),
('e007', 'Medhanvesh', '8:00 AM', '06.07.2025', 'HT Lab4&5', 'ISTE', 'Medhanvesh.jpg', 'https://docs.google.com/Medhanvesh', '4500', '4000', '2500', '2025-09-26 15:55:03.340576'),
('e008', 'Photography talk', '11:00 AM', '23.04.2025', 'Digital Block (seminar hall)', 'CSM', 'Photography_club.jpg', 'https://docs.google.com/Photography_club', '3000', '2000', '1000', '2025-09-26 15:55:03.340576'),
('e009', 'Renewaloegy elergy (Poster Presentation)', '2:00 PM', '30.06.2025', 'OAT', 'Computer Science & Business System', 'Renewaloegy_elergy.jpg', 'https://docs.google.com/Renewaloegy_elergy', '4500', '2000', '1000', '2025-09-26 15:55:03.340576'),
('e010', 'Innovation, design and Entrepreneurship (ide) bootcamp', '2:00pm', '17 February 2025-21 February 2025', '', 'Computer Science & Engineering', 'Innovation,design_and_Entrepreneurship_(ide)_bootcamp.jpg', 'https://docs.google.com/Innovation,design_and_Entrepreneurship_(ide)_bootcamp', '4000', '3000', '1500', '2025-09-26 15:55:03.340576'),
('e011', 'Club Waltz Event Poster', '2pm to 5pm', 'Sep 27&28', 'OAT', 'Mechanical Engineering', 'Club_Waltz_Event_Poster.jpg', 'https://docs.google.com/Club_Waltz_Event_Poster', '3000', '2000', '1500', '2025-09-26 15:55:03.340576'),
('e012', '40th Annual day', '8:00am', '23.04.2025', 'OAT', 'Information Technology', '40th_Annual_day.jpeg', 'https://docs.google.com/40th_Annual_day', '3000', '2000', '1000', '2025-09-26 15:55:03.340576'),
('e013', 'Club Waltz', '2:00pm', '27 & 28 September 2024 ', 'OAT', 'Mechanical Engineering', 'Club_Waltz.jpeg', 'https://docs.google.com/Club_Waltz', '4000', '2000', '1500', '2025-09-26 15:55:03.340576'),
('e014', 'Anime Music Video', '2:00 to 4:00pm ', '20 july 2025', 'SJB (seminar hall)', 'Computer Science & Engineering', 'Anime_Music_Video.jpeg', 'https://docs.google.com/Anime_Music_Video', '3000', '2000', '1500', '2025-09-26 15:55:03.340576'),
('e015', 'Epistemicon-2025', '2:00pm', '20 September 2025', 'OAT', 'Intra Events ', 'Epistemicon-2025.jpeg', '	\r\nhttps://docs.google.com/Epistemicon-2025', 'NIL', 'NIL', 'NIL', '2025-09-26 15:55:03.340576'),
('e016', 'Fix & Run(code on c)', '8:00 Am Onwards', '18 March 2025', 'HT LABS,HI-TECH BLOCK', 'IEEE', 'Fix&Run.jpeg', '	\r\nhttps://docs.google.com/Fix&Run', '4000', '3000', '1500', '2025-09-26 15:55:03.340576'),
('e017', 'IPR Awareness Programme', '3:00-4:00 pm', '20 JUNE 2025', 'ONLINE', 'Intra Events ', 'IPR_Awareness_Programme.jpeg', '	\r\nhttps://docs.google.com/IPR_Awareness_Programme', 'NIL', 'NIL', 'NIL', '2025-09-26 15:55:03.340576'),
('e018', 'Thursday Thoughts', '3:00PM', '06 February 2025', 'OAT', 'Intra Events ', 'Thursday_Thoughts.jpeg', '	\r\nhttps://docs.google.com/Thursday_Thoughts', '4000', '3000', '2000', '2025-09-26 15:55:03.340576');

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
