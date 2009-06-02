<?php
$help['reload']='HAO YOU FIND ME?!';

function rf($function, $location) {
	$file = file_get_contents($location);
	echo $file;
	preg_match('/function '.$function.'\((.+)\) {'.failnet::NL.'([\s\w\dA-Za-z\(\)\[\]{}\'"'.failnet::NL.']+)/', $file, $m); //.'\((.+)\) \{'.failnet::NL.'(.+)'.failnet::NL.'\}/i', $file, $m);
	print_r($m);
	//$failnet->privmsg('/function '.$function.'\((.+)\) \{'.failnet::NL.'(.+)'.failnet::NL.'\}/i');
}
?>