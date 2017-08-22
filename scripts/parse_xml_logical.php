<?php
if (!isset($argv[1]) || (trim($argv[1]) == "")) {
	die ("Please supply a XML file to parse.\n");
}

$file = file_get_contents($argv[1]);
$debug = intval($argv[2]);

if ($file === false) die ("Invalid file or not found.\n");
$xml = simplexml_load_string($file);

$devices = array();
$links = array();

// first run: only get devices
foreach ($xml->root->object as $obj) {
	$id = (int) $obj->attributes()->id;
	if (isset($obj->attributes()->equipment)) {
		$device = array(
			"hostname" => (string) $obj->attributes()->hostname,
			"type" => (string) $obj->attributes()->type,
			"equipment" => (string) $obj->attributes()->equipment
		);
		$devices[$id] = $device;
	}

}

// second run: get links
foreach ($xml->root->object as $obj) {
	if (isset($obj->attributes()->medium)) {
		$from = (int) $obj->mxCell->attributes()->source;
		$to = (int) $obj->mxCell->attributes()->target;
		$link = array(
			"from" => $devices[$from]["hostname"],
			"to" => $devices[$to]["hostname"],
			"medium" => (string) $obj->attributes()->medium,
			"count" => (int) $obj->attributes()->count,
			"port_a" => (string) $obj->attributes()->port_a,
			"port_b" => (string) $obj->attributes()->port_b,
			"port_a_logical" => (string) $obj->attributes()->port_a_logical,
			"port_b_logical" => (string) $obj->attributes()->port_b_logical,
			"id" => (int) $obj->attributes()->id
		);
		
		$links[] = $link;
		
		if ($debug == 1 && ($devices[$from]["hostname"] == "" || $devices[$to]["hostname"] == "")) {
			echo "Empty link:\n";
			print_r($link);
			print_r($obj->mxCell);
		}
	}
}

$json = array("devices" => $devices, "links" => $links);
echo json_encode($json);
?>