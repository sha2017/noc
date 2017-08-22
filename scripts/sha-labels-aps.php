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
    margin-left: 2px;
    margin-right: 2px;
}
.label {
        page-break-after: always;
        padding-top: 2px;
}
.label TD {
	font-size: 20px;
}
</style>
</head>
<body>';

$fibres = explode("\n",file_get_contents("locations.md"));
$start = false;

foreach ($fibres as $line) {
	$tmp = explode("|",$line);

	if ($start && count($tmp) > 0 && trim($tmp[4]) != "") {
	        echo "<div class='label'><table cellpadding='0' cellspacing='0'><tr><td style='width: 180px;' valign='top'>";
        	echo "<span style='font-size: 25px;'><b>"  . trim($tmp[1]) . "</b><br />";
        	echo "<br /><img src='https://wiki.sha2017.org/images/thumb/3/34/Sha_logo_large.png/840px-Sha_logo_large.png' alt='' width='100' />";
        	echo "</td><td><td valign='top'><b>" . trim($tmp[4]) ."</b>";
	        echo "</td></tr></table></div>";
	}

	if (trim($tmp[1]) == "-----------") $start = true;
}
echo '</body></html>';

?>
