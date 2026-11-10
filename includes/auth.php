<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] > 0;
}

function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function current_user_name(): string
{
    return (string) ($_SESSION['user_name'] ?? '');
}

function current_user_role(): string
{
    return (string) ($_SESSION['user_role'] ?? '');
}

function is_admin_or_staff(): bool
{
    return is_logged_in() && in_array(current_user_role(), ['admin', 'staff'], true);
}

function is_super_admin(): bool
{
    return is_logged_in() && current_user_role() === 'admin';
}

function ensure_logged_in(): void
{
    if (!is_logged_in()) {
        set_flash('warning', 'Please log in to continue.');
        redirect_to('public/login.php');
    }
}

function ensure_admin(): void
{
    if (!is_admin_or_staff()) {
        set_flash('warning', 'Please sign in with a staff or admin account.');
        redirect_to('admin/login.php');
    }
}

function ensure_super_admin(): void
{
    ensure_admin();
    if (!is_super_admin()) {
        set_flash('error', 'Only administrators can access this page.');
        redirect_to('admin/admin_dashboard.php');
    }
}
