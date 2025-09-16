<?php 
// Database connection
$dbname = "esp_mlx_dbnorahv3";
$dbuser = "root";
$dbpass = "";
$dbhost = "localhost";

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ESP32 MLX Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/boost.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<style>
body { font-family: Arial, sans-serif; margin: 10px; background: #f4f4f4; }
.card { border: 1px solid #ccc; border-radius: 12px; padding: 20px; margin-bottom: 20px; background: #fff; }
h2 { margin-top: 0; }
.btn { padding: 12px 18px; margin: 5px; border: none; border-radius: 8px; font-size: 1em; cursor: pointer; min-width: 100px; }
.on { background: #4CAF50; color: white; }
.off { background: #f44336; color: white; }
.reset { background: #6c757d; color: white; }
.btn-group { display: flex; flex-wrap: wrap; }
.chart-container {
  position: relative;
  box-sizing: content-box;
  height: 80vh;
  width: 100%;
  margin-bottom: 30px;
}
.highcharts-background { fill: transparent; }
</style>
</head>
<body>

<div class="card">
    <h2>📊 Sensor Data</h2>
    <p><b>Ambient Temp Sensor 1:</b> <span id="ambient1">0</span> °C</p>
    <p><b>Object Temp Sensor 1:</b> <span id="object1">0</span> °C</p>
    <p><b>Applied Speed Sensor 1:</b> <span id="speed1">0</span></p>
    <p><b>Proportional Speed Sensor 1:</b> <span id="proportional1">0</span></p>

    <hr>

    <p><b>Ambient Temp Sensor 2:</b> <span id="ambient2">0</span> °C</p>
    <p><b>Object Temp Sensor 2:</b> <span id="object2">0</span> °C</p>
    <p><b>Applied Speed Sensor 2:</b> <span id="speed2">0</span></p>
    <p><b>Proportional Speed Sensor 2:</b> <span id="proportional2">0</span></p>
</div>

<div class="card">
    <h2>⚙️ Manual Speed Controls</h2>
    <p>Sensor 1:</p>
    <div class="btn-group">
        <button class="btn off" id="speed1_1">Speed1 OFF</button>
        <button class="btn off" id="speed2_1">Speed2 OFF</button>
        <button class="btn off" id="speed3_1">Speed3 OFF</button>
        <button class="btn off" id="speed4_1">Speed4 OFF</button>
    </div>

    <p>Sensor 2:</p>
    <div class="btn-group">
        <button class="btn off" id="speed1_2">Speed1 OFF</button>
        <button class="btn off" id="speed2_2">Speed2 OFF</button>
        <button class="btn off" id="speed3_2">Speed3 OFF</button>
        <button class="btn off" id="speed4_2">Speed4 OFF</button>
    </div>

    <button class="btn reset" id="reset">Reset All Speeds</button>
</div>

<div class="card">
    <h2>📈 Ambient Temp Graph Sensor 1</h2>
    <div id="ambientChart1" class="chart-container"></div>
</div>

<div class="card">
    <h2>📈 Ambient Temp Graph Sensor 2</h2>
    <div id="ambientChart2" class="chart-container"></div>
</div>

<div class="card">
    <h2>📈 Object Temp vs Applied Speed Sensor 1</h2>
    <div id="objSpeedChart1" class="chart-container"></div>
</div>

<div class="card">
    <h2>📈 Object Temp vs Applied Speed Sensor 2</h2>
    <div id="objSpeedChart2" class="chart-container"></div>
</div>

<script>
function createCharts() {
    window.ambientChart1 = Highcharts.chart('ambientChart1', {
        chart: { type: 'spline', backgroundColor: 'transparent' },
        title: { text: '' },
        xAxis: { type: 'datetime' },
        yAxis: { title: { text: '°C' } },
        legend: { enabled: false },
        credits: { enabled: false },
        series: [{ name: 'Ambient Temp 1', data: [], color:'#90ed7d', lineWidth: 2, marker:{enabled:false} }]
    });

    window.ambientChart2 = Highcharts.chart('ambientChart2', {
        chart: { type: 'spline', backgroundColor: 'transparent' },
        title: { text: '' },
        xAxis: { type: 'datetime' },
        yAxis: { title: { text: '°C' } },
        legend: { enabled: false },
        credits: { enabled: false },
        series: [{ name: 'Ambient Temp 2', data: [], color:'#7cb5ec', lineWidth: 2, marker:{enabled:false} }]
    });

    window.objSpeedChart1 = Highcharts.chart('objSpeedChart1', {
        chart: { type: 'spline', backgroundColor: 'transparent' },
        title: { text: '' },
        xAxis: { title: { text: 'Object Temp (°C)' }, min: 0, max: 70, tickPositions:[0,30,35,40,45,50,55,60,70] },
        yAxis: { title: { text: 'Applied Speed (%)' }, min:0, max:100, tickPositions:[0,10,20,30,40,50,60,70,80,90,100] },
        tooltip: {
            shared: true,
            formatter: function() {
                return `Object Temp: ${this.x} °C<br>Applied Speed: ${this.y}%`;
            }
        },
        legend: { enabled: false },
        credits: { enabled: false },
        series: [{
            name:'Applied Speed 1',
            data:[],
            color:'#f45b5b',
            lineWidth: 2,
            marker: { enabled: true, radius: 4 }
        }]
    });

    window.objSpeedChart2 = Highcharts.chart('objSpeedChart2', {
        chart: { type: 'spline', backgroundColor: 'transparent' },
        title: { text: '' },
        xAxis: { title: { text: 'Object Temp (°C)' }, min: 0, max: 70, tickPositions:[0,30,35,40,45,50,55,60,70] },
        yAxis: { title: { text: 'Applied Speed (%)' }, min:0, max:100, tickPositions:[0,10,20,30,40,50,60,70,80,90,100] },
        tooltip: {
            shared: true,
            formatter: function() {
                return `Object Temp: ${this.x} °C<br>Applied Speed: ${this.y}%`;
            }
        },
        legend: { enabled: false },
        credits: { enabled: false },
        series: [{
            name:'Applied Speed 2',
            data:[],
            color:'#ff9800',
            lineWidth: 2,
            marker: { enabled: true, radius: 4 }
        }]
    });
}

createCharts();

// === Continuous Data Logging ===
function refreshData() {
    fetch("fetchSensorData.php")
    .then(res => res.json())
    .then(data => {
        // Update sensor values
        document.getElementById("ambient1").textContent = data.sensor1.amb;
        document.getElementById("object1").textContent = data.sensor1.obj;
        document.getElementById("speed1").textContent = data.sensor1.appliedSpeed + "%";
        document.getElementById("proportional1").textContent = data.sensor1.proportionalSpeed;

        document.getElementById("ambient2").textContent = data.sensor2.amb;
        document.getElementById("object2").textContent = data.sensor2.obj;
        document.getElementById("speed2").textContent = data.sensor2.appliedSpeed + "%";
        document.getElementById("proportional2").textContent = data.sensor2.proportionalSpeed;

        const now = (new Date()).getTime();

        // Ambient charts (time-series)
        ambientChart1.series[0].addPoint([now, parseFloat(data.sensor1.amb)], true, ambientChart1.series[0].data.length > 100);
        ambientChart2.series[0].addPoint([now, parseFloat(data.sensor2.amb)], true, ambientChart2.series[0].data.length > 100);

        // Object Temp vs Applied Speed (spline)
        objSpeedChart1.series[0].addPoint({ x: parseFloat(data.sensor1.obj), y: parseFloat(data.sensor1.appliedSpeed) }, true, false);
        objSpeedChart2.series[0].addPoint({ x: parseFloat(data.sensor2.obj), y: parseFloat(data.sensor2.appliedSpeed) }, true, false);
    });
}

setInterval(refreshData, 3000);

// === Manual Speed Buttons ===
function updateSpeed(speedField, value) {
    fetch("updateSpeeds.php", {
        method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body: new URLSearchParams({[speedField]: value})
    }).then(res => res.text()).then(msg => console.log(msg));
}

["speed1_1","speed2_1","speed3_1","speed4_1","speed1_2","speed2_2","speed3_2","speed4_2"].forEach(s=>{
    const btn = document.getElementById(s);
    btn.addEventListener("click", ()=>{
        const isOn = btn.classList.contains("on");
        btn.classList.toggle("on", !isOn);
        btn.classList.toggle("off", isOn);
        btn.textContent = s + " " + (isOn?"OFF":"ON");
        updateSpeed(s, isOn?0:1);
    });
});

document.getElementById("reset").addEventListener("click", ()=>{
    ["speed1_1","speed2_1","speed3_1","speed4_1","speed1_2","speed2_2","speed3_2","speed4_2"].forEach(s=>{
        const btn = document.getElementById(s);
        btn.classList.remove("on");
        btn.classList.add("off");
        btn.textContent = s + " OFF";
        updateSpeed(s,0);
    });
});
</script>
</body>
</html>
