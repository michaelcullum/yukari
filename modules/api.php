<?php
$x02 = "\x02";
$nl = "\n";
if (!empty($_GET['w'])) {
	$word = urldecode($_GET['w']);
	if (!preg_match('/^[a-z]/', $word)) {
		$f = '0-9';
	} else {
		$f = strtoupper(substr($word, 0, 1));
	}
	$f = file($f);
	foreach ($f as $entry) {
		$entry = explode('::', rtrim($entry));
		if ($word==strtolower($entry[0])) {
			$def = preg_replace('/\<b\>(.+)\<\/b\>/', '$1', $entry[1]);
			$def = preg_replace('/\<br(| \/)\>/', ' ', $def);
			$def = preg_replace('/\<.+\>(.*)\<\/.+\>/', '$1', $def);
			echo '<a id="word">'.$entry[0].'</a><a id="def">'.$def.'</a>';
			exit;
		}
	}
	echo '<a id="word">Error</a><a id="def">word not found</a>';
} elseif ((!empty($_GET['a']))&&(!empty($_GET['d']))) {
	$word = urldecode($_GET['a']);
	$def = urldecode($_GET['d']);
	if (!preg_match('/^[a-z]/i', $word)) {
		$fn = '0-9';
	} else {
		$fn = strtoupper(substr($word, 0, 1));
	}
	$f = file($fn);
	foreach ($f as $entry) {
		$entry = explode('::', rtrim($entry));
		if (strtolower($entry[0])==strtolower($word)) {
			echo '<a id="msg">'.$x02.'Error'.$x02.': "'.$word.'" is already in the dictionary</a>';
			exit;
		}
	}
	if (filesize($fn)>0) {
		$word = $nl.$word;
	}
	file_put_contents('backups/'.$fn.'_'.time(), file_get_contents($fn));
	if (@file_put_contents($fn, file_get_contents($fn).stripslashes($word).'::'.stripslashes($def))) {
		echo '<a id="msg">'.$x02.str_replace($nl, '', $word).$x02.' added</a>';
	} else {
		echo '<a id="msg">SOMEBODY SET US UP THE '.$x02.'BOMB'.$x02.'</a>';
	}
	clean($fn);
} elseif (!empty($_GET['m'])) {
	$word = strtolower(urldecode($_GET['m']));
	if (!preg_match('/^[a-z]/i', $word)) {
		$fn = '0-9';
	} else {
		$fn = strtoupper(substr($word, 0, 1));
	}
	$f = file($fn);
	foreach ($f as &$entry) {
		if (substr(strtolower($entry), 0, strlen($word))==$word) {
			$entry = null;
			$baleeted = true;
		}
	}
	sort($f);
	$f = str_replace($nl.$nl, $nl, preg_replace('/^'.$nl.$nl.'/', '', implode($nl, $f)));
	if ($baleeted) {
		file_put_contents('backups/'.$fn.'_'.time(), file_get_contents($fn));
		if (@file_put_contents($fn, $f)) {
			echo '<a id="msg">Entry '.$x02.'BALEETED'.$x02.'</a>';
		} else {
			echo '<a id="msg">SOMEBODY SET US UP THE '.$x02.'BOMB'.$x02.'</a>';
		}
		clean($fn);
	} else {
		echo '<a id="msg">What is this "'.$x02.urldecode($_GET['m']).$x02.'" you speak of?</a>';
	}
} elseif ((!empty($_GET['c']))&&(!empty($_GET['d']))) {
	$word = strtolower(urldecode($_GET['c']));
	$def = urldecode($_GET['d']);
	if (!preg_match('/^[a-z]/', $word)) {
		$fn = '0-9';
	} else {
		$fn = strtoupper(substr($word, 0, 1));
	}
	$f = file($fn);
	foreach ($f as &$entry) {
		$entry = explode('::', $entry);
		if ($word==strtolower($entry[0])) {
			$entry[1] = $def;
			$changed = true;
		}
		$entry = implode('::', $entry);
	}
	if ($changed) {
		file_put_contents('backups/'.$fn.'_'.time(), file_get_contents($fn));
		if (@file_put_contents($fn, stripslashes(implode($nl, $f)))) {
			echo '<a id="msg">'.$x02.urldecode($_GET['c']).$x02.' changed.</a>';
		} else {
			echo '<a id="msg">SOMEBODY SET US UP THE '.$x02.'BOMB'.$x02.'</a>';
		}
	} else {
		echo '<a id="msg">What is this "'.$x02.urldecode($_GET['c']).$x02.'" you speak of?</a>';
	}
	clean($fn);
}
function clean($fn) {
	global $nl;
	$f = file($fn);
	foreach ($f as &$l) { // $l == line -_-
		if ($l==$nl) $l='';
	}
	$f = implode($f);
	file_put_contents('backups/'.$fn.'.clean.'.time(), stripslashes($f));
	file_put_contents($fn, stripslashes($f));
}
?>