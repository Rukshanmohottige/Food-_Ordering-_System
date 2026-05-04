<?php
$host = "localhost";
$user = "root";
$pass = ""; // XAMPP default
$db   = "food_ordering_system";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("<h2>Database Connection Error</h2><p>Unable to connect to database: " . htmlspecialchars($conn->connect_error) . "</p><p>Make sure:<br>1. MySQL is running<br>2. Database 'food_ordering_system' exists<br>3. Run db_structure.sql to create tables</p>");
}
$conn->set_charset("utf8mb4");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>
