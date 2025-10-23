-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2025 at 07:22 PM
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
-- Database: `ironboot`
--

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'Cash on Delivery',
  `order_status` enum('pending','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
  `customer_name` varchar(255) NOT NULL,
  `shipping_address` text NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_price`, `payment_method`, `order_status`, `customer_name`, `shipping_address`, `phone_number`, `created_at`) VALUES
(1, 1, 3500.00, 'Cash on Delivery', 'shipped', 'user', 'fsdfsfsd\nต. UTD, อ. UTD\nจ. Uttaradit 53000', '0000000000', '2025-10-23 15:43:24'),
(2, 3, 6545.00, 'Cash on Delivery', 'pending', 'aom', '16655\nต. UTD, อ. UTD\nจ. Uttaradit 53000', '0000000000', '2025-10-23 15:57:32');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `size` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `size`) VALUES
(1, 1, 8, 1, 3500.00, '45'),
(2, 2, 12, 1, 4200.00, '45'),
(3, 2, 3, 1, 3500.00, '45');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category`, `brand`, `image_url`) VALUES
(1, 'IRONBOOTS DYNAMO', NULL, 4000.00, 'SPEED', 'IRONBOOTS', 'images/dynamo.jpg'),
(2, 'IRONBOOTS PHANTOM', NULL, 3500.00, 'CONTROL', 'IRONBOOTS', 'images/phantom.jpg'),
(3, 'IRONBOOTS SURGE', NULL, 3500.00, 'SPEED', 'IRONBOOTS', 'images/surge.jpg'),
(4, 'IRONBOOTS SHIELD', NULL, 4500.00, 'TOUCH', 'IRONBOOTS', 'images/shield.jpg'),
(5, 'IRONBOOTS APEX', NULL, 4000.00, 'CONTROL', 'IRONBOOTS', 'images/apex.jpg'),
(6, 'IRONBOOTS LEGACY', NULL, 4500.00, 'TOUCH', 'IRONBOOTS', 'images/legacy.jpg'),
(7, 'IRONBOOTS IGNITE', NULL, 3500.00, 'NEW ARRIVALS', 'IRONBOOTS', 'images/ignite.jpg'),
(8, 'IRONBOOTS VISION', NULL, 3500.00, 'NEW ARRIVALS', 'IRONBOOTS', 'images/vision.jpg'),
(9, 'IRONBOOTS ECLIPSE', NULL, 3800.00, 'CONTROL', 'IRONBOOTS', 'images/eclipse.jpg'),
(10, 'IRONBOOTS QUANTUM', NULL, 5200.00, 'TOUCH', 'IRONBOOTS', 'images/quantum.jpg'),
(11, 'IRONBOOTS PULSAR', NULL, 2900.00, 'SPEED', 'IRONBOOTS', 'images/pulsar.jpg'),
(12, 'IRONBOOTS NOVA', NULL, 4200.00, 'NEW ARRIVALS', 'IRONBOOTS', 'images/nova.jpg'),
(13, 'IRONBOOTS GHOST', NULL, 3000.00, 'BEST SELLERS', 'IRONBOOTS', 'images/ghost.jpg'),
(14, 'IRONBOOTS ATOM', NULL, 4000.00, 'SPEED', 'IRONBOOTS', 'images/atom.jpg'),
(15, 'IRONBOOTS VORTEX', NULL, 4500.00, 'TOUCH', 'IRONBOOTS', 'images/vortex.jpg'),
(16, 'IRONBOOTS GHOST', NULL, 3000.00, 'CONTROL', 'IRONBOOTS', 'images/ghost.jpg'),
(17, 'IRONBOOTS VISION', NULL, 3500.00, 'BEST SELLERS', 'IRONBOOTS', 'images/vision.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'user', 'user@gmail.com', '1234', 'user', '2025-10-23 10:13:36'),
(2, 'admin', 'admin@gmail.com', '1234', 'admin', '2025-10-23 10:14:03'),
(3, 'aom', 'aom@gmail.com', '1234', 'user', '2025-10-23 13:42:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
