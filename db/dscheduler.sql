-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 13, 2024 at 01:33 PM
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
-- Database: `dscheduler`
--

-- --------------------------------------------------------

--
-- Table structure for table `changelog`
--

CREATE TABLE `changelog` (
  `log_id` int(5) NOT NULL,
  `user_id` int(5) DEFAULT NULL,
  `deployment_id` int(5) NOT NULL,
  `old_date` date NOT NULL,
  `new_date` date NOT NULL,
  `change_date` date NOT NULL,
  `change_time` time NOT NULL,
  `info` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deployment`
--

CREATE TABLE `deployment` (
  `deployment_id` int(5) NOT NULL,
  `portal_id` int(5) NOT NULL,
  `deployment_date` date NOT NULL,
  `deployment_plan` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `portal`
--

CREATE TABLE `portal` (
  `pid` int(5) NOT NULL,
  `purl` varchar(30) NOT NULL,
  `version` varchar(10) NOT NULL,
  `pfeatures` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schhedulechange`
--

CREATE TABLE `schhedulechange` (
  `change_id` int(5) NOT NULL,
  `deployment_id` int(5) NOT NULL,
  `existing_date` date NOT NULL,
  `new_date` date NOT NULL,
  `use_id` int(5) NOT NULL,
  `user_note` varchar(100) NOT NULL,
  `change_status` enum('Accepted','Rejected','Pending','') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userid` int(5) NOT NULL,
  `username` varchar(25) NOT NULL,
  `password` varchar(25) NOT NULL,
  `email` varchar(25) NOT NULL,
  `phone` int(10) NOT NULL,
  `type` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userid`, `username`, `password`, `email`, `phone`, `type`) VALUES
(10000, 'admin', 'admin123', 'admin@admin.com', 1234567890, 100);

-- --------------------------------------------------------

--
-- Table structure for table `usertype`
--

CREATE TABLE `usertype` (
  `typeid` int(3) NOT NULL,
  `typename` varchar(25) NOT NULL,
  `viewpdetails` tinyint(1) NOT NULL DEFAULT 0,
  `addpdetails` tinyint(1) NOT NULL DEFAULT 0,
  `editpdetails` tinyint(1) NOT NULL DEFAULT 0,
  `generatereport` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usertype`
--

INSERT INTO `usertype` (`typeid`, `typename`, `viewpdetails`, `addpdetails`, `editpdetails`, `generatereport`) VALUES
(100, 'admin', 1, 1, 1, 1),
(101, 'reportuser', 1, 0, 0, 1),
(102, 'viewuser', 1, 0, 0, 0),
(103, 'adduser', 1, 1, 0, 1),
(104, 'adduser', 1, 1, 0, 1),
(105, 'edituser', 1, 1, 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `changelog`
--
ALTER TABLE `changelog`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk5` (`deployment_id`),
  ADD KEY `fk6` (`user_id`);

--
-- Indexes for table `deployment`
--
ALTER TABLE `deployment`
  ADD PRIMARY KEY (`deployment_id`),
  ADD KEY `fk2` (`portal_id`);

--
-- Indexes for table `portal`
--
ALTER TABLE `portal`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `schhedulechange`
--
ALTER TABLE `schhedulechange`
  ADD PRIMARY KEY (`change_id`),
  ADD KEY `fk3` (`use_id`),
  ADD KEY `fk4` (`deployment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userid`),
  ADD KEY `fk` (`type`);

--
-- Indexes for table `usertype`
--
ALTER TABLE `usertype`
  ADD PRIMARY KEY (`typeid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `changelog`
--
ALTER TABLE `changelog`
  MODIFY `log_id` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deployment`
--
ALTER TABLE `deployment`
  MODIFY `deployment_id` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `portal`
--
ALTER TABLE `portal`
  MODIFY `pid` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schhedulechange`
--
ALTER TABLE `schhedulechange`
  MODIFY `change_id` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userid` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10001;

--
-- AUTO_INCREMENT for table `usertype`
--
ALTER TABLE `usertype`
  MODIFY `typeid` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `changelog`
--
ALTER TABLE `changelog`
  ADD CONSTRAINT `fk5` FOREIGN KEY (`deployment_id`) REFERENCES `deployment` (`deployment_id`),
  ADD CONSTRAINT `fk6` FOREIGN KEY (`user_id`) REFERENCES `users` (`userid`);

--
-- Constraints for table `deployment`
--
ALTER TABLE `deployment`
  ADD CONSTRAINT `fk2` FOREIGN KEY (`portal_id`) REFERENCES `portal` (`pid`);

--
-- Constraints for table `schhedulechange`
--
ALTER TABLE `schhedulechange`
  ADD CONSTRAINT `fk3` FOREIGN KEY (`use_id`) REFERENCES `users` (`userid`),
  ADD CONSTRAINT `fk4` FOREIGN KEY (`deployment_id`) REFERENCES `deployment` (`deployment_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk` FOREIGN KEY (`type`) REFERENCES `usertype` (`typeid`) ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
