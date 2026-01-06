<?php
// Final Daily Calculation
$bandwidth_calculation = $db->query("SELECT rxtotal, txtotal FROM Daily WHERE Y = '$PreviousY' AND M = '$PreviousM' AND D = '$PreviousD'")->fetch(PDO::FETCH_ASSOC);
if (empty($bandwidth_calculation)) {
	$Calculated_rx = $db->prepare("SELECT SUM(rxtotal) txtotal FROM Hourly WHERE M = '$PreviousM' AND D = '$PreviousD'");
	$Calculated_rx->execute();
	$Calculated_rx = $Calculated_rx->fetchColumn();
	$Calculated_tx = $db->prepare("SELECT SUM(txtotal) txtotal FROM Hourly WHERE M = '$PreviousM' AND D = '$PreviousD'");
	$Calculated_tx->execute();
	$Calculated_tx = $Calculated_tx->fetchColumn();
	$stmt = $db->exec("INSERT INTO Daily (Y, M, D, rxtotal, txtotal) VALUES ('$PreviousY', '$PreviousM', '$PreviousD', '$Calculated_rx', '$Calculated_tx') ");
}
else
{
	$Calculated_rx = $db->prepare("SELECT SUM(rxtotal) txtotal FROM Hourly WHERE M = '$PreviousM' AND D = '$PreviousD'");
	$Calculated_rx->execute();
	$Calculated_rx = $Calculated_rx->fetchColumn();
	$Calculated_tx = $db->prepare("SELECT SUM(txtotal) txtotal FROM Hourly WHERE M = '$PreviousM' AND D = '$PreviousD'");
	$Calculated_tx->execute();
	$Calculated_tx = $Calculated_tx->fetchColumn();
	$stmt = $db->exec("Update Daily SET rxtotal = '$Calculated_rx', txtotal = '$Calculated_tx' WHERE Y = '$PreviousY' AND M = '$PreviousM' AND D = '$PreviousD'");
}
// Monthly Calculation
$bandwidth_calculation = $db->query("SELECT rxtotal, txtotal FROM Monthly WHERE Y = '$PreviousY' AND M = '$PreviousM'")->fetch(PDO::FETCH_ASSOC);
if (empty($bandwidth_calculation)) {
	$Calculated_rx = $db->prepare("SELECT SUM(rxtotal) txtotal FROM Daily WHERE M = '$PreviousM'");
	$Calculated_rx->execute();
	$Calculated_rx = $Calculated_rx->fetchColumn();
	$Calculated_tx = $db->prepare("SELECT SUM(txtotal) txtotal FROM Daily WHERE M = '$PreviousM'");
	$Calculated_tx->execute();
	$Calculated_tx = $Calculated_tx->fetchColumn();
	$stmt = $db->exec("INSERT INTO Monthly (Y, M, rxtotal, txtotal) VALUES ('$PreviousY', '$PreviousM', '$Calculated_rx', '$Calculated_tx') ");
}
else
{
	$Calculated_rx = $db->prepare("SELECT SUM(rxtotal) txtotal FROM Daily WHERE M = '$PreviousM'");
	$Calculated_rx->execute();
	$Calculated_rx = $Calculated_rx->fetchColumn();
	$Calculated_tx = $db->prepare("SELECT SUM(txtotal) txtotal FROM Daily WHERE M = '$PreviousM'");
	$Calculated_tx->execute();
	$Calculated_tx = $Calculated_tx->fetchColumn();
	$stmt = $db->exec("Update Monthly SET rxtotal = '$Calculated_rx', txtotal = '$Calculated_tx' WHERE Y = '$PreviousY' AND M = '$PreviousM'");
}
// Yearly Calculation
$bandwidth_calculation = $db->query("SELECT rxtotal, txtotal FROM Yearly WHERE Y = '$PreviousY'")->fetch(PDO::FETCH_ASSOC);
if (empty($bandwidth_calculation)) {
	$Calculated_rx = $db->prepare("SELECT SUM(rxtotal) txtotal FROM Monthly WHERE Y = '$PreviousY'");
	$Calculated_rx->execute();
	$Calculated_rx = $Calculated_rx->fetchColumn();
	$Calculated_tx = $db->prepare("SELECT SUM(txtotal) txtotal FROM Monthly WHERE Y = '$PreviousY'");
	$Calculated_tx->execute();
	$Calculated_tx = $Calculated_tx->fetchColumn();
	$stmt = $db->exec("INSERT INTO Yearly (Y, rxtotal, txtotal) VALUES ('$PreviousY', '$Calculated_rx', '$Calculated_tx') ");
}
else
{
	$Calculated_rx = $db->prepare("SELECT SUM(rxtotal) txtotal FROM Monthly WHERE Y = '$PreviousY'");
	$Calculated_rx->execute();
	$Calculated_rx = $Calculated_rx->fetchColumn();
	$Calculated_tx = $db->prepare("SELECT SUM(txtotal) txtotal FROM Monthly WHERE Y = '$PreviousY'");
	$Calculated_tx->execute();
	$Calculated_tx = $Calculated_tx->fetchColumn();
	$stmt = $db->exec("Update Yearly SET rxtotal = '$Calculated_rx', txtotal = '$Calculated_tx' WHERE Y = '$PreviousY'");
}
// Clean up old records
// Delete previous days
$db->exec("DELETE FROM Hourly WHERE M != '$CurrentM' OR D != '$CurrentD'");
// Delete previous months
$db->exec("DELETE FROM Daily WHERE Y != '$CurrentY' OR M != '$CurrentM'");
// Last 12 months
$db->exec("DELETE FROM Monthly ORDER BY rowid DESC LIMIT -1 OFFSET 12");
// Compress the database
$db->exec('VACUUM');
?>