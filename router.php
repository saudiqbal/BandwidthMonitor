<?php
include 'config.php';
$db = new PDO("sqlite:$db_filename");
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$json_data = file_get_contents($url, 0, stream_context_create(["http"=>["timeout"=>15]]));
$data_array = json_decode($json_data, true);
if ($data_array === null && json_last_error() !== JSON_ERROR_NONE) {
	echo "Error decoding JSON: " . json_last_error_msg();
	exit;
} else {
	$rx = $data_array['rx_bytes'];
	$tx = $data_array['tx_bytes'];
}
if(preg_match('/[^0-9]/', $rx))
{
	echo 'Error: Invalid RX data';
	exit;
}
if(preg_match('/[^0-9]/', $tx))
{
	echo 'Error: Invalid TX data';
	exit;
}

function bytesToMebibytes($bytes) {
	$megabytes = $bytes / 1000000;
	$precision = 2;
	if ( 0 == (int)$megabytes ) {
	return $megabytes;
	}
	$negative = $megabytes / abs($megabytes);
	$megabytes = abs($megabytes);
	$precision = pow(10, $precision);
	return floor( $megabytes * $precision ) / $precision * $negative;
}

[$CurrentY, $CurrentM, $CurrentD, $CurrentH] = explode('-', date('Y-m-d-H'));
$previous_hour = date('Y-m-d-H', time() - 3600 * $ProbeInterval);
[$PreviousY, $PreviousM, $PreviousD, $PreviousH] = explode('-', $previous_hour);
$stmt = $db->query("SELECT rx, tx FROM Hourly WHERE M = '$PreviousM' AND D = '$PreviousD' AND H = '$PreviousH'");
$bandwidth_calculation = $stmt->fetch(PDO::FETCH_ASSOC);
// Check if the result is empty
if (empty($bandwidth_calculation)) {
	$rxtotal = '0';
	$txtotal = '0';
}
else
{
	echo '<br>rxtotal: ';
	echo $bandwidth_calculation["rx"];
	echo '<br>txtotal: ';
	echo $bandwidth_calculation["tx"];
// RX
if($rx < (int)$bandwidth_calculation["rx"])
{
	$rxtotal = $rx;
}
else
{
	$rxtotal = $rx - (int)$bandwidth_calculation["rx"];
}
// TX
if($tx < (int)$bandwidth_calculation["tx"])
{
	$txtotal = $tx;
}
else
{
	$txtotal = $tx - (int)$bandwidth_calculation["tx"];
}
}
// Final hour total
if("'$CurrentM'-'$CurrentD'-'$CurrentH'" == "'$CurrentM'-'$CurrentD'-'00'")
{
	$stmt = $db->exec("INSERT INTO Hourly (M, D, H, rx, tx, rxtotal, txtotal) VALUES ('$PreviousM', '$PreviousD', '24', '$rx', '$tx', '$rxtotal', '$txtotal') ");
	$rxtotal = 0;
	$txtotal = 0;
	include 'daily-maintenence.php';
}

