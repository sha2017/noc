<?php
if (!isset($argv[1]) || (trim($argv[1]) == "")) {
	die ("Please supply a DXF file to parse.\n");
}

$file = file_get_contents($argv[1]);

if ($file === false) die ("Invalid file or not found.\n");

$tmp = explode("\n", $file);
$open = false;
$links = array();
$link = array();
$objects = array();

foreach ($tmp as $line) {
	if ($open) {
		if (preg_match("/(noc\.object\:)(.*)/i",$line,$out)) {
			$objects[trim($out[2])]++;
		}
		elseif (preg_match("/(noc\.)(.*)(\:)(.*)/i",$line,$out)) {
			$link[trim($out[2])] = trim($out[4]);
		}
	}
	elseif (trim($line) == "1001") $open = true;
	
	if ((trim($line) == "0") && $open && count($link) > 0) {
		$open = false;
		$links[] = $link;
		$link = array();
	}
}
for ($i = 0; $i < count($links); $i++) {
	$links[$i]["length_margin"] = (intval($links[$i]["length"]) * 1.1) + 5;
}

echo json_encode($links);
?>