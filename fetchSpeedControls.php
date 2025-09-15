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

if ($data) {
    // ===== Sensor 1 buttons =====
    echo "<h3>Sensor 1 Controls</h3>";
    $buttons1 = ['speed1_1','speed2_1','speed3_1','speed4_1'];
    foreach ($buttons1 as $b) {
        $val = $data[$b];
        $status = $val ? 'ON' : 'OFF';
        $class = $val ? 'on' : 'off';
        echo "<button type='submit' name='$b' value='" . ($val ? 0 : 1) . "' class='btn $class'>$b $status</button> ";
    }
    echo "<br>";

    // ===== Sensor 2 buttons =====
    echo "<h3>Sensor 2 Controls</h3>";
    $buttons2 = ['speed1_2','speed2_2','speed3_2','speed4_2'];
    foreach ($buttons2 as $b) {
        $val = $data[$b];
        $status = $val ? 'ON' : 'OFF';
        $class = $val ? 'on' : 'off';
        echo "<button type='submit' name='$b' value='" . ($val ? 0 : 1) . "' class='btn $class'>$b $status</button> ";
    }
    echo "<br>";

    // ===== Reset button =====
    echo "<button type='submit' name='reset' value='1' class='btn reset'>Reset All Speeds</button>";
} else {
    echo "<p>No data found.</p>";
}

$conn->close();
?>
