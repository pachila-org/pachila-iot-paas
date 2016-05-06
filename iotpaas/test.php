<?php

$teststr = "{\"test\":123}";
$testobj = json_decode($teststr);
if ($testobj) {
	echo  "is object now";
} else {
	echo "decode error";
}
?>