<?php
if (!isset($argv[1]) || (trim($argv[1]) == "")) {
	die ("Please supply a JSON file to parse.\n");
}

$file = file_get_contents($argv[1]);

if ($file === false) die ("Invalid file or not found.\n");
$json = json_decode($file);

foreach ($json->devices as $device) {
	echo "| {$device->hostname} | {$device->equipment} | \n";
}
?>