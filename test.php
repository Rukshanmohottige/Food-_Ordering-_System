<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>System Test - Food Ordering System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
            border-left: 5px solid #28a745;
            padding-left: 10px;
        }
        .test-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #ccc;
        }
        .pass {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .fail {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .pass::before {
            content: "✓ ";
            font-weight: bold;
            color: #28a745;
        }
        .fail::before {
            content: "✗ ";
            font-weight: bold;
            color: #dc3545;
        }
        .warning::before {
            content: "⚠ ";
            font-weight: bold;
            color: #ffc107;
        }
        .code {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 3px;
            font-family: monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table td, table th {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background: #f4f4f4;
            font-weight: bold;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .stat-box h3 {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        .stat-box .value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin: 10px 0;
        }
        .button-group {
            margin: 20px 0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>🧪 Food Ordering System - System Test</h1>
    <p>Comprehensive system check and diagnostics</p>

    <?php
    $pass_count = 0;
    $fail_count = 0;
    $warn_count = 0;

    echo '<h2>1. PHP Environment</h2>';

    // PHP Version
    $php_version = phpversion();
    echo '<div class="test-item pass">PHP Version: ' . $php_version . '</div>';
    $pass_count++;

    // Session Support
    if(session_status() === PHP_SESSION_ACTIVE) {
        echo '<div class="test-item pass">Session Support: Enabled</div>';
        $pass_count++;
    } else {
        echo '<div class="test-item fail">Session Support: Disabled</div>';
        $fail_count++;
    }

    // Extensions
    $required_extensions = ['mysqli', 'hash'];
    foreach($required_extensions as $ext) {
        if(extension_loaded($ext)) {
            echo '<div class="test-item pass">Extension: ' . strtoupper($ext) . ' loaded</div>';
            $pass_count++;
        } else {
            echo '<div class="test-item fail">Extension: ' . strtoupper($ext) . ' NOT loaded</div>';
            $fail_count++;
        }
    }

    echo '<h2>2. Database Connection</h2>';

    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "food_ordering_system";

    $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);

    if($conn->connect_error) {
        echo '<div class="test-item fail">Database Connection: FAILED</div>';
        echo '<div class="test-item fail">Error: ' . htmlspecialchars($conn->connect_error) . '</div>';
        $fail_count += 2;
    } else {
        echo '<div class="test-item pass">Database Connection: SUCCESS</div>';
        echo '<div class="test-item pass">Host: ' . $db_host . '</div>';
        echo '<div class="test-item pass">Database: ' . $db_name . '</div>';
        $pass_count += 3;

        echo '<h2>3. Database Tables</h2>';

        $tables = ['users', 'menu_items', 'orders', 'order_items', 'notifications', 'daily_reports'];

        $result = $conn->query("SHOW TABLES");
        $existing_tables = [];
        while($row = $result->fetch_row()) {
            $existing_tables[] = $row[0];
        }

        foreach($tables as $table) {
            if(in_array($table, $existing_tables)) {
                $count_result = $conn->query("SELECT COUNT(*) as cnt FROM $table");
                $count = $count_result->fetch_assoc()['cnt'];
                echo '<div class="test-item pass">Table: ' . $table . ' (Records: ' . $count . ')</div>';
                $pass_count++;
            } else {
                echo '<div class="test-item fail">Table: ' . $table . ' - MISSING</div>';
                $fail_count++;
            }
        }

        echo '<h2>4. Database Records Summary</h2>';
        echo '<div class="stats">';

        $counts = [];
        $tables_info = [
            'users' => 'Users',
            'menu_items' => 'Menu Items',
            'orders' => 'Orders'
        ];

        foreach($tables_info as $table => $label) {
            $result = $conn->query("SELECT COUNT(*) as cnt FROM $table");
            $count = $result->fetch_assoc()['cnt'];
            $counts[$table] = $count;
            echo '<div class="stat-box">';
            echo '<h3>' . $label . '</h3>';
            echo '<div class="value">' . $count . '</div>';
            echo '</div>';
        }

        echo '</div>';

        // Users Details
        echo '<h2>5. Admin Users</h2>';
        $result = $conn->query("SELECT user_id, name, email, user_role FROM users WHERE user_role IN ('admin', 'staff') ORDER BY user_role DESC");

        if($result->num_rows > 0) {
            echo '<table>';
            echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>';
            while($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['user_id'] . '</td>';
                echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                echo '<td><strong>' . strtoupper($row['user_role']) . '</strong></td>';
                echo '</tr>';
            }
            echo '</table>';
            $pass_count++;
        } else {
            echo '<div class="test-item fail">No admin/staff users found!</div>';
            $fail_count++;
        }

        // Menu Items
        echo '<h2>6. Menu Items</h2>';
        $result = $conn->query("SELECT * FROM menu_items LIMIT 5");

        if($result->num_rows > 0) {
            echo '<table>';
            echo '<tr><th>ID</th><th>Name</th><th>Price</th><th>Available</th></tr>';
            while($row = $result->fetch_assoc()) {
                $available = $row['is_available'] ? '✓ Yes' : '✗ No';
                echo '<tr>';
                echo '<td>' . $row['item_id'] . '</td>';
                echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                echo '<td>Rs ' . number_format($row['price'], 2) . '</td>';
                echo '<td>' . $available . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            $pass_count++;
        } else {
            echo '<div class="test-item warning">No menu items yet - Add items from admin panel</div>';
            $warn_count++;
        }

        // Recent Orders
        echo '<h2>7. Recent Orders</h2>';
        $result = $conn->query("SELECT o.order_id, u.name, o.total_amount, o.status, o.order_time FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.order_time DESC LIMIT 5");

        if($result->num_rows > 0) {
            echo '<table>';
            echo '<tr><th>Order ID</th><th>Customer</th><th>Amount</th><th>Status</th><th>Time</th></tr>';
            while($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>#' . $row['order_id'] . '</td>';
                echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                echo '<td>Rs ' . number_format($row['total_amount'], 2) . '</td>';
                echo '<td>' . ucfirst($row['status']) . '</td>';
                echo '<td>' . $row['order_time'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            $pass_count++;
        } else {
            echo '<div class="test-item warning">No orders yet</div>';
            $warn_count++;
        }

        $conn->close();
    }

    echo '<h2>8. File System Check</h2>';

    $files_to_check = [
        'config/database.php' => 'Database Config',
        'admin/login.php' => 'Admin Login',
        'admin/dashboard.php' => 'Admin Dashboard',
        'admin/admin_logout.php' => 'Admin Logout',
        'public/index.php' => 'Public Home',
        'public/login.php' => 'User Login',
        'public/register.php' => 'User Register',
        'actions/user_login.php' => 'Login Action',
        'actions/place_order.php' => 'Place Order Action'
    ];

    $base_dir = dirname(__FILE__);

    foreach($files_to_check as $file => $label) {
        $full_path = $base_dir . '/' . $file;
        if(file_exists($full_path)) {
            echo '<div class="test-item pass">File exists: ' . $label . ' (' . $file . ')</div>';
            $pass_count++;
        } else {
            echo '<div class="test-item fail">File missing: ' . $label . ' (' . $file . ')</div>';
            $fail_count++;
        }
    }

    echo '<h2>9. Test Results Summary</h2>';

    echo '<div class="stats">';
    echo '<div class="stat-box">';
    echo '<h3>PASSED</h3>';
    echo '<div class="value" style="color: #28a745;">' . $pass_count . '</div>';
    echo '</div>';

    echo '<div class="stat-box">';
    echo '<h3>FAILED</h3>';
    echo '<div class="value" style="color: #dc3545;">' . $fail_count . '</div>';
    echo '</div>';

    echo '<div class="stat-box">';
    echo '<h3>WARNINGS</h3>';
    echo '<div class="value" style="color: #ffc107;">' . $warn_count . '</div>';
    echo '</div>';
    echo '</div>';

    if($fail_count === 0) {
        echo '<div class="test-item pass" style="font-size: 18px; text-align: center; padding: 20px;">✓ All Critical Tests Passed! System is Ready!</div>';
    } else {
        echo '<div class="test-item fail" style="font-size: 18px; text-align: center; padding: 20px;">✗ Some tests failed. See errors above.</div>';
    }

    echo '<h2>10. Quick Links</h2>';
    echo '<div class="button-group">';
    echo '<a href="../admin/login.php" class="btn btn-primary">Admin Login</a>';
    echo '<a href="../public/login.php" class="btn btn-primary">User Login</a>';
    echo '<a href="../public/register.php" class="btn btn-success">User Register</a>';
    echo '<a href="../public/menu.php" class="btn btn-primary">View Menu</a>';
    echo '<a href="test.php" class="btn btn-danger">Refresh Test</a>';
    echo '</div>';
    ?>

    <h2>11. Actions</h2>
    <div class="button-group">
        <form method="POST" style="display: inline;">
            <input type="hidden" name="action" value="clear_sessions">
            <button type="submit" class="btn btn-danger">Clear All Sessions</button>
        </form>
        <form method="POST" style="display: inline;">
            <input type="hidden" name="action" value="test_db">
            <button type="submit" class="btn btn-primary">Test Database Query</button>
        </form>
    </div>

    <?php
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if($action === 'clear_sessions') {
            session_destroy();
            echo '<div class="test-item pass">✓ All sessions cleared</div>';
        }

        if($action === 'test_db') {
            $conn = new mysqli("localhost", "root", "", "food_ordering_system");
            if(!$conn->connect_error) {
                $result = $conn->query("SELECT DATABASE() as db_name");
                $row = $result->fetch_assoc();
                echo '<div class="test-item pass">Database Test Query Successful: ' . $row['db_name'] . '</div>';
                $conn->close();
            } else {
                echo '<div class="test-item fail">Database Test Query Failed</div>';
            }
        }
    }
    ?>

    <hr style="margin: 30px 0;">
    <p style="color: #666; font-size: 12px;">
        Test generated on: <?php echo date('Y-m-d H:i:s'); ?><br>
        For security, delete this file (test.php) after diagnosis.
    </p>

</div>

</body>
</html>
