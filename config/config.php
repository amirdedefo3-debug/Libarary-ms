<?php
/**
 * Application Configuration
 */

// Base URL — adjust if hosted in a subdirectory
define('BASE_URL', 'http://localhost/Library%20ms/Libarary-ms');
define('BASE_PATH', dirname(__DIR__));

// Upload directories
define('UPLOAD_PATH', BASE_PATH . '/uploads/');
define('BOOK_COVERS', BASE_PATH . '/uploads/books/');
define('PROFILE_PHOTOS', BASE_PATH . '/uploads/profiles/');
define('BOOK_PDFS', BASE_PATH . '/uploads/pdfs/');
define('BACKUP_PATH', BASE_PATH . '/backups/');

// Allowed upload types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_PDF_TYPES', ['application/pdf']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// App timezone
date_default_timezone_set('UTC');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoload helper
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/helpers.php';
require_once BASE_PATH . '/includes/session.php';
