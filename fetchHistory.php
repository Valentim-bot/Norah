<?php
// ===== Database settings =====
$dbname = "esp_mlx_dbnorahv3"; // Update if your DB name changed
$dbuser = "root";
$dbpass = "";
$dbhost = "localhost";

// Connect to MySQL
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Adjust LIMIT 100 to however many points you want
$sql = "SELECT timestamp, amb1, obj1, speed1, proportionalSpeed1, amb2, obj2, speed2, proportionalSpeed2
        FROM esp32_table_mlx
        WHERE id='esp32_01'
        ORDER BY timestamp DESC
        LIMIT 100";

$result = $conn->query($sql);
$data = [];

// Fetch each row
while($row = $result->fetch_assoc()) {
    // Structure the data per row for both sensors
    $data[] = [
        "timestamp" => $row['timestamp'],
        "sensor1" => [
            "amb" => floatval($row['amb1']),
            "obj" => floatval($row['obj1']),
            "appliedSpeed" => intval($row['speed1']),
            "proportionalSpeed" => intval($row['proportionalSpeed1'])
        ],
        "sensor2" => [
            "amb" => floatval($row['amb2']),
            "obj" => floatval($row['obj2']),
            "appliedSpeed" => intval($row['speed2']),
            "proportionalSpeed" => intval($row['proportionalSpeed2'])
        ]
    ];
}

// Send JSON response (oldest first)
header('Content-Type: application/json');
echo json_encode(array_reverse($data));

$conn->close();
?>
