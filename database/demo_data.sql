-- ============================================================
-- Library Management System - Demo Data
-- Import this after schema.sql to populate with realistic data
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

USE `library_ms`;

-- ============================================================
-- Demo Authors
-- ============================================================
INSERT INTO `authors` (`name`, `biography`, `nationality`) VALUES
('William Shakespeare', 'English playwright and poet, widely regarded as the greatest writer in the English language.', 'English'),
('Jane Austen', 'English novelist known for her wit, social observation and realism.', 'English'),
('Charles Dickens', 'English writer and social critic who created some of the world''s best-known fictional characters.', 'English'),
('Mark Twain', 'American writer, humorist and lecturer known for his wit and satire.', 'American'),
('George Orwell', 'English novelist and journalist, author of dystopian novels 1984 and Animal Farm.', 'English'),
('J.K. Rowling', 'British author, best known for the Harry Potter fantasy series.', 'British'),
('Stephen King', 'American author of horror, supernatural fiction, suspense, and fantasy novels.', 'American'),
('Agatha Christie', 'English writer known for her detective novels featuring Hercule Poirot and Miss Marple.', 'English'),
('Ernest Hemingway', 'American novelist and journalist, Nobel Prize winner in Literature.', 'American'),
('Harper Lee', 'American novelist known for her novel To Kill a Mockingbird.', 'American'),
('F. Scott Fitzgerald', 'American novelist, essayist, short story writer and screenwriter.', 'American'),
('Emily Dickinson', 'American poet known for her unique style and reclusive life.', 'American'),
('Maya Angelou', 'American poet, memoirist, and civil rights activist.', 'American'),
('Toni Morrison', 'American novelist, essayist, editor, and professor, Nobel Prize winner.', 'American'),
('Gabriel García Márquez', 'Colombian novelist and Nobel Prize winner in Literature.', 'Colombian'),
('Paulo Coelho', 'Brazilian lyricist and novelist, best known for The Alchemist.', 'Brazilian'),
('Haruki Murakami', 'Japanese writer known for his magical realism novels.', 'Japanese'),
('Chinua Achebe', 'Nigerian novelist, poet, professor, and critic, author of Things Fall Apart.', 'Nigerian'),
('Chimamanda Ngozi Adichie', 'Nigerian writer known for her novels and short stories.', 'Nigerian'),
('Salman Rushdie', 'British Indian novelist and essayist known for magical realism.', 'British Indian');
-- ============================================================
-- Demo Publishers
-- ============================================================
INSERT INTO `publishers` (`name`, `contact`, `email`, `address`) VALUES
('Penguin Random House', '+1-212-782-9000', 'info@penguinrandomhouse.com', '1745 Broadway, New York, NY 10019'),
('HarperCollins', '+1-212-207-7000', 'info@harpercollins.com', '195 Broadway, New York, NY 10007'),
('Simon & Schuster', '+1-212-698-7000', 'info@simonandschuster.com', '1230 Avenue of the Americas, New York, NY'),
('Macmillan Publishers', '+1-646-307-5151', 'info@macmillan.com', '120 Broadway, New York, NY 10271'),
('Hachette Book Group', '+1-212-364-1200', 'info@hachettebookgroup.com', '1290 Avenue of the Americas, New York, NY'),
('Scholastic Corporation', '+1-212-343-6100', 'info@scholastic.com', '557 Broadway, New York, NY 10012'),
('Pearson Education', '+1-201-236-7000', 'info@pearson.com', '221 River Street, Hoboken, NJ 07030'),
('McGraw-Hill Education', '+1-212-904-2000', 'info@mheducation.com', '2 Penn Plaza, New York, NY 10121'),
('Oxford University Press', '+44-1865-556767', 'enquiry@oup.com', 'Great Clarendon Street, Oxford OX2 6DP, UK'),
('Cambridge University Press', '+44-1223-312393', 'information@cambridge.org', 'University Printing House, Cambridge CB2 8BS, UK'),
('Wiley', '+1-201-748-6000', 'info@wiley.com', '111 River Street, Hoboken, NJ 07030'),
('Elsevier', '+31-20-485-3911', 'info@elsevier.com', 'Radarweg 29, 1043 NX Amsterdam, Netherlands'),
('Springer', '+49-6221-487-0', 'info@springer.com', 'Tiergartenstraße 17, 69121 Heidelberg, Germany'),
('Taylor & Francis', '+44-20-7017-6000', 'info@taylorandfrancis.com', '2-4 Park Square, Milton Park, Abingdon, Oxon OX14 4RN'),
('SAGE Publications', '+1-805-499-0721', 'info@sagepub.com', '2455 Teller Road, Thousand Oaks, CA 91320');
-- ============================================================
-- Demo Books - Literature & Fiction
-- ============================================================
INSERT INTO `books` (`isbn`, `barcode`, `title`, `subtitle`, `author_id`, `publisher_id`, `category_id`, `edition`, `language`, `shelf_number`, `rack_number`, `quantity`, `available_quantity`, `price`, `purchase_date`, `description`) VALUES
('9780143105985', 'LMS001001', 'To Kill a Mockingbird', NULL, 10, 1, 5, '50th Anniversary Edition', 'English', 'A1', 'R1', 3, 3, 15.99, '2024-01-15', 'A gripping tale of racial injustice and childhood innocence in the American South.'),
('9780451524935', 'LMS001002', '1984', NULL, 5, 2, 5, 'Centennial Edition', 'English', 'A1', 'R2', 5, 4, 13.99, '2024-01-20', 'Orwell\'s dystopian masterpiece about totalitarian control and surveillance.'),
('9780525478812', 'LMS001003', 'Animal Farm', NULL, 5, 1, 5, 'Anniversary Edition', 'English', 'A1', 'R3', 4, 4, 12.99, '2024-02-01', 'A satirical allegorical novella about farm animals who rebel against their human farmer.'),
('9780141439518', 'LMS001004', 'Pride and Prejudice', NULL, 2, 1, 5, 'Penguin Classics', 'English', 'A2', 'R1', 3, 2, 11.99, '2024-01-25', 'Austen\'s witty novel of manners, marriage, and social class in Georgian England.'),
('9780486280615', 'LMS001005', 'Romeo and Juliet', NULL, 1, 3, 5, 'Dover Thrift Edition', 'English', 'A2', 'R2', 6, 5, 8.99, '2024-02-10', 'Shakespeare\'s tragic tale of star-crossed lovers from feuding families.'),
('9780307387899', 'LMS001006', 'The Great Gatsby', NULL, 11, 1, 5, 'Scribner Classics', 'English', 'A2', 'R3', 4, 3, 14.99, '2024-01-30', 'Fitzgerald\'s Jazz Age masterpiece about the American Dream and moral decay.'),
('9780679783268', 'LMS001007', 'Beloved', NULL, 14, 1, 5, 'Vintage International', 'English', 'A3', 'R1', 2, 2, 16.99, '2024-02-05', 'Morrison\'s haunting novel about slavery and its lasting psychological effects.'),
('9780061120084', 'LMS001008', 'The Alchemist', NULL, 16, 2, 5, '25th Anniversary Edition', 'English', 'A3', 'R2', 5, 4, 13.99, '2024-01-18', 'Coelho\'s philosophical novel about a young shepherd\'s journey to fulfill his dreams.'),
('9780439139595', 'LMS001009', 'Harry Potter and the Goblet of Fire', NULL, 6, 6, 5, 'US Edition', 'English', 'A3', 'R3', 3, 2, 18.99, '2024-02-12', 'The fourth book in the beloved Harry Potter series featuring the Triwizard Tournament.'),
('9780345339683', 'LMS001010', 'The Stand', NULL, 7, 3, 5, 'Complete & Uncut Edition', 'English', 'A4', 'R1', 2, 1, 19.99, '2024-01-22', 'King\'s epic post-apocalyptic dark fantasy about good versus evil.');
-- ============================================================
-- Demo Books - Science & Technology
-- ============================================================
INSERT INTO `books` (`isbn`, `barcode`, `title`, `subtitle`, `author_id`, `publisher_id`, `category_id`, `edition`, `language`, `shelf_number`, `rack_number`, `quantity`, `available_quantity`, `price`, `purchase_date`, `description`) VALUES
('9780134685991', 'LMS002001', 'Effective Java', NULL, 1, 7, 2, '3rd Edition', 'English', 'B1', 'R1', 4, 3, 54.99, '2024-01-10', 'Best practices for the Java programming language by Joshua Bloch.'),
('9781449355739', 'LMS002002', 'JavaScript: The Good Parts', NULL, 2, 8, 2, '1st Edition', 'English', 'B1', 'R2', 3, 3, 29.99, '2024-01-12', 'Douglas Crockford reveals the elegant subset of JavaScript.'),
('9780596517748', 'LMS002003', 'JavaScript: The Definitive Guide', NULL, 3, 8, 2, '6th Edition', 'English', 'B1', 'R3', 2, 2, 49.99, '2024-02-01', 'The comprehensive reference and tutorial for JavaScript programmers.'),
('9781449331818', 'LMS002004', 'Learning Python', NULL, 4, 8, 2, '5th Edition', 'English', 'B2', 'R1', 5, 4, 59.99, '2024-01-15', 'Get a comprehensive introduction to Python programming.'),
('9780134694726', 'LMS002005', 'Clean Code', 'A Handbook of Agile Software Craftsmanship', 5, 7, 2, '1st Edition', 'English', 'B2', 'R2', 3, 2, 49.99, '2024-01-20', 'Robert Martin presents best practices for writing clean, maintainable code.'),
('9781617294136', 'LMS002006', 'Spring in Action', NULL, 6, 9, 2, '5th Edition', 'English', 'B2', 'R3', 2, 2, 44.99, '2024-02-05', 'Comprehensive guide to the Spring Framework for Java developers.'),
('9781449367619', 'LMS002007', 'Python for Data Analysis', NULL, 7, 8, 2, '2nd Edition', 'English', 'B3', 'R1', 4, 3, 54.99, '2024-01-25', 'Essential tools for working with data using pandas and NumPy.'),
('9780321573513', 'LMS002008', 'Algorithms', NULL, 8, 7, 2, '4th Edition', 'English', 'B3', 'R2', 3, 3, 84.99, '2024-02-08', 'Sedgewick and Wayne present essential information about algorithms.'),
('9780134052243', 'LMS002009', 'The C++ Programming Language', NULL, 9, 7, 2, '4th Edition', 'English', 'B3', 'R3', 2, 1, 79.99, '2024-01-28', 'Bjarne Stroustrup\'s definitive guide to C++ programming.'),
('9781449355050', 'LMS002010', 'Database System Concepts', NULL, 10, 8, 2, '7th Edition', 'English', 'B4', 'R1', 3, 3, 89.99, '2024-02-10', 'Comprehensive introduction to database system concepts.');
-- ============================================================
-- Demo Books - Mathematics & Science
-- ============================================================
INSERT INTO `books` (`isbn`, `barcode`, `title`, `subtitle`, `author_id`, `publisher_id`, `category_id`, `edition`, `language`, `shelf_number`, `rack_number`, `quantity`, `available_quantity`, `price`, `purchase_date`, `description`) VALUES
('9780321982384', 'LMS003001', 'Thomas\' Calculus', 'Early Transcendentals', 1, 7, 3, '14th Edition', 'English', 'C1', 'R1', 4, 4, 299.99, '2024-01-08', 'Comprehensive calculus textbook for engineering and science students.'),
('9780470458365', 'LMS003002', 'Elementary Linear Algebra', NULL, 2, 11, 3, '11th Edition', 'English', 'C1', 'R2', 3, 3, 249.99, '2024-01-15', 'Introduction to linear algebra with applications.'),
('9780073383095', 'LMS003003', 'Discrete Mathematics and Its Applications', NULL, 3, 8, 3, '8th Edition', 'English', 'C1', 'R3', 2, 2, 279.99, '2024-02-01', 'Comprehensive discrete mathematics for computer science majors.'),
('9780134689685', 'LMS003004', 'University Physics with Modern Physics', NULL, 4, 7, 1, '15th Edition', 'English', 'C2', 'R1', 5, 4, 349.99, '2024-01-20', 'Comprehensive physics textbook covering classical and modern physics.'),
('9781285741550', 'LMS003005', 'Chemistry: The Central Science', NULL, 5, 7, 1, '14th Edition', 'English', 'C2', 'R2', 3, 2, 329.99, '2024-01-25', 'Comprehensive general chemistry textbook.'),
('9780134093413', 'LMS003006', 'Campbell Biology', NULL, 6, 7, 1, '11th Edition', 'English', 'C2', 'R3', 4, 3, 399.99, '2024-02-05', 'The world\'s most successful biology textbook.'),
('9780321973610', 'LMS003007', 'Statistics for Engineers and Scientists', NULL, 7, 7, 3, '4th Edition', 'English', 'C3', 'R1', 2, 2, 259.99, '2024-01-30', 'Applied statistics for engineering and scientific applications.'),
('9780073398143', 'LMS003008', 'Engineering Mechanics: Dynamics', NULL, 8, 8, 1, '8th Edition', 'English', 'C3', 'R2', 3, 3, 289.99, '2024-02-08', 'Fundamental principles of dynamics for engineering students.'),
('9780134685991', 'LMS003009', 'Organic Chemistry', NULL, 9, 7, 1, '9th Edition', 'English', 'C3', 'R3', 2, 1, 319.99, '2024-01-18', 'Comprehensive organic chemistry textbook.'),
('9780321976420', 'LMS003010', 'Fundamentals of Electric Circuits', NULL, 10, 8, 1, '6th Edition', 'English', 'C4', 'R1', 3, 3, 279.99, '2024-02-12', 'Introduction to electric circuit analysis and design.');
-- ============================================================
-- Demo Members
-- ============================================================
INSERT INTO `members` (`user_id`, `member_id`, `student_id`, `department`, `membership_date`, `expiry_date`, `max_borrow_limit`, `status`) VALUES
-- Member user (id=4) already exists from schema.sql
(4, 'MEM20260001', 'STU2026001', 'Computer Science', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 5, 'active');

