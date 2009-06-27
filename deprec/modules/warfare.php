<?php
$failnet->modules[] = 'warfare';
$loaded['warfare']=true;

$help['warfare'] = 'All functions except for |ammo and |type require an auth level of at least 2.
'.failnet::X02.'|ammo'.failnet::X02.' - shows ammo remaining
'.failnet::X02.'|type'.failnet::X02.' - shows current weapon type
'.failnet::X02.'|:type <type>'.failnet::X02.' - changes the weapon type (with a or an prepending it, for example "with a snowball")
'.failnet::X02.'|_type <type>'.failnet::X02.' - changes the weapon type (without a or an preding it, for example "with orange pie")
'.failnet::X02.'|shoot <person>'.failnet::X02.' - shoots someone/something.
Possible outcome of shooting:
0.4 '.failnet::X02.'headshot'.failnet::X02.'
0.2 '.failnet::X02.'stomach'.failnet::X02.'
0.4 '.failnet::X02.'fail'.failnet::X02;

$ammo = 10;
$type = 'snowball';

function shoot($p,$sender) {
	global $loaded,$ammo,$shot,$type,$noa,$health,$failnet;
	if (!$loaded['warfare']) return;
	if ($ammo>0) {
		$ammo--;
		$shot[$p]++;
		$hit = rand(1, 5);
		if ((!preg_match('/\'s (.+)$/i', $type))&&(!preg_match('/^the (.+)$/i', $type))&&(preg_match('/^[a-z]/i', $type))&&(!$noa)) {
			$a = ' a';
			if (preg_match('/^(a|e|i|o|u|her)/i', $type)) {
				$a.='n';
			}
		} else {
			$a = '';
		}
		if ($hit > 3) {
			$failnet->privmsg(failnet::X02.'HEADSHOT'.failnet::X02.'! [I shot '.failnet::X02.$p.failnet::X02.'. Weapon type '.failnet::X02.$type.failnet::X02.'. Ammo left: '.failnet::X02.$ammo.failnet::X02.']');
		} elseif ($hit == 3) {
			$failnet->action('shoots '.failnet::X02.$p.failnet::X02.' in the '.failnet::X02.'stomach'.failnet::X02.' with'.$a.' '.failnet::X02.$type.failnet::X02.' [ammo: '.failnet::X02.$ammo.failnet::X02.']');
		} else {
			$failnet->action(failnet::X02.'failed'.failnet::X02.' to shoot '.failnet::X02.$p.failnet::X02.' with'.$a.' '.failnet::X02.$type.failnet::X02.' [ammo: '.failnet::X02.$ammo.failnet::X02.']');
		}
	} else {
		$failnet->privmsg(failnet::X02.'*click*'.failnet::X02.' - say |:reload');
	}
}
?>