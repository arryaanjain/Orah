-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 26, 2025 at 11:51 AM
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
-- Database: `orah_schema_redone`
--

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `created_at`, `updated_at`) VALUES
(6, 'test', '2025-01-28 12:03:23', '2025-01-28 12:03:23'),
(17, 'newCompany', '2025-03-24 12:14:21', '2025-03-24 12:14:21');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `billing_name` varchar(255) NOT NULL,
  `place` varchar(255) NOT NULL,
  `gst_number` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `billing_name`, `place`, `gst_number`, `email`, `phone`, `company_id`, `user_id`) VALUES
(2, 'man1', 'place1', '', '', '0', 6, 6);

-- --------------------------------------------------------

--
-- Table structure for table `finished_products`
--

CREATE TABLE `finished_products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `creation_date` date DEFAULT curdate(),
  `status` enum('active','inactive') DEFAULT 'active',
  `description` text DEFAULT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finished_products`
--

INSERT INTO `finished_products` (`id`, `product_name`, `creation_date`, `status`, `description`, `company_id`, `user_id`) VALUES
(1, 'test', '2025-02-27', 'active', 'test', 6, 6),
(2, 'new_prod', '2025-02-27', 'active', 'test', 6, 6);

-- --------------------------------------------------------

--
-- Table structure for table `new_prod`
--

