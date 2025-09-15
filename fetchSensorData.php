<?php
// ===== Database settings =====
$dbname = "esp_mlx_dbnorahv3";
$dbuser = "root";
$dbpass = "";
$dbhost = "localhost";

// Connect to MySQL
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch data for ESP32
$sql = "SELECT * FROM esp32_table_mlx WHERE id='esp32_01' LIMIT 1";
$result = $conn->query($sql);
$data = $result->fetch_assoc();

// Set JSON header
header('Content-Type: application/json');

if ($data) {
    // Prepare structured JSON for both sensors
    $response = [
        "sensor1" => [
            "amb" => floatval($data['amb1']),
            "obj" => floatval($data['obj1']),
            "appliedSpeed" => intval($data['speed1']),
            "proportionalSpeed" => intval($data['proportionalSpeed1']),
            "speed1" => intval($data['speed1_1']),
            "speed2" => intval($data['speed2_1']),
            "speed3" => intval($data['speed3_1']),
            "speed4" => intval($data['speed4_1'])
        ],
        "sensor2" => [
            "amb" => floatval($data['amb2']),
            "obj" => floatval($data['obj2']),
            "appliedSpeed" => intval($data['speed2']),
            "proportionalSpeed" => intval($data['proportionalSpeed2']),
            "speed1" => intval($data['speed1_2']),
            "speed2" => intval($data['speed2_2']),
            "speed3" => intval($data['speed3_2']),
            "speed4" => intval($data['speed4_2'])
        ]
    ];
    echo json_encode($response);
} else {
    echo json_encode(["error" => "No data"]);
}

$conn->close();
?>
