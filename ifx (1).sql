-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 15, 2026 at 12:24 PM
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
-- Database: `ifx`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `product_id` int(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` int(255) NOT NULL,
  `quantity` int(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `product_id`, `product_name`, `product_price`, `quantity`, `email`, `image`) VALUES
(72, 21, 'Pair of clothes ', 150, 1, 'jolorebua25@gmail.com', 'Admin/Product/uploads/products/product_68fb98da60764.jpg'),
(81, 48, 'Couple unisex silk plain short sleeve terno pajama set plus size night sleepwear f', 299, 1, '6@gmail.com', 'Admin/Product/uploads/products/product_68ff12fb31b49.webp');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `order_total` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'cod',
  `status` enum('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  `rider_id` int(11) DEFAULT NULL,
  `rider_name` varchar(100) DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `email`, `product_name`, `quantity`, `order_total`, `payment_method`, `status`, `rider_id`, `rider_name`, `assigned_at`, `rating`, `order_date`) VALUES
(1, '02@gmail.com', 'Couple unisex silk plain short sleeve terno pajama set plus size night sleepwear f', 1, 299.00, 'cod', 'Delivered', 5, 'Marc Mamon', '2025-10-28 19:34:52', NULL, '2025-10-28 13:54:57'),
(2, '6@gmail.com', 'Basic Solid Skater Short Sleeves Flowy Dress for Women ', 1, 399.00, 'cod', 'Delivered', 4, 'Justine Panique', '2025-10-28 18:57:12', NULL, '2025-10-29 09:36:11'),
(4, '09@gmail.com', 'Pajamas Terno Sleepwear for Women Tops Long Pants Loungewear', 1, 590.00, 'cod', 'Delivered', 5, 'Marc Mamon', '2025-10-28 19:34:19', NULL, '2025-10-29 10:14:56'),
(6, '09@gmail.com', 'Pajamas Terno Sleepwear for Women Tops Long Pants Loungewear', 1, 590.00, 'cod', 'Delivered', 4, 'Justine Panique', '2025-10-28 19:35:20', NULL, '2025-10-29 10:35:03'),
(8, '09@gmail.com', 'Basic Solid Skater Short Sleeves Flowy Dress for Women ', 1, 399.00, 'cod', '', NULL, NULL, NULL, NULL, '2025-10-29 11:20:07'),
(9, '09@gmail.com', 'Pajamas Terno Sleepwear for Women Tops Long Pants Loungewear', 1, 590.00, 'cod', 'Delivered', 6, 'justine', '2025-10-28 20:25:06', NULL, '2025-10-29 11:24:23'),
(10, '09@gmail.com', 'Terno Tshirt + Pajama Pambahay Adult Sleepwear Terno Pajama', 1, 200.00, 'cod', 'Processing', NULL, NULL, NULL, NULL, '2025-10-29 11:24:23');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `compare_price` decimal(10,2) DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `sku` varchar(50) DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT 0.00,
  `length` decimal(10,2) DEFAULT 0.00,
  `width` decimal(10,2) DEFAULT 0.00,
  `height` decimal(10,2) DEFAULT 0.00,
  `tags` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `category`, `price`, `compare_price`, `stock`, `sku`, `weight`, `length`, `width`, `height`, `tags`, `images`, `status`, `created_at`) VALUES
(25, 'Korean Half turtleneck Thin knitted sweater', 'Stylish and practical, these cargo pants feature multiple pockets and a relaxed fit for everyday comfort. Perfect for casual wear, outdoor adventures, or street-style looks.', 'Tops & Knitwear', 150.00, 0.00, 18, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff0726defd1.jpg\"]', 'active', '2025-10-27 13:46:14'),
(26, ' Asymmetric Collar Print Short Sleeve Oversized T-Shirt ', 'Stylish and practical, these cargo pants feature multiple pockets and a relaxed fit for everyday comfort. Perfect for casual wear, outdoor adventures, or street-style looks.', 'Tops & Knitwear', 200.00, 0.00, 18, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff076b63f87.jpg\"]', 'active', '2025-10-27 13:47:23'),
(27, 'Mini Padded Tranquil Summer Classy Cotton Dress', 'Breeze through sunny days in effortless elegance with this mini padded cotton dress. Designed with a tranquil, minimalist charm, this piece combines comfort and sophistication — perfect for warm-weather outings or casual chic looks. The built-in padding provides a flattering, seamless shape, while the breathable cotton fabric keeps you cool and confident all day.', 'Dresses & Jumpsuits / Rompers', 189.00, 0.00, 10, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff07ade5383.jpg\"]', 'active', '2025-10-27 13:48:29'),
(28, 'Korean retro 100% cotton sleeping cat print top, pure cotton thin women\'s T-shirt', 'Stay cute and comfy with this Korean-inspired retro T-shirt featuring an adorable sleeping cat print. Made from 100% pure cotton, this thin and breathable tee is perfect for warm days or cozy lounging. Its relaxed fit and minimalist design capture the effortless charm of Korean street fashion while keeping you cool and stylish anywhere you go.', 'Tops & Knitwear', 199.00, 0.00, 15, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff07e722f76.jpg\"]', 'active', '2025-10-27 13:49:27'),
(29, 'Korean style loose round neck colorful letter print pure cotton T-shirt', 'Add a pop of fun and color to your casual wardrobe with this Korean-style loose-fit T-shirt. Crafted from 100% pure cotton, it offers a soft, breathable feel that keeps you comfortable all day long. The colorful letter print adds a playful, youthful touch — perfect for expressing your vibrant personality. With its round neck and relaxed silhouette, this tee is a must-have for everyday wear.', 'Casual & Korean Fashion', 169.00, 0.00, 15, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff0815e1020.jpg\"]', 'active', '2025-10-27 13:50:13'),
(30, ' Oversized Round Neck Trendy Car Printed Cotton T-shirt ', 'Drive your style forward with this trendy oversized cotton tee featuring a cool retro car print. Made from soft, breathable cotton, it blends comfort and street-style appeal perfectly. The loose fit and round neckline give it a relaxed, effortless vibe — ideal for daily wear, casual hangouts, or a laid-back Korean street look.', 'Tops & Knitwear', 169.00, 0.00, 25, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff085bbff27.jpg\"]', 'active', '2025-10-27 13:51:23'),
(31, 'sweetheart Neck Puff Sleeve Waffle Knit Tee ', 'Embrace soft femininity and comfort with this stylish waffle knit tee. Designed with a flattering sweetheart neckline and elegant puff sleeves, it blends a romantic silhouette with cozy textures. The waffle-knit fabric offers gentle stretch and breathability, making it perfect for both casual days and dressier moments.', 'Casual & Korean Fashion', 259.00, 0.00, 50, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff088f293df.jpg\"]', 'active', '2025-10-27 13:52:15'),
(32, 'unisex corduroy pants', 'Stay effortlessly stylish and comfortable with these classic unisex corduroy pants. Made from soft, durable corduroy fabric, they feature a relaxed fit that suits any style — from streetwear to casual everyday looks. The ribbed texture adds a vintage charm while offering warmth and comfort for all-day wear. Perfect for both men and women who love timeless, versatile fashion.', 'Bottoms (Pants, Shorts, Skirts, Leggings)', 399.00, 0.00, 20, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff08d6d766f.jpg\"]', 'active', '2025-10-27 13:53:26'),
(33, 'sports suit unisex quick drying long sleeved summer and autum outdoor', 'Stay active and comfortable in any weather with this unisex sports suit designed for summer and autumn outdoor activities. Crafted from lightweight, quick-drying fabric, it keeps you cool, dry, and flexible during workouts, runs, or hikes. The long sleeves provide added sun protection while maintaining breathability and comfort.', 'Outerwear (Jackets, Coats, Cardigans, Hoodies)', 459.00, 0.00, 15, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff0902c98ff.jpg\"]', 'active', '2025-10-27 13:54:10'),
(34, 'stripped baggy long pants for men', 'Step up your streetwear game with these striped baggy long pants — the perfect mix of comfort and modern style. Designed for a relaxed fit, these pants feature vertical stripes that create a sleek, elongated look. Made from lightweight, breathable fabric, they’re ideal for casual days, travel, or lounging in effortless style.', 'Bottoms (Pants, Shorts, Skirts, Leggings)', 699.00, 0.00, 15, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff09913f13c.jpg\"]', 'active', '2025-10-27 13:56:33'),
(35, 'women korean high waisted short skirts', 'Stay cute and confident with these Korean-style high-waisted skorts — the perfect blend of style and comfort. Designed with the look of a skirt and the convenience of shorts, they let you move freely while keeping a polished, feminine silhouette. The high-waist design enhances your shape, giving you that trendy Korean street style vibe that’s perfect for casual or semi-formal wear.', 'Bottoms (Pants, Shorts, Skirts, Leggings)', 189.00, 0.00, 10, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff09d74a970.jpg\"]', 'active', '2025-10-27 13:57:43'),
(36, 'Oversized motorcycle zipper polo ', 'Bring bold attitude and street-style edge to your look with this oversized motorcycle zipper polo. Designed with a modern loose fit and half-zip collar, it combines sporty aesthetics with everyday comfort. The sleek zipper detail and structured silhouette give off a confident, effortlessly cool vibe — perfect for casual outings or a stylish streetwear ensemble.', 'Casual & Korean Fashion', 359.00, 0.00, 13, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff0a07e11e1.jpg\"]', 'active', '2025-10-27 13:58:31'),
(37, 'american retro off-shoulder', 'Channel classic vintage charm with a modern twist in this American retro off-shoulder top. Designed to highlight your neckline and shoulders, it combines a flattering feminine silhouette with a touch of old-school allure. The soft, stretchable fabric ensures a comfortable fit while maintaining a chic, confident look — perfect for casual dates, concerts, or weekend getaways.', 'Tops & Knitwear', 179.00, 0.00, 20, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff0a2b77d86.jpg\"]', 'inactive', '2025-10-27 13:59:07'),
(38, 'ZEKE Men\'s Raglan Short Sleeve', 'Stay cool and sporty with the ZEKE Men’s Raglan Short Sleeve. Designed for comfort and performance, this shirt features contrasting raglan sleeves that give a modern, athletic look. Made from soft, breathable fabric, it provides flexibility and ease of movement — perfect for casual wear, workouts, or outdoor activitie', 'Casual & Korean Fashion', 699.00, 0.00, 10, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff0a5812c48.jpg\"]', 'active', '2025-10-27 13:59:52'),
(39, 'baggy pants ', 'Step into effortless comfort and style with these classic baggy pants. Designed with a relaxed, loose fit, they bring a laid-back vibe that’s perfect for everyday wear or street-style looks. Made from soft, breathable fabric, these pants give you all-day comfort while keeping your outfit trendy and versatile.', 'Bottoms (Pants, Shorts, Skirts, Leggings)', 399.00, 0.00, 5, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff0a7dc9442.jpg\"]', 'active', '2025-10-27 14:00:29'),
(40, 'Casual Basic Solid Dress A-Line Style Halter Tie-Neck Dress ', 'Keep it simple yet stunning with this Casual Basic Solid A-Line Dress. Designed with a flattering halter tie-neck and flowy A-line silhouette, it brings a perfect balance of comfort and charm. The solid color gives it a timeless, minimalist appeal — making it easy to dress up or down for any occasion.', 'Dresses & Jumpsuits / Rompers', 350.00, 0.00, 5, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff0f8dd1008.webp\"]', 'active', '2025-10-27 14:22:05'),
(41, 'Basic Solid Skater Short Sleeves Flowy Dress for Women ', 'Keep your look effortlessly feminine with this Basic Solid Skater Dress. Featuring short sleeves and a flattering flowy silhouette, this piece is perfect for everyday wear. The soft fabric moves gracefully with you, while the skater cut highlights your waist for a naturally chic look. Simple, versatile, and comfortable — a wardrobe essential for any season', 'Dresses & Jumpsuits / Rompers', 399.00, 0.00, 5, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff0fd65a0a3.webp\"]', 'inactive', '2025-10-27 14:23:18'),
(42, 'KY Printed Smocking Romper Floral for woman High Quality Fabric Fit Small To Large', 'Step into effortless charm with the KY Printed Smocking Floral Romper. Designed with a flattering smocked bodice and soft, breathable fabric, this piece combines comfort and style perfectly. Its floral print adds a touch of femininity, while the lightweight material makes it ideal for sunny days or casual outings', 'Dresses & Jumpsuits / Rompers', 200.00, 0.00, 10, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff101377f5d.webp\"]', 'active', '2025-10-27 14:24:19'),
(43, 'Smocking Wideleg Pants Tube Romper Jumpsuit', 'Stay effortlessly chic and comfortable with this Smocking Tube Jumpsuit featuring wide-leg pants. Designed with a stretchy smocked bodice that hugs your curves and a flowy leg cut for easy movement, this piece is perfect for any occasion — from brunch dates to evening strolls.', 'Dresses & Jumpsuits / Rompers', 199.00, 0.00, 10, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff1076ec556.webp\"]', 'active', '2025-10-27 14:25:58'),
(44, 'BeAStar-Isadora Romper/Playsuit Shorts with two Side Pock', 'Step into effortless charm with the BeAStar Isadora Romper — the perfect blend of comfort and casual elegance. Designed with a flattering fit and functional side pockets, this playsuit keeps you stylish and practical all day long. The lightweight fabric and relaxed silhouette make it ideal for warm weather or laid-back days.', 'Dresses & Jumpsuits / Rompers', 600.00, 0.00, 10, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff11409e145.webp\"]', 'active', '2025-10-27 14:29:20'),
(45, 'Hoodie Long Sleeve Jacket with Pocket and Hood Plain Unisex Sweater High Quality Menswear Womenswear', 'Stay cozy and stylish with this high-quality unisex hoodie jacket. Designed for both men and women, it features a soft fabric that keeps you warm while maintaining a sleek, minimal look. The front pocket adds practicality, and the adjustable hood offers extra comfort for everyday wear.', 'Outerwear (Jackets, Coats, Cardigans, Hoodies)', 699.00, 0.00, 10, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff11dfe76a5.webp\"]', 'active', '2025-10-27 14:31:59'),
(46, 'Terno Tshirt + Pajama Pambahay Adult Sleepwear Terno Pajama', 'Experience ultimate comfort and relaxation with this soft Terno Sleepwear Set. Designed with a matching T-shirt and pajama, it’s perfect for lounging at home or getting a good night’s sleep. The breathable fabric keeps you cool and cozy, while the simple design adds a touch of everyday style to your pambahay look.', 'Sleepwear & Sets', 200.00, 0.00, 15, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff126c23ea6.webp\"]', 'inactive', '2025-10-27 14:34:20'),
(47, 'Pajamas Terno Sleepwear for Women Tops Long Pants Loungewear', 'Drift into comfort with this cozy Pajama Terno Set for women. Featuring a soft top and matching long pants, this sleepwear set is perfect for relaxing evenings and restful nights. Its breathable fabric keeps you cool and comfy, while the simple yet elegant design makes it ideal for lounging in style.', 'Sleepwear & Sets', 590.00, 0.00, 20, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff12ba1a642.webp\"]', 'active', '2025-10-27 14:35:38'),
(48, 'Couple unisex silk plain short sleeve terno pajama set plus size night sleepwear f', 'Sleep in luxury with this silky smooth Couple Pajama Set, designed for both men and women. The plain short-sleeve top and matching shorts or pants offer a relaxed yet elegant fit, perfect for warm nights or cozy lounging. Made from premium silk-like fabric, it provides a soft, cool feel against the skin for ultimate comfort.', 'Sleepwear & Sets', 299.00, 0.00, 10, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff12fb31b49.webp\"]', 'active', '2025-10-27 14:36:43'),
(49, 'Cotton Blazer Cardigan With Hood With Pocket Korean Cardigan', 'Stay effortlessly chic with this Korean-inspired Cotton Blazer Cardigan. Made from soft, breathable cotton, it combines the relaxed comfort of a hoodie with the polished look of a blazer. Featuring a hood and functional pockets, this versatile piece is perfect for layering in any season.', 'Outerwear (Jackets, Coats, Cardigans, Hoodies)', 149.00, 0.00, 20, NULL, 0.00, 0.00, 0.00, 0.00, NULL, '[\"uploads\\/products\\/product_68ff1377553e2.webp\"]', 'inactive', '2025-10-27 14:38:47');

-- --------------------------------------------------------

--
-- Table structure for table `reset_tokens`
--

CREATE TABLE `reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `riders`
--

CREATE TABLE `riders` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riders`
--

INSERT INTO `riders` (`id`, `name`, `phone`, `email`, `status`, `created_at`) VALUES
(4, 'Justine Panique', '09165050564', 'jol@gmail.com', 'active', '2025-10-29 01:31:17'),
(5, 'Marc Mamon', '09987654321', 'justinegomez777@gmail.com', 'active', '2025-10-29 01:31:33'),
(6, 'justine', '09987654321', 'sad@gmail.com', 'active', '2025-10-29 02:34:45');

-- --------------------------------------------------------

--
-- Table structure for table `seller`
--

CREATE TABLE `seller` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phonenumber` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop`
--

CREATE TABLE `shop` (
  `id` int(255) NOT NULL,
  `product_id` int(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` int(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userdata`
--

CREATE TABLE `userdata` (
  `id` int(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `phonenumber` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `birthdate` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expire` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userdata`
--

INSERT INTO `userdata` (`id`, `fullname`, `email`, `profile_pic`, `address`, `phonenumber`, `role`, `password`, `birthdate`, `reset_token`, `reset_token_expire`) VALUES
(44, 'JOLO MAMON REBUA', '12@gmail.com', NULL, 'Zone 2', '01010011001', 'admin', '123', '', NULL, NULL),
(45, 'JOLO MAMON REBUA', '123@gmail.com', NULL, 'Zone 2', '09165050564', 'customer', '454545', '', NULL, NULL),
(46, 'JOLO MAMON REBUA', '01@gmail.com', NULL, 'Zone 2', '09165050564', 'admin', '123456', '', NULL, NULL),
(47, 'jolorebua', '02@gmail.com', NULL, 'Brgy Alegre Oton Iloilo', '09987654321', 'customer', '123456', '', NULL, NULL),
(48, 'JOLO MAMON REBUA', 'kas@gmail.com', NULL, 'Zone 2', '09987654321', 'customer', '123456', '', NULL, NULL),
(49, 'JOLO MAMON REBUA', 'kas1@gmail.com', NULL, 'Zone 2', '1234567890', 'admin', '123456', '', NULL, NULL),
(50, 'JOLO MAMON REBUA', '123456@gmail.com', NULL, 'Zone 2', '09987654321', 'customer', '123456', '', NULL, NULL),
(51, 'JOLO MAMON REBUA', '321@gmail.com', NULL, 'Zone 2', '1234567890', 'admin', '123456', '', NULL, NULL),
(52, 'JOLO MAMON REBUA', '6@gmail.com', NULL, 'Zone 2', '09987654321', 'customer', '123456', '', NULL, NULL),
(53, 'JOLO MAMON REBUA', '0@gmail.com', NULL, 'Zone 2', '9634483995', 'admin', '090909', '', NULL, NULL),
(54, 'JOLO MAMON REBUA', '09@gmail.com', NULL, 'Brgy Alegre Oton Iloilo', '09987654321', 'admin', '123456', '', NULL, NULL),
(55, 'lei', 'lei@ups.com', NULL, 'lapaz', '910547538', 'customer', '123456', '', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `visitor_stats`
--

CREATE TABLE `visitor_stats` (
  `id` int(11) NOT NULL,
  `visit_date` date NOT NULL,
  `visitors` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitor_stats`
--

INSERT INTO `visitor_stats` (`id`, `visit_date`, `visitors`) VALUES
(1, '2025-08-11', 120),
(2, '2025-08-12', 150),
(3, '2025-08-13', 90),
(4, '2025-08-14', 200),
(5, '2025-08-15', 170),
(6, '2025-08-16', 80),
(7, '2025-08-17', 60);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reset_tokens`
--
ALTER TABLE `reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `riders`
--
ALTER TABLE `riders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `seller`
--
ALTER TABLE `seller`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shop`
--
ALTER TABLE `shop`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `userdata`
--
ALTER TABLE `userdata`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `birthdate` (`id`);

--
-- Indexes for table `visitor_stats`
--
ALTER TABLE `visitor_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `visit_date` (`visit_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `reset_tokens`
--
ALTER TABLE `reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `riders`
--
ALTER TABLE `riders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `seller`
--
ALTER TABLE `seller`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `shop`
--
ALTER TABLE `shop`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `userdata`
--
ALTER TABLE `userdata`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `visitor_stats`
--
ALTER TABLE `visitor_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reset_tokens`
--
ALTER TABLE `reset_tokens`
  ADD CONSTRAINT `reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `userdata` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
