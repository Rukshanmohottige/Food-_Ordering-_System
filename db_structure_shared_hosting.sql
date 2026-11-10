-- University Canteen Food Ordering System
-- Shared Hosting SQL Import (InfinityFree / ByetHost / cPanel)
-- Import this file inside your ALREADY-CREATED database in phpMyAdmin.

-- 1. Users table (students, staff, admins)
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `user_role` ENUM('student', 'staff', 'admin') NOT NULL DEFAULT 'student',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Menu items table
CREATE TABLE IF NOT EXISTS `menu_items` (
  `item_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `category` VARCHAR(60) NOT NULL DEFAULT 'Mains',
  `description` TEXT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `prep_minutes` INT NOT NULL DEFAULT 10,
  `image_url` VARCHAR(255) NULL,
  `is_available` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Orders table
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('pending', 'preparing', 'ready', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  `take_option` ENUM('takeaway', 'dine-in') NOT NULL DEFAULT 'takeaway',
  `preparation_time` INT NOT NULL DEFAULT 10,
  `notes` VARCHAR(255) NULL,
  `order_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Order items table
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `price` DECIMAL(10, 2) NOT NULL,
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_menu` FOREIGN KEY (`item_id`) REFERENCES `menu_items`(`item_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `message` VARCHAR(255) NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Daily reports table
CREATE TABLE IF NOT EXISTS `daily_reports` (
  `report_id` INT AUTO_INCREMENT PRIMARY KEY,
  `report_date` DATE NOT NULL UNIQUE,
  `total_orders` INT NOT NULL DEFAULT 0,
  `completed_orders` INT NOT NULL DEFAULT 0,
  `cancelled_orders` INT NOT NULL DEFAULT 0,
  `total_income` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Starter menu items (only inserted when menu_items is empty)
INSERT INTO `menu_items` (`name`, `category`, `description`, `price`, `prep_minutes`, `image_url`, `is_available`)
SELECT * FROM (
  SELECT 'Biryani' AS name, 'Rice & Noodles' AS category, 'Fragrant saffron basmati rice cooked with spiced chicken, boiled egg, and roasted cashews.' AS description, 250.00 AS price, 12 AS prep_minutes, 'assets/images/menu/biryani.jpg' AS image_url, 1 AS is_available
  UNION ALL
  SELECT 'Fried Rice', 'Rice & Noodles', 'Wok-fried basmati rice with chicken, egg ribbons, spring onions, and crisp vegetables.', 180.00, 10, 'assets/images/menu/fried-rice.jpg', 1
  UNION ALL
  SELECT 'Kottu Roti', 'Rice & Noodles', 'Chopped flatbread stir-fried with seasonal veggies, egg, and spiced chicken gravy.', 200.00, 12, 'assets/images/menu/kottu-roti.jpg', 1
  UNION ALL
  SELECT 'Lamprais', 'Mains', 'Traditional Dutch-Burgher style rice and meat curry baked in a banana leaf.', 220.00, 15, 'assets/images/menu/lamprais.jpg', 1
  UNION ALL
  SELECT 'Butter Chicken', 'Mains', 'Tender chicken simmered in rich, creamy spiced tomato gravy served with naan.', 280.00, 14, 'assets/images/menu/butter-chicken.jpg', 1
  UNION ALL
  SELECT 'Noodles', 'Rice & Noodles', 'Savory stir-fried canteen noodles with shredded chicken and crisp vegetables.', 150.00, 10, 'assets/images/menu/noodles.jpg', 1
  UNION ALL
  SELECT 'Pizza', 'Snacks & Burgers', 'Freshly baked personal pizza topped with bubbling mozzarella and fresh basil.', 320.00, 14, 'assets/images/menu/pizza.jpg', 1
  UNION ALL
  SELECT 'Burger', 'Snacks & Burgers', 'Crispy fried chicken fillet with melted cheddar, lettuce, and house sauce.', 200.00, 10, 'assets/images/menu/burger.jpg', 1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `menu_items` LIMIT 1);
