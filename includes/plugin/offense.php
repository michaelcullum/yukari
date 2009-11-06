<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 1
 * Copyright:	(c) 2009 - Failnet Project
 * License:		GNU General Public License - Version 2
 *
 *===================================================================
 * 
 */

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-2.0.php>.
 */
 

/**
 * Failnet - Offense plugin,
 * 		This lets Failnet be just plain evil. 
 * 
 *
 * @package plugins
 * @author Obsidian
 * @copyright (c) 2009 - Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_plugin_offense extends failnet_plugin_common
{

	/**
	 * @var array - Is THE GAME mode enabled, and if so, in what channels?
	 */
	public $enable_game = array();

	/**
	 * @var array - When did we last mention the game, for each channel?
	 */
	public $last_game = array();
	
	public function cmd_connect()
	{
		$this->last_game = time();
	}
	
	public function tick()
	{
		foreach($this->enable_game as $channel => $enabled)
		{
			if($enabled === false || !isset($this->failnet->chans[$channel]))
			{
				continue;
			}

			if(($this->last_game[$channel] + 450) < time())
			{
				$this->last_game[$channel] = time();
				if(rand(1, 7) == 1)
				{
					$game_fail = array(
						'Hey everyone, did you just win the game?',
						'LOSE THE GAME.',
						'hai u can has lose the game, right?',
						'Two words for you all.  GAME, and THE.',
						'I think someone just lost the game.',
						'I should make someone lose the game...',
						'THE GAME.',
					);

					$this->call_privmsg($channel, sprintf($game_fail[array_rand($game_fail)], $this->failnet->random_user($channel)));
				}
			}
		}
	}

	public function cmd_privmsg()
	{
		// Process the command
		$text = $this->event->get_arg('text');
		if(!$this->prefix($text))
			return;

		$cmd = $this->purify($text);
		$sender = $this->event->nick;
		$hostmask = $this->event->gethostmask();
		switch ($cmd)
		{
			// Base64 encoding
			case 'slap':
			case 'facepalm':
			case 'beat':
			case 'stab':
			case 'attack':
			case 'kill':
				if($this->failnet->checkuser($text))
				{
					$this->call_privmsg($this->event->source(), $this->failnet->deny());
					return;
				}

				$violence = array(
					'stabs %1$s',
					'throws %1$s into the nearest wall',
					'lights %1$s on fire',
					'spy-checks %1$s',
					'facestabs %1$s',
					'beats %1$s with a wooden spoon',
					'drop-kicks %1$s',
					'feeds %1$s to the Ravenous Bugblatter Beast of Traal',
					'stuffs %1$s into a garbage compactor and activates it',
				);
				$this->call_action($this->event->source(), sprintf($violence[array_rand($violence)], $text));
			break;

			case 'nub':
				if($this->failnet->checkuser($text))
				{
					$this->call_privmsg($this->event->source(), $this->failnet->deny());
					return;
				}

				$this->call_action($this->event->source(), sprintf('hits %1$s in the head with a giant NUB stamp', $text));
			break;

			case '+g':
			case 'gameon':
				if($this->event->fromchannel() !== true)
				{
					$this->call_privmsg($this->event->source(), 'This command can only be used in channel.');
					return;
				}

				$this->enable_game[$this->event->source()] = true;
				$this->last_game[$this->event->source()] = time();
				$this->call_privmsg($this->event->source(), 'Alrighty, game mode enabled.');
			break;

			// URL encoding
			case '-g':
			case 'gameoff':
				if($this->event->fromchannel() !== true)
				{
					$this->call_privmsg($this->event->source(), 'This command can only be used in channel.');
					return;
				}

				$this->enable_game[$this->event->source()] = false;
				$this->last_game[$this->event->source()] = time();
				$this->call_privmsg($this->event->source(), 'Okay, game mode disabled.');
			break;
		}
	}
}

?>