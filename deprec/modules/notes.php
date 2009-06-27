<?php
$modules[]='notes';
$help['notes']='No auth required.
'.$x02.'|+note [<name>] [<content>]'.$x02.' - takes a note
'.$x02.'|:note <name>'.$x02.' - shows the content of <note>';

function takenote($name, $content) {
	global $nl;
	$notes = file('data/notes');
	foreach ($notes as $note) {
		$note = explode(':_:', rtrim($note));
		if ($name == $note[0]) {
			return 'A note with that name already exists, sorry.';
		} else {
			if (file_put_contents('data/notes', file_get_contents('data/notes').$nl.$name.':_:'.$content)) {
				return array_rand(array_flip(array('Got it.', 'I have it.', 'Yos', 'No bombs here.', 'Done.')));
			} else {
				return array_rand(array_flip(array('Oh noes! I haz found fail.', 'The oboes stopped me, sorry.', $nick.', what does the scouter say about his FAIL level? IT\'S OVER 9000!')));
			}
		}
	}
}

function getnote($name) {
	$notes = file('data/notes');
	foreach ($notes as $note) {
		$note = explode(':_:', rtrim($note));
		if ($name == $note[0]) {
			$content = $note[1];
		}
	}
	if (!empty($content)) {
		return $content;	
	} else {
		return 'I couldn\'t find a note with that name.';
	}
}
?>