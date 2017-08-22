<?php
if (!isset($argv[1]) || (trim($argv[1]) == "")) {
	die ("Please supply a JSON file to parse.\n");
}

$file = file_get_contents($argv[1]);

if ($file === false) die ("Invalid file or not found.\n");
$json = json_decode($file);
$dev = array();

foreach ($json->devices as $device) {
	$dev[$device->equipment]++;
	$dev[$device->type]++;
}

print_r($dev);

$links = array();

foreach($json->links as $link) {
	if ($link->from == "" || $link->to == "") {
		echo "Warning: empty link!\n";
		print_r($link);
	}
	$links[$link->medium] += $link->count;
}

print_r($links);
?>