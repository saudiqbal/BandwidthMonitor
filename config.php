<?php
date_default_timezone_set('America/New_York');
$ProbeInterval = 1;
$url = 'http://192.168.1.1/cgi-bin/networkinfo.sh';
$db = new PDO('sqlite:/var/www/html/BandwidthMonitor/BandwidthMonitor.db');
?>