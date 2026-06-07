-- ============================================================
-- Library Management System - Full Database Schema
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `library_ms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `library_ms`;

-- ============================================================
-- TABLE: roles
-- ============================================================
CREATE TABLE `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`name`, `slug`, `description`) VALUES
('Super Admin', 'super_admin', 'Full system access'),
('Librarian', 'librarian', 'Manages books and daily operations'),
('Assistant Librarian', 'assistant', 'Limited management permissions'),
('Member', 'member', 'Can search and borrow books');

-- ============================================================
-- TABLE: permissions
-- ============================================================
CREATE TABLE `permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `module` VARCHAR(50),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `permissions` (`name`, `slug`, `module`) VALUES
('View Books', 'books.view', 'books'),
('Add Books', 'books.add', 'books'),
('Edit Books', 'books.edit', 'books'),
('Delete Books', 'books.delete', 'books'),
('View Members', 'members.view', 'members'),
('Add Members', 'members.add', 'members'),
('Edit Members', 'members.edit', 'members'),
('Delete Members', 'members.delete', 'members'),
('Issue Books', 'transactions.issue', 'transactions'),
('Return Books', 'transactions.return', 'transactions'),
('Manage Users', 'users.manage', 'users'),
('View Reports', 'reports.view', 'reports'),
('Manage Settings', 'settings.manage', 'settings'),
('Manage Fines', 'fines.manage', 'fines'),
('Manage Reservations', 'reservations.manage', 'reservations');

-- ============================================================
-- TABLE: role_permissions
-- ============================================================
CREATE TABLE `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Super Admin gets all permissions
INSERT INTO `role_permissions` SELECT 1, id FROM `permissions`;
-- Librarian
INSERT INTO `role_permissions` VALUES (2,1),(2,2),(2,3),(2,5),(2,6),(2,7),(2,9),(2,10),(2,12),(2,14),(2,15);
-- Assistant
INSERT INTO `role_permissions` VALUES (3,1),(3,2),(3,3),(3,5),(3,6),(3,7),(3,9),(3,10);
-- Member
INSERT INTO `role_permissions` VALUES (4,1);

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` INT UNSIGNED NOT NULL DEFAULT 4,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20),
  `gender` ENUM('male','female','other'),
  `photo` VARCHAR(255) DEFAULT 'default.png',
  `department` VARCHAR(100),
  `address` TEXT,
  `status` ENUM('active','suspended','inactive') DEFAULT 'active',
  `remember_token` VARCHAR(100),
  `reset_token` VARCHAR(100),
  `reset_expires` DATETIME,
  `failed_attempts` TINYINT UNSIGNED DEFAULT 0,
  `locked_until` DATETIME,
  `last_login` DATETIME,
  `email_verified` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Demo Users — one per role
-- Super Admin  → admin@library.com      / Admin@1234
-- Librarian    → librarian@library.com  / Librarian@123
-- Assistant    → assistant@library.com  / Assistant@123
-- Member       → member@library.com     / Member@123
-- ============================================================
INSERT INTO `users` (`role_id`,`username`,`email`,`password`,`full_name`,`status`,`email_verified`) VALUES
(1,'superadmin','admin@library.com',       '$2y$12$eAZ2uqdZqipnWZBuiIpCzu3Y248AbsFU6kDyfxygdGpl3gOBYU5P','Super Admin',    'active',1),
(2,'librarian1','librarian@library.com',   '$2y$12$UeMNOiwDZo9uEn2Xy27LHe0F.dgZbSxww5B7gwH6JvlHbp5Rx5rYC','John Librarian', 'active',1),
(3,'assistant1','assistant@library.com',   '$2y$12$GfSxhkyTGGBecjmT1iug3ONtrYMEJ6iu2lvmSQJpMYN.YmA2rvMMy','Jane Assistant',  'active',1),
(4,'member1',   'member@library.com',      '$2y$12$6s0KovGLvWL7W/81m7OUyeyTKFn03cRUZoRPEPNfK0PojqXSW9JJ6','Alice Member',    'active',1);

-- Member record for the demo member user (user id 4)
INSERT INTO `members` (`user_id`,`member_id`,`membership_date`,`expiry_date`,`max_borrow_limit`,`status`) VALUES
(4,'MEM20260001',CURDATE(),DATE_ADD(CURDATE(), INTERVAL 1 YEAR),5,'active');

-- ============================================================
-- TABLE: login_history
-- ============================================================
CREATE TABLE `login_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `ip_address` VARCHAR(45),
  `user_agent` TEXT,
  `status` ENUM('success','failed') DEFAULT 'success',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: categories
