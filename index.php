<?php
$startTime = array_sum(explode(' ', microtime()));
header("Cache-Control: no-store, no-cache, must-revalidate");
include 'config.php';
$db = new PDO("sqlite:$db_filename");
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
[$CurrentY, $CurrentM, $CurrentD, $CurrentH] = explode('-', date('Y-m-d-H'));
$months = array(
'01' => 'Jan',
'02' => 'Feb',
'03' => 'Mar',
'04' => 'Apr',
'05' => 'May',
'06' => 'Jun',
'07' => 'Jul',
'08' => 'Aug',
'09' => 'Sep',
'10' => 'Oct',
'11' => 'Nov',
'12' => 'Dec'
);
function readableBytes_1000($bytes) {
	if ($bytes <= 0) {
		return "0 Bytes";
	}
	$sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	$factor = floor(log($bytes) / log(1000));
	return sprintf('%.2F', $bytes / pow(1000, $factor)) . ' ' . $sizes[$factor];
}
$hourly_array = $db->query("SELECT M, D, H, rxtotal, txtotal FROM Hourly WHERE M = '$CurrentM' AND D = '$CurrentD' AND H != '00'")->fetchAll(PDO::FETCH_ASSOC);
$hourlyItems = count($hourly_array);
$daily_array = $db->query("SELECT M, D, rxtotal, txtotal FROM Daily WHERE Y = '$CurrentY' AND M = '$CurrentM'")->fetchAll(PDO::FETCH_ASSOC);
$dailyItems = count($daily_array);
$monthly_array = $db->query("SELECT Y, M, rxtotal, txtotal FROM Monthly")->fetchAll(PDO::FETCH_ASSOC);
$monthlyItems = count($monthly_array);
$yearly_array = $db->query("SELECT Y, rxtotal, txtotal FROM Yearly ORDER BY rowid ASC LIMIT 25")->fetchAll(PDO::FETCH_ASSOC);
$yearlyItems = count($yearly_array);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Bandwidth Monitor</title>
<meta name="viewport" content="user-scalable=yes, initial-scale=1, width=device-width">
<link rel="icon" href="data:;base64,iVBORw0KGgo=">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
<script src="chart.umd.min.js"></script>
<script>
function fileSizeSI(bytes) {
	const exponent = Math.floor(Math.log(bytes) / Math.log(1000.0));
	const decimalvalue = (bytes / Math.pow(1000.0, exponent)).toFixed(exponent ? 2 : 0);
		if (bytes <= 0) {
		return "0 Bytes";
	}
	decimal = decimalvalue.replace(/\.00$/,'');
	return `${decimal} ${exponent ? `${'kMGTPEZY'[exponent - 1]}B` : 'B'}`;
}
</script>
<style>
.chartCard {
width: 100%;
display: flex;
align-items: center;
justify-content: center;
}
.chartBox {
width: 100%;
padding: 5px;
border-radius: 3px;
border: solid 3px rgba(54, 162, 235, 1);
background: #d4d4d4;
}
</style>
</head>
<body>
<div class="header">
<span class="headerlogo">Bandwidth Monitor</span>
<input class="menu-btn" type="checkbox" id="menu-btn" />
<label class="menu-icon" for="menu-btn"><span class="navicon"></span></label>
<ul class="menu">
<li><a href="<?php echo $Homeurl; ?>">Home</a></li>
</ul>
</div>
<div class="content">
<div class="card">
<div class="primary-title">
<div class="primary-text">Hourly Data For <?php echo date('l'); ?></div>
</div>
<div class="supporting-text">
<div class="chartCard">
<div class="chartBox"><canvas id="hourly-bar-chart"></canvas></div>
</div>
</div>
<hr>
<div class="actions">
<div class="cardfooter"><?php echo "$CurrentY-$CurrentM-$CurrentD"; ?></div>
<div class="cardfooter float-right"><a href='raw-data.php' class="ClassicButton" title="Edit" style="text-decoration:none;">Raw Data</a></div>
</div>
</div>

<div class="card">
<div class="primary-title">
<div class="primary-text">Daily Data For <?php echo date('F'); ?></div>
</div>
<div class="supporting-text">
<div class="chartCard">
<div class="chartBox"><canvas id="daily-bar-chart"></canvas></div>
</div>
</div>
<hr>
<div class="actions">
<div class="cardfooter"></div>
<div class="cardfooter float-right"><a href='raw-data.php' class="ClassicButton" title="Edit" style="text-decoration:none;">Raw Data</a></div>
</div>
</div>

