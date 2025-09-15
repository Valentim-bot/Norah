<?php 
// Database connection (optional for initial page load)
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
body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
.card { border: 1px solid #ccc; border-radius: 12px; padding: 25px; margin-bottom: 25px; background: #fff; }
h2 { margin-top: 0; }
.btn { padding: 15px 25px; margin: 10px; border: none; border-radius: 10px; font-size: 1.2em; cursor: pointer; }
.on { background: #4CAF50; color: white; }
.off { background: #f44336; color: white; }
.reset { background: #6c757d; color: white; }
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
    <button class="btn off" id="speed1_1">Speed1 OFF</button>
    <button class="btn off" id="speed2_1">Speed2 OFF</button>
    <button class="btn off" id="speed3_1">Speed3 OFF</button>
    <button class="btn off" id="speed4_1">Speed4 OFF</button>

    <p>Sensor 2:</p>
    <button class="btn off" id="speed1_2">Speed1 OFF</button>
    <button class="btn off" id="speed2_2">Speed2 OFF</button>
    <button class="btn off" id="speed3_2">Speed3 OFF</button>
    <button class="btn off" id="speed4_2">Speed4 OFF</button>

    <button class="btn reset" id="reset">Reset All Speeds</button>
</div>

<div class="card">
    <h2>📈 Ambient Temp Graph Sensor 1</h2>
    <div id="ambientChart1" style="height:300px;"></div>
</div>

<div class="card">
    <h2>📈 Ambient Temp Graph Sensor 2</h2>
    <div id="ambientChart2" style="height:300px;"></div>
</div>

<div class="card">
    <h2>📈 Object Temp vs Applied Speed Sensor 1</h2>
    <div id="objSpeedChart1" style="height:300px;"></div>
</div>

<div class="card">
    <h2>📈 Object Temp vs Applied Speed Sensor 2</h2>
    <div id="objSpeedChart2" style="height:300px;"></div>
</div>

<script>
function createCharts() {
    window.ambientChart1 = Highcharts.chart('ambientChart1', {
        chart: { type: 'spline' },
        title: { text: 'Ambient Temp Sensor 1' },
        xAxis: { type: 'datetime' },
        yAxis: { title: { text: '°C' } },
        series: [{ name: 'Ambient Temp 1', data: [], color:'#90ed7d' }]
    });

    window.ambientChart2 = Highcharts.chart('ambientChart2', {
        chart: { type: 'spline' },
        title: { text: 'Ambient Temp Sensor 2' },
        xAxis: { type: 'datetime' },
        yAxis: { title: { text: '°C' } },
        series: [{ name: 'Ambient Temp 2', data: [], color:'#7cb5ec' }]
    });

    window.objSpeedChart1 = Highcharts.chart('objSpeedChart1', {
        chart: { type: 'spline' },
        title: { text: 'Object Temp vs Applied Speed Sensor 1' },
        xAxis: { title: { text: 'Object Temp (°C)' }, min: 0, max: 70, tickPositions:[0,30,35,40,45,50,55,60,70] },
        yAxis: { title: { text: 'Applied Speed (%)' }, min:0, max:100, tickPositions:[0,10,20,30,40,50,60,70,80,90,100] },
        series: [{ name:'Applied Speed 1', data:[], color:'#f45b5b' }]
    });

    window.objSpeedChart2 = Highcharts.chart('objSpeedChart2', {
        chart: { type: 'spline' },
        title: { text: 'Object Temp vs Applied Speed Sensor 2' },
        xAxis: { title: { text: 'Object Temp (°C)' }, min: 0, max: 70, tickPositions:[0,30,35,40,45,50,55,60,70] },
        yAxis: { title: { text: 'Applied Speed (%)' }, min:0, max:100, tickPositions:[0,10,20,30,40,50,60,70,80,90,100] },
        series: [{ name:'Applied Speed 2', data:[], color:'#ff9800' }]
    });
}

createCharts();

function refreshData() {
    fetch("fetchSensorData.php", { method:"GET" })
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

        // Update Ambient Charts
        ambientChart1.series[0].addPoint([now, parseFloat(data.sensor1.amb)], true, ambientChart1.series[0].data.length > 20);
        ambientChart2.series[0].addPoint([now, parseFloat(data.sensor2.amb)], true, ambientChart2.series[0].data.length > 20);

        // Update Object vs Applied Charts
        objSpeedChart1.series[0].addPoint([parseFloat(data.sensor1.obj), parseFloat(data.sensor1.appliedSpeed)], true, false);
        if(objSpeedChart1.series[0].data.length > 40){
            objSpeedChart1.series[0].update({ data: objSpeedChart1.series[0].data.slice(-40) }, true, false);
        }

        objSpeedChart2.series[0].addPoint([parseFloat(data.sensor2.obj), parseFloat(data.sensor2.appliedSpeed)], true, false);
        if(objSpeedChart2.series[0].data.length > 40){
            objSpeedChart2.series[0].update({ data: objSpeedChart2.series[0].data.slice(-40) }, true, false);
        }

        // Update Manual Buttons
        ["speed1_1","speed2_1","speed3_1","speed4_1","speed1_2","speed2_2","speed3_2","speed4_2"].forEach(s => {
            const btn = document.getElementById(s);
            const val = parseInt(data.sensor1[s.replace('_2','_1')] ?? data.sensor2[s]);
            btn.classList.remove('on','off');
            btn.classList.add(val ? 'on' : 'off');
            btn.textContent = s + " " + (val ? "ON" : "OFF");
        });
    });
}

setInterval(refreshData, 3000); // refresh every 3s

function updateSpeed(speedField, value) {
    fetch("updateSpeeds.php", { method:"POST", headers:{"Content-Type":"application/x-www-form-urlencoded"}, body: new URLSearchParams({[speedField]: value}) })
    .then(res => res.text()).then(msg => console.log(msg));
}

["speed1_1","speed2_1","speed3_1","speed4_1","speed1_2","speed2_2","speed3_2","speed4_2"].forEach(s=>{
    const btn = document.getElementById(s);
    btn.addEventListener("click", ()=>{
        const isOn = btn.classList.contains("on");
        btn.classList.remove('on','off');
        btn.classList.add(isOn?'off':'on');
        btn.textContent = s + " " + (isOn?"OFF":"ON");
        updateSpeed(s, isOn?0:1);
    });
});

document.getElementById("reset").addEventListener("click", ()=>{
    ["speed1_1","speed2_1","speed3_1","speed4_1","speed1_2","speed2_2","speed3_2","speed4_2"].forEach(s=>{
        document.getElementById(s).classList.remove('on'); 
        document.getElementById(s).classList.add('off'); 
        document.getElementById(s).textContent = s + " OFF"; 
        updateSpeed(s,0);
    });
});
</script>

</body>
</html>
