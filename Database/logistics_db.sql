-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 03, 2026 at 11:54 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `logistics_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `admin_id` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `Address` varchar(50) NOT NULL,
  `Incharge` varchar(50) NOT NULL,
  `profile` varchar(100) NOT NULL,
  `position` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `sent_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `districts`
--

CREATE TABLE `districts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `billid` varchar(1000) NOT NULL,
  `paymentMode` varchar(50) NOT NULL,
  `ref1` varchar(100) NOT NULL,
  `consignor_name` varchar(255) DEFAULT NULL,
  `consignor_phone` varchar(55) DEFAULT NULL,
  `consignor_email` varchar(255) DEFAULT NULL,
  `consignor_gstin` varchar(40) NOT NULL,
  `consignor_panin` varchar(100) NOT NULL,
  `consignor_address` text DEFAULT NULL,
  `consignor_district` varchar(100) DEFAULT NULL,
  `consignor_state` varchar(100) DEFAULT NULL,
  `consignor_pincode` varchar(10) DEFAULT NULL,
  `consignee_name` varchar(255) DEFAULT NULL,
  `consignee_phone` varchar(55) DEFAULT NULL,
  `consignee_email` varchar(255) DEFAULT NULL,
  `consignee_gstin` varchar(40) NOT NULL,
  `consignee_panin` varchar(100) NOT NULL,
  `consignee_address` text DEFAULT NULL,
  `consignee_district` varchar(100) DEFAULT NULL,
  `consignee_state` varchar(100) DEFAULT NULL,
  `consignee_pincode` varchar(10) DEFAULT NULL,
  `no_of_articles` varchar(20) NOT NULL,
  `actual_weight` varchar(100) NOT NULL,
  `charged_weight` varchar(20) NOT NULL,
  `said_to_contain` varchar(100) NOT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `invoice_date` varchar(100) NOT NULL,
  `ewaybill_no` varchar(100) DEFAULT NULL,
  `goods_value` varchar(30) NOT NULL,
  `value_sep` varchar(100) DEFAULT NULL,
  `basic_freight` decimal(10,2) DEFAULT NULL,
  `document_charge` decimal(10,2) DEFAULT NULL,
  `fuel_surcharge` decimal(10,0) NOT NULL,
  `handling_charge` decimal(10,0) NOT NULL,
  `other_charge` decimal(10,2) DEFAULT NULL,
  `door_collection` decimal(10,2) DEFAULT NULL,
  `door_delivery` decimal(10,2) DEFAULT NULL,
  `total_freight` decimal(10,2) DEFAULT NULL,
  `gst_amount` decimal(10,2) DEFAULT NULL,
  `grand_total` decimal(10,2) DEFAULT NULL,
  `apply_gst` varchar(10) DEFAULT NULL,
  `date_time` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(30) NOT NULL,
  `billdate` varchar(50) NOT NULL,
  `transit_status` varchar(100) NOT NULL,
  `proof_image` varchar(50) NOT NULL,
  `billtime` varchar(50) NOT NULL,
  `fast` varchar(50) NOT NULL,
  `confirmedtime` datetime DEFAULT NULL,
  `deliveredtime` datetime DEFAULT NULL,
  `delivery_status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `monthlyreport`
--

CREATE TABLE `monthlyreport` (
  `id` int(11) NOT NULL,
  `customerName` varchar(100) DEFAULT NULL,
  `customerAddress` varchar(255) DEFAULT NULL,
  `panNo` varchar(15) DEFAULT NULL,
  `gstNo` varchar(20) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `stateCode` varchar(30) DEFAULT NULL,
  `invoiceNo` varchar(30) DEFAULT NULL,
  `invoiceDate` date DEFAULT NULL,
  `particular` varchar(200) DEFAULT NULL,
  `sacCode` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `cgst` decimal(10,2) DEFAULT NULL,
  `sgst` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `applygst` varchar(5) NOT NULL DEFAULT 'false',
  `totalText` varchar(255) DEFAULT NULL,
  `ackno` varchar(15) DEFAULT NULL,
  `preparedby` varchar(20) NOT NULL,
  `preparedat` varchar(40) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `task` varchar(255) NOT NULL,
  `selected_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_details`
--

CREATE TABLE `vehicle_details` (
  `id` int(11) NOT NULL,
  `vehicle_number` varchar(50) NOT NULL,
  `docket_no` varchar(50) NOT NULL,
  `Out_Loc` varchar(100) NOT NULL,
  `Ini_Loc` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `districts`
--
ALTER TABLE `districts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `monthlyreport`
--
ALTER TABLE `monthlyreport`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ackno` (`ackno`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicle_details`
--
ALTER TABLE `vehicle_details`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `districts`
--
ALTER TABLE `districts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `monthlyreport`
--
ALTER TABLE `monthlyreport`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle_details`
--
ALTER TABLE `vehicle_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
