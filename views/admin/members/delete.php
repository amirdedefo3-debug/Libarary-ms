<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
require_once __DIR__ . '/../../../controllers/MemberController.php';
$ctrl = new MemberController();
$ctrl->delete();
