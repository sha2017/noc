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
$cols = array ("id","from","to","length","cores","medium");
$warnings = "";
$ids = array();
$copper = 0;
$smf2core = 0;
$smfmulticore = 0;
$copper_dk = 0;

foreach ($cols as $col) {
	echo $col . ";";
}
echo "length margin;\n";

foreach ($links as $link) {
	if ($ids[$link["id"]]) $warnings .= "Id: {$link["id"]} is already used, double: {$link["from"]} - {$link["to"]}\n";
	$ids[$link["id"]] = true;

	if ($link["medium"] == "cu") $copper += intval($link["length"]) * intval($link["cores"]);
	elseif ($link["medium"] == "smf" && $link["cores"] > 2) $smfmulticore++;
	elseif ($link["medium"] == "smf") $smf2core++;
	
	if ($link["medium"] == "cu" && ( substr($link["from"], 0, 3) == "DK-" && substr($link["to"], 0, 3) == "DK-") ) $copper_dk++;

	foreach ($cols as $col) {
		echo $link[$col] . ";";
	}
	
	echo (intval($link["length"] * 1.1)) + 5;
	echo ";\n";
}

print_r($objects);
echo "Copper: {$copper}m\n";
echo "Multicore SMF: {$smfmulticore}\n";
echo "2-core SMF: {$smf2core}\n";
echo "Copper uplinked DKs: {$copper_dk}\n";
echo $warnings;
?>