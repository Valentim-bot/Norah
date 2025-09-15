<?php
// ===== Database settings =====
$dbname = 'esp_mlx_dbnorahv3';
$dbuser = 'root';
$dbpass = '';
$dbhost = 'localhost';

// Connect to MySQL
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get POST data with defaults
$id = $_POST['id'] ?? 'esp32_01';

// ===== Sensor 1 =====
$amb1 = $_POST['amb1'] ?? 0;
$obj1 = $_POST['obj1'] ?? 0;
$speed1 = $_POST['speed1'] ?? 0;
$proportionalSpeed1 = $_POST['proportionalSpeed1'] ?? 0;

// ===== Sensor 2 =====
$amb2 = $_POST['amb2'] ?? 0;
$obj2 = $_POST['obj2'] ?? 0;
$speed2 = $_POST['speed2'] ?? 0;
$proportionalSpeed2 = $_POST['proportionalSpeed2'] ?? 0;

// ===== Update the table =====
$sql = "UPDATE esp32_table_mlx SET
        amb1='$amb1', obj1='$obj1', speed1='$speed1', proportionalSpeed1='$proportionalSpeed1',
        amb2='$amb2', obj2='$obj2', speed2='$speed2', proportionalSpeed2='$proportionalSpeed2'
        WHERE id='$id'";

if($conn->query($sql) === TRUE){
    echo "Updated successfully";
} else {
    echo "Error: ".$conn->error;
}

$conn->close();
?>
