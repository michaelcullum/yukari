<?php
$failnet->modules[] = 'slashdot';
$loaded['slashdot']=true;

$help['slashdot'] = 'No auth required.
'.failnet::X02.'|slashdot'.failnet::X02.' - returns the title of the latest /. story.';

// Parse and PRIVMSG latest slashdot story
function slashdot($matches) {
	global $loaded;
	if (!$loaded['slashdot']) return;
	$slashdot=file_get_html('http://slashdot.org/slashdot.xml');
	return $slashdot->find('title', 0)->plaintext;
}
?>