$stmt = $db->prepare("SELECT COUNT(*) FROM Hourly WHERE M = '$CurrentM' AND D = '$CurrentD' AND H = '$CurrentH'");
$stmt->execute();
$count = $stmt->fetchColumn();
if ($count == 0) {
	$stmt = $db->exec("INSERT INTO Hourly (M, D, H, rx, tx, rxtotal, txtotal) VALUES ('$CurrentM', '$CurrentD', '$CurrentH', '$rx', '$tx', '$rxtotal', '$txtotal') ");
}
else
{
$stmt = $db->exec("UPDATE Hourly SET rx = '$rx', tx = '$tx', rxtotal = '$rxtotal', txtotal = '$txtotal' WHERE M = '$CurrentM' AND D = '$CurrentD' AND H = '$CurrentH'");
}
// Daily Calculation
$bandwidth_calculation = $db->query("SELECT rxtotal, txtotal FROM Daily WHERE Y = '$CurrentY' AND M = '$CurrentM' AND D = '$CurrentD'")->fetch(PDO::FETCH_ASSOC);
if (empty($bandwidth_calculation)) {
	$Calculated_rx = $db->prepare("SELECT SUM(rxtotal) txtotal FROM Hourly WHERE M = '$CurrentM' AND D = '$CurrentD'");
	$Calculated_rx->execute();
	$Calculated_rx = $Calculated_rx->fetchColumn();
	//$Calculated_rx = bytesToMebibytes($Calculated_rx);
	echo '<br>Daily rxtotal: ['.$Calculated_rx.']';
	$Calculated_tx = $db->prepare("SELECT SUM(txtotal) txtotal FROM Hourly WHERE M = '$CurrentM' AND D = '$CurrentD'");
	$Calculated_tx->execute();
	$Calculated_tx = $Calculated_tx->fetchColumn();
	//$Calculated_tx = bytesToMebibytes($Calculated_tx);
	echo '<br>Daily txtotal: ['.$Calculated_tx.']';
	$stmt = $db->exec("INSERT INTO Daily (Y, M, D, rxtotal, txtotal) VALUES ('$CurrentY', '$CurrentM', '$CurrentD', '$Calculated_rx', '$Calculated_tx') ");
}
else
{
	$Calculated_rx = $db->prepare("SELECT SUM(rxtotal) txtotal FROM Hourly WHERE M = '$CurrentM' AND D = '$CurrentD'");
	$Calculated_rx->execute();
	$Calculated_rx = $Calculated_rx->fetchColumn();
	//$Calculated_rx = bytesToMebibytes($Calculated_rx);
	echo '<br>Daily rxtotal: ['.$Calculated_rx.']';
	$Calculated_tx = $db->prepare("SELECT SUM(txtotal) txtotal FROM Hourly WHERE M = '$CurrentM' AND D = '$CurrentD'");
	$Calculated_tx->execute();
	$Calculated_tx = $Calculated_tx->fetchColumn();
	//$Calculated_tx = bytesToMebibytes($Calculated_tx);
	echo '<br>Daily txtotal: ['.$Calculated_tx.']';
	$stmt = $db->exec("Update Daily SET rxtotal = '$Calculated_rx', txtotal = '$Calculated_tx' WHERE Y = '$CurrentY' AND M = '$CurrentM' AND D = '$CurrentD'");
}
// Monthly Calculation
$bandwidth_calculation = $db->query("SELECT rxtotal, txtotal FROM Monthly WHERE Y = '$CurrentY' AND M = '$CurrentM'")->fetch(PDO::FETCH_ASSOC);
if (empty($bandwidth_calculation)) {
	$Calculated_rx = $db->prepare("SELECT SUM(rxtotal) txtotal FROM Daily WHERE M = '$CurrentM'");
	$Calculated_rx->execute();
	$Calculated_rx = $Calculated_rx->fetchColumn();
	echo '<br>Monthly rxtotal: ['.$Calculated_rx.']';
	$Calculated_tx = $db->prepare("SELECT SUM(txtotal) txtotal FROM Daily WHERE M = '$CurrentM'");
	$Calculated_tx->execute();
	$Calculated_tx = $Calculated_tx->fetchColumn();
	echo '<br>Monthly txtotal: ['.$Calculated_tx.']';
	$stmt = $db->exec("INSERT INTO Monthly (Y, M, rxtotal, txtotal) VALUES ('$CurrentY', '$CurrentM', '$Calculated_rx', '$Calculated_tx') ");
}
else
{
	$Calculated_rx = $db->prepare("SELECT SUM(rxtotal) txtotal FROM Daily WHERE M = '$CurrentM'");
	$Calculated_rx->execute();
	$Calculated_rx = $Calculated_rx->fetchColumn();
	echo '<br>Monthly rxtotal: ['.$Calculated_rx.']';
	$Calculated_tx = $db->prepare("SELECT SUM(txtotal) txtotal FROM Daily WHERE M = '$CurrentM'");
	$Calculated_tx->execute();
	$Calculated_tx = $Calculated_tx->fetchColumn();
	echo '<br>Monthly txtotal: ['.$Calculated_tx.']';
	$stmt = $db->exec("Update Monthly SET rxtotal = '$Calculated_rx', txtotal = '$Calculated_tx' WHERE Y = '$CurrentY' AND M = '$CurrentM'");
}
// Yearly Calculation
$bandwidth_calculation = $db->query("SELECT rxtotal, txtotal FROM Yearly WHERE Y = '$CurrentY'")->fetch(PDO::FETCH_ASSOC);
if (empty($bandwidth_calculation)) {
	$Calculated_rx = $db->prepare("SELECT SUM(rxtotal) txtotal FROM Monthly WHERE Y = '$CurrentY'");
	$Calculated_rx->execute();
	$Calculated_rx = $Calculated_rx->fetchColumn();
	echo '<br>Yearly rxtotal: ['.$Calculated_rx.']';
	$Calculated_tx = $db->prepare("SELECT SUM(txtotal) txtotal FROM Monthly WHERE Y = '$CurrentY'");
	$Calculated_tx->execute();
	$Calculated_tx = $Calculated_tx->fetchColumn();
	echo '<br>Yearly txtotal: ['.$Calculated_tx.']';
	$stmt = $db->exec("INSERT INTO Yearly (Y, rxtotal, txtotal) VALUES ('$CurrentY', '$Calculated_rx', '$Calculated_tx') ");
}
else
{
	$Calculated_rx = $db->prepare("SELECT SUM(rxtotal) txtotal FROM Monthly WHERE Y = '$CurrentY'");
	$Calculated_rx->execute();
	$Calculated_rx = $Calculated_rx->fetchColumn();
	echo '<br>Yearly rxtotal: ['.$Calculated_rx.']';
	$Calculated_tx = $db->prepare("SELECT SUM(txtotal) txtotal FROM Monthly WHERE Y = '$CurrentY'");
	$Calculated_tx->execute();
	$Calculated_tx = $Calculated_tx->fetchColumn();
	echo '<br>Yearly txtotal: ['.$Calculated_tx.']';
	$stmt = $db->exec("Update Yearly SET rxtotal = '$Calculated_rx', txtotal = '$Calculated_tx' WHERE Y = '$CurrentY'");
}
?>