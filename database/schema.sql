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
(4,'member1',   'member@library.com',      '$2y$12$6s0KovGLvWL7W/81m7OUyeyTKFn03cRUZoRPEPNfK0PojqXSW9JJ6','Alice Member',    'active',1),
(4,'student1',  'john.student@university.edu','$2y$12$6s0KovGLvWL7W/81m7OUyeyTKFn03cRUZoRPEPNfK0PojqXSW9JJ6','John Student',    'active',1),
(4,'student2',  'mary.doe@university.edu',    '$2y$12$6s0KovGLvWL7W/81m7OUyeyTKFn03cRUZoRPEPNfK0PojqXSW9JJ6','Mary Doe',        'active',1),
(4,'student3',  'bob.wilson@university.edu',  '$2y$12$6s0KovGLvWL7W/81m7OUyeyTKFn03cRUZoRPEPNfK0PojqXSW9JJ6','Bob Wilson',      'active',1),
(4,'student4',  'lisa.brown@university.edu',  '$2y$12$6s0KovGLvWL7W/81m7OUyeyTKFn03cRUZoRPEPNfK0PojqXSW9JJ6','Lisa Brown',      'active',1);

-- Member records for all demo member users
INSERT INTO `members` (`user_id`,`member_id`,`student_id`,`membership_date`,`expiry_date`,`max_borrow_limit`,`status`,`department`) VALUES
(4,'MEM20260001','STU001',CURDATE(),DATE_ADD(CURDATE(), INTERVAL 1 YEAR),5,'active','Computer Science'),
(5,'MEM20260002','STU002',CURDATE(),DATE_ADD(CURDATE(), INTERVAL 1 YEAR),5,'active','Engineering'),
(6,'MEM20260003','STU003',CURDATE(),DATE_ADD(CURDATE(), INTERVAL 1 YEAR),5,'active','Literature'),
(7,'MEM20260004','STU004',CURDATE(),DATE_ADD(CURDATE(), INTERVAL 1 YEAR),5,'active','Business'),
(8,'MEM20260005','STU005',CURDATE(),DATE_ADD(CURDATE(), INTERVAL 1 YEAR),5,'active','Medicine');

-- ============================================================
-- DEMO BORROW TRANSACTIONS
-- ============================================================
INSERT INTO `borrow_transactions` (`issue_number`,`member_id`,`book_id`,`issued_by`,`issue_date`,`due_date`,`status`) VALUES
('ISS20260001',1,1,2,CURDATE(),DATE_ADD(CURDATE(), INTERVAL 14 DAY),'borrowed'),
('ISS20260002',1,9,2,CURDATE(),DATE_ADD(CURDATE(), INTERVAL 14 DAY),'borrowed'),
('ISS20260003',2,3,2,DATE_SUB(CURDATE(), INTERVAL 5 DAY),DATE_ADD(CURDATE(), INTERVAL 9 DAY),'borrowed'),
('ISS20260004',3,15,3,DATE_SUB(CURDATE(), INTERVAL 10 DAY),DATE_ADD(CURDATE(), INTERVAL 4 DAY),'borrowed'),
('ISS20260005',4,22,2,DATE_SUB(CURDATE(), INTERVAL 16 DAY),DATE_SUB(CURDATE(), INTERVAL 2 DAY),'overdue'),
('ISS20260006',5,8,3,DATE_SUB(CURDATE(), INTERVAL 20 DAY),DATE_SUB(CURDATE(), INTERVAL 6 DAY),'overdue'),
('ISS20260007',1,35,2,DATE_SUB(CURDATE(), INTERVAL 25 DAY),DATE_SUB(CURDATE(), INTERVAL 5 DAY),'returned'),
('ISS20260008',2,12,3,DATE_SUB(CURDATE(), INTERVAL 30 DAY),DATE_SUB(CURDATE(), INTERVAL 10 DAY),'returned');

-- Update book quantities for borrowed books
UPDATE `books` SET `available_quantity` = `available_quantity` - 1 WHERE `id` IN (1,9,3,15,22,8);

