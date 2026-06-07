<?php
// Action-only file — no HTML output, just process and redirect.
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
require_once __DIR__ . '/../../../controllers/BookController.php';
(new BookController())->delete();
