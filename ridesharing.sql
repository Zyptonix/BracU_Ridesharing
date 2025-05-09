-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2025 at 12:38 PM
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
-- Database: `ridesharing`
--

-- --------------------------------------------------------

--
-- Table structure for table `applies_for`
--

CREATE TABLE `applies_for` (
  `Provider_Student_id` int(10) NOT NULL,
  `Passenger_Student_id` int(10) NOT NULL,
  `Card_no` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `Plate_number` int(10) NOT NULL,
  `Model` varchar(30) DEFAULT NULL,
  `Year` int(4) DEFAULT NULL,
  `Colour` char(10) DEFAULT NULL,
  `Student_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chatbox`
--

CREATE TABLE `chatbox` (
  `Chatbox_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `Feedback_id` int(11) NOT NULL,
  `Provider_student_id` int(11) NOT NULL,
  `Receiver_student_id` int(11) NOT NULL,
  `F_comments` varchar(500) NOT NULL,
  `F_type` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `Chatbox_id` int(10) NOT NULL,
  `Message_info` varchar(200) DEFAULT NULL,
  `Timestamps` time DEFAULT NULL,
  `Sender_student_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages_in`
--

CREATE TABLE `messages_in` (
  `Sender_student_id` int(10) NOT NULL,
  `receiver_student_id` int(10) NOT NULL,
  `Chatbox_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ride_cards`
--

CREATE TABLE `ride_cards` (
  `Student_id` int(10) NOT NULL,
  `Card_no` int(10) NOT NULL,
  `Pickup_time` time DEFAULT NULL,
  `Timeslot` varchar(50) DEFAULT NULL,
  `Pickup_Area` varchar(30) DEFAULT NULL,
  `Number_of_empty_seats` int(1) DEFAULT NULL,
  `Pickup_date` date DEFAULT NULL,
  `Gender` varchar(6) NOT NULL,
  `Semester` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ride_preferences`
--

CREATE TABLE `ride_preferences` (
  `preference_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `timeslots` varchar(100) DEFAULT NULL,
  `preferred_gender` enum('Any','Male','Female') DEFAULT 'Any'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `selected_passengers`
--

CREATE TABLE `selected_passengers` (
  `Provider_Student_id` int(10) NOT NULL,
  `Passenger_Student_id` int(10) DEFAULT NULL,
  `Card_no` int(10) NOT NULL,
  `Trip_ID` int(10) NOT NULL,
  `Rating` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `Trip_id` int(10) NOT NULL,
  `Card_no` int(10) NOT NULL,
  `Student_id` int(10) NOT NULL,
  `Pickup_time` time DEFAULT NULL,
  `Is_started` int(1) DEFAULT NULL,
  `Is_completed` int(1) DEFAULT NULL,
  `Car_Provider_rating` decimal(3,2) DEFAULT NULL,
  `T_comments` varchar(200) DEFAULT NULL,
  `Starting_time` datetime NOT NULL,
  `Ending_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `Student_id` int(10) NOT NULL,
  `Name` varchar(30) DEFAULT NULL,
  `Passwords` char(60) DEFAULT NULL,
  `Location` varchar(30) DEFAULT NULL,
  `Semester` int(2) DEFAULT NULL,
  `Brac_mail` varchar(50) DEFAULT NULL,
  `Verification_status` int(1) DEFAULT NULL,
  `Phone_number` int(14) DEFAULT NULL,
  `Address` varchar(60) DEFAULT NULL,
  `Description` varchar(300) DEFAULT NULL,
  `P_flag` int(1) DEFAULT NULL,
  `C_flag` int(1) DEFAULT NULL,
  `Gender` varchar(6) NOT NULL,
  `profile_image` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `Passenger_id` int(11) NOT NULL,
  `Card_no` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applies_for`
--
ALTER TABLE `applies_for`
  ADD PRIMARY KEY (`Passenger_Student_id`,`Provider_Student_id`,`Card_no`),
  ADD UNIQUE KEY `unique_application` (`Passenger_Student_id`,`Card_no`,`Provider_Student_id`),
  ADD KEY `Provider_Student_id` (`Provider_Student_id`,`Card_no`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`Student_id`) USING BTREE,
  ADD UNIQUE KEY `Student_id` (`Student_id`);

--
-- Indexes for table `chatbox`
--
ALTER TABLE `chatbox`
  ADD PRIMARY KEY (`Chatbox_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`Feedback_id`),
  ADD KEY `Provider_student_id` (`Provider_student_id`),
  ADD KEY `Receiver_student_id` (`Receiver_student_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`,`Chatbox_id`),
  ADD KEY `Chatbox_id` (`Chatbox_id`),
  ADD KEY `messages_ibfk_2` (`Sender_student_id`);

--
-- Indexes for table `messages_in`
--
ALTER TABLE `messages_in`
  ADD PRIMARY KEY (`Sender_student_id`,`receiver_student_id`,`Chatbox_id`),
  ADD KEY `receiver_student_id` (`receiver_student_id`),
  ADD KEY `Chatbox_id` (`Chatbox_id`);

--
-- Indexes for table `ride_cards`
--
ALTER TABLE `ride_cards`
  ADD PRIMARY KEY (`Card_no`,`Student_id`),
  ADD KEY `Student_id` (`Student_id`);

--
-- Indexes for table `ride_preferences`
--
ALTER TABLE `ride_preferences`
  ADD PRIMARY KEY (`preference_id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `selected_passengers`
--
ALTER TABLE `selected_passengers`
  ADD PRIMARY KEY (`Provider_Student_id`,`Trip_ID`,`Card_no`),
  ADD KEY `Provider_Student_id` (`Provider_Student_id`,`Card_no`),
  ADD KEY `Passenger_Student_id` (`Passenger_Student_id`),
  ADD KEY `Trip_ID` (`Trip_ID`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`Trip_id`) USING BTREE,
  ADD KEY `Card_no` (`Card_no`,`Student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Student_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`Passenger_id`,`Card_no`),
  ADD KEY `Card_no` (`Card_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chatbox`
--
ALTER TABLE `chatbox`
  MODIFY `Chatbox_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `Feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ride_cards`
--
ALTER TABLE `ride_cards`
  MODIFY `Card_no` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ride_preferences`
--
ALTER TABLE `ride_preferences`
  MODIFY `preference_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `Trip_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applies_for`
--
ALTER TABLE `applies_for`
  ADD CONSTRAINT `applies_for_ibfk_1` FOREIGN KEY (`Provider_Student_id`,`Card_no`) REFERENCES `ride_cards` (`Student_id`, `Card_no`) ON DELETE CASCADE,
  ADD CONSTRAINT `applies_for_ibfk_2` FOREIGN KEY (`Passenger_Student_id`) REFERENCES `users` (`Student_id`) ON DELETE CASCADE;

--
-- Constraints for table `cars`
--
ALTER TABLE `cars`
  ADD CONSTRAINT `Student_id` FOREIGN KEY (`Student_id`) REFERENCES `users` (`Student_id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `Provider_student_id` FOREIGN KEY (`Provider_student_id`) REFERENCES `users` (`Student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Receiver_student_id` FOREIGN KEY (`Receiver_student_id`) REFERENCES `users` (`Student_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`Chatbox_id`) REFERENCES `chatbox` (`Chatbox_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`Sender_student_id`) REFERENCES `users` (`Student_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages_in`
--
ALTER TABLE `messages_in`
  ADD CONSTRAINT `messages_in_ibfk_1` FOREIGN KEY (`Sender_student_id`) REFERENCES `users` (`Student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_in_ibfk_2` FOREIGN KEY (`receiver_student_id`) REFERENCES `users` (`Student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_in_ibfk_3` FOREIGN KEY (`Chatbox_id`) REFERENCES `chatbox` (`Chatbox_id`) ON DELETE CASCADE;

--
-- Constraints for table `ride_cards`
--
ALTER TABLE `ride_cards`
  ADD CONSTRAINT `ride_cards_ibfk_1` FOREIGN KEY (`Student_id`) REFERENCES `users` (`Student_id`) ON DELETE CASCADE;

--
-- Constraints for table `ride_preferences`
--
ALTER TABLE `ride_preferences`
  ADD CONSTRAINT `ride_preferences_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`Student_id`) ON DELETE CASCADE;

--
-- Constraints for table `selected_passengers`
--
ALTER TABLE `selected_passengers`
  ADD CONSTRAINT `selected_passengers_ibfk_1` FOREIGN KEY (`Provider_Student_id`,`Card_no`) REFERENCES `ride_cards` (`Student_id`, `Card_no`) ON DELETE CASCADE,
  ADD CONSTRAINT `selected_passengers_ibfk_2` FOREIGN KEY (`Passenger_Student_id`) REFERENCES `users` (`Student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `selected_passengers_ibfk_3` FOREIGN KEY (`Trip_ID`) REFERENCES `trips` (`Trip_id`) ON DELETE CASCADE;

--
-- Constraints for table `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `trips_ibfk_1` FOREIGN KEY (`Card_no`,`Student_id`) REFERENCES `ride_cards` (`Card_no`, `Student_id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`Passenger_id`) REFERENCES `users` (`Student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`Card_no`) REFERENCES `ride_cards` (`Card_no`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
