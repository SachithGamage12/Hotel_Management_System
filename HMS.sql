-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.38 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for wedding_bliss
CREATE DATABASE IF NOT EXISTS `wedding_bliss` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `wedding_bliss`;

-- Dumping structure for table wedding_bliss.accountsregistration_access
CREATE TABLE IF NOT EXISTS `accountsregistration_access` (
  `id` int NOT NULL AUTO_INCREMENT,
  `is_locked` tinyint(1) DEFAULT '1',
  `unlock_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.accountsregistration_access: ~0 rows (approximately)
INSERT INTO `accountsregistration_access` (`id`, `is_locked`, `unlock_key`, `updated_at`) VALUES
	(1, 0, '1', '2025-09-04 11:18:42');

-- Dumping structure for table wedding_bliss.accounts_users
CREATE TABLE IF NOT EXISTS `accounts_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username` (`username`) USING BTREE,
  UNIQUE KEY `email` (`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.accounts_users: ~0 rows (approximately)
INSERT INTO `accounts_users` (`id`, `username`, `password`, `email`, `is_admin`, `created_at`) VALUES
	(1, 'Sachith', '$2y$10$VE0tyZ6ZTj12RjBeXpmdFuk5wj91FZ6zliGmLs5aaJE24qLJVMJkK', NULL, 0, '2025-09-04 11:18:15');

-- Dumping structure for table wedding_bliss.approvers
CREATE TABLE IF NOT EXISTS `approvers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.approvers: ~0 rows (approximately)
INSERT INTO `approvers` (`id`, `username`, `password`, `created_at`) VALUES
	(1, 'Thilina', '$2y$10$jEUBtd2CDpVnsjd.8/a7auTn0QMuUCJ8tvzk.Oe/FjkoOFPByjmX.', '2025-08-24 12:24:45');

-- Dumping structure for table wedding_bliss.ashtakas
CREATE TABLE IF NOT EXISTS `ashtakas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.ashtakas: ~0 rows (approximately)
INSERT INTO `ashtakas` (`id`, `name`) VALUES
	(1, 'By Customer');

-- Dumping structure for table wedding_bliss.audit_users
CREATE TABLE IF NOT EXISTS `audit_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.audit_users: ~0 rows (approximately)
INSERT INTO `audit_users` (`id`, `username`, `password`) VALUES
	(1, 'Sachith', '$2y$10$gNUXmtHZbEMdLkl/k6FovO.y4JnaWP8tHFdt7NR1R/RkjvD/Zz9D2');

-- Dumping structure for table wedding_bliss.auidtregistration_access
CREATE TABLE IF NOT EXISTS `auidtregistration_access` (
  `id` int NOT NULL AUTO_INCREMENT,
  `is_locked` tinyint(1) DEFAULT '1',
  `unlock_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.auidtregistration_access: ~0 rows (approximately)
INSERT INTO `auidtregistration_access` (`id`, `is_locked`, `unlock_key`, `updated_at`) VALUES
	(1, 0, '1', '2025-09-10 04:01:05');

-- Dumping structure for table wedding_bliss.bookings
CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `booking_code` varchar(4) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_no1` varchar(20) DEFAULT NULL,
  `contact_no2` varchar(20) DEFAULT NULL,
  `bride_address` text,
  `groom_address` text,
  `booking_date` date DEFAULT NULL,
  `details_of_event` text,
  `hall` varchar(100) DEFAULT NULL,
  `number_of_pax` int DEFAULT NULL,
  `total_value` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `booking_code` (`booking_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.bookings: ~0 rows (approximately)

-- Dumping structure for table wedding_bliss.buffer_stock
CREATE TABLE IF NOT EXISTS `buffer_stock` (
  `buffer_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`buffer_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `buffer_stock_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.buffer_stock: ~0 rows (approximately)

-- Dumping structure for table wedding_bliss.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.categories: ~4 rows (approximately)
INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
	(1, 'Electronics', '2025-09-18 07:52:00'),
	(2, 'Clothing', '2025-09-18 07:52:00'),
	(3, 'Food', '2025-09-18 07:52:00'),
	(4, 'Books', '2025-09-18 07:52:00');

-- Dumping structure for table wedding_bliss.edited_bookings
CREATE TABLE IF NOT EXISTS `edited_bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_reference` char(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `contact_no1` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `contact_no2` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `booking_date` date DEFAULT NULL,
  `time_from` time DEFAULT NULL,
  `time_from_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `time_to` time DEFAULT NULL,
  `time_to_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `couple_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `groom_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `bride_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `venue_id` int DEFAULT NULL,
  `menu_id` int DEFAULT NULL,
  `function_type_id` int DEFAULT NULL,
  `day_or_night` enum('day','night') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `no_of_pax` int DEFAULT NULL,
  `floor_coordinator` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `drinks_coordinator` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `bride_dressing` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `groom_dressing` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `bride_arrival_time` time DEFAULT NULL,
  `bride_arrival_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `groom_arrival_time` time DEFAULT NULL,
  `groom_arrival_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `morning_tea_time_from` time DEFAULT NULL,
  `morning_tea_time_from_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `morning_tea_time_to` time DEFAULT NULL,
  `morning_tea_time_to_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `tea_pax` int DEFAULT NULL,
  `kiribath` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `poruwa_time_from` time DEFAULT NULL,
  `poruwa_time_from_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `poruwa_time_to` time DEFAULT NULL,
  `poruwa_time_to_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `poruwa_direction` enum('north','east','south','west') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `registration_time_from` time DEFAULT NULL,
  `registration_time_from_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `registration_time_to` time DEFAULT NULL,
  `registration_time_to_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `registration_direction` enum('north','east','south','west') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `welcome_drink_time` time DEFAULT NULL,
  `welcome_drink_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `floor_table_arrangement` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `drinks_time` time DEFAULT NULL,
  `drinks_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `drinks_pax` int DEFAULT NULL,
  `drink_serving` enum('shot','bottle') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `bites_source` enum('other','customer') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `bite_items` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `buffet_open` time DEFAULT NULL,
  `buffet_open_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `buffet_close` time DEFAULT NULL,
  `buffet_close_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `buffet_type` enum('one_way','two_way') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `ice_coffee_time` time DEFAULT NULL,
  `ice_coffee_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `music_close_time` time DEFAULT NULL,
  `music_close_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `departure_time` time DEFAULT NULL,
  `departure_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `etc_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `music_type_id` int DEFAULT NULL,
  `wedding_car_id` int DEFAULT NULL,
  `jayamangala_gatha_id` int DEFAULT NULL,
  `wes_dance_id` int DEFAULT NULL,
  `ashtaka_id` int DEFAULT NULL,
  `welcome_song_id` int DEFAULT NULL,
  `indian_dhol_id` int DEFAULT NULL,
  `floor_dance_id` int DEFAULT NULL,
  `head_table` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `chair_cover` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `table_cloth` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `top_cloth` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `bow` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `napkin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `vip` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `changing_room_date` date DEFAULT NULL,
  `changing_room_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `honeymoon_room_date` date DEFAULT NULL,
  `honeymoon_room_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `dressing_room_date` date DEFAULT NULL,
  `dressing_room_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `theme_color` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `flower_decor` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `car_decoration` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `milk_fountain` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `champaign` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `cultural_table` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `kiribath_structure` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `cake_structure` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `projector_screen` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `gsky_arrival_time` time DEFAULT NULL,
  `gsky_arrival_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `photo_team_count` int DEFAULT NULL,
  `bridal_team_count` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `booking_reference` (`booking_reference`) USING BTREE,
  KEY `venue_id` (`venue_id`) USING BTREE,
  KEY `menu_id` (`menu_id`) USING BTREE,
  KEY `function_type_id` (`function_type_id`) USING BTREE,
  KEY `music_type_id` (`music_type_id`) USING BTREE,
  KEY `wedding_car_id` (`wedding_car_id`) USING BTREE,
  KEY `jayamangala_gatha_id` (`jayamangala_gatha_id`) USING BTREE,
  KEY `wes_dance_id` (`wes_dance_id`) USING BTREE,
  KEY `ashtaka_id` (`ashtaka_id`) USING BTREE,
  KEY `welcome_song_id` (`welcome_song_id`) USING BTREE,
  KEY `indian_dhol_id` (`indian_dhol_id`) USING BTREE,
  KEY `floor_dance_id` (`floor_dance_id`) USING BTREE,
  CONSTRAINT `edited_bookings_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`),
  CONSTRAINT `edited_bookings_ibfk_10` FOREIGN KEY (`indian_dhol_id`) REFERENCES `indian_dhols` (`id`),
  CONSTRAINT `edited_bookings_ibfk_11` FOREIGN KEY (`floor_dance_id`) REFERENCES `floor_dances` (`id`),
  CONSTRAINT `edited_bookings_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`),
  CONSTRAINT `edited_bookings_ibfk_3` FOREIGN KEY (`function_type_id`) REFERENCES `function_types` (`id`),
  CONSTRAINT `edited_bookings_ibfk_4` FOREIGN KEY (`music_type_id`) REFERENCES `music_types` (`id`),
  CONSTRAINT `edited_bookings_ibfk_5` FOREIGN KEY (`wedding_car_id`) REFERENCES `wedding_cars` (`id`),
  CONSTRAINT `edited_bookings_ibfk_6` FOREIGN KEY (`jayamangala_gatha_id`) REFERENCES `jayamangala_gathas` (`id`),
  CONSTRAINT `edited_bookings_ibfk_7` FOREIGN KEY (`wes_dance_id`) REFERENCES `wes_dances` (`id`),
  CONSTRAINT `edited_bookings_ibfk_8` FOREIGN KEY (`ashtaka_id`) REFERENCES `ashtakas` (`id`),
  CONSTRAINT `edited_bookings_ibfk_9` FOREIGN KEY (`welcome_song_id`) REFERENCES `welcome_songs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.edited_bookings: ~4 rows (approximately)
INSERT INTO `edited_bookings` (`id`, `booking_reference`, `full_name`, `contact_no1`, `contact_no2`, `booking_date`, `time_from`, `time_from_am_pm`, `time_to`, `time_to_am_pm`, `couple_name`, `groom_address`, `bride_address`, `venue_id`, `menu_id`, `function_type_id`, `day_or_night`, `no_of_pax`, `floor_coordinator`, `drinks_coordinator`, `bride_dressing`, `groom_dressing`, `bride_arrival_time`, `bride_arrival_time_am_pm`, `groom_arrival_time`, `groom_arrival_time_am_pm`, `morning_tea_time_from`, `morning_tea_time_from_am_pm`, `morning_tea_time_to`, `morning_tea_time_to_am_pm`, `tea_pax`, `kiribath`, `poruwa_time_from`, `poruwa_time_from_am_pm`, `poruwa_time_to`, `poruwa_time_to_am_pm`, `poruwa_direction`, `registration_time_from`, `registration_time_from_am_pm`, `registration_time_to`, `registration_time_to_am_pm`, `registration_direction`, `welcome_drink_time`, `welcome_drink_time_am_pm`, `floor_table_arrangement`, `drinks_time`, `drinks_time_am_pm`, `drinks_pax`, `drink_serving`, `bites_source`, `bite_items`, `buffet_open`, `buffet_open_am_pm`, `buffet_close`, `buffet_close_am_pm`, `buffet_type`, `ice_coffee_time`, `ice_coffee_time_am_pm`, `music_close_time`, `music_close_time_am_pm`, `departure_time`, `departure_time_am_pm`, `etc_description`, `music_type_id`, `wedding_car_id`, `jayamangala_gatha_id`, `wes_dance_id`, `ashtaka_id`, `welcome_song_id`, `indian_dhol_id`, `floor_dance_id`, `head_table`, `chair_cover`, `table_cloth`, `top_cloth`, `bow`, `napkin`, `vip`, `changing_room_date`, `changing_room_number`, `honeymoon_room_date`, `honeymoon_room_number`, `dressing_room_date`, `dressing_room_number`, `theme_color`, `flower_decor`, `car_decoration`, `milk_fountain`, `champaign`, `cultural_table`, `kiribath_structure`, `cake_structure`, `projector_screen`, `gsky_arrival_time`, `gsky_arrival_time_am_pm`, `photo_team_count`, `bridal_team_count`, `created_at`) VALUES
	(1, '8422', 'Sachith Udara', '0725876139', NULL, '2025-06-19', '00:00:00', 'AM', '00:00:00', 'AM', NULL, 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', NULL, 1, 1, 4, NULL, NULL, NULL, NULL, NULL, NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', NULL, '00:00:00', 'AM', NULL, NULL, NULL, NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '00:00:00', 'AM', NULL, NULL, '2025-06-09 04:07:39'),
	(3, '8421', 'Sachith Gamag', '0725876138', NULL, '2025-06-26', '00:00:00', 'AM', '00:00:00', 'AM', NULL, 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', NULL, 4, 2, 4, NULL, NULL, NULL, NULL, NULL, NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', NULL, '00:00:00', 'AM', NULL, NULL, NULL, NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, 1, 1, 1, 1, 1, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '00:00:00', 'AM', NULL, NULL, '2025-06-09 04:40:12'),
	(5, '6877', 'Sachith fernando', '0725876139', NULL, '2025-06-11', NULL, NULL, NULL, NULL, 'Suchi $ Bchi', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', NULL, NULL, 3, NULL, 'night', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-09 06:56:02'),
	(7, '8423', 'Sachith Gamage', '0725876138', '123', '2025-06-26', NULL, NULL, '18:50:00', NULL, NULL, 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', NULL, 2, 1, 4, 'night', 420, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-09 07:36:41');

-- Dumping structure for table wedding_bliss.floor_dances
CREATE TABLE IF NOT EXISTS `floor_dances` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.floor_dances: ~0 rows (approximately)
INSERT INTO `floor_dances` (`id`, `name`) VALUES
	(1, 'By Customer');

-- Dumping structure for table wedding_bliss.footnotes
CREATE TABLE IF NOT EXISTS `footnotes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `note` text,
  `timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.footnotes: ~0 rows (approximately)

-- Dumping structure for table wedding_bliss.foregistration_access
CREATE TABLE IF NOT EXISTS `foregistration_access` (
  `id` int NOT NULL AUTO_INCREMENT,
  `is_locked` tinyint(1) DEFAULT '1',
  `unlock_key` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.foregistration_access: ~0 rows (approximately)
INSERT INTO `foregistration_access` (`id`, `is_locked`, `unlock_key`, `updated_at`) VALUES
	(1, 0, '1', '2025-07-01 04:55:09');

-- Dumping structure for table wedding_bliss.fo_users
CREATE TABLE IF NOT EXISTS `fo_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.fo_users: ~0 rows (approximately)
INSERT INTO `fo_users` (`id`, `username`, `password`, `email`, `is_admin`, `created_at`) VALUES
	(4, 'Sachith', '$2y$10$ZlxSSEEmKy927xoKGNR4M.eM5m/1f3oGx6VtKefX2/RSE7noIVhoW', NULL, 0, '2025-07-01 05:14:58'),
	(5, 'udara', '$2y$10$DoX9quoM9bdHW0p/FJUD3eo4oat0tI4fd4RjIv8jbBVeLHpa.QMLC', NULL, 0, '2025-07-20 06:36:05');

-- Dumping structure for table wedding_bliss.function_types
CREATE TABLE IF NOT EXISTS `function_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.function_types: ~4 rows (approximately)
INSERT INTO `function_types` (`id`, `name`) VALUES
	(1, 'Wedding'),
	(2, 'Birthday Party'),
	(3, 'Holy'),
	(4, 'Home Coming');

-- Dumping structure for table wedding_bliss.function_unload
CREATE TABLE IF NOT EXISTS `function_unload` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_sheet_no` int NOT NULL,
  `item_id` int NOT NULL,
  `requested_qty` decimal(10,2) DEFAULT NULL,
  `issued_qty` decimal(10,2) DEFAULT NULL,
  `remaining_qty` decimal(10,2) DEFAULT NULL,
  `usage_qty` decimal(10,2) DEFAULT NULL,
  `unload_date` date NOT NULL,
  `function_type` varchar(100) NOT NULL,
  `day_night` varchar(50) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `function_unload_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE,
  CONSTRAINT `function_unload_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `responsible` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.function_unload: ~17 rows (approximately)
INSERT INTO `function_unload` (`id`, `order_sheet_no`, `item_id`, `requested_qty`, `issued_qty`, `remaining_qty`, `usage_qty`, `unload_date`, `function_type`, `day_night`, `created_by`) VALUES
	(1, 1171, 7, 42.00, 42.00, 0.00, 42.00, '2025-08-29', 'HGG Ballroom Function, HGG Orchid Hall Function', 'Day, Night', 1),
	(2, 1171, 4, 9.00, 9.00, 0.00, 9.00, '2025-08-29', 'HGG Ballroom Function, HGG Orchid Hall Function', 'Day, Night', 1),
	(3, 1179, 7, 24.00, 22.00, 0.00, 22.00, '2025-08-20', 'HGG Function, HGG Orchid Hall Function, Outdoor Function', 'Day', 2),
	(4, 1181, 9, 5.00, 5.00, 4.00, 1.00, '2025-08-20', 'Outdoor Function', 'Day', 2),
	(5, 1179, 4, 22.00, 20.00, 0.00, 20.00, '2025-08-20', 'HGG Function, HGG Orchid Hall Function', 'Day', 2),
	(6, 1179, 7, 24.00, 22.00, 0.00, 17.00, '2025-08-20', 'HGG Function, HGG Orchid Hall Function, Outdoor Function', 'Day', 2),
	(7, 1181, 9, 5.00, 5.00, 4.00, 1.00, '2025-08-20', 'Outdoor Function', 'Day', 2),
	(8, 1179, 4, 22.00, 20.00, 0.00, 10.00, '2025-08-20', 'HGG Function, HGG Orchid Hall Function', 'Day', 2),
	(9, 1179, 7, 24.00, 22.00, 0.00, 16.20, '2025-08-20', 'HGG Function, HGG Orchid Hall Function, Outdoor Function', 'Day', 1),
	(10, 1181, 9, 5.00, 5.00, 2.00, 3.00, '2025-08-20', 'Outdoor Function', 'Day', 1),
	(11, 1179, 4, 22.00, 20.00, 0.00, 14.50, '2025-08-20', 'HGG Function, HGG Orchid Hall Function', 'Day', 1),
	(12, 1183, 7, 8.00, 8.00, 0.00, 6.00, '2025-08-31', 'Outdoor Function', 'Night', 2),
	(13, 1183, 4, 1.00, 1.00, 0.00, 1.00, '2025-08-31', 'Outdoor Function', 'Night', 2),
	(14, 1184, 7, 7.00, 5.00, 0.00, 3.00, '2025-09-01', 'HGG Banquet Hall Function, HGG Orchid Hall Function', 'Day, Night', 1),
	(15, 1184, 4, 4.00, 4.00, 1.00, 3.00, '2025-09-01', 'HGG Banquet Hall Function, HGG Orchid Hall Function', 'Day, Night', 1),
	(16, 1184, 7, 7.00, 5.00, 0.00, 2.00, '2025-09-01', 'HGG Banquet Hall Function, HGG Orchid Hall Function', 'Day, Night', 2),
	(17, 1184, 4, 4.00, 4.00, 2.00, 2.00, '2025-09-01', 'HGG Banquet Hall Function, HGG Orchid Hall Function', 'Day, Night', 2);

-- Dumping structure for table wedding_bliss.function_unload_history
CREATE TABLE IF NOT EXISTS `function_unload_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_sheet_no` int DEFAULT NULL,
  `item_id` int DEFAULT NULL,
  `requested_qty` decimal(10,2) DEFAULT NULL,
  `issued_qty` decimal(10,2) DEFAULT NULL,
  `remaining_qty` decimal(10,2) DEFAULT NULL,
  `usage_qty` decimal(10,2) DEFAULT NULL,
  `unload_date` date DEFAULT NULL,
  `function_type` varchar(255) DEFAULT NULL,
  `day_night` varchar(50) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.function_unload_history: ~0 rows (approximately)

-- Dumping structure for table wedding_bliss.grn_items
CREATE TABLE IF NOT EXISTS `grn_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grn_id` int NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int NOT NULL,
  `unit` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `grn_id` (`grn_id`),
  CONSTRAINT `grn_items_ibfk_1` FOREIGN KEY (`grn_id`) REFERENCES `grn_records` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.grn_items: ~25 rows (approximately)
INSERT INTO `grn_items` (`id`, `grn_id`, `item_name`, `quantity`, `unit`) VALUES
	(1, 1, 'Basmathi', 12, 'kg'),
	(2, 1, 'Bred', 3, 'units'),
	(3, 1, 'Pineapple', 23, 'kg'),
	(4, 1, 'Banana', 2, 'kg'),
	(5, 2, 'Basmathi', 12, 'kg'),
	(6, 3, 'Basmathi', 21, 'kg'),
	(7, 3, 'Bred', 11, 'units'),
	(8, 4, 'Basmathi', 23, 'kg'),
	(9, 5, 'Basmathi', 21, 'kg'),
	(10, 6, 'Basmathi', 23, 'kg'),
	(11, 7, 'Basmathi', 12, 'kg'),
	(12, 8, 'Bred', 12, 'units'),
	(13, 9, 'Basmathi', 12, 'kg'),
	(14, 10, 'Basmathi', 12, 'kg'),
	(15, 11, 'Basmathi', 25, 'kg'),
	(16, 12, 'Basmathi', 36, 'kg'),
	(17, 13, 'Basmathi', 23, 'kg'),
	(18, 13, 'Bred', 12, 'units'),
	(19, 14, 'Basmathi', 23, 'kg'),
	(20, 14, 'Bred', 12, 'units'),
	(21, 15, 'Basmathi', 23, 'kg'),
	(22, 15, 'Bred', 10, 'units'),
	(23, 15, 'Pineapple', 13, 'kg'),
	(25, 17, 'Basmathi', 23, 'kg'),
	(26, 17, 'Bred', 12, 'units'),
	(27, 17, 'Papaya', 15, 'kg'),
	(28, 18, 'Basmathi', 12, 'kg'),
	(29, 18, 'Bred', 5, 'units');

-- Dumping structure for table wedding_bliss.grn_records
CREATE TABLE IF NOT EXISTS `grn_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grn_number` varchar(20) NOT NULL,
  `date` datetime NOT NULL,
  `location` varchar(50) NOT NULL,
  `received_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `checked_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.grn_records: ~17 rows (approximately)
INSERT INTO `grn_records` (`id`, `grn_number`, `date`, `location`, `received_by`, `checked_by`) VALUES
	(1, 'GRN-1500', '2025-08-07 04:53:29', 'HGG', NULL, NULL),
	(2, 'GRN-1501', '2025-08-07 06:29:02', 'Sapthapadhi', 'Sachith', 'Sachith Gamage'),
	(3, 'GRN-1502', '2025-08-07 10:19:14', 'HGG', 'Sachith', 'Sachith Gamage'),
	(4, 'GRN-1503', '2025-08-07 10:31:49', 'Sapthapadhi', 'Sachith', 'Sachith Gamage'),
	(5, 'GRN-1504', '2025-08-07 10:38:26', 'HGG', 'Sachith', 'Sachith Gamage'),
	(6, 'GRN-1505', '2025-08-07 10:42:06', 'Sapthapadhi', 'Sachith', 'Sachith Gamage'),
	(7, 'GRN-1506', '2025-08-07 10:47:35', 'Sapthapadhi', 'Sachith', 'Sachith Gamage'),
	(8, 'GRN-1507', '2025-08-07 10:54:32', 'HGG', 'Sachith', 'Sachith Gamage'),
	(9, 'GRN-1508', '2025-08-07 11:07:04', 'Sapthapadhi', 'Sachith', 'Sachith Gamage'),
	(10, 'GRN-1509', '2025-08-07 11:40:49', 'Sapthapadhi', 'Sachith', 'Sachith Gamage'),
	(11, 'GRN-1510', '2025-08-07 11:47:36', 'HGG', 'Sachith', 'Sachith Gamage'),
	(12, 'GRN-1511', '2025-08-07 11:54:42', 'Sapthapadhi', 'Sachith', 'Sachith Gamage'),
	(13, 'GRN-1512', '2025-08-07 12:01:12', 'HGG', 'Sachith', 'Sachith Gamage'),
	(14, 'GRN-1513', '2025-08-07 12:06:56', 'Sapthapadhi', 'Sachith', 'Sachith Gamage'),
	(15, 'GRN-1514', '2025-08-07 12:45:29', 'Sapthapadhi', 'Sachith', 'Sachith Gamage'),
	(17, 'GRN-1515', '2025-08-07 13:15:55', 'Sapthapadhi', 'Sachith', 'Sachith Gamage'),
	(18, 'GRN-1516', '2025-08-12 12:05:51', 'Sapthapadhi', 'Yasith', 'Test');

-- Dumping structure for table wedding_bliss.guests
CREATE TABLE IF NOT EXISTS `guests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grc_number` int NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text,
  `id_type` enum('NIC','Passport') NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `check_in_date` date NOT NULL,
  `check_in_time` time NOT NULL,
  `check_in_time_am_pm` enum('AM','PM') NOT NULL,
  `check_out_date` date NOT NULL,
  `check_out_time` time NOT NULL,
  `check_out_time_am_pm` enum('AM','PM') NOT NULL,
  `rooms` json NOT NULL,
  `meal_plan_id` int DEFAULT NULL,
  `number_of_pax` int DEFAULT NULL,
  `remarks` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grc_number` (`grc_number`),
  KEY `meal_plan_id` (`meal_plan_id`),
  KEY `idx_grc_number` (`grc_number`),
  CONSTRAINT `guests_ibfk_1` FOREIGN KEY (`meal_plan_id`) REFERENCES `meal_plans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.guests: ~0 rows (approximately)
INSERT INTO `guests` (`id`, `grc_number`, `guest_name`, `contact_number`, `email`, `address`, `id_type`, `id_number`, `check_in_date`, `check_in_time`, `check_in_time_am_pm`, `check_out_date`, `check_out_time`, `check_out_time_am_pm`, `rooms`, `meal_plan_id`, `number_of_pax`, `remarks`) VALUES
	(1, 1200, 'Sachith Gamage', '0725876139', 'udarasachith41@gmail.com', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', 'NIC', '200328100859v', '2025-07-24', '02:47:00', 'AM', '2025-07-25', '12:49:00', 'PM', '[{"ac_type": "AC", "room_rate": "Rs. 5000.00 (Standard, AC)", "room_type": "1", "room_number": "101"}, {"ac_type": "Non-AC", "room_rate": "Rs. 6500.00 (Deluxe, Non-AC)", "room_type": "2", "room_number": "202"}]', 1, 3, ''),
	(2, 1201, 'Sachith Gamage', '0725876139', 'udarasachith41@gmail.com', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', 'NIC', '200328100859', '2025-07-20', '12:39:00', 'PM', '2025-07-22', '12:39:00', 'PM', '[{"ac_type": "AC", "room_rate": "Rs. 5000.00 ", "room_type": "1", "room_number": "101"}, {"ac_type": "Non-AC", "room_rate": "Rs. 6500.00 ", "room_type": "2", "room_number": "202"}]', 1, 3, ''),
	(3, 1202, 'Test Test', '0703739158', 'test@gmail.com', 'No.29,Test,Test', 'NIC', '20034567890', '2025-09-01', '03:33:00', 'PM', '2025-09-03', '03:33:00', 'AM', '[{"ac_type": "AC", "room_rate": "Rs. 8000.00 ", "room_type": "2", "room_number": "201"}, {"ac_type": "Non-AC", "room_rate": "Rs. 6500.00 ", "room_type": "2", "room_number": "202"}]', 1, 3, 'no anything');

-- Dumping structure for table wedding_bliss.hggfunction_unload
CREATE TABLE IF NOT EXISTS `hggfunction_unload` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_sheet_no` int NOT NULL,
  `item_id` int NOT NULL,
  `requested_qty` decimal(10,2) DEFAULT NULL,
  `issued_qty` decimal(10,2) DEFAULT NULL,
  `remaining_qty` decimal(10,2) DEFAULT NULL,
  `usage_qty` decimal(10,2) DEFAULT NULL,
  `unload_date` date NOT NULL,
  `function_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `day_night` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `item_id` (`item_id`) USING BTREE,
  KEY `created_by` (`created_by`) USING BTREE,
  CONSTRAINT `fk_hgg_unload_created_by` FOREIGN KEY (`created_by`) REFERENCES `storeresponsible` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hgg_unload_item` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.hggfunction_unload: ~2 rows (approximately)
INSERT INTO `hggfunction_unload` (`id`, `order_sheet_no`, `item_id`, `requested_qty`, `issued_qty`, `remaining_qty`, `usage_qty`, `unload_date`, `function_type`, `day_night`, `created_by`) VALUES
	(21, 1100, 7, 3.00, 3.00, 0.00, 1.00, '2025-08-20', 'HGG Restaurant', '', 3),
	(22, 1100, 7, 3.00, 1.00, 1.00, 0.00, '2025-08-20', 'HGG Restaurant', '', 3);

-- Dumping structure for table wedding_bliss.hggfunction_unload_history
CREATE TABLE IF NOT EXISTS `hggfunction_unload_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_sheet_no` int DEFAULT NULL,
  `item_id` int DEFAULT NULL,
  `requested_qty` decimal(10,2) DEFAULT NULL,
  `issued_qty` decimal(10,2) DEFAULT NULL,
  `remaining_qty` decimal(10,2) DEFAULT NULL,
  `usage_qty` decimal(10,2) DEFAULT NULL,
  `unload_date` date DEFAULT NULL,
  `function_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `day_night` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.hggfunction_unload_history: ~2 rows (approximately)
INSERT INTO `hggfunction_unload_history` (`id`, `order_sheet_no`, `item_id`, `requested_qty`, `issued_qty`, `remaining_qty`, `usage_qty`, `unload_date`, `function_type`, `day_night`, `created_by`, `created_at`) VALUES
	(3, 1100, 7, 3.00, 3.00, 2.00, 1.00, '2025-08-20', 'HGG Restaurant', '', 3, '2025-08-20 15:04:43'),
	(4, 1100, 7, 3.00, 1.00, 1.00, 0.00, '2025-08-20', 'HGG Restaurant', '', 3, '2025-08-21 04:33:05');

-- Dumping structure for table wedding_bliss.hggitems
CREATE TABLE IF NOT EXISTS `hggitems` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_code` varchar(100) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category_id` int NOT NULL,
  `type` enum('KOT','BOT') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `discount_start_date` date DEFAULT NULL,
  `discount_end_date` date DEFAULT NULL,
  `status` enum('active','disabled') DEFAULT 'active',
  `stock` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_code` (`item_code`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `hggitems_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.hggitems: ~3 rows (approximately)
INSERT INTO `hggitems` (`id`, `item_code`, `item_name`, `category_id`, `type`, `price`, `discount_price`, `discount_start_date`, `discount_end_date`, `status`, `stock`, `created_at`) VALUES
	(1, 'F1234', 'Chicken Fride Rice', 3, 'KOT', 1200.00, 1000.00, '2025-09-18', '2025-09-19', 'active', 0, '2025-09-18 07:55:05'),
	(2, 'ITEM001', 'Chicken Fried Rice', 3, 'KOT', 1200.00, 1000.00, '2025-09-18', '2025-09-19', 'active', 0, '2025-09-18 08:23:00'),
	(3, 'BAR0001', 'OLD ARRACK', 3, 'BOT', 1300.00, NULL, NULL, NULL, 'active', -8, '2025-10-26 07:53:06');

-- Dumping structure for table wedding_bliss.hggkitchenregistration_access
CREATE TABLE IF NOT EXISTS `hggkitchenregistration_access` (
  `id` int NOT NULL AUTO_INCREMENT,
  `is_locked` tinyint(1) DEFAULT '1',
  `unlock_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.hggkitchenregistration_access: ~0 rows (approximately)
INSERT INTO `hggkitchenregistration_access` (`id`, `is_locked`, `unlock_key`, `updated_at`) VALUES
	(3, 0, '1', '2025-08-23 04:52:29');

-- Dumping structure for table wedding_bliss.hggkitchen_buffer
CREATE TABLE IF NOT EXISTS `hggkitchen_buffer` (
  `buffer_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `remaining_quantity` int NOT NULL DEFAULT '0',
  `usage` int NOT NULL DEFAULT '0',
  `last_updated` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`buffer_id`) USING BTREE,
  KEY `item_id` (`item_id`) USING BTREE,
  CONSTRAINT `fk_hgg_kitchen_buffer_item` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.hggkitchen_buffer: ~0 rows (approximately)
INSERT INTO `hggkitchen_buffer` (`buffer_id`, `item_id`, `quantity`, `remaining_quantity`, `usage`, `last_updated`) VALUES
	(4, 7, 12, 2, 0, '2025-09-05 06:46:55');

-- Dumping structure for table wedding_bliss.hggkitchen_buffer_history
CREATE TABLE IF NOT EXISTS `hggkitchen_buffer_history` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `quantity_updated` int NOT NULL,
  `update_timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `hggkitchen_buffer_history_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.hggkitchen_buffer_history: ~2 rows (approximately)
INSERT INTO `hggkitchen_buffer_history` (`history_id`, `item_id`, `quantity_updated`, `update_timestamp`) VALUES
	(1, 7, 2, '2025-09-05 12:15:51'),
	(2, 7, 2, '2025-09-05 12:16:55');

-- Dumping structure for table wedding_bliss.hggkitchen_users
CREATE TABLE IF NOT EXISTS `hggkitchen_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username` (`username`) USING BTREE,
  UNIQUE KEY `email` (`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.hggkitchen_users: ~0 rows (approximately)
INSERT INTO `hggkitchen_users` (`id`, `username`, `password`, `email`, `is_admin`, `created_at`) VALUES
	(7, 'Sachith', '$2y$10$pLDFFFMk9Z47.JYDhRo8j.dHfN5NRR4BVhJM/PikUpiZ9CoIeYViq', NULL, 0, '2025-08-23 04:51:00');

-- Dumping structure for table wedding_bliss.hggorder_sheet
CREATE TABLE IF NOT EXISTS `hggorder_sheet` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `requested_qty` decimal(10,2) DEFAULT NULL,
  `issued_qty` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','issued') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'pending',
  `request_date` datetime NOT NULL,
  `issued_date` datetime DEFAULT NULL,
  `order_sheet_no` int NOT NULL,
  `responsible_id` int DEFAULT NULL,
  `function_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `function_date` date DEFAULT NULL,
  `day_night` enum('Day','Night') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`order_id`) USING BTREE,
  UNIQUE KEY `order_sheet_no` (`order_sheet_no`,`item_id`) USING BTREE,
  KEY `item_id` (`item_id`) USING BTREE,
  KEY `responsible_id` (`responsible_id`) USING BTREE,
  CONSTRAINT `fk_hggorder_item` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`),
  CONSTRAINT `fk_hggorder_responsible` FOREIGN KEY (`responsible_id`) REFERENCES `responsible` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.hggorder_sheet: ~0 rows (approximately)
INSERT INTO `hggorder_sheet` (`order_id`, `item_id`, `requested_qty`, `issued_qty`, `status`, `request_date`, `issued_date`, `order_sheet_no`, `responsible_id`, `function_type`, `function_date`, `day_night`) VALUES
	(132, 7, 3.00, 1.00, 'issued', '2025-08-20 10:02:49', NULL, 1100, 2, 'HGG Restaurant', '2025-08-20', NULL);

-- Dumping structure for table wedding_bliss.hggorder_sheet_counter
CREATE TABLE IF NOT EXISTS `hggorder_sheet_counter` (
  `id` int NOT NULL,
  `last_order_sheet_no` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.hggorder_sheet_counter: ~0 rows (approximately)
INSERT INTO `hggorder_sheet_counter` (`id`, `last_order_sheet_no`) VALUES
	(1, 1100);

-- Dumping structure for table wedding_bliss.hgg_users
CREATE TABLE IF NOT EXISTS `hgg_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.hgg_users: ~0 rows (approximately)
INSERT INTO `hgg_users` (`id`, `username`, `password`) VALUES
	(1, 'Sachith', '$2y$10$ZiGO5n6Cw064jkbaxmBRTO/q.mJipF1IeCO8wKqHmhbXjUZeZNPUG');

-- Dumping structure for table wedding_bliss.indian_dhols
CREATE TABLE IF NOT EXISTS `indian_dhols` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.indian_dhols: ~0 rows (approximately)
INSERT INTO `indian_dhols` (`id`, `name`) VALUES
	(1, 'By Customer');

-- Dumping structure for table wedding_bliss.inventory
CREATE TABLE IF NOT EXISTS `inventory` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL,
  `buffer_stock` int NOT NULL DEFAULT (0),
  `unit` varchar(50) NOT NULL,
  `threshold` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.inventory: ~8 rows (approximately)
INSERT INTO `inventory` (`id`, `item_name`, `category`, `buffer_stock`, `unit`, `threshold`) VALUES
	(1, 'Banana', 'Fruits', 94, 'kg', 94),
	(3, 'Papaya', 'Fruits', 26, 'kg', 26),
	(4, 'Pineapple', 'Fruits', 49, 'kg', 54),
	(5, 'Graphes', 'Fruits', 60, 'kg', 60),
	(6, 'කීරී සම්බා', 'Dry Items', 500, 'kg', 500),
	(7, 'Basmathi', 'Dry Items', 24, 'kg', 59),
	(8, 'Rathu Kakulu', 'Dry Items', 56, 'kg', 56),
	(9, 'Bred', 'Dry Items', 30, 'units', 93);

-- Dumping structure for table wedding_bliss.inventoryregistration_access
CREATE TABLE IF NOT EXISTS `inventoryregistration_access` (
  `id` int NOT NULL AUTO_INCREMENT,
  `is_locked` tinyint(1) DEFAULT '1',
  `unlock_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.inventoryregistration_access: ~0 rows (approximately)
INSERT INTO `inventoryregistration_access` (`id`, `is_locked`, `unlock_key`, `updated_at`) VALUES
	(1, 0, '1', '2025-09-04 08:21:37');

-- Dumping structure for table wedding_bliss.inventory_audits
CREATE TABLE IF NOT EXISTS `inventory_audits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `audit_date` date NOT NULL,
  `quantity_at_audit` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `unit_type` varchar(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_item_audit_date` (`item_id`,`audit_date`),
  KEY `idx_item_audit` (`item_id`,`audit_date`),
  KEY `idx_audit_date` (`audit_date`),
  CONSTRAINT `inventory_audits_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.inventory_audits: ~5 rows (approximately)
INSERT INTO `inventory_audits` (`id`, `item_id`, `audit_date`, `quantity_at_audit`, `unit_type`, `created_at`, `updated_at`) VALUES
	(1, 4, '2025-08-22', 0.2000, 'l', '2025-08-29 02:54:53', '2025-09-02 07:52:25'),
	(2, 6, '2025-08-29', 0.0300, 'kg', '2025-08-29 02:54:53', '2025-08-29 02:54:53'),
	(3, 7, '2025-08-29', 20.0000, 'g', '2025-08-29 02:54:53', '2025-08-29 02:54:53'),
	(4, 4, '2025-08-30', 0.1300, 'l', '2025-08-29 02:57:07', '2025-08-29 02:57:07');

-- Dumping structure for table wedding_bliss.inventory_users
CREATE TABLE IF NOT EXISTS `inventory_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username` (`username`) USING BTREE,
  UNIQUE KEY `email` (`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.inventory_users: ~0 rows (approximately)
INSERT INTO `inventory_users` (`id`, `username`, `password`, `email`, `is_admin`, `created_at`) VALUES
	(1, 'Sachith', '$2y$10$Mkm4Ba/Fo5OCxK6OW13mbOYzLyaGQ7yl5fIY6aDFlyoeWWA20Pz66', NULL, 0, '2025-09-04 08:20:57');

-- Dumping structure for table wedding_bliss.invoices
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(20) NOT NULL,
  `table_number` int DEFAULT NULL,
  `payment_type` enum('cash_customer','cash_staff','card_customer','card_staff','credit','other_credit','foc','delivery','take_away') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `creditor_name` varchar(100) DEFAULT NULL,
  `other_creditor_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `foc_responsible` varchar(100) DEFAULT NULL,
  `cashier` varchar(50) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `service_charge` decimal(10,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(10,2) NOT NULL,
  `status` enum('completed','pending','canceled') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `delivery_place` varchar(255) DEFAULT NULL,
  `delivery_charge` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_cashier` (`cashier`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.invoices: ~45 rows (approximately)
INSERT INTO `invoices` (`id`, `invoice_number`, `table_number`, `payment_type`, `creditor_name`, `other_creditor_name`, `foc_responsible`, `cashier`, `subtotal`, `discount`, `service_charge`, `grand_total`, `status`, `created_at`, `updated_at`, `delivery_place`, `delivery_charge`) VALUES
	(1, 'INV-1400', 1, 'cash_customer', NULL, NULL, NULL, 'Sachith', 4800.00, 0.00, 480.00, 5280.00, 'completed', '2025-09-23 04:40:34', NULL, NULL, NULL),
	(2, 'INV-1401', 1, 'cash_staff', NULL, NULL, NULL, 'Sachith', 3600.00, 0.00, 0.00, 3600.00, 'completed', '2025-09-23 05:27:57', NULL, NULL, NULL),
	(3, 'INV-1402', 1, 'delivery', NULL, NULL, NULL, 'Sachith', 4800.00, 0.00, 0.00, 5050.00, 'completed', '2025-09-23 05:53:35', NULL, 'Malangama', 250.00),
	(4, 'INV-1403', 2, 'delivery', NULL, NULL, NULL, 'Sachith', 3600.00, 0.00, 0.00, 3850.00, 'completed', '2025-09-23 07:02:01', NULL, 'Malangama', 250.00),
	(5, '1400', 1, 'credit', 'chanuka', NULL, NULL, 'Sachith', 3600.00, 0.00, 0.00, 3600.00, 'completed', '2025-09-23 09:31:45', NULL, NULL, NULL),
	(6, '1401', 3, 'cash_customer', NULL, NULL, NULL, 'Sachith', 3600.00, 0.00, 360.00, 3960.00, 'completed', '2025-09-23 09:34:01', NULL, NULL, NULL),
	(7, '1402', 3, 'cash_customer', NULL, NULL, NULL, 'Sachith', 4800.00, 0.00, 480.00, 5280.00, 'completed', '2025-09-23 10:55:28', NULL, NULL, NULL),
	(8, '1403', 4, 'delivery', NULL, NULL, NULL, 'Sachith', 3600.00, 0.00, 0.00, 3850.00, 'completed', '2025-09-23 10:56:00', NULL, 'malangama', 250.00),
	(9, '1404', NULL, 'cash_staff', NULL, NULL, NULL, 'Sachith', 4800.00, 0.00, 0.00, 4800.00, 'completed', '2025-09-23 10:57:56', NULL, NULL, NULL),
	(10, '1405', NULL, 'cash_customer', NULL, NULL, NULL, 'Sachith', 3600.00, 0.00, 360.00, 3960.00, 'completed', '2025-09-23 11:00:14', NULL, NULL, NULL),
	(11, '1406', 1, 'delivery', NULL, NULL, NULL, 'Sachith', 3600.00, 200.00, 0.00, 3650.00, 'completed', '2025-09-23 11:18:23', NULL, 'malangama', 250.00),
	(12, '1407', 1, 'take_away', NULL, NULL, NULL, 'Sachith', 4800.00, 400.00, 0.00, 4400.00, 'completed', '2025-09-23 11:28:21', NULL, NULL, NULL),
	(13, '1408', 2, 'delivery', NULL, NULL, NULL, 'Sachith', 13200.00, 1000.00, 0.00, 12700.00, 'completed', '2025-09-23 11:50:32', NULL, 'Malangama', 500.00),
	(14, '1409', 3, 'other_credit', 'Dasun Shanaka', NULL, NULL, 'Sachith', 3600.00, 300.00, 330.00, 3630.00, 'completed', '2025-09-23 11:54:47', NULL, NULL, NULL),
	(15, '1410', 4, 'credit', 'Chamod Amakara', NULL, NULL, 'Sachith', 6000.00, 0.00, 0.00, 6000.00, 'completed', '2025-09-23 11:56:01', NULL, NULL, NULL),
	(16, '1411', 1, 'card_customer', NULL, NULL, NULL, 'Sachith', 6000.00, 0.00, 600.00, 6600.00, 'completed', '2025-09-24 15:31:32', NULL, NULL, NULL),
	(17, '1412', 1, 'other_credit', 'Test', NULL, NULL, 'Sachith', 1200.00, 0.00, 120.00, 1320.00, 'completed', '2025-09-24 15:32:51', NULL, NULL, NULL),
	(18, '1413', 1, 'foc', NULL, NULL, 'Test', 'Sachith', 4800.00, 0.00, 0.00, 4800.00, 'completed', '2025-09-24 15:33:30', NULL, NULL, NULL),
	(19, '1414', 1, 'credit', 'Test', NULL, NULL, 'Sachith', 4800.00, 0.00, 0.00, 4800.00, 'completed', '2025-09-24 15:34:04', NULL, NULL, NULL),
	(20, '1415', 1, 'cash_customer', NULL, NULL, NULL, 'Sachith', 4800.00, 500.00, 430.00, 4730.00, 'completed', '2025-09-24 15:34:55', NULL, NULL, NULL),
	(21, '1416', 1, 'take_away', NULL, NULL, NULL, 'Sachith', 6000.00, 0.00, 0.00, 6000.00, 'completed', '2025-09-24 15:35:38', NULL, NULL, NULL),
	(22, '1417', 1, 'delivery', NULL, NULL, NULL, 'Sachith', 4800.00, 0.00, 0.00, 6300.00, 'completed', '2025-09-24 15:36:19', NULL, 'Rathnapura', 1500.00),
	(23, '1418', 1, 'foc', NULL, NULL, 'Sarath Perera', 'Sachith', 4800.00, 0.00, 0.00, 0.00, 'completed', '2025-09-26 03:16:32', NULL, NULL, NULL),
	(24, '1419', 1, 'foc', NULL, NULL, 'Sarath Perera', 'Sachith', 6000.00, 0.00, 0.00, 0.00, 'completed', '2025-09-26 03:17:38', NULL, NULL, NULL),
	(25, '1420', 2, 'credit', 'Maya', NULL, NULL, 'Sachith', 4800.00, 0.00, 0.00, 4800.00, 'completed', '2025-09-26 03:18:51', NULL, NULL, NULL),
	(26, '1421', NULL, 'foc', NULL, NULL, 'Kavidu', 'Sachith', 4800.00, 0.00, 0.00, 0.00, 'completed', '2025-09-26 04:46:57', NULL, NULL, NULL),
	(27, '1422', NULL, 'credit', 'chanuka', NULL, NULL, 'Sachith', 3600.00, 0.00, 0.00, 3600.00, 'completed', '2025-09-26 04:47:47', NULL, NULL, NULL),
	(28, '1423', NULL, 'other_credit', 'Nimesh', NULL, NULL, 'Sachith', 3600.00, 0.00, 360.00, 3960.00, 'completed', '2025-09-26 04:48:31', NULL, NULL, NULL),
	(29, '1424', NULL, 'delivery', NULL, NULL, NULL, 'Sachith', 4800.00, 0.00, 0.00, 5050.00, 'completed', '2025-09-26 04:49:36', NULL, 'Malangama', 250.00),
	(30, '1425', NULL, 'cash_customer', NULL, NULL, NULL, 'Sachith', 3600.00, 1000.00, 260.00, 2860.00, 'completed', '2025-09-26 04:52:24', NULL, NULL, NULL),
	(31, '1426', 2, 'take_away', NULL, NULL, NULL, 'Sachith', 3600.00, 0.00, 0.00, 3600.00, 'completed', '2025-09-26 07:50:33', NULL, NULL, NULL),
	(33, '1427', 1, 'cash_customer', NULL, NULL, NULL, 'Sachith', 3600.00, 0.00, 360.00, 3960.00, 'completed', '2025-09-26 08:01:20', NULL, NULL, NULL),
	(34, '1428', 1, 'cash_customer', NULL, NULL, NULL, 'Sachith', 4800.00, 0.00, 480.00, 5280.00, 'completed', '2025-09-26 09:48:21', NULL, NULL, NULL),
	(35, '1429', NULL, 'cash_customer', NULL, NULL, NULL, 'Sachith', 3600.00, 0.00, 360.00, 3960.00, 'completed', '2025-09-26 10:38:59', NULL, NULL, NULL),
	(36, '1430', NULL, 'take_away', NULL, NULL, NULL, 'Sachith', 4800.00, 0.00, 0.00, 4800.00, 'completed', '2025-09-26 11:09:36', NULL, NULL, NULL),
	(37, '1431', NULL, 'foc', NULL, NULL, 'Kavidu', 'Sachith', 3600.00, 0.00, 0.00, 0.00, 'completed', '2025-10-25 05:20:45', NULL, NULL, NULL),
	(38, '1432', NULL, 'card_customer', NULL, NULL, NULL, 'Sachith', 3600.00, 0.00, 360.00, 3960.00, 'completed', '2025-10-25 05:52:19', NULL, NULL, NULL),
	(39, '1433', NULL, 'card_customer', NULL, NULL, NULL, 'Sachith', 3600.00, 0.00, 360.00, 3960.00, 'completed', '2025-10-25 06:12:05', NULL, NULL, NULL),
	(40, '1434', NULL, 'delivery', NULL, NULL, NULL, 'Sachith', 3600.00, 0.00, 0.00, 4000.00, 'completed', '2025-10-25 06:12:46', NULL, 'Ahangama', 400.00),
	(41, '1435', 2, 'credit', 'chanuka', NULL, NULL, 'Sachith', 3600.00, 400.00, 0.00, 3200.00, 'completed', '2025-10-25 06:14:54', NULL, NULL, NULL),
	(42, '1436', 1, 'cash_customer', NULL, NULL, NULL, 'Sachith', 4800.00, 0.00, 480.00, 5280.00, 'canceled', '2025-10-25 06:15:04', '2025-10-25 07:27:20', NULL, NULL),
	(43, '1437', NULL, 'cash_customer', NULL, NULL, NULL, 'Sachith', 3600.00, 0.00, 360.00, 3960.00, 'completed', '2025-10-26 07:58:46', NULL, NULL, NULL),
	(44, '1438', NULL, 'cash_staff', NULL, NULL, NULL, 'Sachith', 3900.00, 0.00, 0.00, 3900.00, 'completed', '2025-10-26 10:07:30', NULL, NULL, NULL),
	(47, '1439', NULL, 'cash_customer', NULL, NULL, NULL, 'Sachith', 5200.00, 0.00, 520.00, 5720.00, 'completed', '2025-10-26 10:35:54', NULL, NULL, NULL),
	(48, '1440', NULL, 'card_customer', NULL, NULL, NULL, 'Sachith', 5200.00, 0.00, 520.00, 5720.00, 'completed', '2025-10-26 10:37:46', NULL, NULL, NULL);

-- Dumping structure for table wedding_bliss.invoice_counter
CREATE TABLE IF NOT EXISTS `invoice_counter` (
  `id` int NOT NULL AUTO_INCREMENT,
  `last_invoice_number` int NOT NULL DEFAULT '1400',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.invoice_counter: ~0 rows (approximately)
INSERT INTO `invoice_counter` (`id`, `last_invoice_number`) VALUES
	(1, 1436);

-- Dumping structure for table wedding_bliss.invoice_items
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `item_id` int NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `invoice_items_ibfk_2` (`item_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `hggitems` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.invoice_items: ~6 rows (approximately)
INSERT INTO `invoice_items` (`id`, `invoice_id`, `item_id`, `quantity`, `unit_price`, `total_price`) VALUES
	(40, 33, 1, 3, 1200.00, 3600.00),
	(41, 41, 1, 3, 1200.00, 3600.00),
	(42, 39, 1, 3, 1200.00, 3600.00),
	(43, 36, 1, 3, 1200.00, 3600.00),
	(45, 40, 1, 4, 1200.00, 4800.00),
	(46, 43, 1, 3, 1200.00, 3600.00),
	(51, 48, 3, 4, 1300.00, 5200.00);

-- Dumping structure for table wedding_bliss.invoice_void_log
CREATE TABLE IF NOT EXISTS `invoice_void_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `invoice_number` varchar(20) NOT NULL,
  `void_reason` text NOT NULL,
  `voided_by` varchar(50) NOT NULL,
  `voided_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `invoice_id` (`invoice_id`) USING BTREE,
  CONSTRAINT `invoice_void_log_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.invoice_void_log: ~0 rows (approximately)
INSERT INTO `invoice_void_log` (`id`, `invoice_id`, `invoice_number`, `void_reason`, `voided_by`, `voided_at`) VALUES
	(1, 42, '1436', 'hi', 'Sachith', '2025-10-25 07:27:20');

-- Dumping structure for table wedding_bliss.inv_history
CREATE TABLE IF NOT EXISTS `inv_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int DEFAULT NULL,
  `location_id` int DEFAULT NULL,
  `present_date` date DEFAULT NULL,
  `last_inventory_date` date DEFAULT NULL,
  `last_inventory_qty` int DEFAULT NULL,
  `new_issue_qty` int DEFAULT NULL,
  `transfer_date` date DEFAULT NULL,
  `transfer_location_id` int DEFAULT NULL,
  `transfer_qty` int DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `return_location_id` int DEFAULT NULL,
  `return_qty` int DEFAULT NULL,
  `damage_qty` int DEFAULT NULL,
  `total_qty` int DEFAULT NULL,
  `present_qty` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.inv_history: ~14 rows (approximately)
INSERT INTO `inv_history` (`id`, `item_id`, `location_id`, `present_date`, `last_inventory_date`, `last_inventory_qty`, `new_issue_qty`, `transfer_date`, `transfer_location_id`, `transfer_qty`, `return_date`, `return_location_id`, `return_qty`, `damage_qty`, `total_qty`, `present_qty`, `created_at`) VALUES
	(1, 1, 1, '2025-12-01', '2025-11-01', 25, 3, NULL, NULL, 0, NULL, NULL, 1, 2, 27, 0, '2025-09-03 08:02:25'),
	(2, 1, 1, '2026-01-01', '2025-12-01', 0, 5, NULL, 2, 1, NULL, 2, 1, 1, 4, 8, '2025-09-03 08:16:21'),
	(3, 1, 1, '2026-02-01', '2026-01-01', 8, 3, NULL, 2, 3, NULL, 2, 2, 1, 9, 12, '2025-09-03 08:19:07'),
	(4, 1, 1, '2026-03-01', '2026-02-01', 12, 2, NULL, 2, 3, NULL, 2, 2, 1, 8, 8, '2025-09-03 08:27:47'),
	(8, 1, 1, '2020-08-31', '2025-08-03', 8, 2, NULL, 2, 3, NULL, 2, 1, 3, 3, 3, '2025-09-03 09:47:23'),
	(9, 3, 1, '2025-08-01', '2025-08-03', 2, 7, NULL, 2, 3, NULL, 2, 2, 2, 2, 2, '2025-09-03 09:47:23'),
	(10, 4, 1, '2025-08-01', '2025-08-03', 6, 0, NULL, NULL, 0, NULL, NULL, 0, 0, 6, 6, '2025-09-03 09:47:23'),
	(18, 1, 1, '2025-09-03', '2025-08-03', 6, 0, '2025-08-13', 2, 2, NULL, NULL, 0, 0, 4, 4, '2025-09-03 11:36:35'),
	(19, 3, 1, '2025-09-03', '2025-08-03', 2, 0, NULL, NULL, 0, NULL, NULL, 0, 0, 2, 2, '2025-09-03 11:36:35'),
	(20, 4, 1, '2025-09-03', '2025-08-03', 2, 0, NULL, NULL, 0, NULL, NULL, 0, 0, 2, 2, '2025-09-03 11:36:35'),
	(21, 1, 1, '2025-10-15', '2025-09-03', 4, 0, '2025-09-30', 2, 2, NULL, NULL, 0, 0, 2, 2, '2025-09-03 11:53:06'),
	(22, 3, 1, '2025-10-15', '2025-09-03', 2, 0, NULL, NULL, 0, NULL, NULL, 0, 0, 2, 2, '2025-09-03 11:53:06'),
	(23, 4, 1, '2025-10-15', '2025-09-03', 2, 0, NULL, NULL, 0, NULL, NULL, 0, 0, 2, 2, '2025-09-03 11:53:06'),
	(24, 1, 1, '2025-09-03', '2025-10-15', 2, 8, '2025-08-13', 2, 3, '2025-08-22', 2, 4, 1, 2, 2, '2025-09-03 16:06:37'),
	(25, 3, 1, '2025-09-03', '2025-10-15', 2, 4, NULL, NULL, 0, '2025-09-23', 2, 4, 1, 1, 1, '2025-09-03 16:06:37'),
	(26, 4, 1, '2025-09-03', '2025-10-15', 2, 0, NULL, NULL, 0, NULL, NULL, 0, 0, 2, 2, '2025-09-03 16:06:37');

-- Dumping structure for table wedding_bliss.inv_items
CREATE TABLE IF NOT EXISTS `inv_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `location_id` int DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `remarks` text,
  `last_inventory_date` date DEFAULT NULL,
  `last_inventory_qty` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`),
  CONSTRAINT `inv_items_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `inv_locations` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.inv_items: ~4 rows (approximately)
INSERT INTO `inv_items` (`id`, `location_id`, `name`, `type`, `remarks`, `last_inventory_date`, `last_inventory_qty`) VALUES
	(1, 1, 'Chair', 'wood', 'no', '2025-09-03', 2),
	(2, 2, 'table', 'wood', '', '2025-08-03', 0),
	(3, 1, 'Round Table', 'wooden', '', '2025-09-03', 1),
	(4, 1, 'Wall Fan', 'wall', 'used', '2025-09-03', 2);

-- Dumping structure for table wedding_bliss.inv_locations
CREATE TABLE IF NOT EXISTS `inv_locations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.inv_locations: ~2 rows (approximately)
INSERT INTO `inv_locations` (`id`, `name`) VALUES
	(2, 'Back Office'),
	(1, 'Front office');

-- Dumping structure for table wedding_bliss.issued_order_sheets
CREATE TABLE IF NOT EXISTS `issued_order_sheets` (
  `issued_order_sheet_no` int NOT NULL,
  `order_sheet_no` int NOT NULL,
  `item_id` int NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `requested_qty` int NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `issued_date` datetime NOT NULL,
  PRIMARY KEY (`issued_order_sheet_no`),
  KEY `order_sheet_no` (`order_sheet_no`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `issued_order_sheets_ibfk_1` FOREIGN KEY (`order_sheet_no`) REFERENCES `order_sheet` (`order_sheet_no`),
  CONSTRAINT `issued_order_sheets_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.issued_order_sheets: ~0 rows (approximately)

-- Dumping structure for table wedding_bliss.items
CREATE TABLE IF NOT EXISTS `items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category_id` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_code` (`item_code`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.items: ~0 rows (approximately)
INSERT INTO `items` (`id`, `item_code`, `item_name`, `category_id`, `price`, `stock`, `created_at`, `updated_at`) VALUES
	(1, 'C1', 'Chicken Fried Rice', 3, 1400.00, 1, '2025-09-23 04:39:56', '2025-10-26 07:58:46');

-- Dumping structure for table wedding_bliss.item_requests
CREATE TABLE IF NOT EXISTS `item_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_date` datetime NOT NULL,
  `requester_name` varchar(100) NOT NULL,
  `section` varchar(50) NOT NULL,
  `reason` text NOT NULL,
  `last_request_date` date DEFAULT NULL,
  `manager_id` int NOT NULL,
  `status` enum('pending','accepted','printed') NOT NULL DEFAULT 'pending',
  `approver_id` int DEFAULT NULL,
  `issued_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `manager_id` (`manager_id`),
  KEY `fk_approver_id` (`approver_id`),
  CONSTRAINT `fk_approver_id` FOREIGN KEY (`approver_id`) REFERENCES `approvers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `item_requests_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `managers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.item_requests: ~11 rows (approximately)
INSERT INTO `item_requests` (`id`, `request_date`, `requester_name`, `section`, `reason`, `last_request_date`, `manager_id`, `status`, `approver_id`, `issued_date`) VALUES
	(1, '2025-08-24 17:22:26', 'Malka Fernando', 'IT and Network', 'personal matter', '2025-08-17', 1, 'accepted', NULL, NULL),
	(2, '2025-08-24 17:26:21', 'Malka Fernando', 'IT and Network', 'personal matter', '2025-08-17', 1, 'accepted', NULL, NULL),
	(3, '2025-08-24 17:34:01', 'Pemin Shantha', 'Back office', 'noting', '2025-08-16', 1, 'accepted', NULL, NULL),
	(4, '2025-08-25 10:26:35', 'Malka Fernando', 'IT and Network', 'no', '2025-08-25', 1, 'printed', NULL, NULL),
	(5, '2025-08-25 12:04:45', 'Malka Fernando', 'IT and Network', 'no', '2025-08-25', 1, 'printed', 1, NULL),
	(6, '2025-08-25 13:18:24', 'Pemin Shantha', 'Kitchen', 'nah', '2025-08-25', 1, 'pending', 1, '2025-08-25 13:58:38'),
	(7, '2025-08-25 14:00:54', 'Malka Fernando', 'Kitchen', 'nah', '2025-08-25', 1, 'pending', 1, '2025-08-25 14:01:24'),
	(8, '2025-08-25 14:02:50', 'Malka Fernando', 'Pastry', 'no', '2025-08-25', 1, 'printed', 1, '2025-08-26 17:03:37'),
	(9, '2025-08-25 14:22:52', 'Malka Fernando', 'Pastry', 'no', '2025-08-25', 1, 'printed', 1, '2025-08-26 14:29:30'),
	(10, '2025-08-26 14:35:03', 'Mohomed', 'Back office', 'nah', '2025-08-25', 1, 'printed', 1, '2025-08-26 16:55:12'),
	(11, '2025-08-26 16:59:36', 'Malka Fernando', 'Maintenance', 'nah', '2025-08-26', 1, 'printed', 1, '2025-08-26 17:00:09');

-- Dumping structure for table wedding_bliss.item_stock
CREATE TABLE IF NOT EXISTS `item_stock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `location` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `available_quantity` decimal(10,2) NOT NULL DEFAULT '0.00',
  `last_added_quantity` decimal(10,2) NOT NULL DEFAULT '0.00',
  `last_added_date` datetime DEFAULT NULL,
  `total_quantity` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `item_stock_ibfk_1` (`item_id`),
  CONSTRAINT `item_stock_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.item_stock: ~2 rows (approximately)
INSERT INTO `item_stock` (`id`, `item_id`, `location`, `available_quantity`, `last_added_quantity`, `last_added_date`, `total_quantity`) VALUES
	(6, 6, 'Main Warehouse', 49.99, 10.00, '2025-08-26 16:43:48', 59.99),
	(7, 4, 'Main Warehouse', 19.97, 0.00, NULL, 19.97),
	(8, 1, 'Main Warehouse', 12.00, 0.00, NULL, 12.00);

-- Dumping structure for table wedding_bliss.jayamangala_gathas
CREATE TABLE IF NOT EXISTS `jayamangala_gathas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.jayamangala_gathas: ~0 rows (approximately)
INSERT INTO `jayamangala_gathas` (`id`, `name`) VALUES
	(1, 'By Customer');

-- Dumping structure for table wedding_bliss.kitchen_buffer
CREATE TABLE IF NOT EXISTS `kitchen_buffer` (
  `buffer_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `remaining_quantity` int NOT NULL DEFAULT '0',
  `usage` int NOT NULL DEFAULT '0',
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`buffer_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `kitchen_buffer_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.kitchen_buffer: ~2 rows (approximately)
INSERT INTO `kitchen_buffer` (`buffer_id`, `item_id`, `quantity`, `remaining_quantity`, `usage`, `last_updated`) VALUES
	(1, 1, 40, 30, 0, '2025-08-08 06:08:29'),
	(2, 7, 13, 8, 0, '2025-09-05 06:56:29'),
	(3, 4, 10, 2, 0, '2025-08-22 07:28:01');

-- Dumping structure for table wedding_bliss.kitchen_buffer_history
CREATE TABLE IF NOT EXISTS `kitchen_buffer_history` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `quantity_updated` int NOT NULL,
  `update_timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `kitchen_buffer_history_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.kitchen_buffer_history: ~2 rows (approximately)
INSERT INTO `kitchen_buffer_history` (`history_id`, `item_id`, `quantity_updated`, `update_timestamp`) VALUES
	(1, 7, 3, '2025-09-05 12:26:17'),
	(2, 7, 8, '2025-09-05 12:26:29');

-- Dumping structure for table wedding_bliss.kitchen_issuance_log
CREATE TABLE IF NOT EXISTS `kitchen_issuance_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `item_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `quantity` int NOT NULL,
  `unit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `issue_date` date NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `item_id` (`item_id`) USING BTREE,
  CONSTRAINT `kitchen_issuance_log_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.kitchen_issuance_log: ~0 rows (approximately)

-- Dumping structure for table wedding_bliss.kitchen_requests
CREATE TABLE IF NOT EXISTS `kitchen_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_date` date NOT NULL,
  `required_date` date NOT NULL,
  `requested_by` varchar(100) NOT NULL,
  `notes` text,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `rejection_reason` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.kitchen_requests: ~0 rows (approximately)

-- Dumping structure for table wedding_bliss.locations
CREATE TABLE IF NOT EXISTS `locations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `location_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.locations: ~0 rows (approximately)

-- Dumping structure for table wedding_bliss.logestic_users
CREATE TABLE IF NOT EXISTS `logestic_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.logestic_users: ~0 rows (approximately)
INSERT INTO `logestic_users` (`id`, `username`, `password`) VALUES
	(1, 'Sachith', '$2y$10$GJGWO8CzqiNT1ozC8U6Coe1s5ZpJLPeL6Tj0vB55s4aBb7VikpJn6');

-- Dumping structure for table wedding_bliss.logisticregistration_access
CREATE TABLE IF NOT EXISTS `logisticregistration_access` (
  `id` int NOT NULL AUTO_INCREMENT,
  `is_locked` tinyint(1) DEFAULT '1',
  `unlock_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.logisticregistration_access: ~0 rows (approximately)
INSERT INTO `logisticregistration_access` (`id`, `is_locked`, `unlock_key`, `updated_at`) VALUES
	(4, 0, '1', '2025-08-24 06:26:03');

-- Dumping structure for table wedding_bliss.logistics_grn
CREATE TABLE IF NOT EXISTS `logistics_grn` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grn_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `date` datetime NOT NULL,
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `received_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `checked_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `grn_number` (`grn_number`),
  UNIQUE KEY `grn_number_2` (`grn_number`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.logistics_grn: ~16 rows (approximately)
INSERT INTO `logistics_grn` (`id`, `grn_number`, `date`, `location`, `received_by`, `checked_by`) VALUES
	(1, 'GRN-1500', '2025-08-24 14:44:24', 'Main Warehouse', 'Sachiya', 'Test'),
	(2, 'GRN-1501', '2025-08-24 14:55:16', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(3, 'GRN-1502', '2025-08-25 08:52:47', 'Main Warehouse', '', 'Mithila'),
	(4, 'GRN-1503', '2025-08-25 09:10:30', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(5, 'GRN-1504', '2025-08-25 09:12:23', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(6, 'GRN-1505', '2025-08-25 10:39:55', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(7, 'GRN-1506', '2025-08-25 10:47:18', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(8, 'GRN-1507', '2025-08-25 13:10:44', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(9, 'GRN-1508', '2025-08-26 14:33:25', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(11, 'GRN-1509', '2025-08-26 14:52:49', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(12, 'GRN-1510', '2025-08-26 14:58:14', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(13, 'GRN-1511', '2025-08-26 15:14:49', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(14, 'GRN-1512', '2025-08-26 15:19:40', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(15, 'GRN-1513', '2025-08-26 15:51:44', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(16, 'GRN-1514', '2025-08-26 16:08:40', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(18, 'GRN-1515', '2025-08-26 16:23:55', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(19, 'GRN-1516', '2025-08-26 16:25:10', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(20, 'GRN-1517', '2025-08-26 16:41:11', 'Main Warehouse', 'Sachiya', 'Mithila'),
	(21, 'GRN-1518', '2025-08-26 16:44:04', 'Main Warehouse', 'Sachiya', 'Mithila');

-- Dumping structure for table wedding_bliss.logistics_grn_details
CREATE TABLE IF NOT EXISTS `logistics_grn_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grn_id` int NOT NULL,
  `item_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `quantity` int NOT NULL,
  `unit` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `added_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `grn_id` (`grn_id`),
  CONSTRAINT `logistics_grn_details_ibfk_1` FOREIGN KEY (`grn_id`) REFERENCES `logistics_grn` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.logistics_grn_details: ~26 rows (approximately)
INSERT INTO `logistics_grn_details` (`id`, `grn_id`, `item_name`, `quantity`, `unit`, `added_date`) VALUES
	(1, 1, 'Cocount Powder', 4, 'kg', '2025-08-26 16:34:57'),
	(2, 1, '4L Carpet Cleaner', 5, 'liter', '2025-08-26 16:34:57'),
	(3, 2, 'Cocount Powder', 3, 'kg', '2025-08-26 16:34:57'),
	(4, 2, '4L Carpet Cleaner', 5, 'liter', '2025-08-26 16:34:57'),
	(5, 3, '1 Kg Coconut Powder', 10, 'box', '2025-08-26 16:34:57'),
	(6, 3, '4L Carpet Cleaner', 2, 'liter', '2025-08-26 16:34:57'),
	(7, 4, '1 Kg Coconut Powder', 11, 'box', '2025-08-26 16:34:57'),
	(8, 4, '4L Carpet Cleaner', 13, 'liter', '2025-08-26 16:34:57'),
	(9, 5, '4L Carpet Cleaner', 2, 'liter', '2025-08-26 16:34:57'),
	(10, 5, '1 Kg Coconut Powder', 1, 'box', '2025-08-26 16:34:57'),
	(11, 6, '1 Kg Coconut Powder', 70, 'box', '2025-08-26 16:34:57'),
	(12, 6, '875g Coconut Powder', 10, 'unit', '2025-08-26 16:34:57'),
	(13, 7, 'Cocount Powder', 10, 'kg', '2025-08-26 16:34:57'),
	(14, 7, '4L Carpet Cleaner', 3, 'liter', '2025-08-26 16:34:57'),
	(15, 8, '875g Coconut Powder', 20, 'unit', '2025-08-26 16:34:57'),
	(16, 8, '1 Kg Coconut Powder', 30, 'box', '2025-08-26 16:34:57'),
	(17, 8, '4L Carpet Cleaner', 30, 'liter', '2025-08-26 16:34:57'),
	(18, 9, 'Butter 100g', 20, 'Kg', '2025-08-26 16:34:57'),
	(19, 11, 'Butter 100g', 20, 'kg', '2025-08-26 16:34:57'),
	(20, 12, 'Butter 100g', 5, 'kg', '2025-08-26 16:34:57'),
	(21, 13, 'Butter 100g', 20, 'kg', '2025-08-26 16:34:57'),
	(22, 14, 'Butter 100g', 10, 'kg', '2025-08-26 16:34:57'),
	(23, 15, 'Butter 100g', 10, 'kg', '2025-08-26 16:34:57'),
	(24, 16, 'Butter 100g', 20, 'kg', '2025-08-26 16:34:57'),
	(26, 18, 'Butter 100g', 20, 'Kg', '2025-08-26 16:34:57'),
	(27, 19, 'Butter 100g', 10, 'Kg', '2025-08-26 16:34:57'),
	(28, 20, 'Butter 100g', 20, 'Kg', '2025-08-26 16:40:57'),
	(29, 21, 'Butter 100g', 10, 'Kg', '2025-08-26 16:43:48');

-- Dumping structure for table wedding_bliss.logistics_users
CREATE TABLE IF NOT EXISTS `logistics_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.logistics_users: ~0 rows (approximately)
INSERT INTO `logistics_users` (`id`, `name`, `password`) VALUES
	(1, 'Mithila', '$2y$10$xfCN5.Yu/pD8HI3kHLAijuYE/8Lgr.FeOGNvWLBvv2aB8AQTum0Ie');

-- Dumping structure for table wedding_bliss.logistic_po_items
CREATE TABLE IF NOT EXISTS `logistic_po_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `po_id` int NOT NULL,
  `item_id` int NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `po_id` (`po_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `logistic_po_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `logistic_purchase_orders` (`id`),
  CONSTRAINT `logistic_po_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.logistic_po_items: ~19 rows (approximately)
INSERT INTO `logistic_po_items` (`id`, `po_id`, `item_id`, `quantity`, `unit`) VALUES
	(1, 1, 2, 1.00, 'box'),
	(2, 2, 2, 1.00, 'box'),
	(3, 3, 4, 4.00, 'liter'),
	(4, 4, 2, 2.00, 'box'),
	(5, 4, 4, 4.00, 'liter'),
	(6, 5, 2, 1.00, 'box'),
	(7, 5, 3, 3.00, 'unit'),
	(8, 6, 3, 1.00, 'unit'),
	(9, 7, 3, 1.00, 'unit'),
	(14, 10, 3, 3.00, 'unit'),
	(15, 10, 1, 6.00, 'kg'),
	(16, 11, 2, 3.00, 'box'),
	(17, 11, 3, 5.00, 'unit'),
	(18, 12, 2, 2.00, 'box'),
	(19, 12, 4, 4.00, 'liter'),
	(20, 13, 2, 4.00, 'box'),
	(21, 13, 4, 2.00, 'liter'),
	(22, 14, 2, 3.00, 'box'),
	(23, 15, 2, 2.00, 'box'),
	(24, 15, 4, 5.00, 'liter'),
	(25, 16, 4, 2.00, 'liter'),
	(26, 17, 4, 3.00, 'liter');

-- Dumping structure for table wedding_bliss.logistic_purchase_orders
CREATE TABLE IF NOT EXISTS `logistic_purchase_orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `po_number` int NOT NULL,
  `created_at` datetime NOT NULL,
  `supplier_id` int DEFAULT NULL,
  `confirmed_by` int DEFAULT NULL,
  `requested_by` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_number` (`po_number`),
  KEY `supplier_id` (`supplier_id`),
  KEY `confirmed_by` (`confirmed_by`),
  CONSTRAINT `logistic_purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `logistic_purchase_orders_ibfk_2` FOREIGN KEY (`confirmed_by`) REFERENCES `logestic_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.logistic_purchase_orders: ~15 rows (approximately)
INSERT INTO `logistic_purchase_orders` (`id`, `po_number`, `created_at`, `supplier_id`, `confirmed_by`, `requested_by`) VALUES
	(1, 1500, '2025-08-24 07:36:44', 1, 1, 0),
	(2, 1501, '2025-08-24 07:43:07', 1, 1, 0),
	(3, 1502, '2025-08-24 07:49:08', 1, 1, 0),
	(4, 1503, '2025-08-24 12:17:59', 1, 1, 8),
	(5, 1504, '2025-08-24 12:36:30', 1, 1, 8),
	(6, 1505, '2025-08-24 12:37:50', 1, 1, 8),
	(7, 1506, '2025-08-24 12:42:43', 1, 1, 8),
	(10, 1507, '2025-08-24 13:04:48', 1, 1, 8),
	(11, 1508, '2025-08-24 13:07:53', 1, 1, 8),
	(12, 1509, '2025-08-24 13:15:45', 1, 1, 8),
	(13, 1510, '2025-08-24 13:19:53', 1, 1, 8),
	(14, 1511, '2025-08-24 13:21:07', 1, 1, 8),
	(15, 1512, '2025-08-24 13:26:25', 1, 1, 8),
	(16, 1513, '2025-08-24 13:27:53', 1, 1, 8),
	(17, 1514, '2025-08-24 13:28:39', 1, 1, 8);

-- Dumping structure for table wedding_bliss.logi_users
CREATE TABLE IF NOT EXISTS `logi_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username` (`username`) USING BTREE,
  UNIQUE KEY `email` (`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.logi_users: ~0 rows (approximately)
INSERT INTO `logi_users` (`id`, `username`, `password`, `email`, `is_admin`, `created_at`) VALUES
	(8, 'Sachiya', '$2y$10$qNWqbEVKAxQb.YWEV0ZI4OmOSlFMpGp/HFWY2djCeMu2qcQPe3t5a', NULL, 0, '2025-08-24 06:25:19'),
	(9, 'sachith', '$2y$10$jtGwo0BOW.ntEQUGinjE9e2s4BUmhX72uMPPmsU1JiIkeukI74./a', NULL, 0, '2025-09-03 17:04:20');

-- Dumping structure for table wedding_bliss.mainkitchenregistration_access
CREATE TABLE IF NOT EXISTS `mainkitchenregistration_access` (
  `id` int NOT NULL AUTO_INCREMENT,
  `is_locked` tinyint(1) DEFAULT '1',
  `unlock_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.mainkitchenregistration_access: ~0 rows (approximately)
INSERT INTO `mainkitchenregistration_access` (`id`, `is_locked`, `unlock_key`, `updated_at`) VALUES
	(2, 0, '1', '2025-08-12 04:59:09');

-- Dumping structure for table wedding_bliss.mainkitchen_users
CREATE TABLE IF NOT EXISTS `mainkitchen_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username` (`username`) USING BTREE,
  UNIQUE KEY `email` (`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.mainkitchen_users: ~0 rows (approximately)
INSERT INTO `mainkitchen_users` (`id`, `username`, `password`, `email`, `is_admin`, `created_at`) VALUES
	(6, 'Yasith', '$2y$10$ZXUfs14L3nqufYaYQ62mtOrZU3baWofS2bgOluk8nquEjf9yNJ.4m', NULL, 0, '2025-08-12 04:58:11');

-- Dumping structure for table wedding_bliss.managers
CREATE TABLE IF NOT EXISTS `managers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.managers: ~0 rows (approximately)
INSERT INTO `managers` (`id`, `username`, `password`) VALUES
	(1, 'Sachith', '$2y$10$ZFypGwIc2mdk1kS.aGZWNebnugAnH.KOC1.YvVRCxQwPDkx67o.2a');

-- Dumping structure for table wedding_bliss.meal_plans
CREATE TABLE IF NOT EXISTS `meal_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.meal_plans: ~4 rows (approximately)
INSERT INTO `meal_plans` (`id`, `name`) VALUES
	(1, 'Room Only'),
	(2, 'Bed & Breakfast'),
	(3, 'Half Board'),
	(4, 'Full Board');

-- Dumping structure for table wedding_bliss.menus
CREATE TABLE IF NOT EXISTS `menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.menus: ~6 rows (approximately)
INSERT INTO `menus` (`id`, `name`) VALUES
	(1, 'Rs.3450'),
	(2, 'Rs.2500'),
	(3, '1235'),
	(4, 'Snowy'),
	(5, '4000'),
	(6, 'Rs.6500');

-- Dumping structure for table wedding_bliss.monthly_reports
CREATE TABLE IF NOT EXISTS `monthly_reports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `generated_at` datetime DEFAULT NULL,
  `report_month` int DEFAULT NULL,
  `report_year` int DEFAULT NULL,
  `total_items` int DEFAULT NULL,
  `total_stock_value` decimal(15,2) DEFAULT NULL,
  `total_shortage_value` decimal(15,2) DEFAULT NULL,
  `shortage_items` int DEFAULT NULL,
  `report_data` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_year_month` (`report_year`,`report_month`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.monthly_reports: ~2 rows (approximately)
INSERT INTO `monthly_reports` (`id`, `generated_at`, `report_month`, `report_year`, `total_items`, `total_stock_value`, `total_shortage_value`, `shortage_items`, `report_data`) VALUES
	(11, '2025-09-03 08:30:33', 8, 2025, 7, 396.50, 39058.50, 3, '[{"short": -27, "balance": -27, "item_name": "1 Kg Coconut Powder", "unit_type": "box", "unit_price": 0, "total_issue": 30, "new_received": 3, "shorted_price": 0, "last_audit_qty": 0, "last_audit_date": "N/A", "present_audit_qty": 0, "present_audit_date": "N/A"}, {"short": 4959.07, "balance": 4959.2, "item_name": "4L Carpet Cleaner", "unit_type": "l", "unit_price": 2750, "total_issue": 50.01, "new_received": 5009.01, "shorted_price": 13637442.5, "last_audit_qty": "0.2000", "last_audit_date": "2025-08-22", "present_audit_qty": "0.1300", "present_audit_date": "2025-08-30"}, {"short": 1, "balance": 1, "item_name": "875g Coconut Powder", "unit_type": "unit", "unit_price": 0, "total_issue": 0, "new_received": 1, "shorted_price": 0, "last_audit_qty": 0, "last_audit_date": "N/A", "present_audit_qty": 0, "present_audit_date": "N/A"}, {"short": -30.045, "balance": -30.015, "item_name": "Butter 100g", "unit_type": "kg", "unit_price": 1300, "total_issue": 30.015, "new_received": 0, "shorted_price": -39058.5, "last_audit_qty": 0, "last_audit_date": "N/A", "present_audit_qty": "0.0300", "present_audit_date": "2025-08-29"}, {"short": -20, "balance": 0, "item_name": "Butter 250g", "unit_type": "g", "unit_price": 0, "total_issue": 0, "new_received": 0, "shorted_price": 0, "last_audit_qty": 0, "last_audit_date": "N/A", "present_audit_qty": "20.0000", "present_audit_date": "2025-08-29"}, {"short": 1, "balance": 1, "item_name": "Cocount Powder", "unit_type": "kg", "unit_price": "1200.00", "total_issue": 0, "new_received": 1, "shorted_price": 1200, "last_audit_qty": 0, "last_audit_date": "N/A", "present_audit_qty": 0, "present_audit_date": "N/A"}, {"short": 0, "balance": 0, "item_name": "Milk 100 ml", "unit_type": "ml", "unit_price": 0, "total_issue": 0, "new_received": 0, "shorted_price": 0, "last_audit_qty": 0, "last_audit_date": "N/A", "present_audit_qty": 0, "present_audit_date": "N/A"}]'),
	(12, '2025-09-03 08:32:46', 9, 2025, 7, 396.50, 39.00, 2, '[{"short": 0, "balance": 0, "item_name": "1 Kg Coconut Powder", "unit_type": "box", "unit_price": 0, "total_issue": 0, "new_received": 0, "shorted_price": 0, "last_audit_qty": 0, "last_audit_date": "N/A", "present_audit_qty": 0, "present_audit_date": "N/A"}, {"short": 0.07, "balance": 0.2, "item_name": "4L Carpet Cleaner", "unit_type": "l", "unit_price": 2750, "total_issue": 0, "new_received": 0, "shorted_price": 192.50000000000003, "last_audit_qty": "0.2000", "last_audit_date": "2025-08-22", "present_audit_qty": "0.1300", "present_audit_date": "2025-08-30"}, {"short": 0, "balance": 0, "item_name": "875g Coconut Powder", "unit_type": "unit", "unit_price": 0, "total_issue": 0, "new_received": 0, "shorted_price": 0, "last_audit_qty": 0, "last_audit_date": "N/A", "present_audit_qty": 0, "present_audit_date": "N/A"}, {"short": -0.03, "balance": 0, "item_name": "Butter 100g", "unit_type": "kg", "unit_price": 1300, "total_issue": 0, "new_received": 0, "shorted_price": -39, "last_audit_qty": 0, "last_audit_date": "N/A", "present_audit_qty": "0.0300", "present_audit_date": "2025-08-29"}, {"short": -20, "balance": 0, "item_name": "Butter 250g", "unit_type": "g", "unit_price": 0, "total_issue": 0, "new_received": 0, "shorted_price": 0, "last_audit_qty": 0, "last_audit_date": "N/A", "present_audit_qty": "20.0000", "present_audit_date": "2025-08-29"}, {"short": 0, "balance": 0, "item_name": "Cocount Powder", "unit_type": "kg", "unit_price": "1200.00", "total_issue": 0, "new_received": 0, "shorted_price": 0, "last_audit_qty": 0, "last_audit_date": "N/A", "present_audit_qty": 0, "present_audit_date": "N/A"}, {"short": 0, "balance": 0, "item_name": "Milk 100 ml", "unit_type": "ml", "unit_price": 0, "total_issue": 0, "new_received": 0, "shorted_price": 0, "last_audit_qty": 0, "last_audit_date": "N/A", "present_audit_qty": 0, "present_audit_date": "N/A"}]');

-- Dumping structure for table wedding_bliss.music_types
CREATE TABLE IF NOT EXISTS `music_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.music_types: ~2 rows (approximately)
INSERT INTO `music_types` (`id`, `name`) VALUES
	(1, 'Dj By Customer'),
	(2, 'Dj by ara'),
	(3, 'Dj By Shanaka');

-- Dumping structure for table wedding_bliss.order_sheet
CREATE TABLE IF NOT EXISTS `order_sheet` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `requested_qty` decimal(10,2) DEFAULT NULL,
  `issued_qty` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','issued') DEFAULT 'pending',
  `request_date` datetime NOT NULL,
  `issued_date` datetime DEFAULT NULL,
  `order_sheet_no` int NOT NULL,
  `responsible_id` int DEFAULT NULL,
  `function_type` varchar(50) NOT NULL DEFAULT '',
  `function_date` date DEFAULT NULL,
  `day_night` enum('Day','Night') DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_sheet_no` (`order_sheet_no`,`item_id`),
  KEY `item_id` (`item_id`),
  KEY `responsible_id` (`responsible_id`),
  CONSTRAINT `order_sheet_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`),
  CONSTRAINT `order_sheet_ibfk_2` FOREIGN KEY (`responsible_id`) REFERENCES `responsible` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.order_sheet: ~102 rows (approximately)
INSERT INTO `order_sheet` (`order_id`, `item_id`, `requested_qty`, `issued_qty`, `status`, `request_date`, `issued_date`, `order_sheet_no`, `responsible_id`, `function_type`, `function_date`, `day_night`) VALUES
	(1, 9, 4.00, 0.00, 'issued', '2025-07-28 08:13:42', NULL, 1111, 1, '', NULL, 'Day'),
	(2, 7, 2.00, 0.00, 'issued', '2025-07-28 08:13:42', NULL, 1111, 1, '', NULL, 'Day'),
	(3, 9, 110.00, 0.00, 'issued', '2025-07-28 08:18:43', NULL, 1112, 1, '', NULL, 'Day'),
	(4, 7, 2.00, 0.00, 'issued', '2025-07-28 08:18:43', NULL, 1112, 1, '', NULL, 'Day'),
	(5, 4, 2.00, 0.00, 'issued', '2025-07-28 08:29:19', NULL, 1113, 1, '', NULL, 'Day'),
	(6, 3, 2.00, 2.00, 'issued', '2025-07-28 08:31:06', NULL, 1114, 1, '', NULL, 'Day'),
	(7, 3, 2.00, 2.00, 'issued', '2025-07-28 09:38:49', NULL, 1115, 1, '', NULL, 'Day'),
	(8, 7, 300.00, 0.00, 'issued', '2025-07-28 09:39:46', NULL, 1116, 1, '', NULL, 'Day'),
	(9, 9, 4.00, 0.00, 'issued', '2025-07-28 09:39:46', NULL, 1116, 1, '', NULL, 'Day'),
	(10, 4, 1.00, 0.00, 'issued', '2025-07-28 09:39:46', NULL, 1116, 1, '', NULL, 'Day'),
	(11, 7, 300.00, 0.00, 'issued', '2025-07-28 09:50:54', NULL, 1117, 1, '', NULL, 'Day'),
	(12, 7, 250.00, 0.00, 'issued', '2025-07-28 09:53:08', NULL, 1118, 1, '', NULL, 'Day'),
	(13, 7, 900.00, 0.00, 'issued', '2025-07-28 10:10:54', NULL, 1119, 1, '', NULL, 'Day'),
	(14, 7, 150.00, 0.00, 'issued', '2025-07-28 10:26:50', NULL, 1120, 1, '', NULL, 'Day'),
	(15, 7, 50.00, 50.00, 'issued', '2025-07-28 10:48:24', NULL, 1121, 1, '', NULL, 'Day'),
	(16, 7, 50.00, 50.00, 'issued', '2025-07-28 10:49:16', NULL, 1122, 1, '', NULL, 'Day'),
	(17, 7, 50.00, 0.00, 'issued', '2025-07-28 10:55:41', NULL, 1123, 1, '', NULL, 'Day'),
	(18, 7, 50.00, 0.00, 'issued', '2025-07-28 11:11:24', NULL, 1124, 1, '', NULL, 'Day'),
	(19, 7, 50.00, 0.00, 'issued', '2025-07-28 11:21:04', NULL, 1125, 1, '', NULL, 'Day'),
	(20, 7, 50.00, 0.00, 'issued', '2025-07-28 11:43:00', NULL, 1126, 1, '', NULL, 'Day'),
	(21, 7, 1300.00, 1300.00, 'issued', '2025-07-28 11:51:48', NULL, 1127, 1, '', NULL, 'Day'),
	(22, 7, 400.00, 0.00, 'issued', '2025-07-28 11:54:18', NULL, 1128, 1, '', NULL, 'Day'),
	(23, 7, 50.00, 0.00, 'issued', '2025-07-28 12:04:56', NULL, 1129, 1, '', NULL, 'Day'),
	(24, 7, 320.00, 0.00, 'issued', '2025-07-28 12:26:33', NULL, 1130, 1, '', NULL, 'Day'),
	(25, 7, 60.00, 0.00, 'issued', '2025-07-28 13:17:39', NULL, 1131, 1, '', NULL, 'Day'),
	(26, 7, 5.00, 0.00, 'issued', '2025-07-28 15:39:27', NULL, 1132, 1, '', NULL, 'Day'),
	(27, 1, 1.00, 0.00, 'issued', '2025-07-28 16:20:48', NULL, 1133, 1, '', NULL, 'Day'),
	(28, 7, 3.00, 0.00, 'issued', '2025-07-28 16:20:48', NULL, 1133, 1, '', NULL, 'Day'),
	(29, 7, 45.00, 0.00, 'issued', '2025-07-29 15:39:53', NULL, 1134, 1, '', NULL, 'Day'),
	(30, 9, 20.00, 0.00, 'issued', '2025-07-29 15:39:53', NULL, 1134, 1, '', NULL, 'Day'),
	(31, 7, 800.00, 800.00, 'issued', '2025-07-30 12:20:45', NULL, 1135, 1, '', NULL, 'Day'),
	(32, 7, 300.00, 300.00, 'issued', '2025-07-30 12:22:45', NULL, 1136, 1, '', NULL, 'Day'),
	(33, 7, 20.00, 20.00, 'issued', '2025-08-04 13:53:28', NULL, 1137, 1, '', NULL, 'Day'),
	(34, 9, 21.00, 21.00, 'issued', '2025-08-05 10:13:19', NULL, 1138, 1, 'Birthday', NULL, 'Day'),
	(35, 7, 12.00, 12.00, 'issued', '2025-08-05 10:13:19', NULL, 1138, 1, 'Birthday', NULL, 'Day'),
	(36, 9, 12.00, 12.00, 'issued', '2025-08-05 10:16:13', NULL, 1139, 1, 'Sapthapadhi Function', NULL, 'Day'),
	(37, 7, 21.00, 21.00, 'issued', '2025-08-05 10:16:13', NULL, 1139, 1, 'Sapthapadhi Function', NULL, 'Day'),
	(38, 7, 34.00, 34.00, 'issued', '2025-08-05 10:22:17', NULL, 1140, 1, 'HGG Staff Meal', NULL, 'Day'),
	(39, 9, 3.00, 3.00, 'issued', '2025-08-05 11:13:31', NULL, 1141, 1, 'Sapthapadhi Function', NULL, 'Day'),
	(40, 9, 2.00, 2.00, 'issued', '2025-08-05 11:37:24', NULL, 1142, 1, 'HGG Staff Meal', NULL, 'Day'),
	(41, 7, 12.00, 12.00, 'issued', '2025-08-05 12:37:15', NULL, 1143, 1, 'Sapthapadhi Function', NULL, 'Day'),
	(42, 7, 13.00, 13.00, 'issued', '2025-08-05 12:49:57', NULL, 1144, 1, 'Sapthapadhi Function', NULL, 'Day'),
	(43, 7, 5.00, 5.00, 'issued', '2025-08-05 13:36:26', NULL, 1145, 1, 'HGG Staff Meal', NULL, 'Day'),
	(44, 7, 4.00, 4.00, 'issued', '2025-08-05 13:44:26', NULL, 1146, 1, 'Sapthapadhi Function', NULL, 'Day'),
	(45, 7, 4.00, 4.00, 'issued', '2025-08-05 13:57:49', NULL, 1147, 1, 'Sapthapadhi Function', NULL, 'Day'),
	(46, 7, 5.00, 5.00, 'issued', '2025-08-05 14:06:42', NULL, 1148, 1, 'Sapthapadhi Function', NULL, 'Day'),
	(47, 1, 1.00, 1.00, 'issued', '2025-08-05 14:08:24', NULL, 1149, 1, 'HGG Function', NULL, 'Day'),
	(48, 7, 3.00, 3.00, 'issued', '2025-08-05 14:48:13', NULL, 1150, 1, 'Sapthapadhi Function', NULL, 'Day'),
	(49, 7, 34.00, 34.00, 'issued', '2025-08-05 15:01:33', NULL, 1151, 1, 'Sapthapadhi Function', NULL, 'Day'),
	(50, 7, 4.00, 4.00, 'issued', '2025-08-05 15:07:15', NULL, 1152, 1, 'Sapthapadhi Function', NULL, 'Day'),
	(51, 1, 1.00, 1.00, 'issued', '2025-08-05 15:07:31', NULL, 1153, 1, 'HGG Staff Meal', NULL, 'Day'),
	(52, 7, 11.00, 11.00, 'issued', '2025-08-05 15:24:07', NULL, 1154, 1, 'Sapthapadhi Function', NULL, 'Day'),
	(53, 7, 4.00, 4.00, 'issued', '2025-08-05 15:29:18', NULL, 1155, 1, 'Sapthapadhi Function', NULL, 'Day'),
	(54, 7, 8.00, 8.00, 'issued', '2025-08-05 16:07:20', NULL, 1156, 1, 'Sapthapadhi Function', NULL, 'Day'),
	(55, 7, 4.00, 4.00, 'issued', '2025-08-05 16:28:32', NULL, 1157, 1, 'Sapthapadhi Function', NULL, 'Day'),
	(56, 7, 29.00, 29.00, 'issued', '2025-08-06 08:34:43', NULL, 1158, 1, 'Sapthapadhi Function', '2025-08-06', 'Day'),
	(57, 7, 2.00, 2.00, 'issued', '2025-08-06 08:34:59', NULL, 1159, 1, 'HGG Staff Meal', '2025-08-06', 'Day'),
	(58, 7, 2.00, 0.00, 'issued', '2025-08-06 09:22:04', NULL, 1160, 1, 'HGG Banquet Hall Function', '2025-08-19', 'Day'),
	(59, 7, 2.00, 0.00, 'issued', '2025-08-06 11:34:52', NULL, 1161, 1, 'HGG Staff Meal', '2025-08-07', NULL),
	(60, 1, 1.00, 1.00, 'issued', '2025-08-16 11:35:17', NULL, 1162, 1, 'HGG Banquet Hall Function', '2025-08-22', 'Day'),
	(61, 7, 40.00, 0.00, 'issued', '2025-08-16 14:55:51', NULL, 1163, 1, 'HGG Ballroom Function', '2025-08-07', 'Day'),
	(62, 7, 20.00, 10.00, 'issued', '2025-08-08 14:47:16', NULL, 1164, 2, 'HGG Function', '2025-08-19', 'Day'),
	(63, 1, 4.00, 2.00, 'issued', '2025-08-08 14:47:16', NULL, 1164, 2, 'HGG Function', '2025-08-19', 'Day'),
	(64, 4, 12.00, 8.00, 'issued', '2025-08-08 14:47:16', NULL, 1164, 2, 'HGG Function', '2025-08-19', 'Day'),
	(65, 7, 12.00, 2.00, 'issued', '2025-08-09 11:36:45', NULL, 1165, 2, 'HGG Function', '2025-08-19', 'Day'),
	(66, 7, 11.00, 1.00, 'issued', '2025-08-09 11:38:39', NULL, 1166, 2, 'HGG Banquet Hall Function', '2025-08-19', 'Night'),
	(67, 4, 13.00, 9.00, 'issued', '2025-08-09 11:38:39', NULL, 1166, 2, 'HGG Banquet Hall Function', '2025-08-19', 'Night'),
	(68, 1, 4.00, 2.00, 'issued', '2025-08-09 11:38:39', NULL, 1166, 2, 'HGG Banquet Hall Function', '2025-08-19', 'Night'),
	(69, 7, 24.00, 14.00, 'issued', '2025-08-10 08:14:10', NULL, 1167, 2, 'HGG Orchid Hall Function', '2025-08-25', 'Day'),
	(70, 4, 12.00, 8.00, 'issued', '2025-08-10 08:14:10', NULL, 1167, 2, 'HGG Orchid Hall Function', '2025-08-25', 'Day'),
	(71, 7, 11.00, 1.00, 'issued', '2025-08-10 08:16:36', NULL, 1168, 2, 'HGG Ballroom Function', '2025-08-25', 'Day'),
	(72, 4, 12.00, 8.00, 'issued', '2025-08-10 08:16:36', NULL, 1168, 2, 'HGG Ballroom Function', '2025-08-25', 'Day'),
	(73, 7, 23.00, 13.00, 'issued', '2025-08-10 16:12:23', NULL, 1169, 2, 'HGG Banquet Hall Function', '2025-08-28', 'Night'),
	(74, 4, 12.00, 8.00, 'issued', '2025-08-10 16:12:23', NULL, 1169, 2, 'HGG Banquet Hall Function', '2025-08-28', 'Night'),
	(75, 7, 11.00, 11.00, 'issued', '2025-08-10 16:15:37', NULL, 1170, 2, 'HGG Ballroom Function', '2025-08-28', 'Day'),
	(76, 4, 23.00, 23.00, 'issued', '2025-08-10 16:15:37', NULL, 1170, 2, 'HGG Ballroom Function', '2025-08-28', 'Day'),
	(77, 7, 21.00, 21.00, 'issued', '2025-08-10 16:17:39', NULL, 1171, 2, 'HGG Orchid Hall Function', '2025-08-29', 'Night'),
	(78, 4, 4.00, 4.00, 'issued', '2025-08-10 16:17:39', NULL, 1171, 2, 'HGG Orchid Hall Function', '2025-08-29', 'Night'),
	(79, 7, 21.00, 21.00, 'issued', '2025-08-10 16:18:29', NULL, 1172, 2, 'HGG Ballroom Function', '2025-08-29', 'Day'),
	(80, 4, 5.00, 5.00, 'issued', '2025-08-10 16:18:29', NULL, 1172, 2, 'HGG Ballroom Function', '2025-08-29', 'Day'),
	(81, 7, 12.00, 2.00, 'issued', '2025-08-10 16:24:20', NULL, 1173, 2, 'HGG Orchid Hall Function', '2025-08-30', 'Day'),
	(82, 4, 3.00, 0.00, 'issued', '2025-08-10 16:24:20', NULL, 1173, 2, 'HGG Orchid Hall Function', '2025-08-30', 'Day'),
	(83, 7, 5.00, 5.00, 'issued', '2025-08-10 16:25:17', NULL, 1174, 2, 'Outdoor Function', '2025-08-30', 'Day'),
	(84, 4, 8.00, 7.00, 'issued', '2025-08-10 16:25:17', NULL, 1174, 2, 'Outdoor Function', '2025-08-30', 'Day'),
	(85, 7, 8.00, 3.00, 'issued', '2025-08-10 16:43:07', NULL, 1175, 2, 'HGG Function', '2025-08-17', 'Day'),
	(86, 4, 2.00, 0.00, 'issued', '2025-08-10 16:43:07', NULL, 1175, 2, 'HGG Function', '2025-08-17', 'Day'),
	(87, 7, 11.00, 0.00, 'issued', '2025-08-10 16:44:03', NULL, 1176, 2, 'HGG Orchid Hall Function', '2025-08-17', 'Day'),
	(88, 4, 4.00, 4.00, 'issued', '2025-08-10 16:44:03', NULL, 1176, 2, 'HGG Orchid Hall Function', '2025-08-17', 'Day'),
	(89, 7, 2.00, 2.00, 'issued', '2025-08-11 09:53:02', NULL, 1177, 2, 'Sapthapadhi Function', '2025-08-28', 'Day'),
	(90, 4, 4.00, 4.00, 'issued', '2025-08-11 09:53:02', NULL, 1177, 2, 'Sapthapadhi Function', '2025-08-28', 'Day'),
	(91, 7, 3.00, 3.00, 'issued', '2025-08-11 10:17:07', NULL, 1178, 2, 'HGG Function', '2025-08-28', 'Night'),
	(92, 4, 4.00, 4.00, 'issued', '2025-08-11 10:17:07', NULL, 1178, 2, 'HGG Function', '2025-08-28', 'Night'),
	(93, 7, 11.00, 9.00, 'issued', '2025-08-11 10:36:04', NULL, 1179, 2, 'HGG Orchid Hall Function', '2025-08-20', 'Day'),
	(94, 4, 15.00, 13.00, 'issued', '2025-08-11 10:36:04', NULL, 1179, 2, 'HGG Orchid Hall Function', '2025-08-20', 'Day'),
	(95, 7, 1.00, 1.00, 'issued', '2025-08-11 15:25:09', NULL, 1180, 2, 'HGG Function', '2025-08-20', 'Day'),
	(96, 4, 7.00, 7.00, 'issued', '2025-08-11 15:25:09', NULL, 1180, 2, 'HGG Function', '2025-08-20', 'Day'),
	(97, 7, 12.00, 12.00, 'issued', '2025-08-11 15:27:02', NULL, 1181, 2, 'Outdoor Function', '2025-08-20', 'Day'),
	(98, 9, 5.00, 5.00, 'issued', '2025-08-11 15:27:02', NULL, 1181, 2, 'Outdoor Function', '2025-08-20', 'Day'),
	(99, 7, 12.00, 12.00, 'issued', '2025-08-11 15:29:45', NULL, 1182, 2, 'HGG Banquet Hall Function', '2025-08-21', 'Day'),
	(100, 4, 11.00, 9.00, 'issued', '2025-08-11 15:29:45', NULL, 1182, 2, 'HGG Banquet Hall Function', '2025-08-21', 'Day'),
	(101, 7, 8.00, 8.00, 'issued', '2025-08-11 15:33:58', NULL, 1183, 2, 'Outdoor Function', '2025-08-31', 'Night'),
	(102, 4, 1.00, 1.00, 'issued', '2025-08-11 15:33:58', NULL, 1183, 2, 'Outdoor Function', '2025-08-31', 'Night'),
	(103, 7, 5.00, 3.00, 'issued', '2025-08-11 16:12:35', NULL, 1184, 2, 'HGG Orchid Hall Function', '2025-09-01', 'Day'),
	(104, 4, 2.00, 2.00, 'issued', '2025-08-11 16:12:35', NULL, 1184, 2, 'HGG Orchid Hall Function', '2025-09-01', 'Day'),
	(105, 7, 2.00, 2.00, 'issued', '2025-08-11 16:13:19', NULL, 1185, 2, 'HGG Banquet Hall Function', '2025-09-01', 'Night'),
	(106, 4, 2.00, 2.00, 'issued', '2025-08-11 16:13:19', NULL, 1185, 2, 'HGG Banquet Hall Function', '2025-09-01', 'Night'),
	(107, 7, 1.00, 1.00, 'issued', '2025-08-11 16:13:54', NULL, 1186, 2, 'Sapthapadhi Function', '2025-09-02', 'Night'),
	(108, 7, 4.00, 4.00, 'issued', '2025-08-22 09:13:39', NULL, 1187, 2, 'HGG Red Hall Function', '2025-08-22', 'Day'),
	(109, 4, 2.00, 1.00, 'issued', '2025-08-22 09:13:39', NULL, 1187, 2, 'HGG Red Hall Function', '2025-08-22', 'Day');

-- Dumping structure for table wedding_bliss.order_sheet_counter
CREATE TABLE IF NOT EXISTS `order_sheet_counter` (
  `id` int NOT NULL,
  `last_order_sheet_no` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.order_sheet_counter: ~0 rows (approximately)
INSERT INTO `order_sheet_counter` (`id`, `last_order_sheet_no`) VALUES
	(1, 1187);

-- Dumping structure for table wedding_bliss.payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_reference` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `invoice_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `contact_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `whatsapp_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `rate_per_plate` decimal(10,2) DEFAULT NULL,
  `additional_plate_rate` decimal(10,2) DEFAULT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `value_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `pending_amount` decimal(10,2) NOT NULL,
  `no_of_pax` int DEFAULT NULL,
  `issued_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `invoice_number` (`invoice_number`) USING BTREE,
  KEY `booking_reference` (`booking_reference`) USING BTREE,
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_reference`) REFERENCES `wedding_bookings` (`booking_reference`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.payments: ~18 rows (approximately)
INSERT INTO `payments` (`id`, `booking_reference`, `invoice_number`, `contact_no`, `whatsapp_no`, `email`, `rate_per_plate`, `additional_plate_rate`, `remarks`, `value_type`, `total_amount`, `payment_type`, `payment_amount`, `pending_amount`, `no_of_pax`, `issued_by`, `payment_date`) VALUES
	(23, '8425', 'INV-1416', '0725876139 / 0706773588', '0741773588', 'udarasachith41@gmail.com', 1450.00, 1300.00, 'wedding hall', 'Total Value', 1072500.00, 'Advance Payment', 1000000.00, 72500.00, 390, 'Sachith', '2025-06-26 05:58:15'),
	(24, '8425', 'INV-1417', '0725876139 / 0706773589', '0725876139', 'udarasachith41@gmail.com', 1400.00, 0.00, 'wedding halle', 'Total Value', 546000.00, 'Advance Payment', 500000.00, 46000.00, 390, 'Sachith', '2025-06-26 06:06:43'),
	(25, '8425', 'INV-1418', '0725876139 / 0706773588', '0734256718', 'udarasachith41@gmail.com', 1340.00, 2000.00, 'Hall', 'Total Value', 1302600.00, 'Advance Payment', 1300000.00, 2600.00, 390, 'Sachith', '2025-06-26 06:23:06'),
	(26, '8425', 'INV-1419', '0725876139 / 0706773589', '0725876139', 'udarasachith41@gmail.com', 1240.00, 2400.00, 'wedding hall', 'Total Value', 483600.00, 'Balance Payment', 340000.00, 143600.00, 390, 'Sachith', '2025-06-26 06:38:20'),
	(27, '8425', 'INV-1420', '0725876139 / 0706773589', '0725876139', 'udarasachith41@gmail.com', 1240.00, 2400.00, 'wedding hall', 'Total Value', 483600.00, 'Balance Payment', 340000.00, 143600.00, 390, 'Sachith', '2025-06-26 06:38:22'),
	(28, '8425', 'INV-1421', '0725876139 / 0706773589', '0725876139', 'udarasachith41@gmail.com', 1240.00, 2400.00, 'wedding hall', 'Total Value', 483600.00, 'Balance Payment', 340000.00, 143600.00, 390, 'Sachith', '2025-06-26 06:38:27'),
	(29, '8425', 'INV-1422', '0725876139 / 0706773589', '0725876139', 'udarasachith41@gmail.com', 1240.00, 2400.00, 'wedding hall', 'Total Value', 483600.00, 'Balance Payment', 340000.00, 143600.00, 390, 'Sachith', '2025-06-26 06:38:28'),
	(30, '8425', 'INV-1423', '0725876139 / 0706773589', '0725876139', 'udarasachith41@gmail.com', 1240.00, 2400.00, 'wedding hall', 'Total Value', 483600.00, 'Balance Payment', 340000.00, 143600.00, 390, 'Sachith', '2025-06-26 06:38:28'),
	(31, '8425', 'INV-1424', '0725876139 / 0706773589', '0725876139', 'udarasachith41@gmail.com', 1240.00, 2400.00, 'wedding hall', 'Total Value', 483600.00, 'Balance Payment', 340000.00, 143600.00, 390, 'Sachith', '2025-06-26 06:38:30'),
	(32, '8425', 'INV-1425', '0725876139 / 0706773587', '0725876139', 'udarasachith41@gmail.com', 1240.00, 1500.00, 'Hall', 'Total Value', 1068600.00, 'Advance Payment', 490000.00, 578600.00, 390, 'Sachith', '2025-06-26 06:43:22'),
	(33, '8425', 'INV-1426', '0725876139 / 0706773582', '0745321780', 'j@gmail.com', 1200.00, 1500.00, 'Hall', 'Total Value', 1053000.00, 'Advance Payment', 145000.00, 908000.00, 390, 'Sachith', '2025-06-26 06:48:42'),
	(34, '8425', 'INV-1427', '0725876139 / 0706773588', '0765432189', 'udarasachith41@gmail.com', 1400.00, 1230.00, 'Hall', 'Total Value', 1025700.00, 'Advance Payment', 234000.00, 791700.00, 390, 'Sachith', '2025-06-26 07:00:33'),
	(35, '8425', 'INV-1428', '0725876139 / 0706773582', '0432318675', 'udarasachith41@gmail.com', 1090.00, 1200.00, 'Hall', 'Total Value', 893100.00, 'Advance Payment', 100000.00, 793100.00, 390, 'Sachith', '2025-06-26 07:16:50'),
	(36, '8425', 'INV-1429', '0725876139 / 0706773589', '0543217895', 'udarasachith41@gmail.com', 2340.00, 1560.00, 'Hall', 'Total Value', 1521000.00, 'Advance Payment', 230000.00, 1291000.00, 390, 'Sachith', '2025-06-26 07:29:47'),
	(37, '8423', 'INV-1430', '0725876139 / 085432789', '123456789', 'udarasachith49@gmail.com', 1450.00, 1500.00, 'Hall ', 'Total Value', 1131000.00, 'Balance Payment', 789000.00, 342000.00, 780, 'Sachith', '2025-06-26 07:48:26'),
	(38, '8425', 'INV-1431', '0725876139 / 0706773581', '123456789', 'udarasachith41@gmail.com', 1235.00, 2345.00, 'null', 'Subtotal', 481650.00, 'Advance Payment', 345000.00, 136650.00, 390, 'Sachith', '2025-06-26 08:04:20'),
	(39, '8425', 'INV-1432', '0725876139 / 0706773588', '0741773588', 'udarasachith41@gmail.com', 1500.00, 2300.00, 'wedding hall', 'Subtotal', 585000.00, 'Advance Payment', 450000.00, 135000.00, 390, 'Sachith', '2025-06-26 10:43:54'),
	(40, '8425', 'INV-1433', '0725876139 / 0706773588', '123456789', 'udarasachith41@gmail.com', 3450.00, 3500.00, 'Hall', 'Hall Charges', 30000.00, 'Advance Payment', 20000.00, 10000.00, 390, 'Sachith', '2025-06-26 10:54:53'),
	(41, '8423', 'INV-1434', '0725876139', '0765412378', 'udarasachith41@gmail.com', 5400.00, 1200.00, 'Hall', 'Subtotal', 4212000.00, 'Advance Payment', 1230000.00, 2982000.00, 780, 'Sachith', '2025-07-02 12:34:12'),
	(42, '8426', 'INV-1435', '0725876139 / 0725876139', '0741773588', 'udarasachith41@gmail.com', 2300.00, 1200.00, 'Hall booked', 'Subtotal', 828000.00, 'Advance Payment', 100000.00, 728000.00, 360, 'Sachith', '2025-08-30 15:25:41'),
	(43, '8436', 'INV-1436', '0725876139 / 0741773588', '0703739158', 'udarasachith41@gmail.com', 2300.00, 1400.00, 'nothing', 'Hall Charges', 50000.00, 'Advance Payment', 25000.00, 25000.00, 230, 'Sachith', '2025-09-01 08:11:41');

-- Dumping structure for table wedding_bliss.po_items
CREATE TABLE IF NOT EXISTS `po_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `po_id` int NOT NULL,
  `quantity` int DEFAULT NULL,
  `mass_unit` enum('kg','g','unit') DEFAULT 'unit',
  `item_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `po_id` (`po_id`),
  CONSTRAINT `po_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.po_items: ~27 rows (approximately)
INSERT INTO `po_items` (`id`, `po_id`, `quantity`, `mass_unit`, `item_id`) VALUES
	(1, 6, 40, 'unit', 0),
	(2, 6, 30, 'kg', 0),
	(3, 6, 40, 'kg', 0),
	(4, 7, 20, 'unit', 0),
	(5, 7, 100, 'kg', 0),
	(6, 7, 45, 'kg', 0),
	(7, 8, 40, 'unit', 0),
	(8, 8, 100, 'kg', 0),
	(9, 8, 30, 'kg', 0),
	(10, 9, 40, 'unit', 0),
	(11, 9, 34, 'unit', 0),
	(12, 9, 50, 'kg', 0),
	(13, 12, 17, 'kg', 1),
	(14, 13, 19, 'kg', 4),
	(15, 14, 30, 'kg', 5),
	(16, 14, 12, 'kg', 1),
	(17, 16, 56, 'kg', 7),
	(18, 16, 40, 'kg', 3),
	(19, 17, 340, 'kg', 7),
	(20, 18, 89, 'kg', 1),
	(21, 19, 45, 'kg', 3),
	(22, 19, 12, 'kg', 1),
	(23, 19, 120, 'kg', 7),
	(24, 20, 13, 'kg', 7),
	(25, 21, 34, 'unit', 9),
	(26, 22, 60, 'kg', 7),
	(27, 22, 1, 'unit', 9);

-- Dumping structure for table wedding_bliss.purchased_items
CREATE TABLE IF NOT EXISTS `purchased_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `stock` int NOT NULL DEFAULT (0),
  `unit` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `purchased_date` date NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_purchase` (`item_id`,`stock`,`price`,`purchased_date`,`expiry_date`),
  CONSTRAINT `fk_item_id` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `purchased_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.purchased_items: ~10 rows (approximately)
INSERT INTO `purchased_items` (`id`, `item_id`, `stock`, `unit`, `price`, `expiry_date`, `purchased_date`, `unit_price`) VALUES
	(15, 7, 0, 'kg', 35600.00, '2025-08-31', '2025-07-28', 65.93),
	(16, 7, 0, 'kg', 20000.00, '2025-08-25', '2025-07-29', 83.33),
	(17, 9, 30, 'units', 14500.00, '2025-08-05', '2025-07-30', 254.39),
	(18, 1, 32, 'kg', 156000.00, '2025-08-09', '2025-07-29', 3120.00),
	(19, 7, 0, 'kg', 69000.00, '2025-08-31', '2025-08-06', 1150.00),
	(20, 1, 0, 'kg', 34000.00, '2025-08-12', '2025-08-19', 283.33),
	(21, 1, 20, 'kg', 1400.00, '2025-08-12', '2025-08-18', 35.00),
	(24, 7, 81, 'kg', 8100.00, '2025-08-17', '2025-09-01', 100.00),
	(25, 7, 81, 'kg', 810.00, '2025-08-13', '2025-08-26', 10.00),
	(26, 8, 94, 'kg', 8676.92, '2025-08-13', '2025-08-17', 92.31);

-- Dumping structure for table wedding_bliss.purchased_items_backup
CREATE TABLE IF NOT EXISTS `purchased_items_backup` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `unit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `purchased_date` date NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unique_purchase_backup` (`item_id`,`stock`,`price`,`purchased_date`,`expiry_date`) USING BTREE,
  CONSTRAINT `fk_item_id_backup` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.purchased_items_backup: ~2 rows (approximately)
INSERT INTO `purchased_items_backup` (`id`, `item_id`, `stock`, `unit`, `price`, `expiry_date`, `purchased_date`, `unit_price`) VALUES
	(1, 7, 81, 'kg', 810.00, '2025-08-13', '2025-08-26', 10.00),
	(2, 8, 130, 'kg', 12000.00, '2025-08-13', '2025-08-17', 92.31);

-- Dumping structure for table wedding_bliss.purchases
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `quantity` int NOT NULL,
  `unit` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `purchased_date` date NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `item_id` (`item_id`),
  CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.purchases: ~4 rows (approximately)
INSERT INTO `purchases` (`id`, `item_id`, `quantity`, `unit`, `unit_price`, `total_price`, `expiry_date`, `purchased_date`) VALUES
	(1, 4, 20, 'liter', 2750.00, 55000.00, NULL, '2025-08-24'),
	(2, 1, 20, 'kg', 1200.00, 24000.00, NULL, '2025-07-01'),
	(3, 6, 20, 'Kg', 1300.00, 26000.00, '2025-08-30', '2025-08-26'),
	(4, 6, 20, 'Kg', 1350.00, 27000.00, NULL, '2025-08-14');

-- Dumping structure for table wedding_bliss.purchases_backup
CREATE TABLE IF NOT EXISTS `purchases_backup` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `quantity` int NOT NULL,
  `unit` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `purchased_date` date NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `item_id` (`item_id`),
  CONSTRAINT `purchases_backup_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.purchases_backup: ~4 rows (approximately)
INSERT INTO `purchases_backup` (`id`, `item_id`, `quantity`, `unit`, `unit_price`, `total_price`, `expiry_date`, `purchased_date`) VALUES
	(1, 4, 20, 'liter', 2750.00, 55000.00, NULL, '2025-08-24'),
	(2, 1, 20, 'kg', 1200.00, 24000.00, NULL, '2025-07-01'),
	(3, 6, 20, 'Kg', 1300.00, 26000.00, '2025-08-30', '2025-08-26'),
	(4, 6, 20, 'Kg', 1350.00, 27000.00, NULL, '2025-08-14');

-- Dumping structure for table wedding_bliss.purchase_orders
CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `po_number` varchar(50) NOT NULL,
  `po_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `supplier_id` int NOT NULL,
  `received_by` varchar(100) NOT NULL,
  `confirmed_by` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_number` (`po_number`),
  KEY `supplier_id` (`supplier_id`),
  KEY `confirmed_by` (`confirmed_by`),
  CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`confirmed_by`) REFERENCES `responsibilities` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.purchase_orders: ~18 rows (approximately)
INSERT INTO `purchase_orders` (`id`, `po_number`, `po_date`, `supplier_id`, `received_by`, `confirmed_by`) VALUES
	(1, 'PO-20250722-D5D155', '2025-07-22 14:03:32', 1, 'udara', 1),
	(2, 'PO-20250722-5576F4', '2025-07-22 14:06:44', 1, 'udara', 1),
	(3, 'PO-20250722-5F10C3', '2025-07-22 14:10:04', 1, 'udara', 1),
	(4, 'PO-1500', '2025-07-22 15:17:44', 1, 'udara', 1),
	(5, 'PO-1501', '2025-07-22 15:48:19', 1, 'udara', 1),
	(6, 'PO-1502', '2025-07-22 16:29:32', 1, 'udara', 1),
	(7, 'PO-1503', '2025-07-22 16:38:19', 1, 'udara', 1),
	(8, 'PO-1504', '2025-07-22 16:59:32', 1, 'udara', 1),
	(9, 'PO-1505', '2025-07-23 02:43:14', 1, 'Unknown', 1),
	(12, 'PO-1506', '2025-07-24 04:30:01', 1, 'Unknown', 1),
	(13, 'PO-1507', '2025-07-24 04:51:33', 1, 'Unknown', 1),
	(14, 'PO-1508', '2025-07-24 04:56:40', 1, 'Unknown', 1),
	(15, 'PO-1509', '2025-08-01 06:30:06', 1, 'Sachith', 1),
	(16, 'PO-1510', '2025-08-01 06:30:49', 1, 'Sachith', 1),
	(17, 'PO-1511', '2025-08-01 06:32:24', 1, 'Sachith', 1),
	(18, 'PO-1512', '2025-08-01 06:43:49', 1, 'Sachith', 1),
	(19, 'PO-1513', '2025-08-04 10:11:22', 1, 'Sachith', 1),
	(20, 'PO-1514', '2025-08-04 11:05:30', 1, 'Sachith', 1),
	(21, 'PO-1515', '2025-08-05 02:48:53', 1, 'Unknown', 1),
	(22, 'PO-1516', '2025-08-06 09:22:47', 1, 'Sachith', 1);

-- Dumping structure for table wedding_bliss.refill_requests
CREATE TABLE IF NOT EXISTS `refill_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `buffer_id` int NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `requested_qty` decimal(10,2) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `request_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `buffer_id` (`buffer_id`),
  CONSTRAINT `refill_requests_ibfk_1` FOREIGN KEY (`buffer_id`) REFERENCES `kitchen_buffer` (`buffer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.refill_requests: ~2 rows (approximately)
INSERT INTO `refill_requests` (`id`, `buffer_id`, `item_name`, `requested_qty`, `unit`, `status`, `request_date`) VALUES
	(1, 1, 'Banana', 12.00, 'kg', 'approved', '2025-08-04 11:33:04'),
	(2, 1, 'Banana', 12.00, 'kg', 'approved', '2025-08-06 10:46:52');

-- Dumping structure for table wedding_bliss.registration_access
CREATE TABLE IF NOT EXISTS `registration_access` (
  `id` int NOT NULL AUTO_INCREMENT,
  `is_locked` tinyint(1) DEFAULT '1',
  `unlock_key` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.registration_access: ~0 rows (approximately)
INSERT INTO `registration_access` (`id`, `is_locked`, `unlock_key`, `updated_at`) VALUES
	(1, 0, '1', '2025-06-24 06:12:08');

-- Dumping structure for table wedding_bliss.request_items
CREATE TABLE IF NOT EXISTS `request_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `item_id` int NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_type` varchar(20) NOT NULL,
  `issued_quantity` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `request_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `item_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `request_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.request_items: ~19 rows (approximately)
INSERT INTO `request_items` (`id`, `request_id`, `item_id`, `quantity`, `unit_type`, `issued_quantity`) VALUES
	(1, 1, 2, 31.00, 'box', 0.00),
	(2, 1, 4, 18.00, 'liter', 0.00),
	(3, 2, 2, 31.00, 'box', 0.00),
	(4, 2, 4, 18.00, 'liter', 0.00),
	(5, 3, 1, 1.00, 'kg', 0.00),
	(6, 3, 2, 3.00, 'box', 0.00),
	(7, 4, 2, 90.00, 'box', 90.00),
	(8, 4, 4, 5.50, 'liter', 5.50),
	(9, 4, 1, 5.20, 'g', 5.20),
	(10, 5, 2, 2.00, 'box', 2.00),
	(11, 5, 4, 2.10, 'milliliter', 2.10),
	(12, 6, 2, 32.00, 'box', 30.00),
	(13, 6, 4, 20.00, 'milliliter', 20.00),
	(14, 7, 4, 20.00, 'mililiter', 10.00),
	(15, 8, 4, 20.00, 'mL', 20.00),
	(16, 9, 6, 10.00, 'g', 5.00),
	(17, 10, 6, 13.00, 'Kg', 0.01),
	(18, 11, 4, 12.00, 'l', 0.01),
	(19, 11, 6, 30.00, 'Kg', 30.00);

-- Dumping structure for table wedding_bliss.responsibilities
CREATE TABLE IF NOT EXISTS `responsibilities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.responsibilities: ~0 rows (approximately)
INSERT INTO `responsibilities` (`id`, `name`, `password`, `signature_path`, `created_at`) VALUES
	(1, 'Sachith', '$2y$10$43dHXtYKeEC2FbTPqaIiTey3Ens2Md1WDPq7MEmBqmpiNs5yPaoVu', NULL, '2025-07-23 10:14:51'),
	(2, 'Sachith Gamage', '$2y$10$PQgn9Z/WqXbLFjUwvAgJJe.lLTVUlFco16yoJJ8yBckQZi.21C4lO', NULL, '2025-08-08 09:14:03'),
	(3, 'Udara', '$2y$10$wHCWqa6kJhgMsSn5Ai2gK.PhZ.349wm.Rn75mKNAyABa/XfzY1HvO', NULL, '2025-08-19 03:06:43');

-- Dumping structure for table wedding_bliss.responsible
CREATE TABLE IF NOT EXISTS `responsible` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.responsible: ~0 rows (approximately)
INSERT INTO `responsible` (`id`, `name`, `password`) VALUES
	(1, 'Sachith Gamage', '$2y$10$USweMJMVzGgWeYwWkOGLx.WZr.ypW8T9OmtorJnJ4S62MFVpoWB/W'),
	(2, 'Test', '$2y$10$DKaMH7zhaKLSIu5vczuwDuOFItXDClwr0hQvIwpoJd8pxD92ylETq');

-- Dumping structure for table wedding_bliss.rooms
CREATE TABLE IF NOT EXISTS `rooms` (
  `room_number` varchar(10) NOT NULL,
  PRIMARY KEY (`room_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.rooms: ~17 rows (approximately)
INSERT INTO `rooms` (`room_number`) VALUES
	('105'),
	('201'),
	('202'),
	('203'),
	('204'),
	('205'),
	('206'),
	('207'),
	('208'),
	('209'),
	('301'),
	('302'),
	('303'),
	('304'),
	('305'),
	('306'),
	('307'),
	('308'),
	('309');

-- Dumping structure for table wedding_bliss.room_bookings
CREATE TABLE IF NOT EXISTS `room_bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `guest_name` varchar(255) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `pax` int NOT NULL,
  `created_at` datetime NOT NULL,
  `remarks` text,
  `function_type` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_number` (`room_number`),
  CONSTRAINT `room_bookings_ibfk_1` FOREIGN KEY (`room_number`) REFERENCES `rooms` (`room_number`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.room_bookings: ~15 rows (approximately)
INSERT INTO `room_bookings` (`id`, `guest_name`, `telephone`, `check_in`, `check_out`, `room_number`, `pax`, `created_at`, `remarks`, `function_type`) VALUES
	(1, 'Sachith Gamage', '0741773588', '2025-07-21', '2025-07-23', '105', 3, '2025-07-21 09:39:11', NULL, NULL),
	(2, 'Sachith Gamage', '0741773588', '2025-07-22', '2025-07-24', '201', 3, '2025-07-21 09:40:47', NULL, NULL),
	(3, 'Gimeba', '0741773588', '2025-07-30', '2025-08-04', '105', 3, '2025-07-21 09:43:38', NULL, NULL),
	(4, 'Sachith Gamage', '0741773588', '2025-07-01', '2025-07-15', '105', 2, '2025-07-21 10:27:51', NULL, NULL),
	(5, 'fg', '0741773588', '2025-07-24', '2025-08-04', '203', 5, '2025-07-21 10:29:56', NULL, NULL),
	(6, 'Nayana', '0741773588', '2025-07-24', '2025-07-25', '207', 4, '2025-07-21 15:39:32', NULL, NULL),
	(7, 'Thimathi', '0741773588', '2025-07-02', '2025-07-10', '201', 2, '2025-07-21 16:18:38', NULL, NULL),
	(8, 'Sachith Gamage', '0741773588', '2025-07-24', '2025-07-25', '205', 3, '2025-07-22 08:38:58', NULL, NULL),
	(9, 'Thimathi', '0741773588', '2025-07-23', '2025-08-01', '306', 3, '2025-07-22 08:41:12', NULL, NULL),
	(10, 'Gimeba g', '0741773588', '2025-07-23', '2025-07-25', '309', 2, '2025-07-22 09:19:42', '1 DBL FB\nHM', 'Wedding'),
	(22, 'Mahima Wijesinghe', '0725876139', '2025-07-22', '2025-07-26', '208', 12, '2025-07-22 12:03:39', '3 TPL FB', 'Out Guest Room'),
	(23, 'Mahima Wijesinghe', '0725876139', '2025-07-22', '2025-07-26', '209', 12, '2025-07-22 12:03:39', '3 TPL FB', 'Out Guest Room'),
	(24, 'Mahima Wijesinghe', '0725876139', '2025-07-22', '2025-07-26', '301', 12, '2025-07-22 12:03:39', '3 TPL FB', 'Out Guest Room'),
	(25, 'Sachith Gamage', '0741773588', '2025-07-22', '2025-07-26', '307', 6, '2025-07-22 14:07:48', '1 DBL FB', 'Changing Room'),
	(26, 'Sachith Gamage', '0741773588', '2025-07-22', '2025-07-26', '308', 6, '2025-07-22 14:07:48', '1 DBL FB', 'Changing Room'),
	(28, 'Sachith Gamage', '0741773588', '2025-08-29', '2025-08-30', '105', 3, '2025-08-29 09:11:20', 'no', 'Out Guest Room'),
	(29, 'Sachith Gamage', '0741773588', '2025-08-29', '2025-08-30', '201', 3, '2025-08-29 09:11:20', 'no', 'Out Guest Room');

-- Dumping structure for table wedding_bliss.room_invoices
CREATE TABLE IF NOT EXISTS `room_invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(20) NOT NULL,
  `grc_number` int NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `nic` varchar(50) DEFAULT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `billing_date` date NOT NULL,
  `rooms` json NOT NULL,
  `ac_type` varchar(10) DEFAULT NULL,
  `meal_plan` varchar(255) DEFAULT NULL,
  `remarks` text,
  `value_type` varchar(50) NOT NULL,
  `amount_type` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `advance_payment` decimal(10,2) DEFAULT NULL,
  `pending_amount` decimal(10,2) DEFAULT NULL,
  `issued_by` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `grc_number` (`grc_number`),
  CONSTRAINT `room_invoices_ibfk_1` FOREIGN KEY (`grc_number`) REFERENCES `guests` (`grc_number`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.room_invoices: ~0 rows (approximately)

-- Dumping structure for table wedding_bliss.room_invoice_counter
CREATE TABLE IF NOT EXISTS `room_invoice_counter` (
  `id` int NOT NULL AUTO_INCREMENT,
  `last_invoice_number` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.room_invoice_counter: ~24 rows (approximately)
INSERT INTO `room_invoice_counter` (`id`, `last_invoice_number`) VALUES
	(1, 2084),
	(2, 2084),
	(3, 2084),
	(4, 2084),
	(5, 2084),
	(6, 2084),
	(7, 2084),
	(8, 2084),
	(9, 2084),
	(10, 2084),
	(11, 2084),
	(12, 2084),
	(13, 2084),
	(14, 2084),
	(15, 2084),
	(16, 2084),
	(17, 2084),
	(18, 2084),
	(19, 2084),
	(20, 2084),
	(21, 2084),
	(23, 2084),
	(25, 2084),
	(26, 2084),
	(27, 2085);

-- Dumping structure for table wedding_bliss.room_payments
CREATE TABLE IF NOT EXISTS `room_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_reference` varchar(4) NOT NULL,
  `invoice_number` varchar(8) NOT NULL,
  `ac_type` varchar(10) DEFAULT NULL,
  `meal_plan` varchar(255) DEFAULT NULL,
  `remarks` text,
  `value_type` varchar(50) NOT NULL,
  `amount_type` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `advance_payment` decimal(10,2) DEFAULT NULL,
  `pending_amount` decimal(10,2) DEFAULT NULL,
  `issued_by` varchar(50) DEFAULT NULL,
  `nic` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `contact_no` varchar(25) DEFAULT NULL,
  `payment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.room_payments: ~26 rows (approximately)
INSERT INTO `room_payments` (`id`, `booking_reference`, `invoice_number`, `ac_type`, `meal_plan`, `remarks`, `value_type`, `amount_type`, `total_amount`, `advance_payment`, `pending_amount`, `issued_by`, `nic`, `contact_no`, `payment_date`) VALUES
	(1, '8425', 'INV-2081', 'A/C', 'Breakfast, Lunch, Dinner, Room Only', 'Rooms', 'Subtotal', 'FOC', NULL, NULL, NULL, 'Admin', '200328100859', '0725876139 / 0706773588', '2025-07-02 16:53:55'),
	(2, '8425', 'INV-2082', 'A/C', 'Breakfast, Lunch, Dinner', 'Hall', 'Subtotal', 'FOC', NULL, NULL, NULL, 'Sachith', '200328100859', '0725876139 / 0706773588', '2025-07-02 17:08:39'),
	(3, '8425', 'INV-2083', 'A/C', 'Breakfast, Lunch, Dinner', 'Hall', 'Subtotal', 'FOC', NULL, NULL, NULL, 'Sachith', '200328100859 / 557651519V', '0725876139 / 0706773588', '2025-07-02 17:33:43'),
	(4, '1201', '00002084', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23.00, 'Admin', '200328100859', '0725876139', '2025-07-19 15:14:55'),
	(5, '1201', '00002085', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23.00, 'Admin', '200328100859', '0725876139', '2025-07-19 15:18:10'),
	(6, '1201', '00002086', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23.00, 'Admin', '200328100859', '0725876139', '2025-07-19 15:19:23'),
	(7, '1201', 'INV-2087', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23.00, 'Admin', '200328100859', '0725876139', '2025-07-19 16:31:28'),
	(8, '1201', '00002088', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23.00, 'Admin', '200328100859', '0725876139', '2025-07-19 16:32:10'),
	(9, '1201', 'INV-2089', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23.00, 'Admin', '200328100859', '0725876139', '2025-07-19 16:55:02'),
	(10, '1201', 'INV-2090', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23.00, 'Admin', '200328100859', '0725876139', '2025-07-19 17:08:31'),
	(11, '1201', 'INV-2091', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 12300.00, 10700.00, 'Admin', '200328100859', '0725876139', '2025-07-20 08:28:30'),
	(12, '1201', 'INV-2092', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23.00, 'Admin', '200328100859', '0725876139', '2025-07-20 08:37:49'),
	(13, '1201', 'INV-2093', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23.00, 'Admin', '200328100859', '0725876139', '2025-07-20 09:19:11'),
	(14, '1201', 'INV-2094', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 10000.00, 13000.00, 'Admin', '200328100859', '0725876139', '2025-07-20 09:49:59'),
	(15, '1201', 'INV-2095', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 10000.00, 13000.00, 'Admin', '200328100859', '0725876139', '2025-07-20 09:51:33'),
	(16, '1201', 'INV-2096', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 12000.00, 11000.00, 'Admin', '200328100859', '0725876139', '2025-07-20 11:08:44'),
	(17, '1201', 'INV-2097', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23.00, 'Admin', '200328100859', '0725876139', '2025-07-20 11:10:38'),
	(18, '1201', 'INV-2098', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 12000.00, 11000.00, 'Admin', '200328100859', '0725876139', '2025-07-20 11:11:07'),
	(19, '1201', 'INV-2099', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23.00, 'Admin', '200328100859', '0725876139', '2025-07-20 11:11:29'),
	(20, '1201', 'INV-2100', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 10000.00, 13000.00, 'Admin', '200328100859', '0725876139', '2025-07-20 11:19:04'),
	(21, '1201', 'INV-2101', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23.00, 'Admin', '200328100859', '0725876139', '2025-07-20 11:29:39'),
	(22, '1201', 'INV-2102', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23.00, 'Admin', '200328100859', '0725876139', '2025-07-20 12:01:27'),
	(23, '1201', 'INV-2103', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23.00, 'Admin', '200328100859', '0725876139', '2025-07-20 12:51:55'),
	(24, '1201', 'INV-2104', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 12000.00, 11000.00, 'Admin', '200328100859', '0725876139', '2025-07-20 13:01:27'),
	(25, '1201', 'INV-2105', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23000.00, 'Admin', '200328100859', '0725876139', '2025-07-20 13:01:56'),
	(26, '1201', 'INV-2106', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 23000.00, 0.00, 23000.00, 'Admin', '200328100859', '0725876139', '2025-07-20 13:04:23'),
	(27, '8436', 'INV-2084', 'Non A/C', NULL, 'FOC', 'Subtotal', 'FOC', NULL, NULL, NULL, 'Sachith', '200145673891', '0725876139 / 0706773588', '2025-09-01 13:55:43'),
	(28, '1202', 'INV-2085', 'AC', 'Room Only', NULL, 'Room Booking', 'Invoice', 29000.00, 25000.00, 4000.00, 'Admin', '20034567890', '0703739158', '2025-09-01 15:49:27');

-- Dumping structure for table wedding_bliss.room_payment_details
CREATE TABLE IF NOT EXISTS `room_payment_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `payment_id` int NOT NULL,
  `room_number` varchar(50) DEFAULT NULL,
  `room_type` varchar(50) DEFAULT NULL,
  `hotel` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_id` (`payment_id`),
  CONSTRAINT `room_payment_details_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `room_payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.room_payment_details: ~8 rows (approximately)
INSERT INTO `room_payment_details` (`id`, `payment_id`, `room_number`, `room_type`, `hotel`) VALUES
	(1, 1, '203', 'Changing', 'Grand View Lodge'),
	(2, 1, '204', 'Standard', 'Paragon'),
	(3, 1, '205', 'Honeymoon', 'Sky'),
	(4, 1, '206', 'Anniversary', 'Rose Garden Hotel'),
	(5, 1, '207', 'Standard', 'Sapthpadhi Hotel'),
	(6, 2, '203', 'Standard', 'Grand View Lodge'),
	(7, 3, '203', 'Changing', 'Grand View Lodge'),
	(8, 3, '209', 'Standard', 'Paragon'),
	(9, 27, '201', 'Changing', 'Paragon'),
	(10, 27, '202', 'Changing', 'Paragon');

-- Dumping structure for table wedding_bliss.room_rates
CREATE TABLE IF NOT EXISTS `room_rates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `room_type_id` int NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `ac_type` enum('AC','Non-AC') NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_type_id` (`room_type_id`,`room_number`,`ac_type`),
  CONSTRAINT `room_rates_ibfk_1` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.room_rates: ~5 rows (approximately)
INSERT INTO `room_rates` (`id`, `room_type_id`, `room_number`, `ac_type`, `rate`) VALUES
	(1, 1, '101', 'AC', 5000.00),
	(2, 1, '102', 'Non-AC', 4000.00),
	(3, 2, '201', 'AC', 8000.00),
	(4, 2, '202', 'Non-AC', 6500.00),
	(5, 3, '301', 'AC', 12000.00);

-- Dumping structure for table wedding_bliss.room_types
CREATE TABLE IF NOT EXISTS `room_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.room_types: ~3 rows (approximately)
INSERT INTO `room_types` (`id`, `name`) VALUES
	(1, 'Standard'),
	(2, 'Deluxe'),
	(3, 'Suite');

-- Dumping structure for table wedding_bliss.skyfunction_unload
CREATE TABLE IF NOT EXISTS `skyfunction_unload` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_sheet_no` int NOT NULL,
  `item_id` int NOT NULL,
  `requested_qty` decimal(10,2) DEFAULT NULL,
  `issued_qty` decimal(10,2) DEFAULT NULL,
  `remaining_qty` decimal(10,2) DEFAULT NULL,
  `usage_qty` decimal(10,2) DEFAULT NULL,
  `unload_date` date NOT NULL,
  `function_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `day_night` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `item_id` (`item_id`) USING BTREE,
  KEY `created_by` (`created_by`) USING BTREE,
  CONSTRAINT `fk_unload_created_by` FOREIGN KEY (`created_by`) REFERENCES `storeresponsible` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_unload_item` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.skyfunction_unload: ~2 rows (approximately)
INSERT INTO `skyfunction_unload` (`id`, `order_sheet_no`, `item_id`, `requested_qty`, `issued_qty`, `remaining_qty`, `usage_qty`, `unload_date`, `function_type`, `day_night`, `created_by`) VALUES
	(19, 1101, 7, 9.00, 9.00, 0.00, 4.00, '2025-08-19', 'SKY Birthday Function, SKY Function, SKY Restaurant', 'Day', 3),
	(20, 1103, 4, 1.00, 0.00, 0.00, 0.00, '2025-08-19', 'SKY Restaurant', 'Day', 3);

-- Dumping structure for table wedding_bliss.skyfunction_unload_history
CREATE TABLE IF NOT EXISTS `skyfunction_unload_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_sheet_no` int DEFAULT NULL,
  `item_id` int DEFAULT NULL,
  `requested_qty` decimal(10,2) DEFAULT NULL,
  `issued_qty` decimal(10,2) DEFAULT NULL,
  `remaining_qty` decimal(10,2) DEFAULT NULL,
  `usage_qty` decimal(10,2) DEFAULT NULL,
  `unload_date` date DEFAULT NULL,
  `function_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `day_night` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.skyfunction_unload_history: ~2 rows (approximately)
INSERT INTO `skyfunction_unload_history` (`id`, `order_sheet_no`, `item_id`, `requested_qty`, `issued_qty`, `remaining_qty`, `usage_qty`, `unload_date`, `function_type`, `day_night`, `created_by`, `created_at`) VALUES
	(1, 1101, 7, 9.00, 9.00, 5.00, 4.00, '2025-08-19', 'SKY Birthday Function, SKY Function, SKY Restaurant', 'Day', 3, '2025-08-19 06:53:13'),
	(2, 1103, 4, 1.00, 1.00, 1.00, 0.00, '2025-08-19', 'SKY Restaurant', 'Day', 3, '2025-08-19 06:53:13');

-- Dumping structure for table wedding_bliss.skykitchenregistration_access
CREATE TABLE IF NOT EXISTS `skykitchenregistration_access` (
  `id` int NOT NULL AUTO_INCREMENT,
  `is_locked` tinyint(1) DEFAULT '1',
  `unlock_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.skykitchenregistration_access: ~0 rows (approximately)
INSERT INTO `skykitchenregistration_access` (`id`, `is_locked`, `unlock_key`, `updated_at`) VALUES
	(3, 0, '1', '2025-08-23 06:24:56');

-- Dumping structure for table wedding_bliss.skykitchen_buffer
CREATE TABLE IF NOT EXISTS `skykitchen_buffer` (
  `buffer_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `remaining_quantity` int NOT NULL DEFAULT '0',
  `usage` int NOT NULL DEFAULT '0',
  `last_updated` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`buffer_id`) USING BTREE,
  KEY `item_id` (`item_id`) USING BTREE,
  CONSTRAINT `fk_kitchen_buffer_item` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.skykitchen_buffer: ~0 rows (approximately)
INSERT INTO `skykitchen_buffer` (`buffer_id`, `item_id`, `quantity`, `remaining_quantity`, `usage`, `last_updated`) VALUES
	(3, 7, 4, 3, 0, '2025-09-05 05:55:42');

-- Dumping structure for table wedding_bliss.skykitchen_buffer_history
CREATE TABLE IF NOT EXISTS `skykitchen_buffer_history` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `quantity_updated` int NOT NULL,
  `update_timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `skykitchen_buffer_history_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.skykitchen_buffer_history: ~6 rows (approximately)
INSERT INTO `skykitchen_buffer_history` (`history_id`, `item_id`, `quantity_updated`, `update_timestamp`) VALUES
	(1, 7, 1, '2025-09-05 10:44:41'),
	(2, 7, 1, '2025-09-05 10:45:03'),
	(3, 7, 3, '2025-09-05 10:46:09'),
	(4, 7, 1, '2025-09-05 11:06:39'),
	(5, 7, 2, '2025-09-05 11:07:35'),
	(6, 7, 1, '2025-09-05 11:16:07'),
	(7, 7, 3, '2025-09-05 11:25:42');

-- Dumping structure for table wedding_bliss.skykitchen_users
CREATE TABLE IF NOT EXISTS `skykitchen_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username` (`username`) USING BTREE,
  UNIQUE KEY `email` (`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.skykitchen_users: ~0 rows (approximately)
INSERT INTO `skykitchen_users` (`id`, `username`, `password`, `email`, `is_admin`, `created_at`) VALUES
	(7, 'Yasith', '$2y$10$wgBzmJub11jIiOpUnqqC9.YY8s/0XDw.MBYHlmkx8UsIBbBFBGfkO', NULL, 0, '2025-08-23 06:24:30');

-- Dumping structure for table wedding_bliss.skyorder_sheet
CREATE TABLE IF NOT EXISTS `skyorder_sheet` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `requested_qty` decimal(10,2) DEFAULT NULL,
  `issued_qty` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','issued') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'pending',
  `request_date` datetime NOT NULL,
  `issued_date` datetime DEFAULT NULL,
  `order_sheet_no` int NOT NULL,
  `responsible_id` int DEFAULT NULL,
  `function_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `function_date` date DEFAULT NULL,
  `day_night` enum('Day','Night') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`order_id`) USING BTREE,
  UNIQUE KEY `order_sheet_no` (`order_sheet_no`,`item_id`) USING BTREE,
  KEY `item_id` (`item_id`) USING BTREE,
  KEY `responsible_id` (`responsible_id`) USING BTREE,
  CONSTRAINT `fk_skyorder_item` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`),
  CONSTRAINT `fk_skyorder_responsible` FOREIGN KEY (`responsible_id`) REFERENCES `responsible` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.skyorder_sheet: ~21 rows (approximately)
INSERT INTO `skyorder_sheet` (`order_id`, `item_id`, `requested_qty`, `issued_qty`, `status`, `request_date`, `issued_date`, `order_sheet_no`, `responsible_id`, `function_type`, `function_date`, `day_night`) VALUES
	(108, 7, 2.00, 2.00, 'issued', '2025-08-18 10:56:09', NULL, 1100, 2, 'SKY Restaurant', '2025-08-18', NULL),
	(109, 7, 1.00, 1.00, 'issued', '2025-08-19 08:22:11', NULL, 1101, 2, 'SKY Restaurant', '2025-08-19', NULL),
	(110, 7, 1.00, 1.00, 'issued', '2025-08-19 08:26:18', NULL, 1102, 2, 'SKY Function', '2025-08-19', NULL),
	(111, 7, 1.00, 1.00, 'issued', '2025-08-19 08:27:24', NULL, 1103, 2, 'SKY Restaurant', '2025-08-19', 'Day'),
	(112, 4, 1.00, 1.00, 'issued', '2025-08-19 08:27:24', NULL, 1103, 2, 'SKY Restaurant', '2025-08-19', 'Day'),
	(113, 7, 2.00, 2.00, 'issued', '2025-08-19 08:29:48', NULL, 1104, 2, 'SKY Birthday Function', '2025-08-19', 'Day'),
	(115, 7, 1.00, 1.00, 'issued', '2025-08-19 08:46:21', NULL, 1105, 2, 'SKY Function', '2025-08-19', 'Day'),
	(116, 7, 2.00, 2.00, 'issued', '2025-08-19 10:19:51', NULL, 1106, 2, 'SKY Birthday Function', '2025-08-19', 'Day'),
	(117, 7, 1.00, 1.00, 'issued', '2025-08-19 10:43:13', NULL, 1107, 2, 'SKY Restaurant', '2025-08-19', NULL),
	(118, 7, 10.00, 5.00, 'issued', '2025-08-19 12:27:02', NULL, 1108, 2, 'SKY Restaurant', '2025-08-20', 'Day'),
	(119, 4, 3.00, 2.00, 'issued', '2025-08-19 12:29:23', NULL, 1109, 2, 'SKY Birthday Function', '2025-08-20', 'Day'),
	(120, 7, 12.00, 12.00, 'issued', '2025-08-19 12:29:23', NULL, 1109, 2, 'SKY Birthday Function', '2025-08-20', 'Day'),
	(121, 7, 2.00, 2.00, 'issued', '2025-08-19 13:17:53', NULL, 1110, 2, 'SKY Buffer Stock Refill', '2025-08-19', NULL),
	(122, 7, 2.00, 2.00, 'issued', '2025-08-19 16:02:47', NULL, 1111, 2, 'SKY Function', '2025-08-19', NULL),
	(123, 4, 2.00, 2.00, 'issued', '2025-08-19 16:02:47', NULL, 1111, 2, 'SKY Function', '2025-08-19', NULL),
	(124, 7, 2.00, NULL, 'pending', '2025-08-19 16:09:22', NULL, 1112, 2, 'SKY Buffer Stock Refill', NULL, NULL),
	(125, 7, 2.00, 2.00, 'issued', '2025-08-19 16:09:59', NULL, 1113, 2, 'SKY Function', '2025-08-19', NULL),
	(126, 7, 2.00, NULL, 'pending', '2025-08-19 16:18:22', NULL, 1114, 2, 'SKY Buffer Stock Refill', NULL, NULL),
	(127, 7, 2.00, 2.00, 'issued', '2025-08-19 16:37:59', NULL, 1115, 2, 'SKY Buffer Stock Refill', '2025-08-19', NULL),
	(128, 7, 4.00, 4.00, 'issued', '2025-08-19 16:41:51', NULL, 1116, 2, 'SKY Buffer Stock Refill', '2025-08-19', NULL),
	(129, 7, 2.00, NULL, 'pending', '2025-08-19 16:50:19', NULL, 1117, 2, 'SKY Function', '2025-08-19', NULL),
	(130, 7, 1.00, 1.00, 'issued', '2025-08-20 09:02:56', NULL, 1118, 2, 'SKY Buffer Stock Refill', '2025-08-20', NULL),
	(131, 7, 2.00, 2.00, 'issued', '2025-08-20 09:04:10', NULL, 1119, 2, 'SKY Restaurant', '2025-08-20', NULL);

-- Dumping structure for table wedding_bliss.skyorder_sheet_counter
CREATE TABLE IF NOT EXISTS `skyorder_sheet_counter` (
  `id` int NOT NULL,
  `last_order_sheet_no` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.skyorder_sheet_counter: ~0 rows (approximately)
INSERT INTO `skyorder_sheet_counter` (`id`, `last_order_sheet_no`) VALUES
	(1, 1119);

-- Dumping structure for table wedding_bliss.skyresponsible
CREATE TABLE IF NOT EXISTS `skyresponsible` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.skyresponsible: ~0 rows (approximately)

-- Dumping structure for table wedding_bliss.stock
CREATE TABLE IF NOT EXISTS `stock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `date` date NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.stock: ~9 rows (approximately)
INSERT INTO `stock` (`id`, `item_id`, `date`, `quantity`, `unit`) VALUES
	(1, 1, '2025-08-23', 1.00, ''),
	(2, 2, '2025-08-23', 3.00, ''),
	(3, 3, '2025-08-24', 1.00, ''),
	(4, 4, '2025-08-24', 0.00, ''),
	(5, 4, '2025-08-24', 0.00, ''),
	(6, 4, '2025-08-26', 5000.00, ''),
	(7, 4, '2025-08-24', 0.01, ''),
	(8, 4, '2025-08-26', 4.00, 'milliliter'),
	(9, 4, '2025-08-26', 5.00, 'liter'),
	(10, 2, '2025-09-04', 3.00, 'box');

-- Dumping structure for table wedding_bliss.stock_additions
CREATE TABLE IF NOT EXISTS `stock_additions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `location` varchar(255) NOT NULL,
  `quantity` int NOT NULL,
  `added_date` datetime NOT NULL,
  `grn_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `grn_id` (`grn_id`),
  CONSTRAINT `stock_additions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  CONSTRAINT `stock_additions_ibfk_2` FOREIGN KEY (`grn_id`) REFERENCES `logistics_grn` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.stock_additions: ~2 rows (approximately)
INSERT INTO `stock_additions` (`id`, `item_id`, `location`, `quantity`, `added_date`, `grn_id`) VALUES
	(1, 6, 'Main Warehouse', 20, '2025-08-26 16:40:57', 20),
	(2, 6, 'Main Warehouse', 10, '2025-08-26 16:43:48', 21);

-- Dumping structure for table wedding_bliss.storeresponsible
CREATE TABLE IF NOT EXISTS `storeresponsible` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.storeresponsible: ~0 rows (approximately)
INSERT INTO `storeresponsible` (`id`, `name`, `password`) VALUES
	(3, 'Sachith', '$2y$10$pjUFCgb38sd20yQ6Cen9.OmOAC3Xpr4EwvU.sOKEAc/5/KsL.V6tu');

-- Dumping structure for table wedding_bliss.storesregistration_access
CREATE TABLE IF NOT EXISTS `storesregistration_access` (
  `id` int NOT NULL AUTO_INCREMENT,
  `is_locked` tinyint(1) DEFAULT '1',
  `unlock_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.storesregistration_access: ~0 rows (approximately)
INSERT INTO `storesregistration_access` (`id`, `is_locked`, `unlock_key`, `updated_at`) VALUES
	(2, 0, '1', '2025-08-01 05:53:37');

-- Dumping structure for table wedding_bliss.store_users
CREATE TABLE IF NOT EXISTS `store_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username` (`username`) USING BTREE,
  UNIQUE KEY `email` (`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.store_users: ~0 rows (approximately)
INSERT INTO `store_users` (`id`, `username`, `password`, `email`, `is_admin`, `created_at`) VALUES
	(6, 'Sachith', '$2y$10$HSnQjXKjFVn9bp3BcwcvxeMPGaMw5ArkM.SZXOJEm9PWwQca5fYoy', NULL, 0, '2025-08-01 05:53:51');

-- Dumping structure for table wedding_bliss.suppliers
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `remarks` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.suppliers: ~0 rows (approximately)
INSERT INTO `suppliers` (`id`, `name`, `contact_number`, `address`, `email`, `remarks`, `created_at`) VALUES
	(1, 'Sachith Gamage', '0725876139', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', 'udarasachith41@gmail.com', 'Chicken Supplier', '2025-07-22 14:02:09');

-- Dumping structure for table wedding_bliss.supplier_payments
CREATE TABLE IF NOT EXISTS `supplier_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_date` date DEFAULT NULL,
  `function_date` date DEFAULT NULL,
  `dj_deco_band_dance_cake_car_other` text,
  `function_type` varchar(255) DEFAULT NULL,
  `day_or_night` varchar(10) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `hall_or_location` varchar(255) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `pax` int DEFAULT NULL,
  `front_or_back_officer_name` varchar(255) DEFAULT NULL,
  `officer_signature` text,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `hall_supervisor_name` varchar(255) DEFAULT NULL,
  `hall_supervisor_signature` text,
  `hall_supervisor_sign_time` time DEFAULT NULL,
  `banquet_manager_signature` text,
  `banquet_manager_sign_time` time DEFAULT NULL,
  `sales_or_senior_manager_name` varchar(255) DEFAULT NULL,
  `sales_signature` text,
  `sales_sign_time` time DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `sales_seal` text,
  `booking_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.supplier_payments: ~2 rows (approximately)
INSERT INTO `supplier_payments` (`id`, `request_date`, `function_date`, `dj_deco_band_dance_cake_car_other`, `function_type`, `day_or_night`, `customer_name`, `hall_or_location`, `supplier_name`, `pax`, `front_or_back_officer_name`, `officer_signature`, `start_time`, `end_time`, `hall_supervisor_name`, `hall_supervisor_signature`, `hall_supervisor_sign_time`, `banquet_manager_signature`, `banquet_manager_sign_time`, `sales_or_senior_manager_name`, `sales_signature`, `sales_sign_time`, `amount`, `sales_seal`, `booking_code`, `created_at`) VALUES
	(1, '2025-09-10', '2025-08-16', 'Dj', 'Home Coming', 'Day', 'Sachith Gamage', 'Grand Ball Room', 'Joshap', 390, 'Hirushi', '', '22:24:00', '00:26:00', 'Kanchana', '', '12:24:00', '', '12:24:00', 'Gayana', '', '12:24:00', 123000.00, '', '8425', '2025-09-10 06:55:09'),
	(2, '2025-09-10', '2025-08-16', 'Dj', 'Home Coming', 'Night', 'Sachith Gamage', 'Grand Ball Room', 'Joshap', 390, 'Hirushi', '', '00:00:00', '00:00:00', 'Kanchana', '', '12:28:00', '', '12:28:00', 'Gayana', '', '12:28:00', 13000.00, '', '8425', '2025-09-10 06:59:09'),
	(3, '2025-09-10', '2025-08-16', 'Dj', 'Home Coming', 'Day', 'Sachith Gamage', 'Grand Ball Room', 'Joshap', 390, 'Hirushi', '', '00:00:00', '00:00:00', 'Kanchana', '', '12:31:00', '', '12:31:00', 'Gayana', '', '12:31:00', 13000.00, '', '8425', '2025-09-10 07:02:05');

-- Dumping structure for table wedding_bliss.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.users: ~4 rows (approximately)
INSERT INTO `users` (`id`, `username`, `password`, `email`, `is_admin`, `created_at`) VALUES
	(3, 'Admin', '1234', 'admin@example.com', 1, '2025-06-24 06:00:48'),
	(4, 'Sachith', '$2y$10$vLSBqkjACeWNN05YP.O57OX1PPZH7fpnVqDg/sRJo3pYhSJAPPKCG', '', 0, '2025-06-24 06:13:20'),
	(16, 'tilakj', '$2y$10$spjfHdigftRo7KPHeKoJbu8hRsO1hQ/c4jPNbEoz2VT5ze6c1RgKu', 'new@gmail.com', 0, '2025-06-24 06:48:26'),
	(17, 'Sachith Gamage', '$2y$10$hRvrT53lNEQk6JPzsN7s.OmlWMW5oOQa.UEbMyHf7Sd7CJXO02Ssm', NULL, 0, '2025-06-24 06:51:45');

-- Dumping structure for table wedding_bliss.venues
CREATE TABLE IF NOT EXISTS `venues` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.venues: ~4 rows (approximately)
INSERT INTO `venues` (`id`, `name`) VALUES
	(1, 'Grand Ball Room'),
	(2, 'Grand Ball Room'),
	(3, 'Red'),
	(4, 'Home'),
	(5, 'Red Hall'),
	(6, 'Orange');

-- Dumping structure for table wedding_bliss.wedding_bookings
CREATE TABLE IF NOT EXISTS `wedding_bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `contact_no1` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `contact_no2` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `booking_date` date DEFAULT NULL,
  `time_from` time DEFAULT NULL,
  `time_from_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `time_to` time DEFAULT NULL,
  `time_to_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `couple_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `groom_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `bride_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `venue_id` int DEFAULT NULL,
  `menu_id` int DEFAULT NULL,
  `function_type_id` int DEFAULT NULL,
  `day_or_night` enum('day','night') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `no_of_pax` int DEFAULT NULL,
  `floor_coordinator` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `drinks_coordinator` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `bride_dressing` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `groom_dressing` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `bride_arrival_time` time DEFAULT NULL,
  `bride_arrival_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `groom_arrival_time` time DEFAULT NULL,
  `groom_arrival_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `morning_tea_time_from` time DEFAULT NULL,
  `morning_tea_time_from_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `morning_tea_time_to` time DEFAULT NULL,
  `morning_tea_time_to_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `tea_pax` int DEFAULT NULL,
  `kiribath` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `poruwa_time_from` time DEFAULT NULL,
  `poruwa_time_from_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `poruwa_time_to` time DEFAULT NULL,
  `poruwa_time_to_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `poruwa_direction` enum('north','east','south','west') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `registration_time_from` time DEFAULT NULL,
  `registration_time_from_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `registration_time_to` time DEFAULT NULL,
  `registration_time_to_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `registration_direction` enum('north','east','south','west') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `welcome_drink_time` time DEFAULT NULL,
  `welcome_drink_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `floor_table_arrangement` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `drinks_time` time DEFAULT NULL,
  `drinks_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `drinks_pax` int DEFAULT NULL,
  `drink_serving` enum('shot','bottle') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `bites_source` enum('other','customer') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `bite_items` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `buffet_open` time DEFAULT NULL,
  `buffet_open_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `buffet_close` time DEFAULT NULL,
  `buffet_close_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `buffet_type` enum('one_way','two_way') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `ice_coffee_time` time DEFAULT NULL,
  `ice_coffee_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `music_close_time` time DEFAULT NULL,
  `music_close_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `departure_time` time DEFAULT NULL,
  `departure_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `etc_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `music_type_id` int DEFAULT NULL,
  `wedding_car_id` int DEFAULT NULL,
  `jayamangala_gatha_id` int DEFAULT NULL,
  `wes_dance_id` int DEFAULT NULL,
  `ashtaka_id` int DEFAULT NULL,
  `welcome_song_id` int DEFAULT NULL,
  `indian_dhol_id` int DEFAULT NULL,
  `floor_dance_id` int DEFAULT NULL,
  `head_table` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `chair_cover` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `table_cloth` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `top_cloth` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `bow` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `napkin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `vip` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `changing_room_date` date DEFAULT NULL,
  `changing_room_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `honeymoon_room_date` date DEFAULT NULL,
  `honeymoon_room_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `dressing_room_date` date DEFAULT NULL,
  `dressing_room_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `theme_color` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `flower_decor` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `car_decoration` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `milk_fountain` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `champaign` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `cultural_table` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `kiribath_structure` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `cake_structure` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `projector_screen` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `gsky_arrival_time` time DEFAULT NULL,
  `gsky_arrival_time_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `photo_team_count` int DEFAULT NULL,
  `bridal_team_count` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT (now()),
  `booking_reference` char(4) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `booking_reference` (`booking_reference`),
  KEY `venue_id` (`venue_id`) USING BTREE,
  KEY `menu_id` (`menu_id`) USING BTREE,
  KEY `function_type_id` (`function_type_id`) USING BTREE,
  KEY `music_type_id` (`music_type_id`) USING BTREE,
  KEY `wedding_car_id` (`wedding_car_id`) USING BTREE,
  KEY `jayamangala_gatha_id` (`jayamangala_gatha_id`) USING BTREE,
  KEY `wes_dance_id` (`wes_dance_id`) USING BTREE,
  KEY `ashtaka_id` (`ashtaka_id`) USING BTREE,
  KEY `welcome_song_id` (`welcome_song_id`) USING BTREE,
  KEY `indian_dhol_id` (`indian_dhol_id`) USING BTREE,
  KEY `floor_dance_id` (`floor_dance_id`) USING BTREE,
  CONSTRAINT `wedding_bookings_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`),
  CONSTRAINT `wedding_bookings_ibfk_10` FOREIGN KEY (`indian_dhol_id`) REFERENCES `indian_dhols` (`id`),
  CONSTRAINT `wedding_bookings_ibfk_11` FOREIGN KEY (`floor_dance_id`) REFERENCES `floor_dances` (`id`),
  CONSTRAINT `wedding_bookings_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`),
  CONSTRAINT `wedding_bookings_ibfk_3` FOREIGN KEY (`function_type_id`) REFERENCES `function_types` (`id`),
  CONSTRAINT `wedding_bookings_ibfk_4` FOREIGN KEY (`music_type_id`) REFERENCES `music_types` (`id`),
  CONSTRAINT `wedding_bookings_ibfk_5` FOREIGN KEY (`wedding_car_id`) REFERENCES `wedding_cars` (`id`),
  CONSTRAINT `wedding_bookings_ibfk_6` FOREIGN KEY (`jayamangala_gatha_id`) REFERENCES `jayamangala_gathas` (`id`),
  CONSTRAINT `wedding_bookings_ibfk_7` FOREIGN KEY (`wes_dance_id`) REFERENCES `wes_dances` (`id`),
  CONSTRAINT `wedding_bookings_ibfk_8` FOREIGN KEY (`ashtaka_id`) REFERENCES `ashtakas` (`id`),
  CONSTRAINT `wedding_bookings_ibfk_9` FOREIGN KEY (`welcome_song_id`) REFERENCES `welcome_songs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.wedding_bookings: ~25 rows (approximately)
INSERT INTO `wedding_bookings` (`id`, `full_name`, `contact_no1`, `contact_no2`, `booking_date`, `time_from`, `time_from_am_pm`, `time_to`, `time_to_am_pm`, `couple_name`, `groom_address`, `bride_address`, `venue_id`, `menu_id`, `function_type_id`, `day_or_night`, `no_of_pax`, `floor_coordinator`, `drinks_coordinator`, `bride_dressing`, `groom_dressing`, `bride_arrival_time`, `bride_arrival_time_am_pm`, `groom_arrival_time`, `groom_arrival_time_am_pm`, `morning_tea_time_from`, `morning_tea_time_from_am_pm`, `morning_tea_time_to`, `morning_tea_time_to_am_pm`, `tea_pax`, `kiribath`, `poruwa_time_from`, `poruwa_time_from_am_pm`, `poruwa_time_to`, `poruwa_time_to_am_pm`, `poruwa_direction`, `registration_time_from`, `registration_time_from_am_pm`, `registration_time_to`, `registration_time_to_am_pm`, `registration_direction`, `welcome_drink_time`, `welcome_drink_time_am_pm`, `floor_table_arrangement`, `drinks_time`, `drinks_time_am_pm`, `drinks_pax`, `drink_serving`, `bites_source`, `bite_items`, `buffet_open`, `buffet_open_am_pm`, `buffet_close`, `buffet_close_am_pm`, `buffet_type`, `ice_coffee_time`, `ice_coffee_time_am_pm`, `music_close_time`, `music_close_time_am_pm`, `departure_time`, `departure_time_am_pm`, `etc_description`, `music_type_id`, `wedding_car_id`, `jayamangala_gatha_id`, `wes_dance_id`, `ashtaka_id`, `welcome_song_id`, `indian_dhol_id`, `floor_dance_id`, `head_table`, `chair_cover`, `table_cloth`, `top_cloth`, `bow`, `napkin`, `vip`, `changing_room_date`, `changing_room_number`, `honeymoon_room_date`, `honeymoon_room_number`, `dressing_room_date`, `dressing_room_number`, `theme_color`, `flower_decor`, `car_decoration`, `milk_fountain`, `champaign`, `cultural_table`, `kiribath_structure`, `cake_structure`, `projector_screen`, `gsky_arrival_time`, `gsky_arrival_time_am_pm`, `photo_team_count`, `bridal_team_count`, `created_at`, `booking_reference`) VALUES
	(8, 'Sachith Gamage', '0725876139', '', '2025-06-17', '07:38:00', 'AM', '07:38:00', 'AM', 'qwewq', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', 2, 1, 1, NULL, NULL, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', 1, 1, 1, 1, 1, NULL, 1, 1, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-06-08 11:08:33', NULL),
	(9, 'Thisura', '', '', '2025-06-17', '00:00:00', 'AM', '00:00:00', 'AM', '', '', '', 1, NULL, NULL, NULL, NULL, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-06-08 11:13:57', NULL),
	(10, 'Tharindu Damsara', '', '', '2025-06-25', '00:00:00', 'AM', '00:00:00', 'AM', '', '', '', 2, 1, 1, NULL, NULL, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-06-08 11:17:29', NULL),
	(11, 'Sachith Gamage', '0725876139', '', '2025-06-23', '00:00:00', 'AM', '00:00:00', 'AM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 1, 1, 1, NULL, NULL, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-06-08 11:26:08', NULL),
	(12, 'Sachith Alponsu', '0725876139', '', '2025-06-26', '01:14:00', 'AM', '00:00:00', 'PM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 1, 1, 2, NULL, NULL, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-08 16:04:35', '5206'),
	(13, 'Sachith Gamage', '0725876139', '', '2025-06-17', '00:00:00', 'AM', '00:00:00', 'AM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 3, 1, 1, NULL, NULL, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-06-08 16:11:56', '6876'),
	(14, 'Sachith Gamage', '0725876139', '', '2025-06-13', '00:00:00', 'AM', '00:00:00', 'AM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, 2, NULL, NULL, NULL, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-06-08 16:20:01', '5702'),
	(17, 'Sachith Gamage', '0725876139', '', '2025-06-27', '00:00:00', 'AM', '00:00:00', 'AM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 2, 1, 1, NULL, NULL, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-06-08 16:35:17', '5631'),
	(18, 'Sachith Gamage', '0725876139', '', '2025-06-12', '00:00:00', 'AM', '00:00:00', 'AM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 2, 2, 3, NULL, NULL, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-06-08 16:36:05', '8420'),
	(19, 'Sachith BOBY', '0725876139', '', '2025-06-26', '00:00:00', NULL, '00:00:00', NULL, '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 4, 2, 4, NULL, NULL, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '12:30:00', 'PM', '10:34:00', 'AM', 'south', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', 1, 1, 1, 1, 1, 1, 1, 1, '', '', '', 'Red', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '02:00:00', 'AM', NULL, NULL, '2025-06-09 02:37:15', '8421'),
	(20, 'Sachith Ruchira', '0725876139', '', '2025-06-19', '10:09:00', 'AM', '02:06:00', 'PM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 1, 1, 4, NULL, NULL, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-09 02:38:31', '8422'),
	(21, 'Sachith Bionse', '0725876139', '', '2025-06-27', '10:58:00', 'AM', '05:50:00', 'PM', 'Maneka & Janaka', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 5, 1, 4, 'day', 780, '', '', '', '', '03:05:00', 'AM', '04:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', 3, 2, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '02:00:00', 'AM', NULL, NULL, '2025-06-09 07:17:54', '8423'),
	(22, 'Sachith Mia', '0725876139', '', '2025-06-21', '00:00:00', 'AM', '00:00:00', 'AM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '02:00:00', 'AM', NULL, NULL, '2025-06-10 04:11:24', '8424'),
	(23, 'Sachith Praneetha', '0725876139', '', '2025-08-16', '00:00:00', NULL, '00:00:00', NULL, 'Mia & Jana', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, 4, NULL, NULL, 290, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', '2025-06-19', '200', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-10 05:02:04', '8425'),
	(24, 'Sachith Gamage', '0725876139', '0725876139', '2025-09-12', '09:12:00', 'AM', '02:17:00', 'PM', 'test', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 5, 2, 1, 'day', 240, '', '', '', '', '09:20:00', 'AM', '09:20:00', 'AM', '09:20:00', 'AM', '09:20:00', 'AM', 230, '230', '09:21:00', 'AM', '09:21:00', 'AM', 'east', '09:21:00', 'AM', '09:21:00', 'AM', 'east', '09:24:00', 'AM', 'test', '09:24:00', 'AM', 24, 'shot', 'other', 'test', '03:25:00', 'PM', '04:25:00', 'PM', 'one_way', '05:25:00', 'PM', '05:25:00', 'PM', '05:30:00', 'PM', 'test', 1, 1, 1, 1, 1, 1, 1, 1, 'test', 'White', 'Blue', 'Red', 'Red', 'White', 'red', '2025-08-29', '201', '2025-09-01', '203', '2025-08-29', '203', 'black', 'test', 'test', 'No need', 'No need', 'test', 'test', 'test', 'test', '04:28:00', 'PM', 3, 3, '2025-08-29 03:58:49', '8426'),
	(25, 'Sachith Gamage', '0725876139', '0741773588', '2025-08-25', '07:56:00', 'AM', '07:56:00', 'AM', 'Suchini and nakini', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, NULL, NULL, NULL, 230, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-08-30 14:27:49', '8427'),
	(26, 'Sachith Gamage', '0725876139', '', '2025-08-30', '11:47:00', 'PM', '00:00:00', 'AM', 'nayanai and bayani', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, 1, NULL, NULL, 5000, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-08-30 15:18:01', '8428'),
	(27, 'Gayani mADURI', '0725876139', '0741773588', '2025-08-31', '00:00:00', 'AM', '00:00:00', 'PM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, 5, 4, 'day', 1200, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'PM', '00:00:00', 'PM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-08-31 03:50:47', '8429'),
	(28, 'Sachith Gamage', '0725876139', '', '2025-08-31', '00:00:00', 'PM', '00:00:00', 'AM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, 5, 3, 'day', 1200, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-08-31 03:52:30', '8430'),
	(29, 'Sachith Gamage', '0725876139', '', '2025-08-31', '00:00:00', 'PM', '00:00:00', 'PM', 'Suchi $ Bchi', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, 2, NULL, 'night', 230, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-08-31 04:19:04', '8431'),
	(30, 'Udith Nishantha', '0725876139', '0112321122', '2025-09-01', '00:00:00', 'PM', '00:00:00', 'AM', 'Navoda and Subodh', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 1, 6, 4, 'night', 245, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', 'east', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', 1, 1, 1, 1, 1, 1, 1, 1, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-08-31 04:41:00', '8432'),
	(31, 'Sachith Gamage', '0725876139', '', '2025-08-31', '12:11:00', 'PM', '12:15:00', 'PM', 'qwewq', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 2, 6, 2, 'day', 13456, '', '', '', '', '10:14:00', 'PM', '12:16:00', 'PM', '10:17:00', 'PM', '10:18:00', 'PM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', 1, 2, 1, 1, 1, 1, 1, 1, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-08-31 04:44:03', '8433'),
	(32, 'Sachith Gamage', '0725876139', '', '2025-09-10', '00:00:00', 'PM', '00:00:00', 'PM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 2, 6, 3, 'night', 1239, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', 1, 1, 1, 1, 1, 1, 1, 1, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '12:45:00', 'PM', NULL, NULL, '2025-08-31 05:15:16', '8434'),
	(33, 'Sachith Gamage', '0725876139', '', '2025-08-31', '11:51:00', 'PM', '10:54:00', 'PM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 1, 1, 2, 'day', 124, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', 1, 1, 1, 1, 1, 1, 1, 1, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-08-31 05:23:17', '8435'),
	(34, 'Test Test', '0725876139', '0741773588', '2025-09-01', '12:55:00', 'AM', '11:56:00', 'PM', 'test test', 'No 17/20,Test,test', 'No 17/20,Test,test', 5, 6, 1, 'day', 230, 'test test', 'test test', 'test', 'test', '10:56:00', 'AM', '10:56:00', 'AM', '01:58:00', 'PM', '01:58:00', 'AM', 120, '230', '10:00:00', 'AM', '12:56:00', 'PM', 'east', '10:59:00', 'AM', '10:58:00', 'AM', 'east', '11:57:00', 'AM', 'test test test', '10:57:00', 'AM', 120, 'bottle', 'other', 'test\ntest\ntest', '11:58:00', 'AM', '10:58:00', 'AM', 'one_way', '10:58:00', 'AM', '10:00:00', 'AM', '10:59:00', 'AM', 'test', 2, 1, 1, 1, 1, 1, 1, 1, 'test test test test', 'test', 'test', 'test', 'test', 'test', 'test', '2025-09-01', '203', '2025-09-10', '209', '2025-09-01', '120', 'test', 'test', 'test', 'test', 'test', 'test test test test', 'test test test test', 'test test test test', 'test test test test', '10:58:00', 'AM', 12, 12, '2025-09-01 05:29:04', '8436');

-- Dumping structure for table wedding_bliss.wedding_bookings_history
CREATE TABLE IF NOT EXISTS `wedding_bookings_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_reference` varchar(50) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `contact_no1` varchar(20) DEFAULT NULL,
  `contact_no2` varchar(20) DEFAULT NULL,
  `booking_date` date DEFAULT NULL,
  `time_from` time DEFAULT NULL,
  `time_from_am_pm` enum('AM','PM') DEFAULT NULL,
  `time_to` time DEFAULT NULL,
  `time_to_am_pm` enum('AM','PM') DEFAULT NULL,
  `couple_name` varchar(255) DEFAULT NULL,
  `groom_address` text,
  `bride_address` text,
  `venue_id` int DEFAULT NULL,
  `menu_id` int DEFAULT NULL,
  `function_type_id` int DEFAULT NULL,
  `day_or_night` enum('day','night') DEFAULT NULL,
  `no_of_pax` int DEFAULT NULL,
  `floor_coordinator` varchar(255) DEFAULT NULL,
  `drinks_coordinator` varchar(255) DEFAULT NULL,
  `bride_dressing` text,
  `groom_dressing` text,
  `bride_arrival_time` time DEFAULT NULL,
  `bride_arrival_time_am_pm` enum('AM','PM') DEFAULT NULL,
  `groom_arrival_time` time DEFAULT NULL,
  `groom_arrival_time_am_pm` enum('AM','PM') DEFAULT NULL,
  `morning_tea_time_from` time DEFAULT NULL,
  `morning_tea_time_from_am_pm` enum('AM','PM') DEFAULT NULL,
  `morning_tea_time_to` time DEFAULT NULL,
  `morning_tea_time_to_am_pm` enum('AM','PM') DEFAULT NULL,
  `tea_pax` int DEFAULT NULL,
  `kiribath` text,
  `poruwa_time_from` time DEFAULT NULL,
  `poruwa_time_from_am_pm` enum('AM','PM') DEFAULT NULL,
  `poruwa_time_to` time DEFAULT NULL,
  `poruwa_time_to_am_pm` enum('AM','PM') DEFAULT NULL,
  `poruwa_direction` enum('north','east','south','west') DEFAULT NULL,
  `registration_time_from` time DEFAULT NULL,
  `registration_time_from_am_pm` enum('AM','PM') DEFAULT NULL,
  `registration_time_to` time DEFAULT NULL,
  `registration_time_to_am_pm` enum('AM','PM') DEFAULT NULL,
  `registration_direction` enum('north','east','south','west') DEFAULT NULL,
  `welcome_drink_time` time DEFAULT NULL,
  `welcome_drink_time_am_pm` enum('AM','PM') DEFAULT NULL,
  `floor_table_arrangement` text,
  `drinks_time` time DEFAULT NULL,
  `drinks_time_am_pm` enum('AM','PM') DEFAULT NULL,
  `drinks_pax` int DEFAULT NULL,
  `drink_serving` enum('shot','bottle') DEFAULT NULL,
  `bites_source` enum('other','customer') DEFAULT NULL,
  `bite_items` text,
  `buffet_open` time DEFAULT NULL,
  `buffet_open_am_pm` enum('AM','PM') DEFAULT NULL,
  `buffet_close` time DEFAULT NULL,
  `buffet_close_am_pm` enum('AM','PM') DEFAULT NULL,
  `buffet_type` enum('one_way','two_way') DEFAULT NULL,
  `ice_coffee_time` time DEFAULT NULL,
  `ice_coffee_time_am_pm` enum('AM','PM') DEFAULT NULL,
  `music_close_time` time DEFAULT NULL,
  `music_close_time_am_pm` enum('AM','PM') DEFAULT NULL,
  `departure_time` time DEFAULT NULL,
  `departure_time_am_pm` enum('AM','PM') DEFAULT NULL,
  `etc_description` text,
  `music_type_id` int DEFAULT NULL,
  `wedding_car_id` int DEFAULT NULL,
  `jayamangala_gatha_id` int DEFAULT NULL,
  `wes_dance_id` int DEFAULT NULL,
  `ashtaka_id` int DEFAULT NULL,
  `welcome_song_id` int DEFAULT NULL,
  `indian_dhol_id` int DEFAULT NULL,
  `floor_dance_id` int DEFAULT NULL,
  `head_table` text,
  `chair_cover` text,
  `table_cloth` text,
  `top_cloth` text,
  `bow` text,
  `napkin` text,
  `vip` text,
  `changing_room_date` date DEFAULT NULL,
  `changing_room_number` varchar(50) DEFAULT NULL,
  `honeymoon_room_date` date DEFAULT NULL,
  `honeymoon_room_number` varchar(50) DEFAULT NULL,
  `dressing_room_date` date DEFAULT NULL,
  `dressing_room_number` varchar(50) DEFAULT NULL,
  `theme_color` text,
  `flower_decor` text,
  `car_decoration` text,
  `milk_fountain` text,
  `champaign` text,
  `cultural_table` text,
  `kiribath_structure` text,
  `cake_structure` text,
  `projector_screen` text,
  `gsky_arrival_time` time DEFAULT NULL,
  `gsky_arrival_time_am_pm` enum('AM','PM') DEFAULT NULL,
  `photo_team_count` int DEFAULT NULL,
  `bridal_team_count` int DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_booking_reference` (`booking_reference`),
  KEY `idx_updated_at` (`updated_at`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.wedding_bookings_history: ~31 rows (approximately)
INSERT INTO `wedding_bookings_history` (`id`, `booking_reference`, `full_name`, `contact_no1`, `contact_no2`, `booking_date`, `time_from`, `time_from_am_pm`, `time_to`, `time_to_am_pm`, `couple_name`, `groom_address`, `bride_address`, `venue_id`, `menu_id`, `function_type_id`, `day_or_night`, `no_of_pax`, `floor_coordinator`, `drinks_coordinator`, `bride_dressing`, `groom_dressing`, `bride_arrival_time`, `bride_arrival_time_am_pm`, `groom_arrival_time`, `groom_arrival_time_am_pm`, `morning_tea_time_from`, `morning_tea_time_from_am_pm`, `morning_tea_time_to`, `morning_tea_time_to_am_pm`, `tea_pax`, `kiribath`, `poruwa_time_from`, `poruwa_time_from_am_pm`, `poruwa_time_to`, `poruwa_time_to_am_pm`, `poruwa_direction`, `registration_time_from`, `registration_time_from_am_pm`, `registration_time_to`, `registration_time_to_am_pm`, `registration_direction`, `welcome_drink_time`, `welcome_drink_time_am_pm`, `floor_table_arrangement`, `drinks_time`, `drinks_time_am_pm`, `drinks_pax`, `drink_serving`, `bites_source`, `bite_items`, `buffet_open`, `buffet_open_am_pm`, `buffet_close`, `buffet_close_am_pm`, `buffet_type`, `ice_coffee_time`, `ice_coffee_time_am_pm`, `music_close_time`, `music_close_time_am_pm`, `departure_time`, `departure_time_am_pm`, `etc_description`, `music_type_id`, `wedding_car_id`, `jayamangala_gatha_id`, `wes_dance_id`, `ashtaka_id`, `welcome_song_id`, `indian_dhol_id`, `floor_dance_id`, `head_table`, `chair_cover`, `table_cloth`, `top_cloth`, `bow`, `napkin`, `vip`, `changing_room_date`, `changing_room_number`, `honeymoon_room_date`, `honeymoon_room_number`, `dressing_room_date`, `dressing_room_number`, `theme_color`, `flower_decor`, `car_decoration`, `milk_fountain`, `champaign`, `cultural_table`, `kiribath_structure`, `cake_structure`, `projector_screen`, `gsky_arrival_time`, `gsky_arrival_time_am_pm`, `photo_team_count`, `bridal_team_count`, `updated_at`) VALUES
	(1, '8423', 'Sachith Gamage', '0725876139', '', '2025-06-26', '03:51:00', 'AM', '05:50:00', 'PM', 'Maneka & Janaka', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 5, 1, 4, 'day', 420, '', '', '', '', '03:05:00', 'AM', '04:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', 3, 1, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '03:00:00', 'AM', NULL, NULL, '2025-06-10 09:05:53'),
	(2, '8423', 'Sachith Gamage', '0725876139', '', '2025-06-26', '03:51:00', 'AM', '05:50:00', 'PM', 'Maneka & Janaka', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 5, 1, 4, 'day', 420, '', '', '', '', '03:05:00', 'AM', '04:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', 3, 1, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', 'AM', NULL, NULL, '2025-06-10 09:12:03'),
	(3, '8423', 'Sachith Udara', '0725876139', '', '2025-06-27', '03:54:00', 'AM', '05:50:00', 'PM', 'Maneka & Janaka', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 5, 1, 4, 'day', 420, '', '', '', '', '03:05:00', 'AM', '04:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', 3, 2, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '02:00:00', 'AM', NULL, NULL, '2025-06-10 09:31:12'),
	(4, '8424', 'Sachith Udara', '0725876139', '', '2025-06-21', '00:00:00', 'AM', '00:00:00', 'AM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '02:00:00', 'AM', NULL, NULL, '2025-06-10 09:42:27'),
	(5, '8424', 'Sachith Gamage', '0725876139', '', '2025-06-21', '00:00:00', 'AM', '00:00:00', 'AM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '02:00:00', 'AM', NULL, NULL, '2025-06-10 09:48:32'),
	(6, '8423', 'Sachith Mia', '0725876139', '', '2025-06-27', '08:58:00', 'AM', '05:50:00', 'PM', 'Maneka & Janaka', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 5, 1, 4, 'day', 780, '', '', '', '', '03:05:00', 'AM', '04:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', 3, 2, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '02:00:00', 'AM', NULL, NULL, '2025-06-10 09:58:48'),
	(7, '8423', 'Sachith Bilal', '0725876139', '', '2025-06-27', '10:58:00', 'AM', '05:50:00', 'PM', 'Maneka & Janaka', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 5, 1, 4, 'day', 780, '', '', '', '', '03:05:00', 'AM', '04:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', 3, 2, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '02:00:00', 'AM', NULL, NULL, '2025-06-10 10:02:12'),
	(8, '8423', 'Sachith Bilal', '0725876139', '', '2025-06-27', '10:58:00', 'AM', '05:50:00', 'PM', 'Maneka & Janaka', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 5, 1, 4, 'day', 780, '', '', '', '', '03:05:00', 'AM', '04:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', 3, 2, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '02:00:00', 'AM', NULL, NULL, '2025-06-10 10:09:38'),
	(9, '8423', 'Sachith Gamage', '0725876139', '', '2025-06-27', '10:58:00', 'AM', '05:50:00', 'PM', 'Maneka & Janaka', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 5, 1, 4, 'day', 780, '', '', '', '', '03:05:00', 'AM', '04:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', 3, 2, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '02:00:00', 'AM', NULL, NULL, '2025-06-10 10:13:45'),
	(10, '8421', 'Sachith BOBY', '0725876139', '', '2025-06-26', '00:00:00', 'AM', '00:00:00', 'AM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 4, 2, 4, NULL, NULL, '', '', '', '', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '', '00:00:00', 'AM', NULL, NULL, NULL, '', '00:00:00', 'AM', '00:00:00', 'AM', NULL, '00:00:00', 'AM', '00:00:00', 'AM', '00:00:00', 'AM', '', 1, 1, 1, 1, 1, 1, 1, 1, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '02:00:00', 'AM', NULL, NULL, '2025-06-10 10:20:22'),
	(11, '8421', 'Sachith BOBY', '0725876139', '', '2025-06-26', '00:00:00', NULL, '00:00:00', NULL, '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 4, 2, 4, NULL, NULL, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', 1, 1, 1, 1, 1, 1, 1, 1, '', '', '', 'Red', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '02:00:00', 'AM', NULL, NULL, '2025-06-10 10:29:09'),
	(12, '8421', 'Sachith BOBY', '0725876139', '', '2025-06-26', '00:00:00', NULL, '00:00:00', NULL, '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 4, 2, 4, NULL, NULL, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '12:30:00', 'PM', '10:34:00', 'AM', 'south', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', 1, 1, 1, 1, 1, 1, 1, 1, '', '', '', 'Red', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '02:00:00', 'AM', NULL, NULL, '2025-06-10 10:30:27'),
	(13, '8425', 'Sachith Gamage', '0725876139', '', '2025-06-12', '00:00:00', NULL, '00:00:00', NULL, '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '200', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-10 10:32:55'),
	(14, '8425', 'Sachith Gamage', '0725876139', '', '2025-06-12', '00:00:00', NULL, '00:00:00', NULL, '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '200', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-10 10:33:16'),
	(15, '8425', 'Sachith Gamage', '0725876139', '', '2025-06-12', '00:00:00', NULL, '00:00:00', NULL, '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', '2025-06-19', '200', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-10 10:34:02'),
	(16, '8422', 'Sachith Gamage', '0725876139', '', '2025-06-27', '00:00:00', NULL, '00:00:00', NULL, '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 1, 1, 4, NULL, NULL, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-12 09:38:07'),
	(17, '8422', 'Sachith Gamage', '0725876139', '', '2025-06-14', '00:00:00', NULL, '00:00:00', NULL, '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 1, 1, 4, NULL, NULL, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-12 09:40:26'),
	(18, '8422', 'Sachith Udara', '0725876139', '', '2025-06-27', '00:00:00', NULL, '00:00:00', NULL, '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 6, 1, 4, NULL, 500, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-12 09:51:11'),
	(19, '8422', 'Sachith Ruchira', '0725876139', '', '2025-06-19', '10:09:00', 'AM', '02:06:00', 'PM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 1, 1, 4, NULL, 9000, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-12 10:06:46'),
	(20, '5206', 'Sachith Alponsu', '0725876139', '', '2025-06-26', '01:14:00', 'AM', '00:00:00', 'PM', '', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 1, 1, 2, NULL, NULL, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-12 10:12:19'),
	(21, '8425', 'Sachith Gamage', '0725876139', '', '2025-08-16', '00:00:00', NULL, '00:00:00', NULL, 'Tikiri & Sukiri', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', '2025-06-19', '200', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-12 10:26:22'),
	(22, '8425', 'Sachith Gamage', '0725876139', '', '2025-08-16', '00:00:00', NULL, '00:00:00', NULL, 'Tikiri & Sukiri', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, NULL, NULL, NULL, 900, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', '2025-06-19', '200', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-12 11:07:14'),
	(23, '8425', 'Sachith Mendis', '0725876139', '', '2025-08-16', '00:00:00', NULL, '00:00:00', NULL, 'Tikiri & Sukiri', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, 4, NULL, NULL, 900, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', '2025-06-19', '200', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-12 12:27:50'),
	(24, '8425', 'Sachith Praneeth', '0725876139', '', '2025-08-16', '00:00:00', NULL, '00:00:00', NULL, 'Tikiri & Sukiri', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, 4, NULL, NULL, 200, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', '2025-06-19', '200', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-17 10:56:59'),
	(25, '8425', 'Sachith Praneetha', '0725876139', '', '2025-08-16', '00:00:00', NULL, '00:00:00', NULL, 'Tikiri & Sukiri', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', NULL, 4, NULL, NULL, 290, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', '2025-06-19', '200', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-17 11:01:24'),
	(26, '8425', 'Sachith Prashanth', '0725876139', '', '2025-08-16', '00:00:00', NULL, '00:00:00', NULL, 'Tikiri & Sukiri', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', 'Maya road', 1, 4, NULL, NULL, 390, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', '2025-06-19', '200', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-17 11:09:03'),
	(27, '8425', 'Sachith Prashanth', '0725876139', '', '2025-08-16', '00:00:00', NULL, '00:00:00', NULL, 'Tikiri & Sukiri', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', 'Maya road', NULL, 4, 4, NULL, 390, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', '2025-06-19', '200', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-17 11:40:42'),
	(28, '8425', 'Sachith Prashanth', '0725876139', '', '2025-08-16', '00:00:00', NULL, '00:00:00', NULL, 'Tikiri & Sukiri', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', 'Maya road', 1, 4, 4, NULL, 390, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', '2025-06-19', '200', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-17 12:07:25'),
	(29, '8425', 'Sachith Gamage', '0725876139', '0706773588', '2025-09-11', '00:00:00', NULL, '00:00:00', NULL, 'Tikiri & Sukiri', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', 'Maya road', 1, 4, 4, NULL, 390, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', '2025-06-19', '200', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-06-22 16:52:17'),
	(30, '8427', 'Sachith Gamage', '0725876139', '0741773588', '2025-08-25', '07:56:00', 'AM', '07:56:00', 'AM', 'Suchini and nakini', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 3, 1, NULL, NULL, 230, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-08-30 19:58:49'),
	(31, '8426', 'Sachith Gamage', '0725876139', '0725876139', '2025-09-12', '09:12:00', 'AM', '02:17:00', 'PM', 'Sachith & Janani', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 5, 2, 1, 'day', 360, '', '', '', '', '09:20:00', 'AM', '09:20:00', 'AM', '09:20:00', 'AM', '09:20:00', 'AM', 230, '230', '09:21:00', 'AM', '09:21:00', 'AM', 'east', '09:21:00', 'AM', '09:21:00', 'AM', 'east', '09:24:00', 'AM', 'test', '09:24:00', 'AM', 24, 'shot', 'other', 'test', '03:25:00', 'PM', '04:25:00', 'PM', 'one_way', '05:25:00', 'PM', '05:25:00', 'PM', '05:30:00', 'PM', 'test', 1, 1, 1, 1, 1, 1, 1, 1, 'test', 'White', 'Blue', 'Red', 'Red', 'White', 'red', '2025-08-29', '201', '2025-09-01', '203', '2025-08-29', '203', 'black', 'test', 'test', 'No need', 'No need', 'test', 'test', 'test', 'test', '04:28:00', 'PM', 3, 3, '2025-08-30 20:04:07'),
	(33, '8431', 'Sachith Gamage', '0725876139', '', '2025-09-10', '11:55:00', 'AM', '12:55:00', 'PM', 'Suchi $ Bchi', 'No.17/30,Daladawaththa 2nd lane,Thalpitiya,Wadduwa', '', 1, 2, 4, 'night', 230, '', '', '', '', '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '', '00:00:00', NULL, NULL, NULL, NULL, '', '00:00:00', NULL, '00:00:00', NULL, NULL, '00:00:00', NULL, '00:00:00', NULL, '00:00:00', NULL, '', 3, 1, 1, 1, 1, 1, 1, 1, '', '', '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', '', '', '', '', '', '00:00:00', NULL, NULL, NULL, '2025-08-31 09:56:15'),
	(34, '8436', 'Test ', '0725876139', '0741773588', '2025-09-11', '12:55:00', 'AM', '11:56:00', 'PM', 'test test', 'No 17/20,Test,test', 'No 17/20,Test,test', 2, 1, 1, 'day', 240, 'test test', 'test test', 'test', 'test', '10:56:00', 'AM', '10:56:00', 'AM', '01:58:00', 'PM', '01:58:00', 'AM', 120, '230', '10:00:00', 'AM', '12:56:00', 'PM', 'east', '10:59:00', 'AM', '10:58:00', 'AM', 'east', '11:57:00', 'AM', 'test test test', '10:57:00', 'AM', 120, 'bottle', 'other', 'test\ntest\ntest', '11:58:00', 'AM', '10:58:00', 'AM', 'one_way', '10:58:00', 'AM', '10:00:00', 'AM', '10:59:00', 'AM', 'test', 2, 1, 1, 1, 1, 1, 1, 1, 'test test test test', 'test', 'test', 'test', 'test', 'test', 'test', '2025-09-01', '203', '2025-09-10', '209', '2025-09-01', '120', 'test', 'test', 'test', 'test', 'test', 'test test test test', 'test test test test', 'test test test test', 'test test test test', '10:58:00', 'AM', 12, 12, '2025-09-01 13:48:53');

-- Dumping structure for table wedding_bliss.wedding_cars
CREATE TABLE IF NOT EXISTS `wedding_cars` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.wedding_cars: ~0 rows (approximately)
INSERT INTO `wedding_cars` (`id`, `name`) VALUES
	(1, 'Benze'),
	(2, 'Maruti');

-- Dumping structure for table wedding_bliss.welcome_songs
CREATE TABLE IF NOT EXISTS `welcome_songs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.welcome_songs: ~0 rows (approximately)
INSERT INTO `welcome_songs` (`id`, `name`) VALUES
	(1, 'By Customer');

-- Dumping structure for table wedding_bliss.wes_dances
CREATE TABLE IF NOT EXISTS `wes_dances` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table wedding_bliss.wes_dances: ~0 rows (approximately)
INSERT INTO `wes_dances` (`id`, `name`) VALUES
	(1, 'By Customer');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
