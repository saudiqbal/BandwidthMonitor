<?php
// Login Password
$password='';

header("Cache-Control: no-store, no-cache, must-revalidate");
$fresh='1';
if($fresh=='1')
{
	// Password Generator
	$chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	$count = strlen($chars);
	$bytes = random_bytes(32);
	$result = '';
	foreach (str_split($bytes) as $byte) {
	$result .= $chars[ord($byte) % $count];
	}
	// Change variables
	$filePath = 'setup.php';
	$content = file_get_contents($filePath);
	// Map variable names to their exact new string values
	$replacements = [
	'password' => $result,
	'fresh' => '0'
	];
	// Build regex patterns to find '$variable_name = ...' and replace values
	foreach ($replacements as $varName => $newValue) {
		// Regex looks for $varName followed by optional spaces, an =, optional spaces, and the quoted value
		$pattern = '/(\$' . preg_quote($varName, '/') . '\s*=\s*[\'"])(.*?)([\'"];)/';
		$content = preg_replace($pattern, '${1}' . $newValue . '${3}', $content);
	}
// Update the configuration file safely
file_put_contents($filePath, $content);
}
$error = '1';
$login = '0';
if(isset($_GET['password']) && $password == $_GET['password'] && !empty($_GET['password']))
{
$login = '1';
}
if($login == '1')
{
$message = '<div style="text-align: center;">Create New SQLite Database</div><div style="text-align: center;margin: 50px;"><a href="setup.php?password='.$password.'&setup=true" style="color:#000;background-color: #f44336;padding: 10px 24px;border-radius: 4px;cursor: default;text-decoration: none;">Continue</a> <a href="./" style="color:#000;background-color: #4CAF50;padding: 10px 24px;border-radius: 4px;cursor: default;text-decoration: none;">Cancel</a></div>';
if (isset($_GET['setup']))
{
	$setup = $_GET['setup'];
	if($setup == "true")
	{
		require_once __DIR__ . '/config.php';
		if (file_exists($db_filename))
		{
			$message = "<div style=\"text-align: center;margin: 25px;\"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"20\"><path style=\"fill:#ff5500\" d=\"M19.64 16.36 11.53 2.3A1.85 1.85 0 0 0 10 1.21 1.85 1.85 0 0 0 8.48 2.3L.36 16.36C-.48 17.81.21 19 1.88 19h16.24c1.67 0 2.36-1.19 1.52-2.64zM11 16H9v-2h2zm0-4H9V6h2z\"/></svg> SQLite Database file already exists.</div><div style=\"text-align: center;margin: 25px;\">You can delete the existing SQLite Database file and recreate the database, you will lose all your existing data forever if you choose to continue.</div><div style=\"text-align: center;margin: 50px;\"><a href=\"setup.php?password=$password&setup=true&delete=true\" style=\"color:#000;background-color: #f44336;padding: 10px 24px;border-radius: 4px;cursor: default;text-decoration: none;\">Delete and continue</a> <a href=\"./\" style=\"color:#000;background-color: #4CAF50;padding: 10px 24px;border-radius: 4px;cursor: default;text-decoration: none;\">Cancel</a></div>";
			$error = '1';
			if (isset($_GET['delete']))
			{
				$delete = $_GET['delete'];
				if($delete == "true")
				{
					unlink($db_filename);
					$error = '0';
				}
			}
		} else {
		$error = '0';
		}
		if ($error == '0')
		{
			try
			{
				$db = new PDO("sqlite:$db_filename");
				$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$db->exec("CREATE TABLE Hourly (M TEXT, D TEXT, H TEXT, rx TEXT, tx TEXT, rxtotal TEXT, txtotal TEXT)");
				$db->exec("CREATE TABLE Daily (Y TEXT, M TEXT, D TEXT, rxtotal TEXT, txtotal TEXT)");
				$db->exec("CREATE TABLE Monthly (Y TEXT, M TEXT, rxtotal TEXT, txtotal TEXT)");
				$db->exec("CREATE TABLE Yearly (Y TEXT, rxtotal TEXT, txtotal TEXT)");
				$message = "<div style=\"text-align: center;\">Database Created</div><div style=\"text-align: center;margin: 25px;\"><a href=\"./\" style=\"color:#000;background-color: #4CAF50;padding: 10px 24px;border-radius: 4px;cursor: default;text-decoration: none;\">Continue</a></div>\n";
				$db = NULL;
			}
			catch(PDOException $e)
			{
				$message = 'Exception : '.$e->getMessage();
			}
		}
	}
}
}
else
{
	$message = '<div style="display: flex;justify-content: center;align-items: center;"><form method="GET" id="login">
<div class="login-group"><div class="login-group-prepend"><span class="login-group-text"><input id="password" name="password" type="password" style="border: none; outline: none;" placeholder=\'Password\' required></span></div><input type="submit" name="submit" id="submit" value="Submit" class="login-control"></div></div>
</form></div>';
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Database Setup</title>
<style type="text/css">
.outer {
display: table;
position: absolute;
height: 100%;
width: 100%;
}
.middle {
display: table-cell;
vertical-align: middle;
}
.inner {
margin-left: auto;
margin-right: auto;
width: /*whatever width you want*/;
}
body {
background: #ffffff;
width:100%
top:0;
left:0;
margin:0;
padding:0;
text-align: center;
}
a {
color: #000;
text-decoration: none;
}
#mainContent {
border: 1px solid #ff870d;
margin: 0px 0px;
padding:0px 0px 0px 0px;
width: 600px;
-webkit-border-radius: 5px 5px 5px 5px;
-moz-border-radius: 5px 5px 5px 5px;
border-radius: 5px 5px 5px 5px;
display: inline-block;
}
#aligntop {
background-color:#ff870d;
color:#FFFFFF;
font-size: 14pt;
text-align: center;
padding: 5px 20px;
-moz-box-shadow: 0 0 25px #FF9633;
-webkit-box-shadow: 0 0 25px #FF9633;
box-shadow: 0 0 25px #FF9633;
-webkit-border-radius: 5px 5px 0px 0px;
-moz-border-radius: 5px 5px 0px 0px;
border-radius: 5px 5px 0px 0px;
}
#alignbottom {
background-color:#ffe2c3;
color:#000000;
font-size: 14pt;
padding: 15px 20px;
text-align: left;
-moz-box-shadow: 0 0 25px #FF9633;
-webkit-box-shadow: 0 0 25px #FF9633;
box-shadow: 0 0 25px #FF9633;
-webkit-border-radius: 0px 0px 5px 5px;
-moz-border-radius: 0px 0px 5px 5px;
border-radius: 0px 0px 5px 5px;
}
.login-group {
position: relative;
display: flex;
flex-wrap: wrap;
align-items: stretch;
font-family: monospace;
}
.login-group-prepend {
margin-right: -1px;
}
.login-group>.login-control:not(:first-child), .login-group>.custom-select:not(:first-child) {
border-top-left-radius: 0;
border-bottom-left-radius: 0;
}
.login-group>.login-control, .login-group>.login-control-plaintext, .login-group>.custom-select, .login-group>.custom-file {
position: relative;
width: fit-content;
min-width: 0;
margin-bottom: 0;
}
.login-group>*, *::before, *::after {
box-sizing: border-box;
}
.login-group>.login-group-prepend>.btn, .login-group>.login-group-prepend>.login-group-text, .login-group:not(.has-validation)>.login-group-append:not(:last-child)>.btn, .login-group:not(.has-validation)>.login-group-append:not(:last-child)>.login-group-text, .login-group.has-validation>.login-group-append:nth-last-child(n+3)>.btn, .login-group.has-validation>.login-group-append:nth-last-child(n+3)>.login-group-text, .login-group>.login-group-append:last-child>.btn:not(:last-child):not(.dropdown-toggle), .login-group>.login-group-append:last-child>.login-group-text:not(:last-child) {
border-top-right-radius: 0;
border-bottom-right-radius: 0;
}
.login-group-text {
display: flex;
align-items: center;
padding: 0px;
margin-bottom: 0;
font-size: 1rem;
font-weight: 400;
line-height: 1;
text-align: center;
white-space: nowrap;
color: #495057;
background-color: #f8f9fa;
border: 1px solid #ced4da;
border-radius: .25rem;
}
.login-control:hover {
background-color: #79beff;
color: #1e1e1e;
}
.login-control {
display: block;
width: 50px;
height: calc(1em + 0.75rem + 1px);
padding: .375rem .75rem;
font-size: 1rem;
font-weight: 400;
line-height: 1;
color: #495057;
background-color: #e9ecef;
background-clip: padding-box;
border: 1px solid #ced4da;
border-radius: .25rem;
transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}
.login-group-prepend, .login-group-append {
display: flex;
}
</style>
</head>
<body>
<div class="outer">
<div class="middle">
<div class="inner">
<div id="mainContent">
<div id="aligntop">New Database Setup</div>
<div id="alignbottom"><?php echo $message;?></div>
</div>
</div>
</div>
</div>
</body>
</html>