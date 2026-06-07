<?php
// Action-only — process delete and redirect, no HTML output.
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
require_once __DIR__ . '/../../../controllers/MemberController.php';
(new MemberController())->delete();