CREATE TABLE `new_prod` (
  `id` int(11) NOT NULL,
  `material` varchar(255) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `new_prod`
--

INSERT INTO `new_prod` (`id`, `material`, `qty`, `unit`, `company_id`, `user_id`, `created_at`) VALUES
(1, 'materi3', 4.00, 'unittt1', 6, 6, '2025-02-27 11:22:47'),
(2, 'materi4', 5.00, 'unitt2', 6, 6, '2025-02-27 11:22:47');

-- --------------------------------------------------------

--
-- Table structure for table `order_book`
--

CREATE TABLE `order_book` (
  `id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_book`
--

INSERT INTO `order_book` (`id`, `order_date`, `product_id`, `qty`, `customer_id`, `company_id`, `user_id`) VALUES
(1, '2025-02-28', 1, 20.00, 2, 6, 6),
(2, '2025-02-28', 2, 20.00, 2, 6, 6),
(3, '2025-02-28', 2, 33.00, 2, 6, 6),
(4, '2025-03-17', 1, 3.00, 2, 6, 6),
(5, '2025-03-26', 2, 5.00, 2, 6, 6);

-- --------------------------------------------------------

--
-- Table structure for table `order_book_history`
--

CREATE TABLE `order_book_history` (
  `id` int(11) NOT NULL,
  `original_order_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `moved_to_history_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rm_master`
--

CREATE TABLE `rm_master` (
  `id` int(11) NOT NULL,
  `material` varchar(255) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rm_master`
--

INSERT INTO `rm_master` (`id`, `material`, `company_id`, `user_id`) VALUES
(6, 'materi3', 6, 6),
(7, 'materi4', 6, 6);

-- --------------------------------------------------------

--
-- Table structure for table `rm_master_units`
--

CREATE TABLE `rm_master_units` (
  `id` int(11) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rm_master_units`
--

INSERT INTO `rm_master_units` (`id`, `unit`, `company_id`, `user_id`) VALUES
(5, 'unittt1', 6, 6),
(6, 'unitt2', 6, 6);

-- --------------------------------------------------------

--
-- Table structure for table `rm_purchase`
--

CREATE TABLE `rm_purchase` (
  `id` int(11) NOT NULL,
  `purchase_date` date NOT NULL,
  `material_id` int(11) NOT NULL,
  `qty` bigint(20) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rm_purchase`
--

INSERT INTO `rm_purchase` (`id`, `purchase_date`, `material_id`, `qty`, `unit_id`, `company_id`, `user_id`) VALUES
(6, '2025-02-26', 6, 37, 5, 6, 6),
(7, '2025-02-23', 6, 57, 5, 6, 6),
(8, '2025-03-17', 7, 270, 6, 6, 6),
(9, '2025-03-17', 6, -80, 5, 6, 6),
(10, '2025-03-17', 6, 78, 5, 6, 6),
(11, '2025-03-26', 6, 80, 5, 6, 6),
(12, '2025-03-26', 6, 100, 5, 6, 6),
(13, '2025-03-26', 6, -80, 5, 6, 6),
(14, '2025-03-26', 7, -100, 6, 6, 6),
(15, '2025-03-26', 6, -80, 5, 6, 6);

-- --------------------------------------------------------

--
-- Table structure for table `sales_book`
--

CREATE TABLE `sales_book` (
  `id` int(11) NOT NULL,
  `sales_date` date DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_book`
--

INSERT INTO `sales_book` (`id`, `sales_date`, `order_id`, `qty`, `customer_id`, `company_id`, `user_id`) VALUES
(4, '2025-03-17', 1, 20, 2, 6, 6),
(5, '2025-03-26', 2, 5, 2, 6, 6),
(6, '2025-03-26', 1, 3, 2, 6, 6);

-- --------------------------------------------------------

--
-- Table structure for table `test`
--

CREATE TABLE `test` (
  `id` int(11) NOT NULL,
  `material` varchar(255) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test`
--

INSERT INTO `test` (`id`, `material`, `qty`, `unit`, `company_id`, `user_id`, `created_at`) VALUES
(1, 'materi3', 4.00, 'unittt1', 6, 6, '2025-02-27 00:40:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `date_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `company_id`, `username`, `email`, `password`, `role`, `date_time`) VALUES
(6, 6, 'test', 'test@gmail.com', '$2y$10$XT30wdTUgty26o4ZCaVRrOK.OOnldz.vb7XjXAMnqp97qf23vUOgu', 'admin', '2025-01-28 13:03:23'),
(20, 17, 'test1', 'test1@test.com', '$2y$10$zUOtFz2skNCPMqWnKnJ3C.MuqIWosw369Dfby0qI6etAjkAjOZYKK', 'admin', '2025-03-24 13:14:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gst_number` (`gst_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `finished_products`
--
ALTER TABLE `finished_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_name` (`product_name`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `new_prod`
--
ALTER TABLE `new_prod`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_book`
--
ALTER TABLE `order_book`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_book_history`
--
ALTER TABLE `order_book_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `original_order_id` (`original_order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rm_master`
--
ALTER TABLE `rm_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `material` (`material`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rm_master_units`
--
ALTER TABLE `rm_master_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unit` (`unit`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rm_purchase`
--
ALTER TABLE `rm_purchase`
  ADD PRIMARY KEY (`id`),
  ADD KEY `material_id` (`material_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sales_book`
--
ALTER TABLE `sales_book`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `company_id` (`company_id`,`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `finished_products`
--
ALTER TABLE `finished_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `new_prod`
--
ALTER TABLE `new_prod`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_book`
--
ALTER TABLE `order_book`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_book_history`
--
ALTER TABLE `order_book_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rm_master`
--
ALTER TABLE `rm_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `rm_master_units`
--
ALTER TABLE `rm_master_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `rm_purchase`
--
ALTER TABLE `rm_purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `sales_book`
--
ALTER TABLE `sales_book`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `test`
--
ALTER TABLE `test`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `finished_products`
--
ALTER TABLE `finished_products`
  ADD CONSTRAINT `finished_products_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `finished_products_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_book`
--
ALTER TABLE `order_book`
  ADD CONSTRAINT `order_book_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `finished_products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_book_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_book_ibfk_3` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_book_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_book_history`
--
ALTER TABLE `order_book_history`
  ADD CONSTRAINT `order_book_history_ibfk_1` FOREIGN KEY (`original_order_id`) REFERENCES `order_book` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_book_history_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `finished_products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_book_history_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_book_history_ibfk_4` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_book_history_ibfk_5` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rm_master`
--
ALTER TABLE `rm_master`
  ADD CONSTRAINT `rm_master_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rm_master_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rm_master_units`
--
ALTER TABLE `rm_master_units`
  ADD CONSTRAINT `rm_master_units_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rm_master_units_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rm_purchase`
--
ALTER TABLE `rm_purchase`
  ADD CONSTRAINT `rm_purchase_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `rm_master` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rm_purchase_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `rm_master_units` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rm_purchase_ibfk_3` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rm_purchase_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_book`
--
ALTER TABLE `sales_book`
  ADD CONSTRAINT `sales_book_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `finished_products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_book_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_book_ibfk_3` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_book_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
