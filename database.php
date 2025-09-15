<?php
// ===== Database credentials =====
$dbname = 'esp_mlx_dbnorahv3';  // Update if your DB name changed
$dbuser = 'root';
$dbpass = '';
$dbhost = 'localhost';

// Connect to MySQL
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: return success message
echo json_encode([
    "status" => "success",
    "message" => "Database connected successfully!"
]);
?>