-- ============================================================
-- DEMO RETURN TRANSACTIONS
-- ============================================================
INSERT INTO `return_transactions` (`borrow_id`,`returned_to`,`return_date`,`book_condition`,`fine_amount`) VALUES
(7,2,DATE_SUB(CURDATE(), INTERVAL 5 DAY),'good',0.00),
(8,3,DATE_SUB(CURDATE(), INTERVAL 10 DAY),'good',0.00);

-- ============================================================
-- DEMO FINES FOR OVERDUE BOOKS
-- ============================================================
INSERT INTO `fines` (`borrow_id`,`member_id`,`amount`,`days_overdue`,`status`) VALUES
(5,4,10.00,2,'pending'),
(6,5,30.00,6,'pending');

-- ============================================================
-- DEMO RESERVATIONS
-- ============================================================
INSERT INTO `reservations` (`reservation_number`,`member_id`,`book_id`,`reserved_date`,`expiry_date`,`status`) VALUES
('RES20260001',2,5,CURDATE(),DATE_ADD(CURDATE(), INTERVAL 3 DAY),'pending'),
('RES20260002',3,18,CURDATE(),DATE_ADD(CURDATE(), INTERVAL 3 DAY),'approved'),
('RES20260003',4,25,DATE_SUB(CURDATE(), INTERVAL 1 DAY),DATE_ADD(CURDATE(), INTERVAL 2 DAY),'pending');

-- ============================================================
-- DEMO NOTIFICATIONS
-- ============================================================
INSERT INTO `notifications` (`user_id`,`title`,`message`,`type`,`is_read`) VALUES
(4,'Book Due Reminder','Your book "Good to Great" is due tomorrow. Please return it on time.','due_reminder',0),
(5,'Overdue Book','Your book "A Brief History of Time" is 6 days overdue. Fine: $30.00','overdue',0),
(6,'Reservation Approved','Your reservation for "Influence: The Psychology of Persuasion" has been approved.','reservation',0),
(4,'Fine Notice','You have a pending fine of $10.00 for overdue book.','fine',1),
(7,'Welcome','Welcome to the Library Management System!','general',1);

-- ============================================================
-- DEMO ACTIVITY LOGS
-- ============================================================
INSERT INTO `activity_logs` (`user_id`,`action`,`module`,`description`,`ip_address`) VALUES
(2,'book_issued','transactions','Issued book "Harry Potter and the Philosopher\'s Stone" to Alice Member','127.0.0.1'),
(3,'book_issued','transactions','Issued book "Linear Algebra and Its Applications" to Lisa Brown','127.0.0.1'),
(2,'book_returned','transactions','Book "The Story of Art" returned by John Student','127.0.0.1'),
(1,'user_created','users','Created new member account for Bob Wilson','127.0.0.1'),
(2,'book_added','books','Added new book "Clean Code" to the library','127.0.0.1');

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
('Medicine','Medical and health sciences'),
('Business','Business and economics'),
('Philosophy','Philosophy and ethics'),
('Psychology','Psychology and behavior'),
('Education','Teaching and learning');

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

INSERT INTO `authors` (`name`,`biography`,`nationality`) VALUES
('J.K. Rowling','British author best known for the Harry Potter fantasy series','British'),
('Stephen King','American author of horror, supernatural fiction, suspense, crime, science-fiction, and fantasy novels','American'),
('Agatha Christie','English writer known for her detective novels','British'),
('George Orwell','English novelist and essayist, journalist and critic','British'),
('Harper Lee','American novelist widely known for To Kill a Mockingbird','American'),
('Jane Austen','English novelist known primarily for her six major novels','British'),
('William Shakespeare','English playwright, poet and actor','British'),
('Mark Twain','American writer, humorist, entrepreneur, publisher, and lecturer','American'),
('Charles Dickens','English writer and social critic','British'),
('Ernest Hemingway','American novelist, short-story writer, and journalist','American'),
('F. Scott Fitzgerald','American novelist and short story writer','American'),
('Leo Tolstoy','Russian writer who is regarded as one of the greatest authors of all time','Russian'),
('Victor Hugo','French poet, novelist, and dramatist of the Romantic movement','French'),
('Oscar Wilde','Irish poet and playwright','Irish'),
('Emily Dickinson','American poet','American');

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

