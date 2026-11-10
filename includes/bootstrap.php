<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Computes the project root URL dynamically regardless of folder name (e.g. /food_order_system, /ORR, or /).
 */
function app_base_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $projectDir = realpath(dirname(__DIR__));
    $docRootRaw = trim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $docRoot = $docRootRaw !== '' ? realpath($docRootRaw) : false;

    if ($projectDir !== false && $docRoot !== false) {
        $normProject = str_replace('\\', '/', $projectDir);
        $normDocRoot = rtrim(str_replace('\\', '/', $docRoot), '/');
        if (strcasecmp($normProject, $normDocRoot) === 0) {
            $base = '';
            return $base;
        }
        if (stripos($normProject, $normDocRoot . '/') === 0) {
            $rel = substr($normProject, strlen($normDocRoot));
            $base = rtrim($rel, '/');
            return $base;
        }
    }

    // Fallback when running via PHP built-in server or symlink outside DOCUMENT_ROOT
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    foreach (['/public/', '/admin/', '/actions/'] as $marker) {
        $pos = strpos($scriptName, $marker);
        if ($pos !== false) {
            $base = rtrim(substr($scriptName, 0, $pos), '/');
            return $base;
        }
    }

    $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    $base = ($dir === '.' || $dir === '/') ? '' : $dir;
    return $base;
}

function app_url(string $path = ''): string
{
    $clean = ltrim($path, '/');
    $base = app_base_url();
    if ($clean === '') {
        return $base === '' ? '/' : $base . '/';
    }
    return ($base === '' ? '' : $base) . '/' . $clean;
}

function redirect_to(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token = null): bool
{
    $submitted = $token ?? (string) ($_POST['csrf_token'] ?? '');
    $expected = (string) ($_SESSION['csrf_token'] ?? '');
    return $submitted !== '' && $expected !== '' && hash_equals($expected, $submitted);
}

function require_valid_post(string $fallbackPath = 'public/index.php'): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        redirect_to($fallbackPath);
    }
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired or the request could not be verified. Please try again.');
        redirect_to($fallbackPath);
    }
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flashes'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

/**
 * @return array<int, array{type:string, message:string}>
 */
function consume_flashes(): array
{
    $flashes = $_SESSION['flashes'] ?? [];
    unset($_SESSION['flashes']);
    return is_array($flashes) ? $flashes : [];
}

function hash_password(string $plain): string
{
    return password_hash($plain, PASSWORD_DEFAULT);
}

/**
 * Verifies modern password_hash() hashes and transparently upgrades legacy SHA-256 hashes on login.
 */
function verify_and_upgrade_password(mysqli $conn, int $userId, string $plain, string $storedHash): bool
{
    if (password_verify($plain, $storedHash)) {
        if (password_needs_rehash($storedHash, PASSWORD_DEFAULT)) {
            $newHash = hash_password($plain);
            $stmt = $conn->prepare('UPDATE users SET password = ? WHERE user_id = ?');
            $stmt->bind_param('si', $newHash, $userId);
            $stmt->execute();
            $stmt->close();
        }
        return true;
    }

    // Backward compatibility with legacy SHA-256 passwords
    if (strlen($storedHash) === 64 && ctype_xdigit($storedHash)) {
        $sha = hash('sha256', $plain);
        if (hash_equals(strtolower($storedHash), $sha)) {
            $newHash = hash_password($plain);
            $stmt = $conn->prepare('UPDATE users SET password = ? WHERE user_id = ?');
            $stmt->bind_param('si', $newHash, $userId);
            $stmt->execute();
            $stmt->close();
            return true;
        }
    }

    return false;
}

/**
 * @return array<int, int> map of item_id => quantity
 */
function cart_items(): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (!is_array($cart)) {
        return [];
    }
    $clean = [];
    foreach ($cart as $itemId => $qty) {
        $id = (int) $itemId;
        $q = (int) $qty;
        if ($id > 0 && $q > 0) {
            $clean[$id] = min($q, 50);
        }
    }
    return $clean;
}

function cart_count(): int
{
    return array_sum(cart_items());
}

function cart_add(int $itemId, int $qty = 1): void
{
    if ($itemId <= 0 || $qty <= 0) {
        return;
    }
    $cart = cart_items();
    $current = $cart[$itemId] ?? 0;
    $cart[$itemId] = min(50, $current + $qty);
    $_SESSION['cart'] = $cart;
}

function cart_set(int $itemId, int $qty): void
{
    if ($itemId <= 0) {
        return;
    }
    $cart = cart_items();
    if ($qty <= 0) {
        unset($cart[$itemId]);
    } else {
        $cart[$itemId] = min(50, $qty);
    }
    $_SESSION['cart'] = $cart;
}

function cart_clear(): void
{
    unset($_SESSION['cart']);
}

function get_unread_notification_count(mysqli $conn, int $userId): int
{
    try {
        $stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $cnt = (int) ($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
        $stmt->close();
        return $cnt;
    } catch (Throwable $e) {
        return 0;
    }
}

function order_status_label(string $status): string
{
    return match ($status) {
        'pending' => 'Pending',
        'preparing' => 'Preparing',
        'ready' => 'Ready for Pickup',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        default => ucfirst($status),
    };
}

function category_emoji(string $category): string
{
    $c = strtolower(trim($category));
    if (str_contains($c, 'rice') || str_contains($c, 'noodle') || str_contains($c, 'main')) {
        return '🍛';
    }
    if (str_contains($c, 'burger') || str_contains($c, 'snack')) {
        return '🍔';
    }
    if (str_contains($c, 'breakfast')) {
        return '🍳';
    }
    if (str_contains($c, 'drink') || str_contains($c, 'beverage') || str_contains($c, 'juice')) {
        return '🥤';
    }
    if (str_contains($c, 'dessert') || str_contains($c, 'sweet')) {
        return '🍰';
    }
    return '🍽️';
}

function format_currency(float|int|string $amount): string
{
    return 'Rs. ' . number_format((float) $amount, 2);
}

function set_old_input(string $key, string $value): void
{
    $_SESSION['_old'][$key] = $value;
}

function consume_old_input(string $key, string $default = ''): string
{
    $val = $_SESSION['_old'][$key] ?? $default;
    unset($_SESSION['_old'][$key]);
    return is_string($val) ? $val : $default;
}

function perform_logout(string $message, string $redirectPath): never
{
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    set_flash('info', $message);
    redirect_to($redirectPath);
}

function media_url(?string $path): ?string
{
    $raw = trim((string) $path);
    if ($raw === '') {
        return null;
    }
    if (preg_match('#^(https?://|/)#i', $raw)) {
        return $raw;
    }
    if (str_starts_with($raw, '../')) {
        $raw = ltrim(substr($raw, 3), '/');
    }
    return app_url($raw);
}
