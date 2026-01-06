<!DOCTYPE html>
<html lang="en">
<head>
<title>Bandwidth Monitor</title>
</head>
<body>
<?php
if (isset($_GET['setup']))
{
$setup = $_GET['setup'];
if($setup == "true")
{
try
{
//open the database
	$db = new PDO('sqlite:/var/www/html/BandwidthMonitor/BandwidthMonitor.db');
	//create the tables
	$db->exec("CREATE TABLE Hourly (M TEXT, D TEXT, H TEXT, rx TEXT, tx TEXT, rxtotal TEXT, txtotal TEXT)");
	$db->exec("CREATE TABLE Daily (Y TEXT, M TEXT, D TEXT, rxtotal TEXT, txtotal TEXT)");
	$db->exec("CREATE TABLE Monthly (Y TEXT, M TEXT, rxtotal TEXT, txtotal TEXT)");
	$db->exec("CREATE TABLE Yearly (Y TEXT, rxtotal TEXT, txtotal TEXT)");
	echo "Database and Table created sucessfully.<br>";
	
// close the database connection
$db = NULL;
	
}
catch(PDOException $e)
{
	print 'Exception : '.$e->getMessage();
}
exit();
}
}
echo "<a href=\"setup.php?setup=true\">Start the setup</a><br><br>";
?>
</body>
</html>