-- Add more demo members
INSERT INTO `users` (`role_id`, `username`, `email`, `password`, `full_name`, `phone`, `gender`, `department`, `status`, `email_verified`) VALUES
(4, 'student1', 'john.doe@university.edu', '$2y$12$6s0KovGLvWL7W/81m7OUyeyTKFn03cRUZoRPEPNfK0PojqXSW9JJ6', 'John Doe', '+1234567801', 'male', 'Computer Science', 'active', 1),
(4, 'student2', 'jane.smith@university.edu', '$2y$12$6s0KovGLvWL7W/81m7OUyeyTKFn03cRUZoRPEPNfK0PojqXSW9JJ6', 'Jane Smith', '+1234567802', 'female', 'Mathematics', 'active', 1),
(4, 'student3', 'mike.johnson@university.edu', '$2y$12$6s0KovGLvWL7W/81m7OUyeyTKFn03cRUZoRPEPNfK0PojqXSW9JJ6', 'Mike Johnson', '+1234567803', 'male', 'Physics', 'active', 1),
(4, 'student4', 'emma.davis@university.edu', '$2y$12$6s0KovGLvWL7W/81m7OUyeyTKFn03cRUZoRPEPNfK0PojqXSW9JJ6', 'Emma Davis', '+1234567804', 'female', 'Literature', 'active', 1),
(4, 'student5', 'alex.wilson@university.edu', '$2y$12$6s0KovGLvWL7W/81m7OUyeyTKFn03cRUZoRPEPNfK0PojqXSW9JJ6', 'Alex Wilson', '+1234567805', 'other', 'Chemistry', 'active', 1);

