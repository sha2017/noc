<?php
echo '<html>
<head>
<title>Labels</title>
<style type="text/css">
body {
    font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
    font-size: 12px;
    line-height: 1.42857143;
    color: #333;
    background-color: #fff;
    margin: 0;
    margin-left: 5px;
    margin-right: 5px;
}
.label {
        page-break-after: always;
        padding-top: 5px;
}
.label TD {
	font-size: 12px;
}
</style>
</head>
<body>';

$assigned_switches = json_decode(file_get_contents("assigned-switches.json"));
$links = json_decode(file_get_contents("export-logical-diagram.json"));
$dlink = array();

foreach ($links->links as $link) {
	$tmp1 = array("local_port" => $link->port_a, "remote_port" => $link->port_b, "remote_device" => $link->to);
	$tmp2 = array("local_port" => $link->port_b, "remote_port" => $link->port_a, "remote_device" => $link->from);
	$dlink[$link->from][] = $tmp1;
	$dlink[$link->to][] = $tmp2;
}

foreach($links->devices as $dev) {
	if (preg_match("/(cumulus-|upstream-|dist-|mx240-|wlc-)/i",$dev->hostname)) continue;

       	echo "<div class='label'><table cellpadding='0' cellspacing='0'><tr><td style='width: 175px;' valign='top'>";
        echo "<span style='font-size: 25px;'><b>{$dev->hostname}</b></span><br />{$dev->equipment}";
	echo "<br />" . $assigned_switches->{$dev->hostname}->serial;
	echo "<br /><img src='https://wiki.sha2017.org/images/thumb/3/34/Sha_logo_large.png/840px-Sha_logo_large.png' alt='' width='100' />";
        echo "</td><td><td valign='top'><b>Links:</b><br />1: Button<br />2: ArtNet<br />";
	asort($dlink[$dev->hostname]);
	foreach ($dlink[$dev->hostname] as $port) {
		echo preg_replace("/(ge-|swp|Ethernet)/i","",$port['local_port']) . ": {$port['remote_device']} " . preg_replace("/(swp|Ethernet)/i","",$port['remote_port']) . "<br />";
	}
        echo "</td></tr></table></div>";
}
echo '</body></html>';

?>