-- ============================================================
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `status` ENUM('active','inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`name`,`description`) VALUES
('Science','Science and natural sciences'),
('Technology','Technology and computing'),
('Mathematics','Mathematics and statistics'),
('History','History and archaeology'),
('Literature','Literature and poetry'),
('Religion','Religious texts and studies'),
('Arts','Arts and culture'),
('Medicine','Medical and health sciences');

-- ============================================================
-- TABLE: authors
-- ============================================================
CREATE TABLE `authors` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `biography` TEXT,
  `nationality` VARCHAR(100),
  `photo` VARCHAR(255) DEFAULT 'default.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: publishers
-- ============================================================
CREATE TABLE `publishers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `contact` VARCHAR(50),
  `email` VARCHAR(150),
  `address` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: books
-- ============================================================
CREATE TABLE `books` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `isbn` VARCHAR(20),
  `barcode` VARCHAR(50),
  `title` VARCHAR(300) NOT NULL,
  `subtitle` VARCHAR(300),
  `author_id` INT UNSIGNED,
  `publisher_id` INT UNSIGNED,
  `category_id` INT UNSIGNED,
  `edition` VARCHAR(50),
  `language` VARCHAR(50) DEFAULT 'English',
  `shelf_number` VARCHAR(20),
  `rack_number` VARCHAR(20),
  `quantity` INT UNSIGNED DEFAULT 1,
  `available_quantity` INT UNSIGNED DEFAULT 1,
  `price` DECIMAL(10,2) DEFAULT 0.00,
  `purchase_date` DATE,
  `description` TEXT,
  `cover_image` VARCHAR(255) DEFAULT 'default_book.png',
  `pdf_file` VARCHAR(255),
  `status` ENUM('available','unavailable') DEFAULT 'available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `author_id` (`author_id`),
  KEY `publisher_id` (`publisher_id`),
  FULLTEXT KEY `search` (`title`,`subtitle`),
  FOREIGN KEY (`author_id`) REFERENCES `authors`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`publisher_id`) REFERENCES `publishers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: members
-- ============================================================
CREATE TABLE `members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `member_id` VARCHAR(30) NOT NULL,
  `student_id` VARCHAR(30),
  `department` VARCHAR(100),
  `membership_date` DATE NOT NULL,
  `expiry_date` DATE NOT NULL,
  `max_borrow_limit` TINYINT UNSIGNED DEFAULT 5,
  `status` ENUM('active','expired','suspended') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id` (`member_id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: borrow_transactions
-- ============================================================
CREATE TABLE `borrow_transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `issue_number` VARCHAR(30) NOT NULL,
  `member_id` INT UNSIGNED NOT NULL,
  `book_id` INT UNSIGNED NOT NULL,
  `issued_by` INT UNSIGNED NOT NULL,
  `issue_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `return_date` DATE,
  `status` ENUM('borrowed','returned','overdue','lost') DEFAULT 'borrowed',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `issue_number` (`issue_number`),
  KEY `member_id` (`member_id`),
  KEY `book_id` (`book_id`),
  KEY `issued_by` (`issued_by`),
  FOREIGN KEY (`member_id`) REFERENCES `members`(`id`),
  FOREIGN KEY (`book_id`) REFERENCES `books`(`id`),
  FOREIGN KEY (`issued_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: return_transactions
-- ============================================================
CREATE TABLE `return_transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `borrow_id` INT UNSIGNED NOT NULL,
  `returned_to` INT UNSIGNED NOT NULL,
  `return_date` DATE NOT NULL,
  `book_condition` ENUM('good','damaged','lost') DEFAULT 'good',
  `fine_amount` DECIMAL(10,2) DEFAULT 0.00,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`),
  FOREIGN KEY (`borrow_id`) REFERENCES `borrow_transactions`(`id`),
  FOREIGN KEY (`returned_to`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: reservations
-- ============================================================
CREATE TABLE `reservations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reservation_number` VARCHAR(30) NOT NULL,
  `member_id` INT UNSIGNED NOT NULL,
  `book_id` INT UNSIGNED NOT NULL,
  `reserved_date` DATE NOT NULL,
  `expiry_date` DATE NOT NULL,
  `status` ENUM('pending','approved','collected','cancelled','rejected') DEFAULT 'pending',
  `approved_by` INT UNSIGNED,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservation_number` (`reservation_number`),
  KEY `member_id` (`member_id`),
  KEY `book_id` (`book_id`),
  FOREIGN KEY (`member_id`) REFERENCES `members`(`id`),
  FOREIGN KEY (`book_id`) REFERENCES `books`(`id`),
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: fines
-- ============================================================
CREATE TABLE `fines` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `borrow_id` INT UNSIGNED NOT NULL,
  `member_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `days_overdue` INT UNSIGNED DEFAULT 0,
  `status` ENUM('pending','paid','waived') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`),
  KEY `member_id` (`member_id`),
  FOREIGN KEY (`borrow_id`) REFERENCES `borrow_transactions`(`id`),
  FOREIGN KEY (`member_id`) REFERENCES `members`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: payments
-- ============================================================
CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fine_id` INT UNSIGNED NOT NULL,
  `amount_paid` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('cash','card','online') DEFAULT 'cash',
  `received_by` INT UNSIGNED NOT NULL,
  `payment_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `receipt_number` VARCHAR(30),
  PRIMARY KEY (`id`),
  KEY `fine_id` (`fine_id`),
  FOREIGN KEY (`fine_id`) REFERENCES `fines`(`id`),
  FOREIGN KEY (`received_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: notifications
-- ============================================================
CREATE TABLE `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('due_reminder','overdue','reservation','fine','general') DEFAULT 'general',
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: activity_logs
-- ============================================================
CREATE TABLE `activity_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(50),
  `description` TEXT,
  `ip_address` VARCHAR(45),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: settings
-- ============================================================
CREATE TABLE `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(100) NOT NULL,
  `value` TEXT,
  `group` VARCHAR(50) DEFAULT 'general',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`key`,`value`,`group`) VALUES
('site_name','Library Management System','general'),
('site_email','admin@library.com','general'),
('site_phone','+1234567890','general'),
('site_address','123 Library Street','general'),
('fine_per_day','5.00','fines'),
('max_fine','500.00','fines'),
('borrow_limit','5','borrowing'),
('borrow_days','14','borrowing'),
('reservation_days','3','reservations'),
('currency','USD','general'),
('currency_symbol','$','general'),
('opening_hours','8:00 AM - 8:00 PM','general'),
('session_timeout','30','security'),
('max_failed_attempts','5','security');

-- ============================================================
-- TABLE: backups
-- ============================================================
CREATE TABLE `backups` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename` VARCHAR(255) NOT NULL,
  `size` BIGINT UNSIGNED DEFAULT 0,
  `created_by` INT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
