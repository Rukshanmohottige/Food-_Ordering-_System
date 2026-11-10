<?php
declare(strict_types=1);

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS');
if ($pass === false) {
    $pass = ''; // XAMPP default
}
$db = getenv('DB_NAME') ?: 'food_ordering_system';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Database Connection Error</title>';
    echo '<style>body{font-family:system-ui,-apple-system,sans-serif;background:#f8fafc;color:#0f172a;padding:40px;margin:0}';
    echo '.box{max-width:600px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;box-shadow:0 4px 16px rgba(15,23,42,.06)}';
    echo 'h2{margin-top:0;color:#b91c1c}code{background:#f1f5f9;padding:2px 6px;border-radius:4px}</style></head><body><div class="box">';
    echo '<h2>Database Connection Error</h2>';
    echo '<p>Unable to connect to the database. Please check:</p>';
    echo '<ol><li>MySQL is running in XAMPP Control Panel.</li>';
    echo '<li>Database <code>' . htmlspecialchars($db, ENT_QUOTES, 'UTF-8') . '</code> exists.</li>';
    echo '<li>Run <code>db_structure.sql</code> to initialize the tables.</li></ol>';
    echo '</div></body></html>';
    exit;
}

if (!function_exists('ensure_schema_upgrades')) {
    /**
     * Safely upgrades an older food_ordering_system database in-place without dropping existing data.
     */
    function ensure_schema_upgrades(mysqli $conn): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['_schema_checked'])) {
            return;
        }

        $columnExists = static function (mysqli $conn, string $table, string $column): bool {
            $stmt = $conn->prepare(
                'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1'
            );
            $stmt->bind_param('ss', $table, $column);
            $stmt->execute();
            $exists = $stmt->get_result()->num_rows > 0;
            $stmt->close();
            return $exists;
        };

        $tableExists = static function (mysqli $conn, string $table): bool {
            $stmt = $conn->prepare(
                'SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1'
            );
            $stmt->bind_param('s', $table);
            $stmt->execute();
            $exists = $stmt->get_result()->num_rows > 0;
            $stmt->close();
            return $exists;
        };

        try {
            // Ensure users.password can store modern password_hash() strings (60-255 chars)
            if ($tableExists($conn, 'users')) {
                $stmt = $conn->prepare(
                    "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'password' LIMIT 1"
                );
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($row && (int) $row['CHARACTER_MAXIMUM_LENGTH'] < 255) {
                    $conn->query('ALTER TABLE `users` MODIFY `password` VARCHAR(255) NOT NULL');
                }
            }

            if ($tableExists($conn, 'menu_items')) {
                if (!$columnExists($conn, 'menu_items', 'category')) {
                    $conn->query("ALTER TABLE `menu_items` ADD COLUMN `category` VARCHAR(60) NOT NULL DEFAULT 'Mains' AFTER `name`");
                }
                if (!$columnExists($conn, 'menu_items', 'prep_minutes')) {
                    $conn->query('ALTER TABLE `menu_items` ADD COLUMN `prep_minutes` INT NOT NULL DEFAULT 10 AFTER `price`');
                }
                if (!$columnExists($conn, 'menu_items', 'image_url')) {
                    $conn->query('ALTER TABLE `menu_items` ADD COLUMN `image_url` VARCHAR(255) NULL AFTER `prep_minutes`');
                }
            }

            if ($tableExists($conn, 'orders')) {
                if (!$columnExists($conn, 'orders', 'notes')) {
                    $conn->query('ALTER TABLE `orders` ADD COLUMN `notes` VARCHAR(255) NULL AFTER `preparation_time`');
                }
                if (!$columnExists($conn, 'orders', 'updated_at')) {
                    $conn->query('ALTER TABLE `orders` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `order_time`');
                }
            }

            if ($tableExists($conn, 'notifications') && !$columnExists($conn, 'notifications', 'created_at')) {
                $conn->query('ALTER TABLE `notifications` ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
            }

            if ($tableExists($conn, 'daily_reports')) {
                if (!$columnExists($conn, 'daily_reports', 'completed_orders')) {
                    $conn->query('ALTER TABLE `daily_reports` ADD COLUMN `completed_orders` INT NOT NULL DEFAULT 0');
                }
                if (!$columnExists($conn, 'daily_reports', 'cancelled_orders')) {
                    $conn->query('ALTER TABLE `daily_reports` ADD COLUMN `cancelled_orders` INT NOT NULL DEFAULT 0');
                }
                if (!$columnExists($conn, 'daily_reports', 'total_income')) {
                    if ($columnExists($conn, 'daily_reports', 'total_revenue')) {
                        $conn->query('ALTER TABLE `daily_reports` CHANGE COLUMN `total_revenue` `total_income` DECIMAL(10, 2) NOT NULL DEFAULT 0.00');
                    } else {
                        $conn->query('ALTER TABLE `daily_reports` ADD COLUMN `total_income` DECIMAL(10, 2) NOT NULL DEFAULT 0.00');
                    }
                }
            }

            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['_schema_checked'] = true;
            }
        } catch (mysqli_sql_exception $e) {
            // Do not crash normal requests if DB user lacks ALTER privileges.
        }
    }
}

ensure_schema_upgrades($conn);
