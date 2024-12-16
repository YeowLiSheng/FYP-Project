-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 16, 2024 at 11:51 AM
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
  `admin_joined_date` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`staff_id`, `admin_id`, `admin_name`, `admin_image`, `admin_password`, `admin_email`, `admin_contact_number`, `admin_gender`, `admin_joined_date`) VALUES
(15, 'superadmin', 'Yeow li sheng', 'uploads/1731986093_Screenshot 2024-05-19 155108.png', 'superadmin', 'lishengyao1068@gmail.com', '016-7168518', 'male', '2024-11-17 12:50:53'),
(16, 'admin1', 'lisheng', 'uploads/1732109113_Screenshot 2024-05-19 155108.png', 'lisheng123', 'lisheng1068@gmail.com', '016-7168518', 'male', '2024-11-17 12:51:50');

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
(15, 22, 'fangyong1002@gmail.com', 'fangyong', 'very good\r\n');

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
(2, 'Man Bag');

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

--
-- Dumping data for table `contact_us`
--

INSERT INTO `contact_us` (`id`, `user_email`, `message`, `status`, `user_id`) VALUES
(7, 'lishengyao1068@gmail.com', 'vvv', 1, 36),
(17, 'lishengyao1068@gmail.com', 'haha\r\n', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `faq`
--

CREATE TABLE `faq` (
  `faq_id` int(11) NOT NULL,
  `faq_question` text NOT NULL,
  `faq_answer` text NOT NULL,
  `faq_type` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faq`
--

INSERT INTO `faq` (`faq_id`, `faq_question`, `faq_answer`, `faq_type`) VALUES
(1, 'What products do you offer?', 'At YLS, we specialize in luxury bags and accessories, including:\r\n\r\nHandbags (Tote bags, Shoulder bags, Clutches)\r\nWallets and Purses\r\nBelts\r\nScarves\r\nJewelry (Bracelets, Earrings, Necklaces)', 'Order_shipping'),
(2, 'Are your products authentic?', 'Yes, all our products are 100% authentic and sourced from reputable suppliers. We guarantee the quality and authenticity of every item we sell.', 'Order_queries'),
(3, 'What payment do we accept?', 'We only accept credit/debit card for payment. E-wallet is not available.', 'Payment'),
(4, 'Why my order status show delivered but i didn\'t received anything?', 'If the order status show delivered but you didn\'t received the product, please wait for another 6 hours for the courier to delivery the product to you. If still didn\'t receive the product after 6 hours, please click on contact us to let us know.', 'Order_problem'),
(5, 'What are the products we offer?', 'We only sell for Women\'s bag, Men\'s bag and Accessories.', 'Product_info');

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
  `delivery_charge` decimal(10,2) DEFAULT 0.00,
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

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `Grand_total`, `discount_amount`, `delivery_charge`, `final_amount`, `order_status`, `shipping_address`, `shipping_method`, `user_message`, `updated_at`) VALUES
(30, 36, '2024-11-16 18:33:29', 6000.00, 600.00, 10.00, 5410.00, 'Complete', 'yongpeng, 83711, yongpeng, JOHOR', 'Standard', 'black', '2024-12-06 16:18:45'),
(31, 36, '2024-11-26 15:42:15', 6000.00, 0.00, 10.00, 6010.00, 'Complete', 'yongpeng, 83711, yongpeng, JOHOR', 'Standard', '', '2024-12-06 15:45:50'),
(32, 36, '2024-11-26 15:51:53', 1000.00, 0.00, 10.00, 1010.00, 'Complete', 'yongpeng, 83711, yongpeng, JOHOR', 'Standard', '', '2024-12-06 15:53:37'),
(33, 36, '2024-12-04 14:28:33', 5000.00, 250.00, 10.00, 4760.00, 'Complete', 'yongpeng, 83711, yongpeng, JOHOR', 'Standard', 'avds', '2024-12-13 02:54:40'),
(34, 36, '2024-12-07 21:36:40', 11000.00, 500.00, 10.00, 10510.00, 'Processing', 'yongpeng, 83711, yongpeng, JOHOR', 'Standard', '', '2024-12-07 21:36:40');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `detail_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`detail_id`, `order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `total_price`) VALUES
(37, 30, 1, 'Capucines Mini', 1, 1000.00, 1000.00),
(38, 30, 2, 'Capucines WW', 1, 5000.00, 5000.00),
(39, 31, 1, 'Capucines Mini', 1, 1000.00, 1000.00),
(40, 31, 2, 'Capucines WW', 1, 5000.00, 5000.00),
(41, 32, 1, 'Capucines Mini', 1, 1000.00, 1000.00),
(42, 33, 2, 'Capucines WW', 1, 5000.00, 5000.00),
(43, 34, 1, 'Capucines Mini', 1, 1000.00, 1000.00),
(44, 34, 2, 'Capucines WW', 2, 5000.00, 10000.00);

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
(1, 36, 32, 1010.00, '2024-11-26 15:51:53', 'Completed'),
(2, 36, 33, 4760.00, '2024-12-04 14:28:33', 'Completed'),
(3, 36, 34, 10510.00, '2024-12-07 21:36:40', 'Completed');

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
  `Quick_View1` text NOT NULL,
  `Quick_View2` text NOT NULL,
  `Quick_View3` text NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_stock` int(11) NOT NULL,
  `color1` varchar(20) NOT NULL,
  `color2` varchar(20) NOT NULL,
  `size1` varchar(20) NOT NULL,
  `size2` varchar(20) NOT NULL,
  `tags` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `category_id`, `product_status`, `product_name`, `product_des`, `product_image`, `Quick_View1`, `Quick_View2`, `Quick_View3`, `product_price`, `product_stock`, `color1`, `color2`, `size1`, `size2`, `tags`) VALUES
(1, 1, 1, 'Capucines Mini', 'Part of the Trilogy line, this Capucines Mini is the epitome of sophistication with its triple chain adornment, one of which is strung with delicate mother-of-pearl effect. It is crafted from timeless Taurillon leather and opens to reveal a compartmented interior with a flat pocket for cards. The perfect evening style, it can be worn cross-body or carried by hand.', 'CapucinesMini.avif', 'CapucinesMini.avif', 'CapucinesMini2.avif', 'CapucinesMini3.avif', 1000.00, 3, 'Brown', 'Black', 'PM', 'MM', 'Fashion'),
(2, 1, 1, 'Capucines WW', 'Part of the Trilogy line, this Capucines WW is the epitome of sophistication with its triple chain adornment. Look closely to notice the jewel-like chain details, including signature mini padlocks and enameled Monogram flowers. This double-carry style is crafted from iconic Taurillon leather and is accented with gold-toned hardware. It is fitted with multiple interior pockets and can be worn several ways.', 'CapucinesWW.avif', 'CapucinesWW.avif', 'CapucinesWW2.avif', 'CapucinesWW3.avif', 5000.00, 7, 'Grey', 'White', 'PM', 'MM', 'Crafts'),
(3, 1, 1, 'Capucines BB', 'Fashioned from full-grain Taurillon leather, the now-classic Capucines BB handbag displays a host of House signatures: leather-wrapped LV Initials, jewel-like handle mounts inspired by historic trunks, and a distinctive flap with a Monogram Flower motif. Timelessly elegant, this charming model can be carried by hand or worn cross-body on its detachable strap. First launched in 2013, the Capucines is named for the Parisian street where Louis Vuitton first opened shop, in 1854.', 'CapucinesBB.avif', 'CapucinesBB.avif', 'CapucinesBB2.avif', 'CapucinesBB3.avif', 300.00, 10, 'Yellow', 'Beige', 'PM', 'MM', 'Lifestyle'),
(4, 2, 1, 'Speedy P9 Bandoulière 40', 'The Speedy P9 Bandoulière 40 bag is made from extra-soft calfskin in a colorway inspired by precious stones. This less structured version of the iconic Speedy is body-friendly and very comfortable to carry. As part of Pharrell Williams’ cowboy-influenced collection for Fall-Winter 2024, the handle mounts feature embossed horses.\r\n\r\n', 'SpeedyP9.avif', 'SpeedyP9.avif', 'SpeedyP92.avif', 'SpeedyP93.avif', 1000.00, 10, 'Brown', 'Black', 'PM', 'MM', 'Fashion'),
(5, 2, 1, 'Speedy P9 Bandoulière 40', 'The Speedy P9 Bandoulière 40 bag is made from extra-soft calfskin in a colorway inspired by precious stones. This less-structured version of the iconic Speedy is body-friendly and very comfortable to carry. As part of Pharrell Williams’ cowboy-influenced collection for Fall-Winter 2024, the handle mounts feature embossed horses.', 'SpeedyP940.avif', 'SpeedyP940.avif', 'SpeedyP9402.avif', 'SpeedyP9403.avif', 1000.00, 8, 'Yellow', 'Beige', 'PM', 'MM', 'Lifestyle'),
(6, 1, 1, 'Capucines M11341', 'Part of the new permanent Flower Crown line, this sophisticated Capucines Mini design is adorned with a crown of mother-of-pearl effect Monogram flowers, finished with elegant gold-toned hardware. The refined design opens to reveal a compartmented interior fitted with multiple pockets, and features a removable strap, offering numerous carry options.', 'CapucinesMM.avif', 'CapucinesMM.avif', 'CapucinesMM2.avif', 'CapucinesMM3.avif', 1000.00, 10, 'Brown', 'White', 'PM', 'MM', 'Fashion'),
(7, 2, 1, 'Speedy P9 Bandoulière 50', 'The super-supple Speedy P9 Bandoulière 50 bag is fashioned from calfskin with natural cowhide trim and lambskin lining. The bag’s vibrant colorway and slouchy structure brings a relaxed sophistication to any look. In addition to the two rolled-leather top handles, the Speedy features an adjustable and detachable strap for comfortable shoulder wear.', 'SpeedyP950.avif', 'SpeedyP950.avif', 'SpeedyP9502.avif', 'SpeedyP9503.png', 1000.00, 10, 'Yellow', 'Beige', 'PM', 'MM', 'Lifestyle');

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
(89, 39, 2, 'ss', NULL, 36, 'active', 15, 'ss', '2024-12-11 17:23:29', '2024-12-11 22:50:37', NULL),
(90, 41, 5, 'ookokKokosca', NULL, 36, 'active', NULL, NULL, '2024-12-11 23:29:13', NULL, NULL),
(91, 37, 5, 'ok', NULL, 36, 'active', NULL, NULL, '2024-12-11 23:29:42', NULL, NULL),
(92, 42, 3, 'weiijsdjsidai', 'uploads/reviews/675b31d9f282b_Cheong Wei Kit 1221203087 Timetable.png', 36, 'inactive', 15, 'jangan beli babi', '2024-12-13 02:56:25', '2024-12-13 02:57:55', '2024-12-13 02:58:54');

-- --------------------------------------------------------

--
-- Table structure for table `shopping_cart`
--

CREATE TABLE `shopping_cart` (
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `color` text NOT NULL,
  `size` text NOT NULL,
  `total_price` double NOT NULL,
  `final_total_price` decimal(10,2) NOT NULL,
  `voucher_applied` int(11) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shopping_cart`
--

INSERT INTO `shopping_cart` (`cart_id`, `product_id`, `user_id`, `qty`, `color`, `size`, `total_price`, `final_total_price`, `voucher_applied`, `discount_amount`) VALUES
(120, 4, 37, 2, 'Black', 'PM', 2000, 0.00, 0, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `superadmin`
--

CREATE TABLE `superadmin` (
  `superadmin_id` int(11) NOT NULL,
  `superadmin_name` varchar(100) NOT NULL,
  `superadmin_image` text NOT NULL,
  `superadmin_password` varchar(150) NOT NULL,
  `superadmin_email` varchar(100) NOT NULL,
  `superadmin_contact_number` varchar(100) NOT NULL
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
  `user_status` varchar(100) NOT NULL,
  `user_date_of_birth` date NOT NULL,
  `user_join_time` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `user_name`, `user_image`, `user_password`, `user_email`, `user_contact_number`, `user_gender`, `user_status`, `user_date_of_birth`, `user_join_time`) VALUES
(36, 'Yeow Li sheng', 'uploads/WhatsApp Image 2023-03-07 at 11.59.00 AM.jpeg', 'QWERTYUIOP1234567890!', 'lishengyao1068@gmail.com', '011-1125595', 'female', '', '2024-10-03', '2024-10-27 22:46:05'),
(37, 'JL Chong', '', 'Jlchong0516204777!', 'jlchong2004@gmail.com', '011-1150551', 'female', '', '2024-10-16', '2024-10-27 23:11:23');

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
(9, 36, 'yongpeng', 'JOHOR', 'yongpeng', '83711'),
(10, 37, '11 Jalan Teratai 18', 'Johor ', 'Johor Bahru', '81100');

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
(3, 'DIS4THREET', 3.00, 'Active', 2, 3000.00, '3% discount for all purchasement above $3000', '3-dis.jpg');

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
-- Dumping data for table `voucher_usage`
--

INSERT INTO `voucher_usage` (`usage_id`, `user_id`, `voucher_id`, `usage_num`) VALUES
(41, 36, 1, 1),
(42, 36, 2, 3);

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
  ADD PRIMARY KEY (`comment_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `contact_us`
--
ALTER TABLE `contact_us`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`faq_id`);

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
  ADD KEY `product_id` (`product_id`);

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
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `superadmin`
--
ALTER TABLE `superadmin`
  ADD PRIMARY KEY (`superadmin_id`);

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
  ADD PRIMARY KEY (`usage_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

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
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contact_us`
--
ALTER TABLE `contact_us`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `faq`
--
ALTER TABLE `faq`
  MODIFY `faq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product_status`
--
ALTER TABLE `product_status`
  MODIFY `p_status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `superadmin`
--
ALTER TABLE `superadmin`
  MODIFY `superadmin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `voucher`
--
ALTER TABLE `voucher`
  MODIFY `voucher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `voucher_usage`
--
ALTER TABLE `voucher_usage`
  MODIFY `usage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

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
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

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
  ADD CONSTRAINT `shopping_cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `user_address`
--
ALTER TABLE `user_address`
  ADD CONSTRAINT `user_address_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