INSERT INTO `publishers` (`name`,`contact`,`email`,`address`) VALUES
('Penguin Random House','+1-212-366-2000','info@penguinrandomhouse.com','1745 Broadway, New York, NY 10019'),
('HarperCollins Publishers','+1-212-207-7000','info@harpercollins.com','195 Broadway, New York, NY 10007'),
('Macmillan Publishers','+1-646-307-5151','info@macmillan.com','120 Broadway, New York, NY 10271'),
('Simon & Schuster','+1-212-698-7000','info@simonandschuster.com','1230 Avenue of the Americas, New York, NY 10020'),
('Hachette Book Group','+1-212-364-1200','info@hbgusa.com','1290 Avenue of the Americas, New York, NY 10104'),
('Scholastic Corporation','+1-212-343-6100','info@scholastic.com','557 Broadway, New York, NY 10012'),
('Oxford University Press','+44-1865-556767','enquiry@oup.com','Great Clarendon Street, Oxford OX2 6DP, UK'),
('Cambridge University Press','+44-1223-312393','information@cambridge.org','University Printing House, Cambridge CB2 8BS, UK'),
('Pearson Education','+1-201-236-7000','info@pearson.com','221 River Street, Hoboken, NJ 07030'),
('McGraw-Hill Education','+1-800-338-3987','customer.service@mheducation.com','2 Penn Plaza, New York, NY 10121');

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
-- DEMO BOOKS DATA - 50 Books Across All Categories
-- ============================================================
INSERT INTO `books` (`isbn`,`title`,`subtitle`,`author_id`,`publisher_id`,`category_id`,`edition`,`language`,`shelf_number`,`rack_number`,`quantity`,`available_quantity`,`price`,`purchase_date`,`description`,`status`) VALUES
-- Literature Books
('9780747532743','Harry Potter and the Philosopher''s Stone',NULL,1,1,5,'1st','English','A1','R1',3,3,25.99,'2024-01-15','The first book in the Harry Potter series','available'),
('9780544003415','The Lord of the Rings',NULL,1,1,5,'50th Anniversary','English','A1','R2',2,2,45.99,'2024-01-20','Epic fantasy novel by J.R.R. Tolkien','available'),
('9780451524935','1984',NULL,4,2,5,'Reprint','English','A2','R1',4,4,15.99,'2024-02-01','Dystopian social science fiction novel','available'),
('9780061120084','To Kill a Mockingbird',NULL,5,2,5,'50th Anniversary','English','A2','R2',3,3,18.99,'2024-02-05','Novel dealing with racial injustice','available'),
('9780141439518','Pride and Prejudice',NULL,6,1,5,'Penguin Classics','English','A3','R1',2,2,12.99,'2024-02-10','Romantic novel by Jane Austen','available'),

-- Science Books
('9780393319927','A Brief History of Time','From the Big Bang to Black Holes',1,3,1,'Updated','English','B1','R1',2,2,22.99,'2024-01-25','Popular science book on cosmology','available'),
('9780385537179','The Elegant Universe','Superstrings, Hidden Dimensions, and the Quest for the Ultimate Theory',2,4,1,'2nd','English','B1','R2',1,1,28.99,'2024-02-15','Book on string theory and physics','available'),
('9781400077915','The Double Helix','A Personal Account of the Discovery of the Structure of DNA',3,5,1,'Touchstone','English','B2','R1',2,2,16.99,'2024-02-20','Scientific memoir about DNA discovery','available'),

