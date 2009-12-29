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
	 * @var array - Data for The Game.
	 */
	public $thegame = array();
	
	public function tick()
	{
		foreach($this->thegame as $channel_name => $channel)
		{
			if($channel['enabled'] === false || !isset($this->failnet->chans[$channel_name]))
				continue;

			if(($channel['last'] + 450) < time())
			{
				$channel['last'] = time();
				if(rand(1, 15) == 1)
				{
					$game_fail = array(
						'Hey everyone, did you just win the game?',
						'LOSE THE GAME.',
						'hai u can has lose the game, right?',
						'Two words for you all.  GAME, and THE.',
						'I think someone just lost the game.',
						'I should make someone lose the game...',
						'THE GAME.',
						'Never gonna LOSE THE GAME~',
					);

					$this->call_privmsg($channel_name, sprintf($game_fail[array_rand($game_fail)], $this->failnet->random_user($channel_name)));
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
		$sender = $this->event->hostmask->nick;
		$hostmask = $this->event->hostmask;
		switch ($cmd)
		{
			// Base64 encoding
			case 'slap':
			case 'facepalm':
			case 'beat':
			case 'stab':
			case 'attack':
			case 'kill':
				// Self-defense!
				if($this->failnet->checkuser($text))
				{
					$this->call_privmsg($this->event->source(), $this->failnet->deny());
					return;
				}

				// No null-victims
				if(empty($text))
				{
					$this->call_privmsg($this->event->source(), $this->event->hostmask->nick . ': That was full of fail.');
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
				// Self-defense!
				if($this->failnet->checkuser($text))
				{
					$this->call_privmsg($this->event->source(), $this->failnet->deny());
					return;
				}

				// No null-victims
				if(empty($text))
				{
					$this->call_privmsg($this->event->source(), $this->event->hostmask->nick . ': That was full of fail.');
					return;
				}

				$this->call_action($this->event->source(), sprintf('hits %1$s in the head with a giant NUB stamp', $text));
			break;

			case '+g':
			case 'gameon':
				// Check to see if there was a param passed...if not, we check to see if this is from a channel.
				if($text === false && $this->event->fromchannel() === true)
				{
					$this->thegame[$this->event->source()] = array(
						'enabled'		=> true,
						'last'			=> time(),
						'start'			=> date('D m/d/Y - h:i:s A'),
						'who'			=> $this->event->hostmask->nick . ' [' . $this->event->hostmask . ']',
					);
					$this->call_privmsg($this->event->source(), 'Alrighty, game mode enabled.');
				}
				elseif($text !== false)
				{
					$this->thegame[$text] = array(
						'enabled'		=> true,
						'last'			=> time(),
						'start'			=> date('D m/d/Y - h:i:s A'),
						'who'			=> $this->event->hostmask->nick . ' [' . $this->event->hostmask . ']',
					);
					$this->call_privmsg($sender, 'Alrighty, game mode enabled.');
				}
				else
				{
					// We sent this via a private message and did not supply the channel.  That was smart.
					$this->call_privmsg($sender, 'Please specify a channel.');
				}
			break;

			case '-g':
			case 'gameoff':
				// Check to see if there was a param passed...if so, we check to see if this is from a channel.
				if($text === false && $this->event->fromchannel() === true)
				{
					$this->thegame[$this->event->source()] = array(
						'enabled'		=> false,
						'last'			=> time(),
					);
					$this->call_privmsg($this->event->source(), 'Okay, game mode disabled.');
				}
				elseif($text !== false)
				{
					$this->thegame[$text] = array(
						'enabled'		=> false,
						'last'			=> time(),
					);
					$this->call_privmsg($sender, 'Okay, game mode disabled.');
				}
				else
				{
					// We sent this via a private message and did not supply the channel.  That was smart.
					$this->call_privmsg($sender, 'Please specify a channel.');
				}
			break;

			case 'lastgame':
			case 'gamewho':
			case 'gamewhen':
				// Check to see if there was a param passed...if so, we check to see if this is from a channel.
				if($text === false && $this->event->fromchannel() === true)
				{
					if(!isset($this->thegame[$this->event->source()]) || $this->thegame[$this->event->source()]['enabled'] !== true)
					{
						$this->call_privmsg($this->event->source(), 'The Game is not afoot anyways.');
					}
					else
					{
						$this->call_privmsg($this->event->source(), 'The Game was begun by ' . $this->thegame[$this->event->source()]['who'] . ' on ' . $this->thegame[$this->event->source()]['start']);
					}
				}
				elseif($text !== false)
				{
					if(!isset($this->thegame[$text]) || $this->thegame[$text]['enabled'] !== true)
					{
						$this->call_privmsg($sender, 'The Game is not afoot in that channel anyways.');
					}
					else
					{
						$this->call_privmsg($sender, 'The Game was begun by ' . $this->thegame[$text]['who'] . ' on ' . $this->thegame[$text]['start']);
					}
				}
				else
				{
					// We sent this via a private message and did not supply the channel.  That was smart.
					$this->call_privmsg($sender, 'Please specify a channel.');
				}
			break;

			case 'game':
				// Check to see if there was a param passed...if so, we check to see if this is from a channel.
				if($text === false && $this->event->fromchannel() === true)
				{
					if(!isset($this->thegame[$this->event->source()]) || $this->thegame[$this->event->source()]['enabled'] !== true)
					{
						$this->call_privmsg($this->event->source(), 'The Game is not ongoing.');
					}
					else
					{
						$this->call_privmsg($this->event->source(), 'The Game is afoot.');
					}
				}
				elseif($text !== false)
				{
					if(!isset($this->thegame[$text]) || $this->thegame[$text]['enabled'] !== true)
					{
						$this->call_privmsg($sender, 'The Game is not ongoing in that channel.');
					}
					else
					{
						$this->call_privmsg($sender, 'The Game is afoot in that channel.');
					}
				}
				else
				{
					// We sent this via a private message and did not supply the channel.  That was smart.
					$this->call_privmsg($sender, 'Please specify a channel.');
				}
			break;

			case 'whatisthegame':
				$this->call_privmsg($this->event->source(), (($text === false) ? $this->event->hostmask->nick : $text) . ': Let me tell you about The Game.');
				$this->call_privmsg($this->event->source(), 'The Game is a simple Game, and there are three rules.');
				$this->call_privmsg($this->event->source(), 'Rule 1) You are always playing The Game.');
				$this->call_privmsg($this->event->source(), 'Rule 2) Every time you think about The Game, you lose.');
				$this->call_privmsg($this->event->source(), 'Rule 3) Loss of The Game must be announced to someone else.');
			break;
		}
	}
}

?>