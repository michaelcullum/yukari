<?php
$modules[] = 'alchemy';
$loaded['alchemy']=true;

$help['alchemy'] = 'No authorization required.
'.$x02.'|createpotion <name>'.$x02.' - creates a potion
'.$x02.'|addingredient [<potion>] [<ingredient>]'.$x02.' - adds one <ingredient> to <potion>
'.$x02.'|mixpotion <potion>'.$x02.' - mixes the ingredients in a potion (currently borken)
'.$x02.'|ingredients'.$x02.' - lists all available ingredients
'.$x02.'|whatsin <potion>'.$x02.' - lists all the ingredients in <potion>
'.$x02.'|remove [<quantity>] [<ingredient>] from [<potion>]'.$x02.' - removes <quantity> <ingredients>s from <potion>';

$ingredients = explode('::', file_get_contents('modules/alchemy/ingredients'));
$ingrflip = $ingredients;
foreach ($ingrflip as &$ingr) {
	$ingr = remove_p($ingr, 1);
}
$ingrflip = array_flip($ingrflip);

$combos = explode($nl, file_get_contents('modules/alchemy/combos'));
foreach ($combos as &$combo) {
	$combo = explode('>>', rtrim($combo));
	$combo[0] = explode('::', $combo[0]);
	sort($combo[0]);
	$combo[0] = implode('::', $combo[0]);
}

function createpotion($name) {
	global $potions,$x02,$loaded;
	if (!$loaded['alchemy']) return;
	if (dirname($name)!='.') {
		deny();
	} else {
		if (!empty($potions[remove_p($name,1)])) {
			privmsg($x02.'Error'.$x02.': potion "'.$name.'" already exists');
		} else {
			$potions[remove_p($name,1)] = array('water');
		}
	}
}

function addingredient($potion, $ingredient) {
	global $potions,$ingredients,$ingrflip,$nl,$x02,$loaded;
	if (!$loaded['alchemy']) return;
	$ingredient = remove_p($ingredient, 1);
	$potion = remove_p($potion, 1);
	print_r($ingrflip);
	echo $nl.'['.$ingredient.']'.$nl;
	if (isset($ingrflip[$ingredient])) {
		if (!empty($potions[$potion])) {
			$potions[$potion][]=$ingredient;
			privmsg($x02.$ingredient.$x02.' added to '.$x02.$potion.$x02);
		} else {
			pde();
		}
	} else {
		privmsg('There is no such ingredient as '.$x02.$ingredient.$x02.'.');
	}
}

function save_potion($potion) {
	global $potions,$x02,$loaded;
	if (!$loaded['alchemy']) return;
	$fn = 'modules/alchemy/potions/'.$potion.(rand(0, 100)/100)*time();
	if (dirname($fn)!='.') {
		deny();
	} else {
		if (!empty($potions[$potion])) {
			file_put_contents($fn, implode("::", $potions[$potion]));
			privmsg('Potion '.$x02.$potion.$x02.' saved.');
		} else {
			pde();
		}
	}
}

function mix($potion) {
	global $potions,$ingredients,$combos,$x02,$loaded;
	if (!$loaded['alchemy']) return;
	if (!empty($potions[$potion])) {
		$ingrs = array_keys($potions[$potion]);
		sort($ingrs);
		$ingrs = implode("::", $ingrs);
		foreach ($combos as $combo) {
			if ($ingrs == $combo[0]) {
				privmsg($x02.'Result'.$x02.': '.$combo[1]);
				$done=1;
			}
		}
		if (!$done) {
			privmsg($x02.'Result'.$x02.': YOUR HEAD ASPLODES!');
		}
		unset($potions[$potion]);
	} else {
		pde();
	}
}

function whatisin($potion) {
	global $potions,$x02,$loaded;
	if (!$loaded['alchemy']) return;
	if (!empty($potions[$potion])) {
		privmsg(implode(', ', $potions[$potion]));
	} else {
		pde();
	}
}

function removefrom($amnt, $ingr, $potion) {
	global $ingredients, $potions, $ingrflip,$x02,$loaded;
	if (!$loaded['alchemy']) return;
	$original = $amnt;
	if (!empty($potions[$potion])) {
		foreach ($potions[$potion] as $key => &$val) {
			if ($val == remove_p($ingr, 1)) {
				$yarr = array_flip($potions[$potion]);
				$yarr = $yarr[$val];
				unset($potions[$potion][$yarr]);
				sort($potions[$potion]);
				$amnt--;
				$done = 1;
				if ($amnt==0) {
					last;
				}
			}
		}
		if ($done) {
			privmsg($x02.$ingr.$x02.' (quantity: '.$x02.$original.$x02.') removed from '.$x02.$potion.$x02);
		} else {
			privmsg('Potion '.$x02.$potion.$x02.' doesn\'t contain '.$x02.$ingr.$x02.'.');
		}
	} else {
		pde();
	}
}

function pde() { // potion doesn't exist
	global $x02;
	privmsg('Potion '.$x02.$potion.$x02.' doesn\'t exist.');
}
?>