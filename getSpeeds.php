<?php
// ===== Database settings =====
$dbname = 'esp_mlx_dbnorahv3';
$dbuser = 'root';
$dbpass = '';
$dbhost = 'localhost';

// Connect to MySQL
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get the ESP32 ID from POST (default to 'esp32_01')
$id = $_POST['id'] ?? 'esp32_01';

// Fetch data from the table
$sql = "SELECT * FROM esp32_table_mlx WHERE id='$id'";
$result = $conn->query($sql);

if($result->num_rows > 0){
    $row = $result->fetch_assoc();

    // Prepare JSON response with all speed flags for both sensors
    $response = [
        // Sensor 1 buttons
        "speed1_1" => (int)$row['speed1_1'],
        "speed2_1" => (int)$row['speed2_1'],
        "speed3_1" => (int)$row['speed3_1'],
        "speed4_1" => (int)$row['speed4_1'],

        // Sensor 2 buttons
        "speed1_2" => (int)$row['speed1_2'],
        "speed2_2" => (int)$row['speed2_2'],
        "speed3_2" => (int)$row['speed3_2'],
        "speed4_2" => (int)$row['speed4_2']
    ];

    echo json_encode($response);
} else {
    echo json_encode(["error"=>"No data"]);
}

$conn->close();
?>
