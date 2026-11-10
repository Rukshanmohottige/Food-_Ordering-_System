<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !verify_csrf()) {
    set_flash('warning', 'Session check failed. Please try logging out again.');
    redirect_to('public/index.php');
}

perform_logout('You have been logged out successfully.', 'public/login.php');
