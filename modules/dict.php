<?php
$modules[] = 'dict';
$loaded['dict']=true;

$help['dict'] = 'dict - A module that uses the dictionary.youreofftask.com API.
No definitions are stored locally, but on dictionary.youreofftask.com, which explains the delays.
All functions but |define require an auth level of at least 1.
'.$x02.'|define <word>'.$x02.' - defines word
'.$x02.'|+def [<word>] [<definition>]'.$x02.' - adds a definition to the dictionary
'.$x02.'|-def <word>'.$x02.' - removes <word> from the dictionary
'.$x02.'|:def [<word>] [<definition>]'.$x02.' - changes the definition of <word> to <definition>';

function yotdict($word) {
	global $x02,$loaded;
	if (!$loaded['dict']) return;
	$def = file_get_html('http://dictionary.youreofftask.com/api.php?w='.urlencode(strtolower($word)));
	return html_entity_decode($x02.$def->find('a[id=word]', 0)->plaintext.$x02.': '.$def->find('a[id=def]', 0)->plaintext);
}
function adddef($word, $def) {
	global $loaded;
	if (!$loaded['dict']) return;
	$msg = file_get_html('http://dictionary.youreofftask.com/api.php?a='.urlencode($word).'&d='.urlencode($def));
	return $msg->find('a[id=msg]', 0)->plaintext;
}
function minusdef($word) {
	global $loaded;
	if (!$loaded['dict']) return;
	$msg = file_get_html('http://dictionary.youreofftask.com/api.php?m='.urlencode($word));
	return $msg->find('a[id=msg]', 0)->plaintext;
}
function changedef($word, $def) {
	global $loaded;
	if (!$loaded['dict']) return;
	$msg = file_get_html('http://dictionary.youreofftask.com/api.php?c='.urlencode($word).'&d='.urlencode($def));
	return $msg->find('a[id=msg]', 0)->plaintext;
}
?>