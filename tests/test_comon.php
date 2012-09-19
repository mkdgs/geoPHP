<?php
$run = 1;
if ( !$run ) die(' set run '); 
ini_set('error_reporting', -1);
ini_set('display_errors', 1);
ini_set('html_errors',1);
set_time_limit(0);
//header("Content-type: text");

include_once('../geoPHP.inc');
if (geoPHP::geosInstalled()) {
	print "GEOS is installed.\n";
}
else {
	print "GEOS is not installed.\n";
}