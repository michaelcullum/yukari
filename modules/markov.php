<?php
function markov($msg,$force=0) {
	global $nl,$prob,$maxlength;
	if (($force)||(rand(1,100)<=$prob)) {
		echo $nl.$nl.'markov tiem'.$nl.$nl;
		initmarkov($msg);
		$markov = explode($nl, `dadadodo -c 1 markovtext`);
		unset($markov[-1]);
		foreach ($markov as &$bagel) {
			$bagel = trim($bagel);
		}
		$markov = implode(' ', $markov);
		if ((ereg('Yow!', $markov))||(strlen($markov)>$maxlength)||(strlen($markov)<13)||(strlen($markov)>50)||(ereg($nl.$markov.$nl, file_get_contents('usedkov')))) {
			echo 'D:'.$markov.$nl;
			return markov(1);
		} else {
			file_put_contents('usedkov', file_get_contents('usedkov').$markov.$nl);
			echo 'o.'.$markov.$nl;
			return trim($markov);
		}
	} else {
		return '';
	}
}
function getline() {
	global $nl;
	$str = array_rand(array_flip(explode($nl, file_get_contents('log')))).$nl.array_rand(array_flip(explode($nl, file_get_contents('log')))).$nl.array_rand(array_flip(explode($nl, file_get_contents('log')))).$nl.array_rand(array_flip(explode($nl, file_get_contents('log')))).$nl.array_rand(array_flip(explode($nl, file_get_contents('log')))).$nl.array_rand(array_flip(explode($nl, file_get_contents('log')))).$nl;
	if (strlen($str)>9) {
		return $str;
	} else {
		return getline(); // /me recurses
	}
}
function initmarkov($msg) {
	global $nl;
	if (!preg_match('/[\?\!\.]^/', $msg)) {
		$msg.='.';
	}
	file_put_contents('markovtext', str_repeat($msg.$nl, rand(4,6)).getline());
}
?>