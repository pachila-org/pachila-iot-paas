<?php

function substr_startswith($haystack, $needle) {
	return substr($haystack, 0, strlen($needle)) === $needle;
}

