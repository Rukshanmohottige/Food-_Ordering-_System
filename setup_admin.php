<?php
include "config/database.php";

// Delete old admin accounts
$conn->query("DELETE FROM users WHERE email IN ('admin@canteen.com', 'staff@canteen.com') AND user_role IN ('admin', 'staff')");

// Hash the password: admin123
$password = hash('sha256', 'admin123');

// Create admin user
$stmt = $conn->prepare("INSERT INTO users (name, email, password, user_role, created_at) VALUES (?, ?, ?, ?, NOW())");
$name_admin = 'Admin User';
$email_admin = 'admin@canteen.com';
$role_admin = 'admin';
$stmt->bind_param('ssss', $name_admin, $email_admin, $password, $role_admin);
$stmt->execute();

// Create staff user
$stmt = $conn->prepare("INSERT INTO users (name, email, password, user_role, created_at) VALUES (?, ?, ?, ?, NOW())");
$name_staff = 'Staff User';
$email_staff = 'staff@canteen.com';
$role_staff = 'staff';
$stmt->bind_param('ssss', $name_staff, $email_staff, $password, $role_staff);
$stmt->execute();

echo "<h2 style='color: green;'>✓ Admin accounts created successfully!</h2>";
echo "<hr>";
echo "<h3>Login Credentials:</h3>";
echo "<table style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'><strong>Role</strong></td>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'><strong>Email</strong></td>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'><strong>Password</strong></td>";
echo "</tr>";
echo "<tr>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'>Admin</td>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'>admin@canteen.com</td>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'>admin123</td>";
echo "</tr>";
echo "<tr>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'>Staff</td>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'>staff@canteen.com</td>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'>admin123</td>";
echo "</tr>";
echo "</table>";
echo "<hr>";
echo "<p><strong>⚠️ IMPORTANT:</strong> Delete this file after setup!</p>";
echo "<p><a href='admin/login.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a></p>";
?>