INSERT INTO `members` (`user_id`, `member_id`, `student_id`, `department`, `membership_date`, `expiry_date`, `max_borrow_limit`, `status`) VALUES
(5, 'MEM20260002', 'STU2026002', 'Computer Science', DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 335 DAY), 5, 'active'),
(6, 'MEM20260003', 'STU2026003', 'Mathematics', DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_ADD(CURDATE(), INTERVAL 305 DAY), 5, 'active'),
(7, 'MEM20260004', 'STU2026004', 'Physics', DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 350 DAY), 5, 'active'),
(8, 'MEM20260005', 'STU2026005', 'Literature', DATE_SUB(CURDATE(), INTERVAL 45 DAY), DATE_ADD(CURDATE(), INTERVAL 320 DAY), 5, 'active'),
(9, 'MEM20260006', 'STU2026006', 'Chemistry', DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 355 DAY), 5, 'active');
-- ============================================================
-- Demo Borrow Transactions
-- ============================================================
INSERT INTO `borrow_transactions` (`issue_number`, `member_id`, `book_id`, `issued_by`, `issue_date`, `due_date`, `status`) VALUES
('ISS20260001', 1, 1, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 5 DAY), INTERVAL 14 DAY), 'borrowed'),
('ISS20260002', 2, 11, 2, DATE_SUB(CURDATE(), INTERVAL 8 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 8 DAY), INTERVAL 14 DAY), 'borrowed'),
('ISS20260003', 3, 21, 3, DATE_SUB(CURDATE(), INTERVAL 12 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 12 DAY), INTERVAL 14 DAY), 'borrowed'),
('ISS20260004', 1, 4, 2, DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 20 DAY), INTERVAL 14 DAY), 'returned'),
('ISS20260005', 4, 15, 2, DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 3 DAY), INTERVAL 14 DAY), 'borrowed'),
('ISS20260006', 5, 7, 3, DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 25 DAY), INTERVAL 14 DAY), 'overdue'),
('ISS20260007', 2, 18, 2, DATE_SUB(CURDATE(), INTERVAL 18 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 18 DAY), INTERVAL 14 DAY), 'overdue'),
('ISS20260008', 6, 9, 2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 DAY), INTERVAL 14 DAY), 'borrowed'),
('ISS20260009', 3, 25, 3, DATE_SUB(CURDATE(), INTERVAL 7 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 7 DAY), INTERVAL 14 DAY), 'borrowed'),
('ISS20260010', 4, 12, 2, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 30 DAY), INTERVAL 14 DAY), 'returned');

