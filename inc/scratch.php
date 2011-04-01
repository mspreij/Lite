<?php

$data = 'A	asdfsa	sadfs	dfsadfsa
B	abbfsa	sadfs	xsdfxxxx
C	asdccc	sadfs	yyyyyyy';

foreach(explode("\n", $data) as $row) $arr[] = explode("\t", $row);


print_r(key_unnest($arr));


#________________________________________
# key_unnest($arr, $first_only = false) /
function key_unnest($arr, $first_only = false) {
  foreach($arr as $row) {
    $key = array_shift($row);
    $out[$key] = $first_only ? array_shift($row) : $row;
  }
  return $out;
}


?>