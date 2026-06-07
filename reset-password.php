<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
$ctrl = new AuthController();
$ctrl->resetPassword();