-- Update available quantities for borrowed books
UPDATE `books` SET `available_quantity` = `available_quantity` - 1 WHERE `id` IN (1, 11, 21, 15, 7, 18, 9, 25);

-- ============================================================
-- Demo Return Transactions
-- ============================================================
INSERT INTO `return_transactions` (`borrow_id`, `returned_to`, `return_date`, `book_condition`, `fine_amount`) VALUES
(4, 2, DATE_SUB(CURDATE(), INTERVAL 18 DAY), 'good', 0.00),
(10, 2, DATE_SUB(CURDATE(), INTERVAL 25 DAY), 'good', 0.00);

-- ============================================================
-- Demo Reservations
-- ============================================================
INSERT INTO `reservations` (`reservation_number`, `member_id`, `book_id`, `reserved_date`, `expiry_date`, `status`, `approved_by`) VALUES
('RES20260001', 1, 2, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'pending', NULL),
('RES20260002', 2, 13, DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 2 DAY), INTERVAL 3 DAY), 'approved', 2),
('RES20260003', 5, 8, DATE_SUB(CURDATE(), INTERVAL 1 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 DAY), INTERVAL 3 DAY), 'pending', NULL),
('RES20260004', 6, 20, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'pending', NULL);
-- ============================================================
-- Demo Fines
-- ============================================================
INSERT INTO `fines` (`borrow_id`, `member_id`, `amount`, `days_overdue`, `status`) VALUES
(6, 5, 55.00, 11, 'pending'),
(7, 2, 20.00, 4, 'pending');

