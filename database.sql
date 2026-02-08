-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 07, 2026 at 03:57 PM
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
-- Database: `essence_art_gallery`
--

-- --------------------------------------------------------

--
-- Table structure for table `Addresses`
--

CREATE TABLE `Addresses` (
  `AddressID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `AddressType` enum('shipping','billing') NOT NULL,
  `FullName` varchar(100) NOT NULL COMMENT 'Recipient name',
  `Phone` varchar(20) NOT NULL,
  `AddressLine1` varchar(255) NOT NULL,
  `AddressLine2` varchar(255) DEFAULT NULL,
  `City` varchar(100) NOT NULL,
  `County` varchar(100) NOT NULL,
  `PostalCode` varchar(20) DEFAULT NULL,
  `Country` varchar(50) DEFAULT 'Kenya',
  `IsDefault` tinyint(1) DEFAULT 0 COMMENT 'Default address for this type',
  `DateAdded` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer addresses';

-- --------------------------------------------------------

--
-- Table structure for table `ArtworkImages`
--

CREATE TABLE `ArtworkImages` (
  `ImageID` int(11) NOT NULL,
  `ArtworkID` int(11) NOT NULL,
  `ImageURL` varchar(255) NOT NULL,
  `ImageOrder` int(11) DEFAULT 1 COMMENT '1=main, 2+=additional',
  `ImageType` varchar(20) DEFAULT 'gallery' COMMENT 'main, detail, framed, signature',
  `Caption` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Multiple photos per artwork';

-- --------------------------------------------------------

--
-- Table structure for table `Artworks`
--

CREATE TABLE `Artworks` (
  `ArtworkID` int(11) NOT NULL,
  `Title` varchar(150) NOT NULL,
  `Description` text DEFAULT NULL COMMENT 'Story and details about the piece',
  `Medium` varchar(100) DEFAULT NULL COMMENT 'Charcoal, Oil, Acrylic, etc.',
  `Dimensions` varchar(50) DEFAULT NULL COMMENT 'e.g., 24x36 inches',
  `YearCreated` int(11) DEFAULT NULL,
  `Price` decimal(10,2) NOT NULL,
  `DiscountPrice` decimal(10,2) DEFAULT NULL COMMENT 'Sale price if applicable',
  `IsAvailable` tinyint(1) DEFAULT 1 COMMENT '1=available, 0=not available',
  `IsFeatured` tinyint(1) DEFAULT 0 COMMENT 'Show on homepage',
  `ShowPrice` tinyint(1) DEFAULT 1,
  `IsSold` tinyint(1) DEFAULT 0 COMMENT '1=sold, 0=not sold',
  `CategoryID` int(11) NOT NULL,
  `MainImageURL` varchar(255) DEFAULT NULL,
  `ViewCount` int(11) DEFAULT 0 COMMENT 'Track popularity',
  `DateAdded` datetime DEFAULT current_timestamp(),
  `DateUpdated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `TechnicalDetails` text DEFAULT NULL COMMENT 'Frame info, condition, materials'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Artwork/product catalog';

--
-- Dumping data for table `Artworks`
--

INSERT INTO `Artworks` (`ArtworkID`, `Title`, `Description`, `Medium`, `Dimensions`, `YearCreated`, `Price`, `DiscountPrice`, `IsAvailable`, `IsFeatured`, `ShowPrice`, `IsSold`, `CategoryID`, `MainImageURL`, `ViewCount`, `DateAdded`, `DateUpdated`, `TechnicalDetails`) VALUES
(1, 'Kendrick Lamar', 'Well..this is just a potrait of one of the greatest hiphop artists.', 'charcoal on canvas', '', 2025, 500.00, NULL, 1, 1, 1, 0, 1, 'uploads/artworks/artwork_68e3e577c581a1.71787371.jpg', 0, '2025-10-06 18:51:19', '2026-01-10 11:55:58', ''),
(2, 'Lebron James', 'All time basket ball Goat', 'charcoal on canvas', '', 2025, 400.00, NULL, 1, 1, 1, 0, 1, 'uploads/artworks/artwork_69047e7162b1f6.95580116.jpg', 0, '2025-10-06 21:05:28', '2026-01-10 11:55:55', ''),
(3, 'Chadwick Boseman', 'Drawn as an acknowledgement of the Late Chadwick and his works in the film industry.Wakanda Forever.', 'charcoal on canvas', '', 2025, 500.00, NULL, 1, 1, 1, 0, 1, 'uploads/artworks/artwork_68f5f4aa5f0095.18616495.jpg', 0, '2025-10-20 11:36:58', '2026-01-10 11:55:52', 'Wooden Frame'),
(4, 'Ice Cube', 'Straight Outta Compton', 'charcoal on canvas', '', 2025, 100.00, NULL, 1, 0, 1, 0, 1, 'uploads/artworks/artwork_6904708b5571f6.65510961.jpg', 0, '2025-10-31 11:17:15', '2026-01-10 11:55:48', ''),
(5, 'Nairobi', 'Nairobi at Sunset', '', '', 2025, 100.00, NULL, 1, 1, 1, 0, 2, 'uploads/artworks/artwork_6904728ea8bac0.31134201.jpg', 0, '2025-10-31 11:25:50', '2026-01-10 11:55:45', ''),
(6, 'Kobe Bryant', 'NBA  MVP 2008', 'paint', '', 2025, 100.00, NULL, 1, 0, 1, 0, 1, 'uploads/artworks/artwork_69047911b25c97.13917463.jpeg', 0, '2025-10-31 11:53:37', '2026-01-10 11:55:43', ''),
(7, 'Carrie Wahu', '', 'charcoal on canvas', '', 2025, 100.00, NULL, 1, 1, 1, 0, 1, 'uploads/artworks/artwork_69047b6a1bbda3.24514275.jpg', 0, '2025-10-31 12:03:38', '2026-01-10 11:55:40', ''),
(8, 'Michael Jordan', 'Basket Ball All Time Legend', 'charcoal on canvas', '', 2025, 100.00, NULL, 1, 1, 0, 0, 1, 'uploads/artworks/artwork_69393679dc2b26.98947221.jpg', 0, '2025-12-10 11:59:37', '2026-01-30 11:52:40', '');

-- --------------------------------------------------------

--
-- Table structure for table `Cart`
--

CREATE TABLE `Cart` (
  `CartID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `ArtworkID` int(11) NOT NULL,
  `Quantity` int(11) DEFAULT 1,
  `DateAdded` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Cart`
--

INSERT INTO `Cart` (`CartID`, `UserID`, `ArtworkID`, `Quantity`, `DateAdded`) VALUES
(9, 1, 7, 1, '2025-12-18 09:07:51'),
(10, 1, 8, 1, '2026-01-30 11:54:01'),
(11, 1, 2, 1, '2026-01-30 12:44:29');

-- --------------------------------------------------------

--
-- Table structure for table `Categories`
--

CREATE TABLE `Categories` (
  `CategoryID` int(11) NOT NULL,
  `CategoryName` varchar(50) NOT NULL,
  `Description` text DEFAULT NULL,
  `ImageURL` varchar(255) DEFAULT NULL,
  `DisplayOrder` int(11) DEFAULT 0 COMMENT 'For custom sorting',
  `IsActive` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Art categories (Portraits, Landscapes, etc.)';

--
-- Dumping data for table `Categories`
--

INSERT INTO `Categories` (`CategoryID`, `CategoryName`, `Description`, `ImageURL`, `DisplayOrder`, `IsActive`) VALUES
(1, 'Portraits', 'Portrait artwork featuring people and faces', NULL, 1, 1),
(2, 'Landscapes', 'Natural scenery and outdoor scenes', NULL, 2, 1),
(3, 'Abstract', 'Abstract and contemporary art pieces', NULL, 3, 1),
(4, 'Still Life', 'Still life compositions and arrangements', NULL, 4, 1),
(5, 'Urban', 'City scenes and urban landscapes', NULL, 5, 1),
(6, 'Wildlife', 'Animals and wildlife artwork', NULL, 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ContactMessages`
--

CREATE TABLE `ContactMessages` (
  `MessageID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Subject` varchar(200) DEFAULT NULL,
  `Message` text NOT NULL,
  `DateSent` datetime DEFAULT current_timestamp(),
  `IsRead` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Customers`
--

CREATE TABLE `Customers` (
  `CustomerID` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL COMMENT 'Hashed password',
  `Phone` varchar(20) DEFAULT NULL,
  `DateRegistered` datetime DEFAULT current_timestamp(),
  `LastLogin` datetime DEFAULT NULL,
  `IsActive` tinyint(1) DEFAULT 1 COMMENT '1=active, 0=deactivated',
  `IsSubscribed` tinyint(1) DEFAULT 0 COMMENT '1=subscribed to newsletter'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer accounts and profiles';

--
-- Dumping data for table `Customers`
--

INSERT INTO `Customers` (`CustomerID`, `FirstName`, `LastName`, `Email`, `Password`, `Phone`, `DateRegistered`, `LastLogin`, `IsActive`, `IsSubscribed`) VALUES
(1, 'Admin', 'User', 'admin@essenceofart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2025-10-05 20:57:50', NULL, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `Inquiries`
--

CREATE TABLE `Inquiries` (
  `InquiryID` int(11) NOT NULL,
  `InquiryType` enum('commission','question','feedback','general') NOT NULL,
  `CustomerID` int(11) DEFAULT NULL COMMENT 'Optional - if logged in',
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Subject` varchar(200) NOT NULL,
  `Message` text NOT NULL,
  `DateSubmitted` datetime DEFAULT current_timestamp(),
  `Status` enum('new','read','responded','closed') DEFAULT 'new',
  `AdminResponse` text DEFAULT NULL COMMENT 'Your reply',
  `ResponseDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contact inquiries and commission requests';

-- --------------------------------------------------------

--
-- Table structure for table `Newsletter`
--

CREATE TABLE `Newsletter` (
  `SubscriberID` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `DateSubscribed` datetime DEFAULT current_timestamp(),
  `IsActive` tinyint(1) DEFAULT 1 COMMENT '1=subscribed, 0=unsubscribed',
  `Source` varchar(50) DEFAULT NULL COMMENT 'homepage, checkout, etc.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Newsletter subscribers';

-- --------------------------------------------------------

--
-- Table structure for table `OrderItems`
--

CREATE TABLE `OrderItems` (
  `OrderItemID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `ArtworkID` int(11) NOT NULL,
  `ArtworkTitle` varchar(200) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `OrderItems`
--

INSERT INTO `OrderItems` (`OrderItemID`, `OrderID`, `ArtworkID`, `ArtworkTitle`, `Price`, `Quantity`, `Subtotal`) VALUES
(1, 1, 6, 'Kobe Bryant', 100.00, 1, 100.00),
(2, 2, 7, 'Carrie Wahu', 100.00, 1, 100.00),
(3, 2, 2, 'Lebron James', 400.00, 1, 400.00),
(4, 3, 3, 'Chadwick Boseman', 500.00, 1, 500.00),
(5, 4, 8, 'Michael Jordan', 100.00, 1, 100.00),
(6, 5, 8, 'Michael Jordan', 100.00, 1, 100.00),
(7, 6, 3, 'Chadwick Boseman', 500.00, 1, 500.00),
(8, 7, 6, 'Kobe Bryant', 100.00, 1, 100.00),
(9, 8, 3, 'Chadwick Boseman', 500.00, 1, 500.00),
(10, 9, 3, 'Chadwick Boseman', 500.00, 1, 500.00),
(11, 9, 6, 'Kobe Bryant', 100.00, 1, 100.00),
(12, 10, 6, 'Kobe Bryant', 100.00, 1, 100.00),
(13, 11, 8, 'Michael Jordan', 100.00, 1, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `Orders`
--

CREATE TABLE `Orders` (
  `OrderID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `OrderNumber` varchar(50) DEFAULT NULL,
  `CustomerName` varchar(100) NOT NULL,
  `CustomerEmail` varchar(100) NOT NULL,
  `CustomerPhone` varchar(20) NOT NULL,
  `ShippingAddress` text NOT NULL,
  `ShippingCity` varchar(50) DEFAULT NULL,
  `ShippingArea` varchar(100) DEFAULT NULL,
  `ShippingLandmark` varchar(200) DEFAULT NULL,
  `OrderTotal` decimal(10,2) NOT NULL,
  `PaymentMethod` enum('M-Pesa','Cash on Delivery','Bank Transfer','Card') DEFAULT NULL,
  `PaymentStatus` varchar(50) DEFAULT 'Pending',
  `MpesaCheckoutRequestID` varchar(100) DEFAULT NULL,
  `MpesaReceiptNumber` varchar(50) DEFAULT NULL,
  `MpesaTransactionDate` datetime DEFAULT NULL,
  `MpesaPhoneNumber` varchar(20) DEFAULT NULL,
  `MpesaResultDesc` text DEFAULT NULL,
  `OrderStatus` enum('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  `OrderDate` datetime DEFAULT current_timestamp(),
  `Notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Orders`
--

INSERT INTO `Orders` (`OrderID`, `UserID`, `OrderNumber`, `CustomerName`, `CustomerEmail`, `CustomerPhone`, `ShippingAddress`, `ShippingCity`, `ShippingArea`, `ShippingLandmark`, `OrderTotal`, `PaymentMethod`, `PaymentStatus`, `MpesaCheckoutRequestID`, `MpesaReceiptNumber`, `MpesaTransactionDate`, `MpesaPhoneNumber`, `MpesaResultDesc`, `OrderStatus`, `OrderDate`, `Notes`) VALUES
(1, NULL, NULL, 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'Meru-678908', NULL, NULL, NULL, 100.00, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, 'Pending', '2025-11-21 07:03:16', ''),
(2, NULL, NULL, 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', '0720679285', NULL, NULL, NULL, 500.00, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, 'Pending', '2025-11-28 23:31:51', ''),
(3, NULL, NULL, 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'Meru-Nchiiru', NULL, NULL, NULL, 500.00, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, 'Pending', '2025-12-09 11:23:33', ''),
(4, NULL, NULL, 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'Nairobi', NULL, NULL, NULL, 100.00, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, 'Pending', '2025-12-13 16:53:33', ''),
(5, NULL, NULL, 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'Nakuru', NULL, NULL, NULL, 100.00, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, 'Pending', '2025-12-13 17:30:12', ''),
(6, NULL, NULL, 'Muraguri Igariura', 'muraguriigariura@gmail.com', '0768907024', 'Meru-Nchiiru', NULL, NULL, NULL, 500.00, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, 'Pending', '2025-12-13 18:52:06', ''),
(7, 1, 'ORD-2025-4689', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'Continental', 'Meru', 'Meru Town', '', 100.00, 'M-Pesa', 'Pending', NULL, NULL, NULL, NULL, NULL, 'Pending', '2025-12-16 11:29:36', 'M-Pesa Phone: 0720679285\n'),
(8, 1, 'ORD-2025-1430', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'Continental', 'Meru', 'Meru Town', '', 500.00, 'M-Pesa', 'Pending', NULL, NULL, NULL, NULL, NULL, 'Pending', '2025-12-16 16:20:01', 'M-Pesa Phone: 0720679285\n'),
(9, 1, 'ORD-2025-7710', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0768907024', 'GreenWood Mall', 'Meru', 'Meru Town', '', 600.00, 'M-Pesa', 'Pending', NULL, NULL, NULL, NULL, NULL, 'Pending', '2025-12-16 17:37:52', 'M-Pesa Phone: 0720679285\n'),
(10, NULL, 'ORD-2025-2108', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'Continental', 'Meru', 'Meru Town', '', 100.00, 'Cash on Delivery', 'Pending', NULL, NULL, NULL, NULL, NULL, 'Pending', '2025-12-17 13:46:22', ''),
(11, 1, 'ORD-2025-2204', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0708374150', 'Total energies', 'Meru', 'Meru Town', '', 100.00, 'Bank Transfer', 'Pending', NULL, NULL, NULL, NULL, NULL, 'Pending', '2025-12-17 14:37:26', ''),
(12, 1, 'ORD-2025-1353', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'GreenWood Mall', 'Meru', 'Meru Town', '', 100.00, 'M-Pesa', 'Pending Payment', 'ws_CO_18122025093034095708374149', NULL, NULL, '254708374149', NULL, 'Pending', '2025-12-18 09:30:32', 'M-Pesa Phone: 254708374149\n'),
(13, 1, 'ORD-2025-7059', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'GreenWood Mall', 'Meru', 'Meru Town', '', 100.00, 'M-Pesa', 'Pending Payment', 'ws_CO_18122025094516752708374149', NULL, NULL, '254708374149', NULL, 'Pending', '2025-12-18 09:45:15', 'M-Pesa Phone: 254708374149\n'),
(14, 1, 'ORD-2025-2377', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'GreenWood Mall', 'Meru', 'Meru Town', '', 100.00, 'M-Pesa', 'Pending Payment', 'ws_CO_18122025100651892708374149', NULL, NULL, '254708374149', NULL, 'Pending', '2025-12-18 10:06:48', 'M-Pesa Phone: 254708374149\n'),
(15, 1, 'ORD-2025-6947', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'GreenWood Mall', 'Meru', 'Meru Town', '', 100.00, 'M-Pesa', 'Pending Payment', 'ws_CO_18122025101812732708374149', NULL, NULL, '254708374149', NULL, 'Pending', '2025-12-18 10:18:11', 'M-Pesa Phone: 254708374149\n'),
(16, 1, 'ORD-2025-6605', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'GreenWood Mall', 'Meru', 'Meru Town', '', 100.00, 'M-Pesa', 'Pending Payment', 'ws_CO_18122025103315855708374149', NULL, NULL, '254708374149', NULL, 'Pending', '2025-12-18 10:33:14', 'M-Pesa Phone: 254708374149\n'),
(17, 1, 'ORD-2025-0595', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'GreenWood Mall', 'Meru', 'Meru Town', '', 100.00, 'M-Pesa', 'Pending Payment', 'ws_CO_18122025103828414720679285', NULL, NULL, '254720679285', NULL, 'Pending', '2025-12-18 10:38:27', 'M-Pesa Phone: 0720679285\n'),
(18, 1, 'ORD-2025-7160', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'GreenWood Mall', 'Meru', 'Meru Town', '', 100.00, 'M-Pesa', 'Pending Payment', 'ws_CO_18122025104008896720679285', NULL, NULL, '254720679285', NULL, 'Pending', '2025-12-18 10:40:05', 'M-Pesa Phone: 0720679285\n'),
(19, 1, 'ORD-2025-3313', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'GreenWood Mall', 'Meru', 'Meru Town', '', 100.00, 'M-Pesa', 'Pending Payment', 'ws_CO_18122025104844127720679285', NULL, NULL, '254720679285', NULL, 'Pending', '2025-12-18 10:48:42', 'M-Pesa Phone: 0720679285\n'),
(20, 1, 'ORD-2026-2428', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'GreenWood Mall', 'Meru', 'Meru Town', '', 100.00, 'M-Pesa', 'Pending Payment', 'ws_CO_10012026120044521720679285', NULL, NULL, '254720679285', NULL, 'Pending', '2026-01-10 12:00:43', 'M-Pesa Phone: 0720679285\n'),
(21, 1, 'ORD-2026-6518', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'GreenWood Mall', 'Meru', 'Meru Town', '', 100.00, 'M-Pesa', 'Pending Payment', 'ws_CO_20012026124010595759182300', NULL, NULL, '254759182300', NULL, 'Pending', '2026-01-20 12:40:07', 'M-Pesa Phone: 0759182300\n'),
(22, 1, 'ORD-2026-0836', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'GreenWood Mall', 'Meru', 'Meru Town', '', 100.00, 'M-Pesa', 'Payment Failed', 'ws_CO_20012026125827129720679285', NULL, NULL, '254720679285', 'Request Cancelled by user.', 'Pending', '2026-01-20 12:58:25', 'M-Pesa Phone: 0720679285\n'),
(23, 1, 'ORD-2026-7849', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'GreenWood Mall', 'Meru', 'Meru Town', '', 100.00, 'M-Pesa', 'Payment Failed', 'ws_CO_20012026125931089759182300', NULL, NULL, '254759182300', 'No response from user.', 'Pending', '2026-01-20 12:59:29', 'M-Pesa Phone: 0759182300\n'),
(24, 1, 'ORD-2026-0473', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0759182300', 'GreenWood Mall', 'Meru', 'Meru Town', '', 100.00, 'M-Pesa', 'Payment Failed', 'ws_CO_20012026130025403759182300', NULL, NULL, '254759182300', 'DS timeout user cannot be reached.', 'Pending', '2026-01-20 13:00:23', 'M-Pesa Phone: 0759182300\n'),
(25, 1, 'ORD-2026-3256', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0720679285', 'GreenWood Mall', 'Meru', 'Meru Town', 'Opposite Seven Sunday', 200.00, 'M-Pesa', 'Pending Payment', 'ws_CO_30012026115730163720679285', NULL, NULL, '254720679285', NULL, 'Pending', '2026-01-30 11:57:28', 'M-Pesa Phone: 0720679285\n'),
(26, 1, 'ORD-2026-8024', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0759182300', 'GreenWood Mall', 'Meru', 'Meru Town', '', 200.00, 'M-Pesa', 'Pending Payment', 'ws_CO_30012026115921812759182300', NULL, NULL, '254759182300', NULL, 'Pending', '2026-01-30 11:59:19', 'M-Pesa Phone: 0759182300\n'),
(27, 1, 'ORD-2026-5089', 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '0795271621', 'GreenWood Mall', 'Meru', 'Meru Town', 'Opposite Seven Sunday', 600.00, 'M-Pesa', 'Pending Payment', 'ws_CO_30012026124607220795271621', NULL, NULL, '254795271621', NULL, 'Pending', '2026-01-30 12:46:02', 'M-Pesa Phone: 0795271621\n');

-- --------------------------------------------------------

--
-- Table structure for table `Payments`
--

CREATE TABLE `Payments` (
  `PaymentID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL COMMENT 'One payment per order',
  `PaymentMethod` enum('mpesa','stripe','bank_transfer') NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `PaymentDate` datetime DEFAULT current_timestamp(),
  `Status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `TransactionID` varchar(100) DEFAULT NULL COMMENT 'From M-Pesa or Stripe',
  `PhoneNumber` varchar(20) DEFAULT NULL COMMENT 'For M-Pesa payments',
  `PaymentDetails` text DEFAULT NULL COMMENT 'JSON or serialized payment data',
  `FailureReason` text DEFAULT NULL COMMENT 'If payment failed, reason why'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Payment transactions';

-- --------------------------------------------------------

--
-- Table structure for table `Reviews`
--

CREATE TABLE `Reviews` (
  `ReviewID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `ArtworkID` int(11) DEFAULT NULL COMMENT 'Can be NULL for general testimonials',
  `Rating` int(11) NOT NULL CHECK (`Rating` between 1 and 5),
  `ReviewText` text NOT NULL,
  `DatePosted` datetime DEFAULT current_timestamp(),
  `IsVerifiedPurchase` tinyint(1) DEFAULT 0 COMMENT 'Did they actually buy it?',
  `IsApproved` tinyint(1) DEFAULT 0 COMMENT 'Admin approval before showing',
  `IsFeatured` tinyint(1) DEFAULT 0 COMMENT 'Show on homepage',
  `AdminResponse` text DEFAULT NULL COMMENT 'Your reply to the review'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer reviews and testimonials';

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `UserID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `UserType` enum('customer','admin') DEFAULT 'customer',
  `DateRegistered` datetime DEFAULT current_timestamp(),
  `LastLogin` datetime DEFAULT NULL,
  `IsActive` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`UserID`, `FullName`, `Email`, `Password`, `Phone`, `Address`, `UserType`, `DateRegistered`, `LastLogin`, `IsActive`) VALUES
(1, 'Igariura Muraguri', 'igariuramuraguri@gmail.com', '$2y$10$XTu5tW5s5vONlyNQKzSJ/u7FuxU0oIXgOi1fRyKD8uSkkCBfc904y', '0720679285', NULL, 'customer', '2025-12-09 10:18:16', '2026-01-30 12:48:40', 1),
(2, 'kindness', 'kindnessebeneza@gmail.com', '$2y$10$o4o1CXUzYqBkftIxpwagVeQbPUJ.LFQSwr5VE0HxR06lHeaCOZhri', '0759182300', NULL, 'customer', '2025-12-10 11:02:31', NULL, 1),
(3, 'Muraguri Igariura', 'muraguriigariura@gmail.com', '$2y$10$mUcZ1SY6jriTmOlp8Dptm.3YYqA.jDMpIsF7HzCCKRthbJIKVqP4a', '0720679285', NULL, 'customer', '2025-12-13 18:49:18', '2026-01-20 16:12:16', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Addresses`
--
ALTER TABLE `Addresses`
  ADD PRIMARY KEY (`AddressID`),
  ADD KEY `idx_customer` (`CustomerID`),
  ADD KEY `idx_default` (`IsDefault`);

--
-- Indexes for table `ArtworkImages`
--
ALTER TABLE `ArtworkImages`
  ADD PRIMARY KEY (`ImageID`),
  ADD KEY `idx_artwork` (`ArtworkID`),
  ADD KEY `idx_order` (`ImageOrder`);

--
-- Indexes for table `Artworks`
--
ALTER TABLE `Artworks`
  ADD PRIMARY KEY (`ArtworkID`),
  ADD KEY `idx_category` (`CategoryID`),
  ADD KEY `idx_available` (`IsAvailable`),
  ADD KEY `idx_featured` (`IsFeatured`),
  ADD KEY `idx_price` (`Price`),
  ADD KEY `idx_views` (`ViewCount`);

--
-- Indexes for table `Cart`
--
ALTER TABLE `Cart`
  ADD PRIMARY KEY (`CartID`),
  ADD UNIQUE KEY `unique_user_artwork` (`UserID`,`ArtworkID`),
  ADD KEY `ArtworkID` (`ArtworkID`);

--
-- Indexes for table `Categories`
--
ALTER TABLE `Categories`
  ADD PRIMARY KEY (`CategoryID`),
  ADD UNIQUE KEY `CategoryName` (`CategoryName`),
  ADD KEY `idx_active` (`IsActive`),
  ADD KEY `idx_order` (`DisplayOrder`);

--
-- Indexes for table `ContactMessages`
--
ALTER TABLE `ContactMessages`
  ADD PRIMARY KEY (`MessageID`);

--
-- Indexes for table `Customers`
--
ALTER TABLE `Customers`
  ADD PRIMARY KEY (`CustomerID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `idx_email` (`Email`),
  ADD KEY `idx_active` (`IsActive`);

--
-- Indexes for table `Inquiries`
--
ALTER TABLE `Inquiries`
  ADD PRIMARY KEY (`InquiryID`),
  ADD KEY `CustomerID` (`CustomerID`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_date` (`DateSubmitted`),
  ADD KEY `idx_type` (`InquiryType`);

--
-- Indexes for table `Newsletter`
--
ALTER TABLE `Newsletter`
  ADD PRIMARY KEY (`SubscriberID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `idx_active` (`IsActive`),
  ADD KEY `idx_email` (`Email`);

--
-- Indexes for table `OrderItems`
--
ALTER TABLE `OrderItems`
  ADD PRIMARY KEY (`OrderItemID`),
  ADD KEY `OrderID` (`OrderID`),
  ADD KEY `ArtworkID` (`ArtworkID`);

--
-- Indexes for table `Orders`
--
ALTER TABLE `Orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD UNIQUE KEY `OrderNumber` (`OrderNumber`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `Payments`
--
ALTER TABLE `Payments`
  ADD PRIMARY KEY (`PaymentID`),
  ADD UNIQUE KEY `OrderID` (`OrderID`),
  ADD KEY `idx_order` (`OrderID`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_transaction` (`TransactionID`);

--
-- Indexes for table `Reviews`
--
ALTER TABLE `Reviews`
  ADD PRIMARY KEY (`ReviewID`),
  ADD KEY `idx_customer` (`CustomerID`),
  ADD KEY `idx_artwork` (`ArtworkID`),
  ADD KEY `idx_approved` (`IsApproved`),
  ADD KEY `idx_featured` (`IsFeatured`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Addresses`
--
ALTER TABLE `Addresses`
  MODIFY `AddressID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ArtworkImages`
--
ALTER TABLE `ArtworkImages`
  MODIFY `ImageID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Artworks`
--
ALTER TABLE `Artworks`
  MODIFY `ArtworkID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `Cart`
--
ALTER TABLE `Cart`
  MODIFY `CartID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `Categories`
--
ALTER TABLE `Categories`
  MODIFY `CategoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ContactMessages`
--
ALTER TABLE `ContactMessages`
  MODIFY `MessageID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Customers`
--
ALTER TABLE `Customers`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Inquiries`
--
ALTER TABLE `Inquiries`
  MODIFY `InquiryID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Newsletter`
--
ALTER TABLE `Newsletter`
  MODIFY `SubscriberID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `OrderItems`
--
ALTER TABLE `OrderItems`
  MODIFY `OrderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `Orders`
--
ALTER TABLE `Orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `Payments`
--
ALTER TABLE `Payments`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Reviews`
--
ALTER TABLE `Reviews`
  MODIFY `ReviewID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Addresses`
--
ALTER TABLE `Addresses`
  ADD CONSTRAINT `Addresses_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `Customers` (`CustomerID`) ON DELETE CASCADE;

--
-- Constraints for table `ArtworkImages`
--
ALTER TABLE `ArtworkImages`
  ADD CONSTRAINT `ArtworkImages_ibfk_1` FOREIGN KEY (`ArtworkID`) REFERENCES `Artworks` (`ArtworkID`) ON DELETE CASCADE;

--
-- Constraints for table `Artworks`
--
ALTER TABLE `Artworks`
  ADD CONSTRAINT `Artworks_ibfk_1` FOREIGN KEY (`CategoryID`) REFERENCES `Categories` (`CategoryID`);

--
-- Constraints for table `Cart`
--
ALTER TABLE `Cart`
  ADD CONSTRAINT `Cart_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `Cart_ibfk_2` FOREIGN KEY (`ArtworkID`) REFERENCES `Artworks` (`ArtworkID`) ON DELETE CASCADE;

--
-- Constraints for table `Inquiries`
--
ALTER TABLE `Inquiries`
  ADD CONSTRAINT `Inquiries_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `Customers` (`CustomerID`) ON DELETE SET NULL;

--
-- Constraints for table `OrderItems`
--
ALTER TABLE `OrderItems`
  ADD CONSTRAINT `OrderItems_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `Orders` (`OrderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `OrderItems_ibfk_2` FOREIGN KEY (`ArtworkID`) REFERENCES `Artworks` (`ArtworkID`);

--
-- Constraints for table `Orders`
--
ALTER TABLE `Orders`
  ADD CONSTRAINT `Orders_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`);

--
-- Constraints for table `Payments`
--
ALTER TABLE `Payments`
  ADD CONSTRAINT `Payments_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `Orders` (`OrderID`);

--
-- Constraints for table `Reviews`
--
ALTER TABLE `Reviews`
  ADD CONSTRAINT `Reviews_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `Customers` (`CustomerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `Reviews_ibfk_2` FOREIGN KEY (`ArtworkID`) REFERENCES `Artworks` (`ArtworkID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;