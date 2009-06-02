<?php
$failnet->modules[] = 'xkcd';
$loaded['xkcd']=true;

$help['xkcd'] = 'No auth required.
'.failnet::X02.'|xkcd'.failnet::X02.' - returns the value of the title attribute in the <img> element of the latest xkcd comic.';

function xkcd() {
	global $loaded,$failnet;
	if (!$loaded['xkcd']) return;
	$xkcd = file_get_html('http://xkcd.com');
	foreach($xkcd->find('img') as $img) {
	    if (ereg('http\:\/\/imgs\.xkcd\.com\/comics\/', $img->src)) {
        	return 'The Omnipotent Randall quoth, "'.failnet::X02.$img->title.failnet::X02.'"';
        	last;
    	}
	}
}
?>