-- ============================================================
-- Demo Notifications
-- ============================================================
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `is_read`) VALUES
(4, 'Book Due Tomorrow', 'Your book "To Kill a Mockingbird" is due tomorrow. Please return it on time.', 'due_reminder', 0),
(5, 'Welcome to Library', 'Welcome to our library system! You can now browse and borrow books.', 'general', 1),
(6, 'Overdue Book', 'Your book "Beloved" is overdue. Please return it as soon as possible.', 'overdue', 0),
(7, 'Reservation Approved', 'Your reservation for "Clean Code" has been approved. You have 3 days to collect it.', 'reservation', 0),
(8, 'Fine Payment Due', 'You have a pending fine of $20.00. Please settle your payment.', 'fine', 0),
(9, 'New Books Available', 'We have added new books in Computer Science section. Check them out!', 'general', 1),
(4, 'Membership Renewal', 'Your membership will expire in 30 days. Please renew to continue borrowing books.', 'general', 0),
(6, 'Book Returned Successfully', 'Thank you for returning "Harry Potter and the Goblet of Fire" on time.', 'general', 1);

-- ============================================================
-- Demo Activity Logs
-- ============================================================
INSERT INTO `activity_logs` (`user_id`, `action`, `module`, `description`, `ip_address`) VALUES
(1, 'Login', 'auth', 'Super Admin logged into system', '127.0.0.1'),
(2, 'Book Issue', 'transactions', 'Issued book "To Kill a Mockingbird" to member MEM20260001', '192.168.1.100'),
(3, 'Book Return', 'transactions', 'Processed return for book "Pride and Prejudice"', '192.168.1.101'),
(2, 'Member Add', 'members', 'Added new member: Jane Smith', '192.168.1.100'),
(1, 'Settings Update', 'settings', 'Updated fine per day to $5.00', '127.0.0.1'),
(4, 'Book Search', 'books', 'Searched for books in Science category', '192.168.1.200'),
(2, 'Book Add', 'books', 'Added new book: "Clean Architecture"', '192.168.1.100'),
(5, 'Profile Update', 'profile', 'Updated profile information', '192.168.1.201');

-- ============================================================
-- Update Counters for Demo Data
-- ============================================================
-- Update book borrowed counts (simulate usage)
UPDATE `books` SET `available_quantity` = `quantity` - 1 WHERE `id` IN (1, 11, 21, 15, 7, 18, 9, 25);

-- Note: This completes the demo data.
-- When imported, this will provide a realistic library system with:
-- - 30 books across different categories
-- - 6 members (including the original demo member)
-- - Active borrow transactions
-- - Overdue books and fines
-- - Reservations pending approval
-- - Notifications for different scenarios
-- - Activity logs showing system usage

COMMIT;