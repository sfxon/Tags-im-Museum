<?php

$date = "10. Juni 1982";

$format = "j. F Y";
//setlocale(LC_ALL, 'de_DE');
$loc_de = setlocale(LC_TIME, 'de_DE');

var_dump($loc_de);

$parsedDate = date_parse_from_format($format, $date);

var_dump($parsedDate);