<?php
// ===== Database settings =====
$dbname = 'esp_mlx_dbnorahv3';
$dbuser = 'root';
$dbpass = '';
$dbhost = 'localhost';

// Connect to MySQL
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$id = 'esp32_01';

// ===== Sensor 1 & Sensor 2 speed fields =====
$fields_sensor1 = ['speed1_1','speed2_1','speed3_1','speed4_1'];
$fields_sensor2 = ['speed1_2','speed2_2','speed3_2','speed4_2'];

// ===== Reset all speeds =====
if(isset($_POST['reset'])){
    $conn->query("
        UPDATE esp32_table_mlx SET 
        speed1_1=0, speed2_1=0, speed3_1=0, speed4_1=0,
        speed1_2=0, speed2_2=0, speed3_2=0, speed4_2=0
        WHERE id='$id'
    ");
    echo "All speeds reset";
    $conn->close();
    exit;
}

// ===== Toggle single speed button for Sensor 1 =====
foreach($fields_sensor1 as $f){
    if(isset($_POST[$f])){
        $val = intval($_POST[$f]);
        $sql = "UPDATE esp32_table_mlx SET $f='$val' WHERE id='$id'";
        $conn->query($sql);
        echo "Updated $f to $val";
        $conn->close();
        exit;
    }
}

// ===== Toggle single speed button for Sensor 2 =====
foreach($fields_sensor2 as $f){
    if(isset($_POST[$f])){
        $val = intval($_POST[$f]);
        $sql = "UPDATE esp32_table_mlx SET $f='$val' WHERE id='$id'";
        $conn->query($sql);
        echo "Updated $f to $val";
        $conn->close();
        exit;
    }
}

$conn->close();
?>
