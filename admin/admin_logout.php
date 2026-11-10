<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !verify_csrf()) {
    set_flash('warning', 'Session check failed. Please try logging out again.');
    redirect_to('admin/admin_dashboard.php');
}

perform_logout('You have signed out of the admin portal.', 'admin/login.php');
