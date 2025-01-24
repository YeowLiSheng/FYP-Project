-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2025 at 04:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fyp`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `staff_id` int(11) NOT NULL,
  `admin_id` varchar(150) NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `admin_image` varchar(255) NOT NULL,
  `admin_password` varchar(150) NOT NULL,
  `admin_email` varchar(100) NOT NULL,
  `admin_contact_number` varchar(100) NOT NULL,
  `admin_gender` varchar(50) NOT NULL,
  `admin_joined_date` varchar(50) NOT NULL,
  `admin_status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`staff_id`, `admin_id`, `admin_name`, `admin_image`, `admin_password`, `admin_email`, `admin_contact_number`, `admin_gender`, `admin_joined_date`, `admin_status`) VALUES
(15, 'superadmin', 'Yeow li sheng', 'uploads/1731986093_Screenshot 2024-05-19 155108.png', 'superadmin', 'lishengyao1068@gmail.com', '016-7168518', 'male', '2024-11-17 12:50:53', 1),
(26, 'admin1', 'Cheong Wei Kit', '', 'QWERTYUIOP1234567890!', 'cheongweikit12345@gmail.com', '011-39701086', '', '2025-01-08 14:20:59', 1);

-- --------------------------------------------------------

--
-- Table structure for table `bank_card`
--