-- Technology Books
('9780134685991','Effective Java','Best Practices for the Java Platform',4,9,2,'3rd','English','C1','R1',3,3,52.99,'2024-01-30','Java programming best practices','available'),
('9780596517748','JavaScript: The Good Parts',NULL,5,8,2,'1st','English','C1','R2',4,4,32.99,'2024-02-08','JavaScript programming guide','available'),
('9780321751041','Design Patterns','Elements of Reusable Object-Oriented Software',6,9,2,'1st','English','C2','R1',2,2,65.99,'2024-02-12','Software design patterns book','available'),
('9780134494166','Clean Code','A Handbook of Agile Software Craftsmanship',7,9,2,'1st','English','C2','R2',3,3,42.99,'2024-02-18','Software development best practices','available'),

-- Mathematics Books
('9780486458243','Calculus Made Easy',NULL,8,6,3,'Dover','English','D1','R1',2,2,14.99,'2024-01-18','Introduction to differential and integral calculus','available'),
('9780691158402','Linear Algebra and Its Applications','5th Edition',9,7,3,'5th','English','D1','R2',2,2,285.99,'2024-02-03','Comprehensive linear algebra textbook','available'),
('9780486652818','Differential Equations',NULL,10,6,3,'Dover','English','D2','R1',1,1,18.99,'2024-02-25','Mathematical analysis of differential equations','available'),

-- History Books
('9780195014761','The Guns of August',NULL,11,7,4,'Reissue','English','E1','R1',2,2,18.99,'2024-01-22','Account of the first month of World War I','available'),
('9780140449136','A People''s History of the United States',NULL,12,1,4,'Revised','English','E1','R2',3,3,22.99,'2024-02-07','Alternative perspective on American history','available'),
('9780393326970','Sapiens','A Brief History of Humankind',13,3,4,'1st','English','E2','R1',4,4,24.99,'2024-02-14','Book about the history of humanity','available'),

-- Medicine Books
('9780323087872','Gray''s Anatomy for Students','4th Edition',14,10,8,'4th','English','F1','R1',1,1,89.99,'2024-01-28','Medical anatomy textbook','available'),
('9780071802154','Harrison''s Principles of Internal Medicine','20th Edition',15,10,8,'20th','English','F1','R2',1,1,299.99,'2024-02-11','Comprehensive internal medicine reference','available'),
('9780323376532','Robbins Basic Pathology','10th Edition',1,10,8,'10th','English','F2','R1',1,1,119.99,'2024-02-16','Pathology textbook for medical students','available'),

-- Business Books
('9780812981001','Good to Great','Why Some Companies Make the Leap... and Others Don''t',2,2,9,'1st','English','G1','R1',3,3,27.99,'2024-01-16','Business management and leadership book','available'),
('9781591846444','The Lean Startup','How Today''s Entrepreneurs Use Continuous Innovation',3,4,9,'1st','English','G1','R2',2,2,26.99,'2024-02-04','Entrepreneurship and business development','available'),
('9780307887894','The 4-Hour Workweek','Escape 9-5, Live Anywhere, and Join the New Rich',4,4,9,'Expanded','English','G2','R1',2,2,16.99,'2024-02-19','Lifestyle design and productivity book','available'),

-- Philosophy Books
('9780486406510','Meditations',NULL,5,6,10,'Dover','English','H1','R1',2,2,9.99,'2024-01-19','Philosophical reflections by Marcus Aurelius','available'),
('9780140449266','The Republic',NULL,6,1,10,'Penguin Classics','English','H1','R2',1,1,14.99,'2024-02-06','Plato''s work on justice and political philosophy','available'),
('9780486284606','Beyond Good and Evil',NULL,7,6,10,'Dover','English','H2','R1',1,1,12.99,'2024-02-21','Nietzsche''s critique of traditional morality','available'),

-- Psychology Books
('9780143105428','Thinking, Fast and Slow',NULL,8,1,11,'1st','English','I1','R1',3,3,17.99,'2024-01-26','Behavioral psychology and decision making','available'),
('9781451673319','Nudge','Improving Decisions About Health, Wealth, and Happiness',9,4,11,'Revised','English','I1','R2',2,2,16.99,'2024-02-09','Behavioral economics and psychology','available'),
('9780142181881','Influence','The Psychology of Persuasion',10,1,11,'Revised','English','I2','R1',2,2,18.99,'2024-02-22','Psychology of persuasion and influence','available'),

