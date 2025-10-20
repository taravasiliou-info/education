-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Aug 08, 2025 at 08:53 PM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `play2learn`
--

-- --------------------------------------------------------

--
-- Table structure for table `anagram_hunt_scores`
--

CREATE TABLE `anagram_hunt_scores` (
  `score_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `score` int NOT NULL,
  `max_number` int NOT NULL,
  `operation` varchar(30) DEFAULT NULL,
  `end_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `anagram_hunt_scores`
--

INSERT INTO `anagram_hunt_scores` (`score_id`, `user_id`, `score`, `max_number`, `operation`, `end_time`) VALUES
(13, 15, 1, 5, NULL, '2025-08-08 09:49:41'),
(17, 15, 1, 5, NULL, '2025-08-08 11:38:57'),
(18, 15, 1, 5, NULL, '2025-08-08 11:45:36'),
(19, 15, 0, 8, NULL, '2025-08-08 13:37:35'),
(20, 17, 1, 5, NULL, '2025-08-08 16:28:25'),
(21, 18, 1, 7, NULL, '2025-08-08 16:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `math_facts_scores`
--

CREATE TABLE `math_facts_scores` (
  `score_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `score` int NOT NULL,
  `max_number` int NOT NULL,
  `operation` varchar(30) DEFAULT NULL,
  `end_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `math_facts_scores`
--

INSERT INTO `math_facts_scores` (`score_id`, `user_id`, `score`, `max_number`, `operation`, `end_time`) VALUES
(9, 15, 11, 10, 'add', '2025-08-08 10:06:16'),
(12, 15, 17, 10, 'add', '2025-08-08 15:40:11'),
(13, 15, 14, 10, 'add', '2025-08-08 15:59:05'),
(14, 15, 10, 10, 'div', '2025-08-08 16:06:43'),
(15, 15, 16, 10, 'mul', '2025-08-08 16:09:49'),
(16, 17, 14, 10, 'mul', '2025-08-08 16:29:42'),
(17, 18, 9, 10, 'div', '2025-08-08 16:37:56');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `review` longtext NOT NULL,
  `featured` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `review`, `featured`) VALUES
(7, 15, 'If two plus two equals four, you plus this math game equals more.', 1),
(8, 17, 'I had a great time playing these games and I learned while I was playing.', 1),
(9, 17, 'This is a review that should not be featured on the homepage.', 0),
(10, 18, 'I am getting so much better at remembering my math facts. Thanks for this fun game.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tokens`
--

CREATE TABLE `tokens` (
  `token_id` int NOT NULL,
  `token` char(64) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(30) NOT NULL,
  `pass_phrase` varchar(500) NOT NULL,
  `is_admin` tinyint NOT NULL DEFAULT '0',
  `date_registered` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `registration_confirmed` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `username`, `pass_phrase`, `is_admin`, `date_registered`, `registration_confirmed`) VALUES
(15, 'Tara', 'Vasiliou', 'taravasiliou@gmail.com', 'admin', '$2y$10$8ipWtVe8Ty3uk6x4vjm1Nerv29UW3IGibvlrRXfx5v4PSM8o.4eVK', 1, '2025-08-05 16:10:14', 1),
(17, 'Murray', 'Vasiliou', 'mrpoops@gmail.com', 'Murray', '$2y$10$4vTkuUXUyUeZca.AUvtdzenZJAPYzrSeatSF1KPVakyTnL.b.Tyt.', 0, '2025-08-08 16:26:33', 0),
(18, 'Winnie', 'Pooh', 'poohbear@gmail.com', 'Pooh', '$2y$10$eR1SvdRjJ.61ZQ0.mMJT4ug6ddYDARr93PCZ3X7OhuXCjr2FM.5y2', 0, '2025-08-08 16:34:30', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anagram_hunt_scores`
--
ALTER TABLE `anagram_hunt_scores`
  ADD PRIMARY KEY (`score_id`);

--
-- Indexes for table `math_facts_scores`
--
ALTER TABLE `math_facts_scores`
  ADD PRIMARY KEY (`score_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`);

--
-- Indexes for table `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`token_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anagram_hunt_scores`
--
ALTER TABLE `anagram_hunt_scores`
  MODIFY `score_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `math_facts_scores`
--
ALTER TABLE `math_facts_scores`
  MODIFY `score_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tokens`
--
ALTER TABLE `tokens`
  MODIFY `token_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