CREATE TABLE `bank_card` (
  `card_id` int(11) NOT NULL,
  `card_holder_name` varchar(244) NOT NULL,
  `card_number` varchar(19) NOT NULL,
  `valid_thru` varchar(25) NOT NULL,
  `cvv` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank_card`
--

INSERT INTO `bank_card` (`card_id`, `card_holder_name`, `card_number`, `valid_thru`, `cvv`) VALUES
(1, 'Cheong Wei Kit', '1234 5678 1234 5678', '11/26', '567'),
(2, 'Chong Jun Li', '1111 2222 3333 4444', '12/26', '888'),
(3, 'Yeow Li Sheng', '1233 4566 7899 1233', '01/27', '666');

-- --------------------------------------------------------

--
-- Table structure for table `blog`
--

CREATE TABLE `blog` (
  `blog_id` int(11) NOT NULL,
  `picture` text NOT NULL,
  `title` varchar(150) NOT NULL,
  `subtitle` text NOT NULL,
  `description` text NOT NULL,
  `date` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog`
--

INSERT INTO `blog` (`blog_id`, `picture`, `title`, `subtitle`, `description`, `date`) VALUES
(14, 'photo-1441986300917-64674bd600d8.jpg', 'The Great Big List of Women’s Gifts for the Holidays', 'Find the perfect gift for every woman on your list this holiday season with our ultimate gift guide!', 'The holiday season is the perfect time to show the women in your life how much they mean to you with a thoughtful gift. Whether you\'re shopping for a friend, family member, partner, or colleague, finding the right gift can sometimes feel overwhelming. That’s why we’ve created the ultimate list of women’s gifts for the holidays, offering something special for every personality and interest.\r\n\r\nFor the fashion-forward women, consider trendy accessories like stylish scarves, handbags, or chic jewelry. A cozy knit sweater or a sleek winter coat could also be the perfect gift to keep her warm and fashionable. If she loves beauty and self-care, pamper her with luxurious skincare products, a soothing bathrobe, or a spa day voucher for a relaxing experience.\r\n\r\nFor the women who enjoy a good book or home decor, a personalized journal, a cozy blanket, or elegant candles can make her feel special. For tech lovers, the latest gadgets like smartwatches, headphones, or a portable speaker are sure to impress. And don’t forget about the fitness enthusiasts—smart water bottles, yoga mats, or workout clothes can inspire her to stay healthy and active in the new year.\r\n\r\nNo matter her taste, this list will help you find the perfect holiday gift to make her smile and feel loved.', '14Dec2024'),
(16, 'pexels-shattha-pilabut-38930-135620.jpg', 'How to Choose the Right Bag and Accessories for Any Outfit', 'Learn the art of pairing bags and accessories with your outfits to elevate your style effortlessly!', 'Choosing the right bag and accessories for your outfit can make all the difference between a good look and a great one. The key is to balance functionality with style, ensuring your accessories complement, not overpower, your attire. When selecting a bag, consider the occasion first. For a casual day out, a crossbody or tote bag offers practicality and versatility, while a structured handbag or clutch is perfect for a formal evening event.\r\n\r\nThe color of your bag plays an important role in creating harmony with your outfit. Neutral tones like black, beige, or gray are timeless and versatile, while bold colors or patterns can add a fun pop to a more neutral ensemble. Matching your accessories with your bag can create a cohesive look, but don’t be afraid to mix textures. Pairing a leather bag with gold jewelry or a suede bag with silver accents can add dimension and interest to your outfit.\r\n\r\nConsider the shape and size of your bag based on your outfit. A sleek, slim bag works well with tailored outfits, while a slouchy, oversized bag suits more relaxed, casual looks. Finally, don’t forget to add personal touches like scarves, hats, or statement jewelry that reflect your unique style. By thoughtfully selecting your bag and accessories, you’ll always feel confident and stylish, no matter the occasion.', '14Dec2024'),
(17, 'pngtree-shopping-noon-clothing-store-clothes-display-shopping-mall-buying-clothes-photography-picture-image_1486807.jpg', 'Master the Art of Pairing Bags and Accessories with Your Outfits', 'Unlock the secret to flawless style by learning how to perfectly pair your bags and accessories with any look!', 'When it comes to completing an outfit, the right bag and accessories are key to transforming your look from simple to stunning. Mastering the art of pairing bags and accessories can elevate your style effortlessly, ensuring you feel confident and chic wherever you go. The first step is understanding the occasion. For casual days, opt for a crossbody or slouchy bag that’s both practical and stylish, while a structured handbag or elegant clutch works wonders for formal events or nights out.\r\n\r\nNext, consider the color palette of your outfit. Neutral bags in shades like black, beige, or gray are versatile and easy to pair with almost anything, while bold, colorful bags or those with unique patterns can add personality to more neutral ensembles. When choosing accessories, keep your bag’s style in mind—complement it with jewelry, scarves, or hats that either match or contrast in texture and tone to create a balanced look.\r\n\r\nSize also matters—pair a small, sleek bag with tailored outfits and larger, more relaxed bags with casual or oversized clothing. Finally, personal style plays a huge role. Whether you prefer minimalism or bold statement pieces, find accessories that reflect your individuality. By thoughtfully pairing bags and accessories with your outfits, you can effortlessly create looks that are both stylish and practical, no matter the occasion.', '14Dec2024'),
(22, 'photo-1441984904996-e0b6ba687e04.jpg', 'Unleash Your Style with Our Bags & Accessories', 'Explore a wide variety of trendy bags and accessories to enhance your style. From classic to contemporary, find the perfect match for any outfit.', 'Unleash your style with our exclusive collection of bags and accessories! Whether you’re looking for a sleek, sophisticated handbag, a spacious tote for your daily essentials, or a bold statement piece to stand out, we’ve got something for every occasion. Our wide range of products includes trendy designs, timeless classics, and everything in between, ensuring that you find the perfect match for your personal style. From elegant evening bags to casual crossbody options, each item is crafted with quality materials and attention to detail. Accessories like scarves, belts, and jewelry complete your look with that extra touch of flair. \r\n\r\nWith a focus on both fashion and functionality, our collection is designed to elevate your wardrobe and make you feel confident wherever you go. Whether you\'re dressing up for a special event or adding the perfect finishing touch to your everyday outfit, our bags and accessories are here to help you express your unique style. Shop now and discover the endless possibilities!', '12jan2024');

-- --------------------------------------------------------

--
-- Table structure for table `blog_comment`
--

CREATE TABLE `blog_comment` (
  `comment_id` int(11) NOT NULL,
  `blog_id` int(11) NOT NULL,
  `user_email` varchar(150) NOT NULL,
  `user_name` varchar(150) NOT NULL,
  `comment` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_comment`
--

INSERT INTO `blog_comment` (`comment_id`, `blog_id`, `user_email`, `user_name`, `comment`) VALUES
(15, 22, 'fangyong1002@gmail.com', 'fangyong', 'very good\r\n'),
(16, 16, 'lishengyao1068@gmail.com', 'Chin Foong sin ', 'XDDD');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(1, 'Women Bag'),
(2, 'Man Bag'),
(3, 'Accessories');

-- --------------------------------------------------------

--
-- Table structure for table `contact_us`
--

CREATE TABLE `contact_us` (
  `id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` tinyint(1) DEFAULT 0,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `Grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `final_amount` decimal(10,2) NOT NULL,
  `order_status` enum('Processing','Shipping','Complete') NOT NULL DEFAULT 'Processing',
  `shipping_address` varchar(255) NOT NULL,
  `shipping_method` varchar(50) NOT NULL DEFAULT 'Standard',
  `user_message` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `Grand_total`, `discount_amount`, `final_amount`, `order_status`, `shipping_address`, `shipping_method`, `user_message`, `updated_at`) VALUES
(75, 36, '2025-01-22 03:31:32', 16000.00, 800.00, 15200.00, 'Complete', '21 jalan teratai 22.Taman Johor Jaya, 81100, Johor Bahru, Johor', 'Standard', '', '2025-01-23 05:54:44'),
(76, 39, '2025-01-21 03:52:52', 3900.00, 0.00, 3900.00, 'Complete', '18 jalan Anggerik 55, Taman Johor Jaya, 81100, Johor Bahru, Johor', 'Standard', '', '2025-01-23 05:54:54'),
(77, 39, '2024-12-24 03:55:16', 2000.00, 0.00, 2000.00, 'Complete', '22 jalan angggerik 55,81100 Taman Johor Jaya, 81100, Johor Bahru, Johor', 'Standard', '', '2025-01-23 06:38:03'),
(78, 39, '2024-11-05 04:03:40', 3000.00, 0.00, 3000.00, 'Shipping', '18 jalan Anggerik 55, Taman Johor Jaya, 84110, Johor Bahru, Johor', 'Standard', '', '2025-01-23 05:55:23'),
(79, 39, '2024-12-17 04:06:06', 1000.00, 0.00, 1000.00, 'Shipping', '22 jalan angggerik 55,81100Taman Johor Jaya, 81100, Johor Bahru, Pahang', 'Standard', '', '2025-01-23 05:55:34'),
(80, 38, '2024-11-03 04:09:34', 7000.00, 0.00, 7000.00, 'Complete', '18 jalan Anggerik 55, Taman Johor Jaya, 81000, johor bahru, Johor', 'Standard', '', '2025-01-23 05:55:47'),
(81, 38, '2025-01-23 04:21:41', 17700.00, 885.00, 16815.00, 'Complete', '18 jalan Anggerik 55, Taman Johor Jaya, 81000, johor bahru, Johor', 'Standard', '', '2025-01-23 04:38:29'),
(82, 40, '2024-12-16 04:27:07', 27000.00, 0.00, 27000.00, 'Complete', '12,Jalan Rosmerah 99,81100 Taman Johor Jaya, 81100, Johor Bahru, Johor', 'Standard', '', '2025-01-23 06:38:20'),
(83, 40, '2025-01-02 04:29:35', 1000.00, 0.00, 1000.00, 'Processing', '12,Jalan Rosmerah 99,81100 Taman Johor Jaya, 81100, Johor Bahru, Johor', 'Standard', '', '2025-01-23 05:56:42'),
(84, 40, '2025-01-23 04:30:21', 7000.00, 0.00, 7000.00, 'Processing', '12,Jalan Rosmerah 99,81100 Taman Johor Jaya, 81100, Johor Bahru, Johor', 'Standard', '', '2025-01-23 04:30:21'),
(85, 41, '2024-12-30 04:34:04', 4500.00, 0.00, 4500.00, 'Processing', '23,Jalan Keembong 19, Taman Johor Jaya, 81100, Johor Bahru, Johor', 'Standard', '', '2025-01-23 05:56:11'),
(86, 41, '2025-01-23 04:34:52', 9000.00, 900.00, 8100.00, 'Processing', '23,Jalan Keembong 19, Taman Johor Jaya, 81100, Johor Bahru, Johor', 'Standard', '', '2025-01-23 04:34:52'),
(87, 36, '2025-01-23 14:31:49', 6100.00, 305.00, 5795.00, 'Processing', '21 jalan teratai 22.Taman Johor Jaya, 81100, Johor Bahru, Johor', 'Standard', '', '2025-01-23 14:31:49'),
(90, 36, '2025-01-23 14:53:38', 7000.00, 0.00, 7000.00, 'Processing', '21 jalan teratai 22.Taman Johor Jaya, 81100, Johor Bahru, Johor', 'Standard', '', '2025-01-23 14:53:38'),
(91, 36, '2025-01-23 14:55:43', 4000.00, 0.00, 4000.00, 'Complete', '21 jalan teratai 22.Taman Johor Jaya, 81100, Johor Bahru, Johor', 'Standard', '', '2025-01-23 15:06:38'),
(92, 36, '2025-01-23 14:56:28', 9000.00, 0.00, 9000.00, 'Shipping', '21 jalan teratai 22.Taman Johor Jaya, 81100, Johor Bahru, Johor', 'Standard', '', '2025-01-23 15:05:50'),
(93, 36, '2025-01-23 14:57:10', 9000.00, 0.00, 9000.00, 'Complete', '21 jalan teratai 22.Taman Johor Jaya, 81100, Johor Bahru, Johor', 'Standard', '', '2025-01-23 15:14:54'),
(94, 39, '2025-01-23 15:47:33', 9000.00, 0.00, 9000.00, 'Complete', '18 jalan Anggerik 55, Taman Johor Jaya, 81100, Johor Bahru, Johor', 'Standard', '', '2025-01-23 15:51:10');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `detail_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`detail_id`, `order_id`, `variant_id`, `quantity`, `unit_price`, `total_price`) VALUES
(83, 75, 13, 2, 1000.00, 2000.00),
(84, 75, 19, 2, 7000.00, 14000.00),
(85, 76, 7, 1, 3000.00, 3000.00),
(86, 76, 10, 1, 900.00, 900.00),
(87, 77, 32, 2, 1000.00, 2000.00),
(88, 78, 70, 1, 3000.00, 3000.00),
(89, 79, 32, 1, 1000.00, 1000.00),
(90, 80, 18, 1, 7000.00, 7000.00),
(91, 81, 8, 3, 5000.00, 15000.00),
(92, 81, 10, 3, 900.00, 2700.00),
(93, 82, 19, 2, 7000.00, 14000.00),
(94, 82, 28, 1, 12000.00, 12000.00),
(95, 82, 53, 1, 1000.00, 1000.00),
(96, 83, 13, 1, 1000.00, 1000.00),
(97, 84, 18, 1, 7000.00, 7000.00),
(98, 85, 36, 1, 4000.00, 4000.00),
(99, 85, 61, 1, 500.00, 500.00),
(100, 86, 70, 3, 3000.00, 9000.00),
(101, 87, 2, 2, 300.00, 600.00),
(102, 87, 6, 1, 500.00, 500.00),
(103, 87, 8, 1, 5000.00, 5000.00),
(106, 90, 18, 1, 7000.00, 7000.00),
(107, 91, 36, 1, 4000.00, 4000.00),
(108, 92, 21, 1, 9000.00, 9000.00),
(109, 93, 21, 1, 9000.00, 9000.00),
(110, 94, 20, 1, 9000.00, 9000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `payment_date` datetime NOT NULL DEFAULT current_timestamp(),
  `payment_status` enum('Pending','Completed','Failed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `user_id`, `order_id`, `payment_amount`, `payment_date`, `payment_status`) VALUES
(73, 36, 75, 15200.00, '2025-01-23 03:31:32', 'Completed'),
(74, 39, 76, 3900.00, '2025-01-23 03:52:52', 'Completed'),
(75, 39, 77, 2000.00, '2025-01-23 03:55:16', 'Completed'),
(76, 39, 78, 3000.00, '2025-01-23 04:03:40', 'Completed'),
(77, 39, 79, 1000.00, '2025-01-23 04:06:06', 'Completed'),
(78, 38, 80, 7000.00, '2025-01-23 04:09:34', 'Completed'),
(79, 38, 81, 16815.00, '2025-01-23 04:21:41', 'Completed'),
(80, 40, 82, 27000.00, '2025-01-23 04:27:07', 'Completed'),
(81, 40, 83, 1000.00, '2025-01-23 04:29:35', 'Completed'),
(82, 40, 84, 7000.00, '2025-01-23 04:30:21', 'Completed'),
(83, 41, 85, 4500.00, '2025-01-23 04:34:04', 'Completed'),
(84, 41, 86, 8100.00, '2025-01-23 04:34:52', 'Completed'),
(85, 36, 87, 5795.00, '2025-01-23 14:31:49', 'Completed'),
(88, 36, 90, 7000.00, '2025-01-23 14:53:38', 'Completed'),
(89, 36, 91, 4000.00, '2025-01-23 14:55:43', 'Completed'),
(90, 36, 92, 9000.00, '2025-01-23 14:56:28', 'Completed'),
(91, 36, 93, 9000.00, '2025-01-23 14:57:10', 'Completed'),
(92, 39, 94, 9000.00, '2025-01-23 15:47:34', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_status` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_des` varchar(800) NOT NULL,
  `product_image` text NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `tags` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `category_id`, `product_status`, `product_name`, `product_des`, `product_image`, `product_price`, `tags`) VALUES
(1, 1, 1, 'Capucines Mini', 'Part of the Trilogy line, this Capucines Mini is the epitome of sophistication with its triple chain adornment, one of which is strung with delicate mother-of-pearl effect. It is crafted from timeless Taurillon leather and opens to reveal a compartmented interior with a flat pocket for cards. The perfect evening style, it can be worn cross-body or carried by hand.', 'CapucinesMini.avif', 1000.00, 'Fashion'),
(2, 1, 2, 'Capucines WW', 'Part of the Trilogy line, this Capucines WW is the epitome of sophistication with its triple chain adornment. Look closely to notice the jewel-like chain details, including signature mini padlocks and enameled Monogram flowers. This double-carry style is crafted from iconic Taurillon leather and is accented with gold-toned hardware. It is fitted with multiple interior pockets and can be worn several ways.', 'CapucinesWW.avif', 5000.00, 'Crafts'),
(3, 1, 1, 'Capucines BB', 'Fashioned from full-grain Taurillon leather, the now-classic Capucines BB handbag displays a host of House signatures: leather-wrapped LV Initials, jewel-like handle mounts inspired by historic trunks, and a distinctive flap with a Monogram Flower motif. Timelessly elegant, this charming model can be carried by hand or worn cross-body on its detachable strap. First launched in 2013, the Capucines is named for the Parisian street where Louis Vuitton first opened shop, in 1854.', 'CapucinesBB.avif', 300.00, 'Lifestyle'),
(4, 1, 1, 'Speedy P9 Bandoulière', 'The Speedy P9 Bandoulière 40 bag is made from extra-soft calfskin in a colorway inspired by precious stones. This less structured version of the iconic Speedy is body-friendly and very comfortable to carry. As part of Pharrell Williams’ cowboy-influenced collection for Fall-Winter 2024, the handle mounts feature embossed horses.\r\n\r\n', 'SpeedyP9.avif', 1000.00, 'Fashion'),
(5, 2, 1, 'Speedy P9 Bandoulière 40', 'The Speedy P9 Bandoulière 40 bag is made from extra-soft calfskin in a colorway inspired by precious stones. This less-structured version of the iconic Speedy is body-friendly and very comfortable to carry. As part of Pharrell Williams’ cowboy-influenced collection for Fall-Winter 2024, the handle mounts feature embossed horses.', 'SpeedyP940.avif', 1000.00, 'Lifestyle'),
(6, 1, 1, 'Capucines M11341', 'Part of the new permanent Flower Crown line, this sophisticated Capucines Mini design is adorned with a crown of mother-of-pearl effect Monogram flowers, finished with elegant gold-toned hardware. The refined design opens to reveal a compartmented interior fitted with multiple pockets, and features a removable strap, offering numerous carry options.', 'CapucinesMM.avif', 1000.00, 'Fashion'),
(7, 2, 1, 'Speedy P9 Bandoulière 50', 'The super-supple Speedy P9 Bandoulière 50 bag is fashioned from calfskin with natural cowhide trim and lambskin lining. The bag’s vibrant colorway and slouchy structure brings a relaxed sophistication to any look. In addition to the two rolled-leather top handles, the Speedy features an adjustable and detachable strap for comfortable shoulder wear.', 'SpeedyP950.avif', 1000.00, 'Lifestyle'),
(8, 1, 1, 'LV x TM Capucines Mini', 'Crafted in playful proportions, the Capucines Mini makes a modern statement in Taurillon leather in a fresh shade of green inspired by the rainbow palette of Japanese artist Takashi Murakami. The LV Initials, lining, keybell and strap are all accented with a joyful multicolored version of the House’s Monogram motif, while gleaming golden hardware, including a removable chain, completes the look with a polished touch. ', 'LVxTM.avif', 8000.00, 'Fashion'),
(9, 1, 1, 'Capucines East-West Mini', 'The Capucines East-West Mini is playfully reimagined as part of the re-edition of the Louis Vuitton x Murakami collection to make an elegant statement. Printed and emobossed on cowhide leather, the House’s iconic Monogram Flowers are vibrantly reimagined in the Japanese artist’s vernacular superflat style, while a fresh blue shade adds a pop of color to the iconic LV Initials and removable braided strap. ', 'EastWest.avif', 8500.00, 'Craft'),
(10, 1, 1, 'GO-14 PM', 'Inspired by the enchanted Cruise 2025 show venue in Barcelona, this collector’s edition of the iconic Go-14 PM is a tribute to Creative Director Nicolas Ghesquière’s signature shape. It is crafted from tweed, designed to recreate the runway look and to match perfectly with the Ready-To-Wear fabric. The interior is fitted with a flat pocket, plus a mirror for cosmetic touch-ups on the go.', 'GO-14.avif', 7000.00, 'Lifestyle'),
(11, 1, 1, 'GO-14 MM', 'As part of the Nautical Collection, this GO-14 MM in padded lambskin is adorned with an embroidered-cotton rope motif, inspired by sailors\'s knots. The removable and adjustable sliding chain and LV Twist lock, both in gold-color metal, bring a luxurious feel. In addition to the chain, there is a removable top handle.', 'GOMM.avif', 9000.00, 'Streetstyle'),
(12, 1, 1, 'Speedy Bandoulière 20', 'This edition of the iconic Speedy Bandoulière 20 features the House’s Monogram Empreinte leather in an elegant color palette, inspired by the LV Milky Way line. It is designed with silver-toned hardware, including a signature padlock and keybell. The compact shape holds enough space to store a smartphone, wallet and keys, plus a favorite lipstick.', 'Speedy BandouliereG.avif', 3000.00, 'Lifestyle'),
(13, 1, 1, 'LV x TM Neverfull MM', 'As part of the re-edition of the Louis Vuitton x Murakami collection, the House’s iconic Neverfull MM handbag is joyfully reimagined with a colorful print that combines the artist’s vernacular superflat style with Monogram Flower motifs. Crafted from Monogram Empreinte leather with elegant gold-toned hardware, this timeless tote is detailed with sleek top handles and emblematic side laces to adjust the volume of the body. Complete with a practical removable pouch. ', 'Neverfull.avif', 4000.00, 'Streetstyle'),
(14, 1, 1, 'OnTheGo PM', 'A soft cream-tone makes the Monogram Empreinte leather of this OnTheGo PM bag seem luminous. The LVs and Monogram Flowers embossed into the leather are perfectly complemented by the rectangular lines of this small tote. It can hold smaller essentials like a mini tablet. Two rolled-leather top handles and a detachable strap give carry options.', 'OntheGo.avif', 4000.00, 'Lifestyle'),
(15, 2, 1, 'Speedy P9 Bandoulière 25', 'The Speedy P9 Bandoulière 25 is made from calf leather made from acid colours, with a sunbleach effect. An ideal everyday bag, the Speedy P9 comes with a removable leather pouch inside and is lined in luxurious lambskin. The two top handles and removable and adjustable shoulder strap give carry options.', 'Speedy25.avif', 12000.00, 'Craft'),
(16, 2, 1, 'Trio Messenger', 'Fashioned from Monogram Shadow embossed calfskin leather, the Trio Messenger bag combines three accessories in one. It’s composed of a detachable coin purse, a main bag, and a front zipped pocket that can be removed and used on its own. The olive-green colorway with the white Monogram pattern brings an outdoorsy feel.', 'Messenger.avif', 3000.00, 'Streetstyle'),
(17, 2, 1, 'Discovery Backpack', 'Made from calf leather with an embossed Monogram pattern, the Discovery Backpack combines style, comfort, and functionality. Its supple, body-friendly shape features three outside pockets for easy access to essentials and numerous inside pockets to keep things organized. The black-color hardware completes the understated design.', 'Discovery.avif', 4000.00, 'Fashion'),
(18, 2, 1, 'Duo Slingbag', 'The Duo Slingbag is made from Monogram Shadow leather, a supple calfskin leather with a print on top of the embossed Monogram signature. A modern alternative, the Duo comes with a removable pouch, perfect for keys, coins, or other small essentials. The adjustable shoulder strap can be worn on the right or left side.', 'Duo.avif', 3000.00, 'Lifestyle'),
(19, 2, 1, 'Keepall Bandoulière 50', 'The elegant two-tone colorway of the Monogram Shadow embossed calfskin from which this Keepall Bandoulière 50 is constructed brings a sporty vibe to this emblematic travel bag. Small enough to be used as carry-on luggage, it features a zipped pocket inside its roomy interior. Rolled-leather top handles and a signed removable strap give carry options.', 'Keepall.avif', 5000.00, 'Craft'),
(20, 2, 1, 'D-LV Backpack', 'The cool styling of the D-LV Backpack takes on a fashion-forward look as part of the Taigarama line. Finely embossed Taiga cowhide is paired with the House’s signature Monogram canvas in a lustrous shade of white. Ideal for short trips or to carry everyday essentials, it offers multiple compartments, including a special tablet computer pocket.', 'D-LVW.avif', 4000.00, 'Fashion'),
(21, 2, 1, 'Noé Cargo', 'The Noé Cargo is a small bucket-style bag made from glossy black calfskin, with the technical brilliance of nylon, and lined in black textile. The silver-toned hardware, including the LV Initials connected to a ball chain, are the only elements to break the monochromatic aspect of the bag. The Noé features a roomy front pocket.', 'Cargo.avif', 4000.00, 'Lifestyle'),
(22, 2, 1, 'Montsouris Cargo Backpack', 'Shiny and supple, the Montsouris Cargo Backpack is made from calf leather in the deepest black and lined in black textile. The glossy calfskin, imbued with a sheen close to that of nylon, is stunning no matter what the light. It features two zipped pockets on the front, both of which close with a pearl zip-pull.', 'Montsouris.avif', 5000.00, 'Lifestyle'),
(23, 3, 1, 'Vuitton Chain Belt', 'The Vuitton Chain belt is a double chain, made of pearls, beads, and in this stylish iteration, metallic letters that spell out VUITTON. These varied elements make up one of the chains, and the other is a classic style with links. The stylish accessory livens up a pair of pants when affixed to a belt loop.', 'Vuitton.avif', 1000.00, 'Streetstyle'),
(24, 3, 1, 'LV Pearls Bracelet', 'The LV Pearls Bracelet is a chic rendition of a classic style, now done in a subdued palette. Grey and metallic pearls are linked with charms of Louis Vuitton signatures, engraved with the Damier pattern. This piece of jewelry is regal yet suitable for all occasions.', 'Bracelet.avif', 900.00, 'Fashion'),
(25, 3, 1, 'LV Pearls Necklace', 'The LV Pearls necklace is a moody piece of jewelry that captures attention with its rich materials. Featuring a chain of pearls and House signature charms, the individual pieces are engraved with the Damier pattern. This is a style for the true Louis Vuitton lover, who appreciates the unexpected details.', 'Necklace.avif', 1200.00, 'Lifestyle'),
(26, 3, 1, 'LV Flight Square Sunglasses', 'From Spring-Summer 2025 comes the LV Flight Square sunglasses, a sophisticated silhouette with a modern edge that continues the show\'s homage to velocity and exploration. This model, featuring a geometric bar layered on the top the brow line, and equipped with large metal hinges noticeably engraved with a substantial LV signature.', 'Sunglasses.avif', 1000.00, 'Fashion'),
(27, 3, 1, 'Damier Staples Bangle', 'Designed by creative director Pharrell Williams, the Damier Staples pattern combines the historical symbols of the Maison with the long-revered Damier Héritage motif. An everyday piece of jewelry, the style is engraved in this take-notice motif plus the Monogram print. Please refer to the included instructions on how to open and securely wear the bangle.', 'Bangle.avif', 700.00, 'Craft'),
(28, 3, 1, 'LV Backstage Hair Clips', 'From the exclusive Louis Vuitton x Murakami collection, a creative collaboration uniting Louis Vuitton and Takashi Murakami, come these LV x TM Monogram Multicolor hair clips. The acclaimed Japanese artist imagined this vibrant interpretation of the House pattern, using a palette of 33 vivid hues printed on a sophisticated white background. Designed to add a joyful signature touch to any coiffure, the clips can be worn singly or as a set.', 'Clips.avif', 600.00, 'Lifestyle'),
(29, 3, 1, 'LV Iconic Earrings', 'The finely crafted LV Iconic earrings offer a sophisticated new way to wear one of the House’s most recognizable signatures. The famous LV Initials are attached to hoops of lustrous gold-tone metal delicately encrusted with sparkling crystals. Light and comfortable, these resplendent earrings add a glint of radiant elegance to any look.', 'Earrings.avif', 700.00, 'Craft'),
(30, 3, 1, 'Superflat Necklace', 'Designed to create a fashionable layered effect, the LV x TM Superflat necklace is presented in the new LV x TM collection, the Maison’s exciting collaboration with Takashi Murakami. The noted Japanese artist revisits iconic Louis Vuitton’s signatures in his inimitable Superflat style: note the whimsical LV Hands emblem, executed in meticulously detailed colored enamel. Murakami’s playful Panda is featured too, accompanied by a dainty cluster of gold-tone Monogram charms.', 'Superflat.avif', 1300.00, 'Fashion'),
(31, 3, 1, 'LV Hit Sunglasses', 'The LV Hit sunglasses is the statement model that will add freshness to your look thanks to its classic pilot shape and orange lenses. The super light weight will make the pair the perfect daily companion. The model subtly celebrates the House with the LV Signature in gold color on the temples, and Louis Vuitton inscribed on the right lens.', 'Hit.avif', 500.00, 'Lifestyle'),
(32, 3, 1, 'Damier Heritage Scarf', 'The wool Damier Heritage Scarf offers warmth and luxury as well as timeless elegance infused with a modern touch. The iconic Damier pattern remains a signature, and is now elevated by Creative Director Pharrell Williams\'s embroidered interpretation of \"Marque L.Vuitton Déposée\" signature. At once classic and current, this accessory beautifully represents the House\'s savoir-faire.', 'Scarf.avif', 600.00, 'Lifestyle');

-- --------------------------------------------------------

--
-- Table structure for table `product_status`
--

CREATE TABLE `product_status` (
  `p_status_id` int(11) NOT NULL,
  `product_status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_status`
--

INSERT INTO `product_status` (`p_status_id`, `product_status`) VALUES
(1, 'Available'),
(2, 'Unavailable');

-- --------------------------------------------------------

--
-- Table structure for table `product_variant`
--

CREATE TABLE `product_variant` (
  `variant_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `promotion_id` int(11) DEFAULT NULL,
  `color` varchar(20) NOT NULL,
  `size` varchar(20) NOT NULL,
  `stock` int(11) NOT NULL,
  `Quick_View1` varchar(255) NOT NULL,
  `Quick_View2` varchar(255) NOT NULL,
  `Quick_View3` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variant`
--

INSERT INTO `product_variant` (`variant_id`, `product_id`, `promotion_id`, `color`, `size`, `stock`, `Quick_View1`, `Quick_View2`, `Quick_View3`) VALUES
(1, 1, NULL, 'Black', 'MM', 0, 'CapucinesMini.avif', 'CapucinesMini2.avif', 'CapucinesMini3.avif'),
(2, 3, NULL, 'Brown', 'MM', 0, 'CapucinesBB.avif', 'CapucinesBB2.avif', 'CapucinesBB3.avif'),
(3, 3, NULL, 'White', 'MM', 8, 'CapucinesBBW.avif', 'CapucinesBBW2.avif', 'CapucinesBBW3.avif'),
(4, NULL, 1, 'Purple', 'MM', 10, 'SpeedyP940.avif', 'SpeedyP9402.avif', 'SpeedyP9403.avif'),
(5, NULL, 2, 'Brown', 'MM', 10, 'CapucinesMM.avif', 'CapucinesMM2.avif', 'CapucinesMM3.avif'),
(6, NULL, 3, 'Brown', '19', 9, 'Monogram.avif', 'Monogram2.avif', 'Monogram3.avif'),
(7, NULL, 4, 'Grey', 'PM', 8, 'NeonoeBB.avif', 'NeonoeBB2.avif', 'NeonoeBB3.avif'),
(8, NULL, 2, 'Black', 'MM', 4, 'CapucinesMMB.avif', 'CapucinesMMB2.avif', 'CapucinesMMB3.avif'),
(10, NULL, 6, 'Black', 'M', 6, 'Vers.avif', 'Vers2.avif', 'Vers3.avif'),
(12, 2, NULL, 'White', 'PM', 10, 'CapucinesWW.avif', 'CapucinesWW2.avif', 'CapucinesWW3.avif'),
(13, 7, NULL, 'Lightblue', 'PM', 7, 'SpeedyP950.avif', 'SpeedyP9502.avif', 'SpeedyP9503.avif'),
(14, 8, NULL, 'White', 'MM', 10, 'LVxTM.avif', 'LVxTM2.avif', 'LVxTM3.avif'),
(15, 8, NULL, 'Lightgreen', 'MM', 10, 'LVxTMG.avif', 'LVxTMG2.avif', 'LVxTMG3.avif'),
(16, 9, NULL, 'Lightblue', 'MM', 9, 'EastWest.avif', 'EastWest2.avif', 'EastWest3.avif'),
(17, 9, NULL, 'White', 'MM', 10, 'EastWestW.avif', 'EastWestW2.avif', 'EastWestW3.avif'),
(18, 10, NULL, 'Black', 'PM', 7, 'GO-14.avif', 'GO-142.avif', 'GO-143.avif'),
(19, 10, NULL, 'Brown', 'PM', 6, 'GO-14B.avif', 'GO-14B2.avif', 'GO-14B3.avif'),
(20, 11, NULL, 'Wheat', 'MM', 9, 'GOMM.avif', 'GOMM2.avif', 'GOMM3.avif'),
(21, 11, NULL, 'Darkblue', 'MM', 7, 'GOMMB.avif', 'GOMMB2.avif', 'GOMMB3.avif'),
(22, 12, NULL, 'Grey', 'PM', 10, 'Speedy BandouliereG.avif', 'Speedy BandouliereG2.avif', 'Speedy BandouliereG3.avif'),
(23, 12, NULL, 'Black', 'PM', 10, 'Speedy Bandouliere.avif', 'Speedy Bandouliere2.avif', 'Speedy Bandouliere3.avif'),
(24, 13, NULL, 'Brown', 'MM', 10, 'Neverfull.avif', 'Neverfull2.avif', 'Neverfull3.avif'),
(25, 13, NULL, 'Black', 'MM', 10, 'NeverfullB.avif', 'NeverfullB2.avif', 'NeverfullB3.avif'),
(26, 14, NULL, 'Brown', 'PM', 10, 'OntheGo.avif', 'OntheGo2.avif', 'OntheGo3.avif'),
(27, 14, NULL, 'Wheat', 'PM', 10, 'OntheGoWh.avif', 'OntheGoWh2.avif', 'OntheGoWh3.avif'),
(28, 15, NULL, 'Yellow', 'MM', 9, 'Speedy25.avif', 'Speedy252.avif', 'Speedy253.avif'),
(30, NULL, 7, 'Black', 'MM', 10, 'Speedy25B.avif', 'Speedy25B2.avif', 'Speedy25B3.avif'),
(31, NULL, 7, 'Purple', 'MM', 10, 'SpeedyP940.avif', 'SpeedyP9402.avif', 'SpeedyP9403.avif'),
(32, 7, NULL, 'Pink', 'MM', 7, 'SpeedyP9.avif', 'SpeedyP92.avif', 'SpeedyP93.avif'),
(33, 16, NULL, 'Black', 'PM', 10, 'Messenger.avif', 'Messenger2.avif', 'Messenger3.avif'),
(34, 16, NULL, 'Green', 'PM', 10, 'MessengerG.avif', 'MessengerG2.avif', 'MessengerG3.avif'),
(35, 17, NULL, 'Black', 'MM', 10, 'Discovery.avif', 'Discovery2.avif', 'Discovery3.avif'),
(36, 17, NULL, 'Darkblue', 'MM', 8, 'DiscoveryB.avif', 'DiscoveryB2.avif', 'DiscoveryB3.avif'),
(37, 18, NULL, 'Black', 'PM', 10, 'Duo.avif', 'Duo2.avif', 'Duo3.avif'),
(38, 18, NULL, 'Darkblue', 'PM', 10, 'DuoB.avif', 'DuoB2.avif', 'DuoB3.avif'),
(39, 19, NULL, 'Brown', 'MM', 10, 'Keepall.avif', 'Keepall2.avif', 'Keepall3.avif'),
(40, 19, NULL, 'Green', 'MM', 10, 'KeepallG.avif', 'KeepallG2.avif', 'KeepallG3.avif'),
(41, 20, NULL, 'White', 'MM', 10, 'D-LVW.avif', 'D-LVW2.avif', 'D-LVW3.avif'),
(42, 20, NULL, 'Black', 'MM', 10, 'D-LV.avif', 'D-LV2.avif', 'D-LV3.avif'),
(43, 21, NULL, 'Brown', 'PM', 10, 'Cargo.avif', 'Cargo2.avif', 'Cargo3.avif'),
(44, 21, NULL, 'Black', 'PM', 10, 'CargoB.avif', 'CargoB2.avif', 'CargoB3.avif'),
(45, 22, NULL, 'Brown', 'PM', 10, 'Montsouris.avif', 'Montsouris2.png', 'Montsouris3.avif'),
(46, 22, NULL, 'Black', 'PM', 10, 'MontsourisB.avif', 'MontsourisB2.avif', 'MontsourisB3.avif'),
(47, 23, NULL, 'Grey', 'M', 10, 'Vuitton.avif', 'Vuitton2.avif', 'Vuitton3.avif'),
(48, 23, NULL, 'Yellow', 'M', 10, 'VuittonY.avif', 'VuittonY2.avif', 'VuittonY3.avif'),
(49, 24, NULL, 'Black', 'S', 10, 'Bracelet.avif', 'Bracelet2.avif', 'Bracelet3.avif'),
(50, 24, NULL, 'Grey', 'S', 10, 'BraceletG.avif', 'BraceletG2.avif', 'BraceletG3.avif'),
(51, 25, NULL, 'Black', 'S', 10, 'Necklace.avif', 'Necklace2.avif', 'Necklace3.avif'),
(52, 25, NULL, 'Grey', 'S', 10, 'NecklaceG.avif', 'NecklaceG2.avif', 'NecklaceG3.avif'),
(53, 26, NULL, 'Black', 'M', 9, 'Sunglasses.avif', 'Sunglasses2.avif', 'Sunglasses3.avif'),
(54, 26, NULL, 'Wheat', 'M', 10, 'SunglassesWh.avif', 'SunglassesWh2.avif', 'SunglassesWh3.avif'),
(55, 27, NULL, 'White', 'M', 10, 'Bangle.avif', 'Bangle2.avif', 'Bangle3.avif'),
(56, 28, NULL, 'Black', 'S', 10, 'Clips.avif', 'Clips2.avif', 'Clips3.avif'),
(57, 28, NULL, 'White', 'S', 10, 'ClipsW.avif', 'ClipsW2.avif', 'ClipsW3.avif'),
(58, 29, NULL, 'Pink', 'S', 700, 'Earrings.avif', 'Earrings2.avif', 'Earrings3.avif'),
(59, 29, NULL, 'Yellow', 'S', 10, 'EarringsY.avif', 'EarringsY2.avif', 'EarringsY3.avif'),
(60, 30, NULL, 'Yellow', 'M', 10, 'Superflat.avif', 'Superflat2.avif', 'Superflat3.avif'),
(61, 31, NULL, 'Black', 'M', 9, 'Hit.avif', 'Hit2.avif', 'Hit3.avif'),
(62, 32, NULL, 'Black', 'M', 10, 'Scarf.avif', 'Scarf2.avif', 'Scarf3.avif'),
(63, 32, NULL, 'Grey', 'M', 10, 'ScarfG.avif', 'ScarfG2.avif', 'ScarfG3.avif'),
(64, NULL, 8, 'Black', 'MM', 10, 'Tote.avif', 'Tote2.avif', 'Tote3.avif'),
(65, NULL, 8, 'Green', 'MM', 10, 'ToteG.avif', 'ToteG2.avif', 'ToteG3.avif'),
(66, NULL, 9, 'Green', 'MM', 10, 'Shopper.avif', 'Shopper2.avif', 'Shopper3.avif'),
(67, NULL, 9, 'Yellow', 'MM', 10, 'ShopperY.avif', 'ShopperY2.avif', 'ShopperY3.avif'),
(68, NULL, 10, 'Black', 'MM', 10, 'Christopher.avif', 'Christopher2.avif', 'Christopher3.avif'),
(69, NULL, 10, 'Grey', 'MM', 10, 'ChristopherG.avif', 'ChristopherG2.avif', 'ChristopherG3.avif'),
(70, NULL, 4, 'Wheat', 'MM', 6, 'NeonoeMM.avif', 'NeonoeMM2.avif', 'NeonoeMM3.avif'),
(71, NULL, 11, 'Brown', 'PM', 10, 'PorteBr.avif', 'PorteBr2.avif', 'PorteBr3.avif'),
(72, NULL, 11, 'Black', 'PM', 10, 'Porte.avif', 'Porte2.avif', 'Porte3.avif'),
(73, NULL, 12, 'Grey', 'S', 10, 'Alien.avif', 'Alien2.avif', 'Alien3.avif'),
(74, NULL, 13, 'Pink', 'S', 10, 'Carry.avif', 'Carry2.avif', 'Carry3.avif'),
(75, NULL, 13, 'Brown', 'S', 10, 'CarryBr.avif', 'CarryBr2.avif', 'CarryBr3.avif'),
(76, NULL, 14, 'Yellow', 'M', 10, 'Volt.avif', 'Volt2.avif', 'Volt3.avif'),
(77, NULL, 14, 'Grey', 'M', 0, 'VoltG.avif', 'VoltG2.avif', 'VoltG3.avif');

-- --------------------------------------------------------

--
-- Table structure for table `promotion_product`
--

CREATE TABLE `promotion_product` (
  `promotion_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `promotion_status` int(11) NOT NULL,
  `promotion_name` varchar(100) NOT NULL,
  `promotion_des` varchar(800) NOT NULL,
  `promotion_image` text NOT NULL,
  `promotion_price` decimal(10,2) NOT NULL,
  `tags` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotion_product`
--

INSERT INTO `promotion_product` (`promotion_id`, `category_id`, `promotion_status`, `promotion_name`, `promotion_des`, `promotion_image`, `promotion_price`, `tags`) VALUES
(1, 2, 1, 'Speedy P9 Bandoulière 40', 'The Speedy P9 Bandoulière 40 bag is made from extra-soft calfskin in a colorway inspired by precious stones. This less-structured version of the iconic Speedy is body-friendly and very comfortable to carry. As part of Pharrell Williams’ cowboy-influenced collection for Fall-Winter 2024, the handle mounts feature embossed horses.', 'SpeedyP940.avif', 5000.00, 'Lifestyle'),
(2, 1, 1, 'Capucines M114', 'Part of the new permanent Flower Crown line, this sophisticated Capucines Mini design is adorned with a crown of mother-of-pearl effect Monogram flowers, finished with elegant gold-toned hardware. The refined design opens to reveal a compartmented interior fitted with multiple pockets, and features a removable strap, offering numerous carry options.', 'CapucinesMM.avif', 5000.00, 'Fashion'),
(3, 3, 1, 'Monogram Double Spin Bracelet', 'The Monogram Double Spin bracelet adds a lively update to classic House signatures. Understated, silver-toned brass engraved with Monogram emblems and colorful braided canvas add a bright twist to the Louis Vuitton iconography. This blend of special details makes this a perfect piece of casual yet elevated jewelry for daily wear.', 'Monogram.avif', 500.00, 'Lifestyle'),
(4, 1, 1, 'Neon BB', 'aawdwadasssxczssscawd', 'NeonoeBB.avif', 3000.00, 'Fashion'),
(6, 3, 1, 'LV Vers Damoflage Cap', 'The LV Vers Damoflage cap interprets the sporty accessory with a bit of fashionable flair that yields both edgy and classic appeal. Pharrell Williams\'s pixelated Damier print is rendered, in a first, in black, and the LV Vers insignia is embellished with thoughtfully placed pearls. Comfort is priority, thanks to an adjustable back strap. Every cap features a different pattern of the Damoflage print making each piece unique.', 'Vers.avif', 900.00, 'Fashion'),
(7, 2, 1, 'Speedy P9 Bandoulière 30', 'Made from calfskin with a Monogram print, the Speedy P9 Bandoulière 30 bag is the perfect size: compact but roomy enough for daily essentials. The meticulous detailing, such as the heritage side lock, or the premium lambskin lining, attests to the exquisite craftsmanship of this bag. In addition to the top handles, there’s a detachable strap.', 'Speedy25B.avif', 12000.00, 'Craft'),
(8, 2, 1, 'Georges Tote MM', 'Named after the son of the House’s founder, the exclusive new Georges Tote combines sumptuous leather with superb craftsmanship. Richly grained Cuir Millésime calfskin is enhanced with an array of iconic details, from the S-Lock closure to the metal signature plate inside. Practical and sophisticated, it’s ideal for business use – perfect for men who appreciate rare, exceptional bags.', 'Tote.avif', 10000.00, 'Lifestyle'),
(9, 2, 1, 'Shopper Tote MM', 'Directly inspired by Louis Vuitton’s shopping bag, the spacious Shopper Tote MM is made from saffron-color cowhide leather. The smooth leather is incredibly soft to the touch and features the words “Louis Vuitton” and “Maison Fondée En 1854” on the side. In addition to the two top handles, there’s a removable strap for shoulder carry.', 'Shopper.avif', 5000.00, 'Craft'),
(10, 2, 1, 'Christopher MM', 'The Christopher MM backpack reinvents the rugged spirit of a hiking pack in masculine Monogram Eclipse canvas and Monogram Eclipse Reverse canvas with black leather trim. The grays and blacks are complemented by the silver highlights of the buckles and zips. Multiple pockets and a leather flap with a press-stud keep belongings safe.', 'Christopher.avif', 4000.00, 'Fashion'),
(11, 1, 1, 'Porte Document Voyage PM', 'Part of the Cuir Millesime collection, this travel document holder is crafted from a rare, ultra-luxurious material: leather from calves raised exclusively for Louis Vuitton at the Domaine des Massifs in central France. The hides are tanned in Italy using a special process that gives them a marvelously smooth natural tan finish. An exceptional accessory for connoisseurs of fine leather.', 'PorteBr.avif', 9000.00, 'Lifestyle'),
(12, 3, 1, 'LV Alien Bag Charm', 'A design from Spring-Summer 2025, the LV Alien Bag Charm is a joyful piece that honors the show\'s key inspiration of exploration. The extraterrestrial accessory is covered in an allover Monogram pattern and can also hold a pair of wireless earphones. This makes a delightful gift for anyone with a sense of humor.', 'Alien.avif', 500.00, 'Fashion'),
(13, 1, 1, 'CarryAll PM', 'The elegant codes of the LV Milky Way line inform this bicolor version of the iconic CarryAll PM. This edition is crafted from Monogram Empreinte leather, elevated with gold-toned hardware for a sophisticated finish. It comes with a removable name tag and offers a plethora of pockets inside to keep valuable secure. Carry it by hand or attach the shoulder strap.', 'Carry.avif', 2000.00, 'Craft'),
(14, 3, 1, 'LV Volt One Pendant', 'Part of the LV Volt One collection, a tribute to the energy and rhythm of the LV Initials, this dazzling pendant is crafted from 18-karat yellow gold, set with pavé diamonds and suspended from an adjustable chain. The large L holds a small V, within which is a brilliant diamond, whose setting perfectly respects the V shape. Pieces from this range can be mixed and matched at will.', 'Volt.avif', 5000.00, 'Fashion');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `detail_id` int(11) NOT NULL,
  `rating` int(5) NOT NULL,
  `comment` text NOT NULL,
  `image` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `staff_id` int(11) DEFAULT NULL,
  `admin_reply` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `admin_reply_updated_at` datetime DEFAULT NULL,
  `status_updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `detail_id`, `rating`, `comment`, `image`, `user_id`, `status`, `staff_id`, `admin_reply`, `created_at`, `admin_reply_updated_at`, `status_updated_at`) VALUES