<div class="card">
<div class="primary-title">
<div class="primary-text">Monthly Data</div>
</div>
<div class="supporting-text">
<div class="chartCard">
<div class="chartBox"><canvas id="monthly-bar-chart"></canvas></div>
</div>
</div>
<hr>
<div class="actions">
<div class="cardfooter">Last 12 months</div>
<div class="cardfooter float-right"><a href='raw-data.php' class="ClassicButton" title="Edit" style="text-decoration:none;">Raw Data</a></div>
</div>
</div>

<div class="card">
<div class="primary-title">
<div class="primary-text">Yearly Data</div>
</div>
<div class="supporting-text">
<div class="chartCard">
<div class="chartBox"><canvas id="yearly-bar-chart"></canvas></div>
</div>
</div>
<hr>
<div class="actions">
<div class="cardfooter"></div>
<div class="cardfooter float-right"><a href='raw-data.php' class="ClassicButton" title="Edit" style="text-decoration:none;">Raw Data</a></div>
</div>
</div>
</div>
<div class="footer">
<div style="margin: 0 auto; padding:25px;">
<?php $totalTime = array_sum(explode(' ', microtime())) - $startTime;
$totalTime = round($totalTime, 4);
echo "Page generated in " . $totalTime . " seconds";
?>
</div>
</div>
<script>
// Hourly
var ctx = document.getElementById("hourly-bar-chart").getContext('2d');
var myChart = new Chart(ctx, {
type: 'bar',
data: {
labels: [<?php $i = 0;
foreach ($hourly_array as $row) {
$hourlyItemsrx = (int)$row['rxtotal'];
$hourlyItemstx = (int)$row['txtotal'];
$hourlyItemstotal = $hourlyItemsrx + $hourlyItemstx;
if(++$i === $hourlyItems) {
	echo '"'.$row['H'].': '.readableBytes_1000($hourlyItemstotal).' (Rx: '.readableBytes_1000($hourlyItemsrx).' / Tx: '.readableBytes_1000($hourlyItemstx).')"';
}
else {
	echo '"'.$row['H'].': '.readableBytes_1000($hourlyItemstotal).' (Rx: '.readableBytes_1000($hourlyItemsrx).' / Tx: '.readableBytes_1000($hourlyItemstx).')"' . ",";
}
}?>],
datasets: [{
label: 'Rx',
backgroundColor: "rgba(255, 111, 0, 0.70)",
data: [<?php $i = 0;
foreach ($hourly_array as $row) {
if(++$i === $hourlyItems) {
echo $row['rxtotal'];
}
else {
echo $row['rxtotal'] . ",";
}
}?>],
}, {
label: 'Tx',
backgroundColor: "rgba(255, 0, 0, 0.70)",
data: [<?php $i = 0;
foreach ($hourly_array as $row) {
if(++$i === $hourlyItems) {
echo $row['txtotal'];
}
else {
echo $row['txtotal'] . ",";
}
}?>],
}],
},
options: {
tooltips: {
displayColors: true,
callbacks: {
mode: 'x',
},
},
scales: {
x: {
stacked: true,
},
y: {
stacked: true,
ticks: {
callback: function(value, index, ticks) {
	return fileSizeSI(value);
	}
}
}
},
responsive: true
}
});
// Daily
var ctx = document.getElementById("daily-bar-chart").getContext('2d');
var myChart = new Chart(ctx, {
type: 'bar',
data: {
labels: [<?php $i = 0;
foreach ($daily_array as $row) {
$dailyItemsrx = (int)$row['rxtotal'];
$dailyItemstx = (int)$row['txtotal'];
$dailyItemstotal = $dailyItemsrx + $dailyItemstx;
if(++$i === $dailyItems) {
	echo '"'.$row['D'].': '.readableBytes_1000($dailyItemstotal).' (Rx: '.readableBytes_1000($dailyItemsrx).' / Tx: '.readableBytes_1000($dailyItemstx).')"';
}
else {
	echo '"'.$row['D'].': '.readableBytes_1000($dailyItemstotal).' (Rx: '.readableBytes_1000($dailyItemsrx).' / Tx: '.readableBytes_1000($dailyItemstx).')"' . ",";
}
}?>],
datasets: [{
label: 'Rx',
backgroundColor: "rgba(255, 111, 0, 0.70)",
data: [<?php $i = 0;
foreach ($daily_array as $row) {
if(++$i === $dailyItems) {
echo $row['rxtotal'];
}
else {
echo $row['rxtotal'] . ",";
}
}?>],
}, {
label: 'Tx',
backgroundColor: "rgba(255, 0, 0, 0.70)",
data: [<?php $i = 0;
foreach ($daily_array as $row) {
if(++$i === $dailyItems) {
echo $row['txtotal'];
}
else {
echo $row['txtotal'] . ",";
}
}?>],
}],
},
options: {
tooltips: {
displayColors: true,
callbacks: {
mode: 'x',
},
},
scales: {
x: {
stacked: true,
},
y: {
stacked: true,
ticks: {
callback: function(value, index, ticks) {
	return fileSizeSI(value);
	}
}
}
},
responsive: true
}
});
// Monthly
var ctx = document.getElementById("monthly-bar-chart").getContext('2d');
var myChart = new Chart(ctx, {
type: 'bar',
data: {
labels: [<?php $i = 0;
foreach ($monthly_array as $row) {
$monthlyItemsrx = (int)$row['rxtotal'];
$monthlyItemstx = (int)$row['txtotal'];
$monthlyItemstotal = $monthlyItemsrx + $monthlyItemstx;
if(++$i === $monthlyItems) {
	echo '"'.$months[$row['M']]." ".$row['Y'].': '.readableBytes_1000($monthlyItemstotal).' (Rx: '.readableBytes_1000($monthlyItemsrx).' / Tx: '.readableBytes_1000($monthlyItemstx).')"';
}
else {
	echo '"'.$months[$row['M']]." ".$row['Y'].': '.readableBytes_1000($monthlyItemstotal).' (Rx: '.readableBytes_1000($monthlyItemsrx).' / Tx: '.readableBytes_1000($monthlyItemstx).')"' . ",";
}
}?>],
datasets: [{
label: 'Rx',
backgroundColor: "rgba(255, 111, 0, 0.70)",
data: [<?php $i = 0;
foreach ($monthly_array as $row) {
if(++$i === $monthlyItems) {
echo $row['rxtotal'];
}
else {
echo $row['rxtotal'] . ",";
}
}?>],
}, {
label: 'Tx',
backgroundColor: "rgba(255, 0, 0, 0.70)",
data: [<?php $i = 0;
foreach ($monthly_array as $row) {
if(++$i === $monthlyItems) {
echo $row['txtotal'];
}
else {
echo $row['txtotal'] . ",";
}
}?>],
}],
},
options: {
tooltips: {
displayColors: true,
callbacks: {
mode: 'x',
},
},
scales: {
x: {
stacked: true,
},
y: {
stacked: true,
ticks: {
callback: function(value, index, ticks) {
	return fileSizeSI(value);
	}
}
}
},
responsive: true
}
});
// Yearly
var ctx = document.getElementById("yearly-bar-chart").getContext('2d');
var myChart = new Chart(ctx, {
type: 'bar',
data: {
labels: [<?php $i = 0;
foreach ($yearly_array as $row) {
$yearlyItemsrx = (int)$row['rxtotal'];
$yearlyItemstx = (int)$row['txtotal'];
$yearlyItemstotal = $yearlyItemsrx + $yearlyItemstx;
if(++$i === $yearlyItems) {
	echo '"'.$row['Y'].': '.readableBytes_1000($yearlyItemstotal).' (Rx: '.readableBytes_1000($yearlyItemsrx).' / Tx: '.readableBytes_1000($yearlyItemstx).')"';
}
else {
	echo '"'.$row['Y'].': '.readableBytes_1000($yearlyItemstotal).' (Rx: '.readableBytes_1000($yearlyItemsrx).' / Tx: '.readableBytes_1000($yearlyItemstx).')"' . ",";
}
}?>],
datasets: [{
label: 'Rx',
backgroundColor: "rgba(255, 111, 0, 0.70)",
data: [<?php $i = 0;
foreach ($yearly_array as $row) {
if(++$i === $yearlyItems) {
echo $row['rxtotal'];
}
else {
echo $row['rxtotal'] . ",";
}
}?>],
}, {
label: 'Tx',
backgroundColor: "rgba(255, 0, 0, 0.70)",
data: [<?php $i = 0;
foreach ($yearly_array as $row) {
if(++$i === $yearlyItems) {
echo $row['txtotal'];
}
else {
echo $row['txtotal'] . ",";
}
}?>],
}],
},
options: {
tooltips: {
displayColors: true,
callbacks: {
mode: 'x',
},
},
scales: {
x: {
stacked: true,
},
y: {
stacked: true,
ticks: {
callback: function(value, index, ticks) {
	return fileSizeSI(value);
	}
}
}
},
responsive: true
}
});
</script>
</body>
</html>