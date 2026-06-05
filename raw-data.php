<?php
$startTime = array_sum(explode(' ', microtime()));
header("Cache-Control: no-store, must-revalidate");
include 'config.php';
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
$monthly_array = $db->query("SELECT Y, M, rxtotal, txtotal FROM Monthly ORDER BY rowid DESC")->fetchAll(PDO::FETCH_ASSOC);
$monthlyItems = count($monthly_array);
$yearly_array = $db->query("SELECT Y, rxtotal, txtotal FROM Yearly ORDER BY rowid DESC")->fetchAll(PDO::FETCH_ASSOC);
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
<div class="wrapper">
<div class="table">
<div class="row tableheader">
<div class="cell">Time Stamp</div><div class="cell">Total</div><div class="cell">Rx</div><div class="cell">Tx</div></div>
<?php foreach ($hourly_array as $row) {
	echo '<div class="row">';
	echo '<div class="cell" data-title="Time Stamp">';
	echo $row['H'];
	echo '</div>';
	echo '<div class="cell" data-title="Total">';
	echo (int)$row['rxtotal']+(int)$row['txtotal'].' <span class="siunits">'.(int)$row['rxtotal']+(int)$row['txtotal'].'</span>';
	echo '</div>';
	echo '<div class="cell" data-title="Rx">';
	echo $row['rxtotal'].' <span class="siunits">'.$row['rxtotal'].'</span>';
	echo '</div>';
	echo '<div class="cell" data-title="Tx">';
	echo $row['txtotal'].' <span class="siunits">'.$row['txtotal'].'</span>';
	echo '</div>';
	echo '</div>'."\n";
}
?>
</div>
</div>
</div>
<hr>
<div class="actions">
<div class="cardfooter"><?php echo "$CurrentY-$CurrentM-$CurrentD"; ?></div>
<div class="cardfooter float-right"><a href='./' class="ClassicButton" title="Edit" style="text-decoration:none;">Graphical Data</a></div>
</div>
</div>

<div class="card">
<div class="primary-title">
<div class="primary-text">Daily Data For <?php echo date('F'); ?></div>
</div>
<div class="supporting-text">
<div class="wrapper">
<div class="table">
<div class="row tableheader">
<div class="cell">Time Stamp</div><div class="cell">Total</div><div class="cell">Rx</div><div class="cell">Tx</div></div>
<?php foreach ($daily_array as $row) {
	echo '<div class="row">';
	echo '<div class="cell" data-title="Time Stamp">';
	echo $row['D'];
	echo '</div>';
	echo '<div class="cell" data-title="Total">';
	echo (int)$row['rxtotal']+(int)$row['txtotal'].' <span class="siunits">'.(int)$row['rxtotal']+(int)$row['txtotal'].'</span>';
	echo '</div>';
	echo '<div class="cell" data-title="Rx">';
	echo $row['rxtotal'].' <span class="siunits">'.$row['rxtotal'].'</span>';
	echo '</div>';
	echo '<div class="cell" data-title="Tx">';
	echo $row['txtotal'].' <span class="siunits">'.$row['txtotal'].'</span>';
	echo '</div>';
	echo '</div>'."\n";
}
?>
</div>
</div>
</div>
<hr>
<div class="actions">
<div class="cardfooter"></div>
<div class="cardfooter float-right"><a href='./' class="ClassicButton" title="Edit" style="text-decoration:none;">Graphical Data</a></div>
</div>
</div>

<div class="card">
<div class="primary-title">
<div class="primary-text">Monthly Data</div>
</div>
<div class="supporting-text">
<div class="wrapper">
<div class="table">
<div class="row tableheader">
<div class="cell">Time Stamp</div><div class="cell">Total</div><div class="cell">Rx</div><div class="cell">Tx</div></div>
<?php foreach ($monthly_array as $row) {
	echo '<div class="row">';
	echo '<div class="cell" data-title="Time Stamp">';
	echo $months[$row['M']]." ".$row['Y'];
	echo '</div>';
	echo '<div class="cell" data-title="Total">';
	echo (int)$row['rxtotal']+(int)$row['txtotal'].' <span class="siunits">'.(int)$row['rxtotal']+(int)$row['txtotal'].'</span>';
	echo '</div>';
	echo '<div class="cell" data-title="Rx">';
	echo $row['rxtotal'].' <span class="siunits">'.$row['rxtotal'].'</span>';
	echo '</div>';
	echo '<div class="cell" data-title="Tx">';
	echo $row['txtotal'].' <span class="siunits">'.$row['txtotal'].'</span>';
	echo '</div>';
	echo '</div>'."\n";
}
?>
</div>
</div>
</div>
<hr>
<div class="actions">
<div class="cardfooter">Last 12 months</div>
<div class="cardfooter float-right"><a href='./' class="ClassicButton" title="Edit" style="text-decoration:none;">Graphical Data</a></div>
</div>
</div>

<div class="card">
<div class="primary-title">
<div class="primary-text">Yearly Data</div>
</div>
<div class="supporting-text">
<div class="wrapper">
<div class="table">
<div class="row tableheader">
<div class="cell">Time Stamp</div><div class="cell">Total</div><div class="cell">Rx</div><div class="cell">Tx</div></div>
<?php foreach ($yearly_array as $row) {
	echo '<div class="row">';
	echo '<div class="cell" data-title="Time Stamp">';
	echo $row['Y'];
	echo '</div>';
	echo '<div class="cell" data-title="Total">';
	echo (int)$row['rxtotal']+(int)$row['txtotal'].' <span class="siunits">'.(int)$row['rxtotal']+(int)$row['txtotal'].'</span>';
	echo '</div>';
	echo '<div class="cell" data-title="Rx">';
	echo $row['rxtotal'].' <span class="siunits">'.$row['rxtotal'].'</span>';
	echo '</div>';
	echo '<div class="cell" data-title="Tx">';
	echo $row['txtotal'].' <span class="siunits">'.$row['txtotal'].'</span>';
	echo '</div>';
	echo '</div>'."\n";
}
?>
</div>
</div>
</div>
<hr>
<div class="actions">
<div class="cardfooter"></div>
<div class="cardfooter float-right"><a href='./' class="ClassicButton" title="Edit" style="text-decoration:none;">Graphical Data</a></div>
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
function fileSizeSI(bytes) {
	const exponent = Math.floor(Math.log(bytes) / Math.log(1000.0));
	const decimal = (bytes / Math.pow(1000.0, exponent)).toFixed(exponent ? 2 : 0);
		if (bytes <= 0) {
		return "0 Bytes";
	}
	return `${decimal} ${exponent ? `${'kMGTPEZY'[exponent - 1]}B` : 'B'}`;
}
var number_of_elements = document.getElementsByClassName('siunits').length;
var i=0;
while (i<number_of_elements) {
var sivalue = document.getElementsByClassName("siunits")[i].innerText;
sivalue = fileSizeSI(sivalue);
document.getElementsByClassName('siunits')[i].textContent = "("+sivalue+")";
i++;
}
</script>
</body>
</html>