-- Education Books
('9780134683454','Educational Psychology','Theory and Practice',11,9,12,'12th','English','J1','R1',1,1,299.99,'2024-01-17','Comprehensive educational psychology textbook','available'),
('9780470910122','Understanding by Design','Expanded 2nd Edition',12,5,12,'2nd','English','J1','R2',2,2,32.99,'2024-02-13','Curriculum design and educational planning','available'),
('9781416605362','The Differentiated Classroom','Responding to the Needs of All Learners',13,6,12,'2nd','English','J2','R1',2,2,27.99,'2024-02-24','Strategies for differentiated instruction','available'),

-- Arts Books
('9780714847030','Ways of Seeing',NULL,14,3,7,'1st','English','K1','R1',2,2,16.99,'2024-01-21','Art criticism and visual culture','available'),
('9780500204184','The Story of Art',NULL,15,3,7,'16th','English','K1','R2',1,1,39.99,'2024-02-17','Comprehensive history of art','available'),
('9780691070650','Art and Physics','Parallel Visions in Space, Time, and Light',1,7,7,'1st','English','K2','R1',1,1,29.99,'2024-02-23','Exploration of connections between art and science','available'),

-- Religion Books
('9780199535941','The Holy Bible','New International Version',2,7,6,'NIV','English','L1','R1',5,5,19.99,'2024-01-23','Christian Bible - New International Version','available'),
('9780140449501','The Koran',NULL,3,1,6,'Penguin Classics','English','L1','R2',2,2,15.99,'2024-02-01','English translation of the Quran','available'),
('9780553213102','Siddhartha',NULL,4,4,6,'Bantam Classics','English','L2','R1',3,3,9.99,'2024-02-26','Novel about spiritual enlightenment','available'),

-- Additional Popular Books
('9780316769174','The Catcher in the Rye',NULL,8,2,5,'Little Brown','English','A4','R1',2,2,16.99,'2024-01-24','Classic coming-of-age novel','available'),
('9780307277671','The Da Vinci Code',NULL,9,4,5,'1st','English','A4','R2',3,3,24.99,'2024-02-02','Mystery thriller novel','available'),
('9780439708180','The Hunger Games',NULL,10,6,5,'1st','English','A5','R1',4,4,12.99,'2024-02-27','Dystopian young adult novel','available'),
('9780307269751','Life of Pi',NULL,11,4,5,'1st','English','A5','R2',2,2,15.99,'2024-01-27','Adventure novel about survival','available'),
('9780143039433','The Kite Runner',NULL,12,1,5,'1st','English','A6','R1',2,2,16.99,'2024-02-28','Novel about friendship and redemption','available'),

-- Science Fiction
('9780553293357','Foundation',NULL,13,4,5,'1st','English','A7','R1',2,2,15.99,'2024-01-29','Science fiction novel by Isaac Asimov','available'),
('9780441172719','Dune',NULL,14,2,5,'Ace','English','A7','R2',2,2,18.99,'2024-02-12','Epic science fiction novel','available'),
('9780345391803','The Hitchhiker''s Guide to the Galaxy',NULL,15,4,5,'1st','English','A8','R1',3,3,13.99,'2024-02-15','Comedic science fiction series','available'),

-- Reference Books
('9780199571123','Oxford English Dictionary','Concise Edition',1,7,12,'12th','English','R1','R1',1,1,49.99,'2024-01-31','Comprehensive English dictionary','available'),
('9780521315203','Cambridge Grammar of English',NULL,2,8,12,'1st','English','R1','R2',1,1,89.99,'2024-02-18','Comprehensive English grammar reference','available'),
('9780143039436','The Elements of Style',NULL,3,1,12,'4th','English','R2','R1',3,3,12.99,'2024-02-29','Writing style and grammar guide','available');

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
