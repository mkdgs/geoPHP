<?php
require_once 'test_comon.php';
$ct = file_get_contents('./input/route.gpx');
$poly = new Polygon();
$b = geoPHP::load($ct)->asBinary();
//->asBinary();
echo $b = geoPHP::load($b)->asText();