(102, 91, 5, 'High Quality Product', 'uploads/reviews/67917407a549d_lv.jpg', 38, 'active', 15, 'ok', '2025-01-23 06:41:11', '2025-01-23 17:51:58', '2025-01-23 07:14:11'),
(103, 92, 5, 'I love it. It wear let me feel good.', NULL, 38, 'active', NULL, NULL, '2025-01-23 06:41:39', NULL, NULL),
(104, 85, 5, 'High Quality', NULL, 39, 'active', NULL, NULL, '2025-01-23 06:42:39', NULL, NULL),
(105, 86, 5, 'Good Product', NULL, 39, 'active', NULL, NULL, '2025-01-23 06:42:53', NULL, NULL),
(106, 87, 3, 'good', NULL, 39, 'active', NULL, NULL, '2025-01-23 06:43:21', NULL, NULL),
(108, 109, 5, 'good', NULL, 36, 'active', NULL, NULL, '2025-01-23 15:38:26', NULL, NULL),
(109, 110, 5, 'High Quality', 'uploads/reviews/6791f5674b1e6_images.jpg', 39, 'active', 15, 'Thank you', '2025-01-23 15:53:11', '2025-01-23 17:50:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shopping_cart`
--

CREATE TABLE `shopping_cart` (
  `cart_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `total_price` double NOT NULL,
  `final_total_price` decimal(10,2) NOT NULL,
  `voucher_applied` int(11) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_image` text NOT NULL,
  `user_password` varchar(150) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_contact_number` varchar(50) NOT NULL,
  `user_gender` varchar(50) NOT NULL,
  `user_date_of_birth` date NOT NULL,
  `user_join_time` varchar(150) NOT NULL,
  `user_status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `user_name`, `user_image`, `user_password`, `user_email`, `user_contact_number`, `user_gender`, `user_date_of_birth`, `user_join_time`, `user_status`) VALUES
(36, 'Yeow Li sheng', 'uploads/WhatsApp Image 2023-03-07 at 11.59.00 AM.jpeg', 'QWERTYUIOP1234567890!', 'lishengyao1068@gmail.com', '011-1125595', 'female', '2024-10-03', '2024-10-27 22:46:05', 1),
(37, 'JL Chong', '', 'Jlchong0516204777!', 'jlchong2004@gmail.com', '011-1150551', 'female', '2024-10-16', '2024-10-27 23:11:23', 1),
(38, 'Chin Foong sin ', 'uploads/Cheong Wei Kit 1221203087 Timetable.png', 'QWERTYUIOP1234567890!', 'cheongweikit12345@gmail.com', '011-11255922', 'female', '2025-01-02', '2025-01-08 12:59:17', 1),
(39, 'Bryan Chu', 'uploads/Screenshot 2024-01-16 210542.png', 'Bryan!12', 'bryanchu@gmail.com', '011-12345678', 'male', '1997-01-01', '2025-01-23 03:32:52', 1),
(40, 'Amanda', '', 'Amanda!1', 'amanda@gmail.com', '011-56789012', 'female', '2002-01-24', '2025-01-23 04:25:35', 1),
(41, 'Tan Lily', '', 'Tanlily!1', 'Tanlily@gmail.com', '011-56789144', 'female', '2009-07-16', '2025-01-23 04:31:58', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_address`
--

CREATE TABLE `user_address` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `city` varchar(50) NOT NULL,
  `postcode` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_address`
--

INSERT INTO `user_address` (`address_id`, `user_id`, `address`, `state`, `city`, `postcode`) VALUES
(10, 37, '11 Jalan Teratai 18', 'Johor ', 'Johor Bahru', '81100'),
(11, 38, '18 jalan Anggerik 55, Taman Johor Jaya', 'Johor', 'johor bahru', '81000'),
(12, 36, '21 jalan teratai 22.Taman Johor Jaya', 'Johor', 'Johor Bahru', '81100'),
(13, 39, '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `voucher`
--

CREATE TABLE `voucher` (
  `voucher_id` int(11) NOT NULL,
  `voucher_code` varchar(30) NOT NULL,
  `discount_rate` decimal(4,2) NOT NULL,
  `voucher_status` varchar(10) NOT NULL,
  `usage_limit` int(11) NOT NULL,
  `minimum_amount` decimal(10,2) NOT NULL,
  `voucher_des` varchar(100) NOT NULL,
  `voucher_pic` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voucher`
--

INSERT INTO `voucher` (`voucher_id`, `voucher_code`, `discount_rate`, `voucher_status`, `usage_limit`, `minimum_amount`, `voucher_des`, `voucher_pic`) VALUES
(1, 'NEWUSER01', 10.00, 'Active', 1, 0.00, '10% discount for all new user with no minimum spend!!!', 'New-user-discount.png'),
(2, 'DIS4FIVET', 5.00, 'Active', 3, 5000.00, '5% discount for all purchasement above $5000', '5-discount.png'),
(3, 'DIS4THREET', 3.00, 'Active', 2, 3000.00, '3% discount for all purchasement above $3000', '3-dis.jpg'),
(4, 'TIMESALES22', 15.00, 'Active', 1, 1000.00, 'Limited time sales!!!', 'LMtime.png'),
(5, 'AMZDIS10', 10.00, 'Active', 5, 2500.00, 'Amazing discount for all users!!!', 'AMZ.webp'),
(6, 'NSESON8', 8.00, 'Active', 5, 3000.00, 'New season sales!!!', 'SAL.png');

-- --------------------------------------------------------

--
-- Table structure for table `voucher_usage`
--

CREATE TABLE `voucher_usage` (
  `usage_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `usage_num` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`staff_id`);

--
-- Indexes for table `bank_card`
--
ALTER TABLE `bank_card`
  ADD PRIMARY KEY (`card_id`);

--
-- Indexes for table `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`blog_id`);

--
-- Indexes for table `blog_comment`
--
ALTER TABLE `blog_comment`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `idx_blog_id` (`blog_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `contact_us`
--
ALTER TABLE `contact_us`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `order_details_ibfk_2` (`variant_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_status`
--
ALTER TABLE `product_status`
  ADD PRIMARY KEY (`p_status_id`);

--
-- Indexes for table `product_variant`
--
ALTER TABLE `product_variant`
  ADD PRIMARY KEY (`variant_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_promotion_id` (`promotion_id`);

--
-- Indexes for table `promotion_product`
--
ALTER TABLE `promotion_product`
  ADD PRIMARY KEY (`promotion_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_review` (`detail_id`,`user_id`),
  ADD KEY `detail_id` (`detail_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `test` (`staff_id`);

--
-- Indexes for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `shopping_cart_ibfk_2` (`variant_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_address`
--
ALTER TABLE `user_address`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`voucher_id`);

--
-- Indexes for table `voucher_usage`
--
ALTER TABLE `voucher_usage`
  ADD PRIMARY KEY (`usage_id`),
  ADD KEY `idx_user_voucher` (`user_id`,`voucher_id`),
  ADD KEY `voucher_id` (`voucher_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `bank_card`
--
ALTER TABLE `bank_card`
  MODIFY `card_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `blog`
--
ALTER TABLE `blog`
  MODIFY `blog_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `blog_comment`
--
ALTER TABLE `blog_comment`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_us`
--
ALTER TABLE `contact_us`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `product_status`
--
ALTER TABLE `product_status`
  MODIFY `p_status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_variant`
--
ALTER TABLE `product_variant`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `promotion_product`
--
ALTER TABLE `promotion_product`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=301;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `voucher`
--
ALTER TABLE `voucher`
  MODIFY `voucher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `voucher_usage`
--
ALTER TABLE `voucher_usage`
  MODIFY `usage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `product_variant` (`variant_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `product_variant`
--
ALTER TABLE `product_variant`
  ADD CONSTRAINT `product_variant_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`detail_id`) REFERENCES `order_details` (`detail_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `test` FOREIGN KEY (`staff_id`) REFERENCES `admin` (`staff_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD CONSTRAINT `shopping_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `shopping_cart_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `product_variant` (`variant_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_address`
--
ALTER TABLE `user_address`
  ADD CONSTRAINT `user_address_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `voucher_usage`
--
ALTER TABLE `voucher_usage`
  ADD CONSTRAINT `voucher_usage_ibfk_1` FOREIGN KEY (`voucher_id`) REFERENCES `voucher` (`voucher_id`),
  ADD CONSTRAINT `voucher_usage_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
