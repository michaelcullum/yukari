<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0
 * SVN ID:		$Id$
 * Copyright:	(c) 2009 - Failnet Project
 * License:		http://opensource.org/licenses/gpl-2.0.php  |  GNU Public License v2
 *
 *===================================================================
 * 
 */

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
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
 * @ignore
 */
if(!defined('IN_FAILNET')) exit(1);

/**
 * Failnet - Administration plugin,
 * 		This allows the owner or authorized users to control Failnet. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_plugin_admin extends failnet_plugin_common
{
	/**
	 * When was the last time we requested a dai?  This is used for dai confirm timeouts.
	 * @var integer
	 */
	private $dai = 0;

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
			// Terminates Failnet
			case 'quit':
			case 'die':
			case 'dai':
				// Check auths
				if ($this->failnet->auth->authlevel($hostmask) < 50)
				{
					$this->call_privmsg($this->event->source(), $this->failnet->deny());
					return;
				}

				if(($this->dai + 60) < time())
				{
					$this->dai = time();
					$this->call_privmsg($this->event->source(), 'Are you sure? If so, please repeat |dai.');
				}
				else
				{
					// Okay, we've confirmed it.  Time to go to sleep.
					if($this->failnet->get('speak'))
					{
						foreach($this->failnet->chans as $channame => $chan)
						{
							$this->call_privmsg($channame, $this->failnet->get('dai_msg'));
						}
					}
					$this->call_quit(false);
				}
			break;

			// Restart Failnet
			case 'restart':
			case 'reboot':
				// Check auths
				if ($this->failnet->auth->authlevel($hostmask) < 50)
				{
					$this->call_privmsg($this->event->source(), $this->failnet->deny());
					return;
				}

				// Let's announce the restart if we've permission to speak.
				if($this->failnet->get('speak'))
				{
					foreach($this->failnet->chans as $channame => $chan)
					{
						$this->call_privmsg($channame, $this->failnet->get('restart_msg'));
					}
				}
				$this->call_quit(true);
			break;

			// Change a config variable...if we DARE
			case 'set':
				// Check auths
				if ($this->failnet->auth->authlevel($hostmask) < 100)
				{
					$this->call_privmsg($this->event->source(), $this->failnet->deny());
					return;
				}

				// Check for empty text or invalid number of parameters
				if($text === false)
				{
					$this->call_notice($sender, 'Please specify the setting to change and what to change it to.');
					return;
				}

				$param = explode(' ', $text);
				if(count($param) != 2)
				{
					$this->call_notice($sender, 'Invalid number of arguments entered for set command.');
					return;
				}

				try
				{
					$this->failnet->sql('config', 'update')->execute(array(':name' => $param[0], ':value' => $param[1]));
				}
				catch (PDOException $e)
				{
					// Something went boom.  Time to panic!
					$this->db->rollBack();
					if(file_exists(FAILNET_ROOT . 'data/restart.inc')) 
						unlink(FAILNET_ROOT . 'data/restart.inc');
					trigger_error($e, E_USER_WARNING);
					sleep(3);
					exit(1);
				}
			break;

			// Load a plugin if it isn't already loaded
			case 'load':
				// Check auths
				if ($this->failnet->auth->authlevel($hostmask) < 70)
				{
					$this->call_privmsg($this->event->source(), $this->failnet->deny());
					return;
				}

				// Check for empty text
				if($text === false)
				{
					$this->call_notice($sender, 'Please specify the plugin to load.');
					return;
				}

				// Check to see if we've loaded that plugin already, and if not load it
				if($this->failnet->manager->load($text))
				{
					$this->call_notice($sender, 'Plugin loaded successfully.');
				}
				else
				{
					$this->call_notice($sender, 'Plugin already loaded.');
				}
			break;

			// Returns how long Failnet has been running for
			case 'uptime':
				$this->call_privmsg($this->event->source(), 'I\'ve been running for ' . timespan(time() - $this->failnet->start, true));
			break;

			// How much memory is Failnet using?
			case 'memuse':
				$this->call_privmsg($this->event->source(), 'Memory use is ' . get_formatted_filesize(memory_get_usage()) . ', and memory peak is ' . get_formatted_filesize(memory_get_peak_usage()));
			break;

			// Join a channel!
			case 'join':
				// Check auths
				if ($this->failnet->auth->authlevel($hostmask) < 5)
				{
					$this->call_privmsg($this->event->source(), $this->failnet->deny());
					return;
				}

				// Make sure we specified at least the channel name to join.
				if($text !== false)
				{
					// Check to see if we're trying to join a channel with a key
					$param = explode(' ', $text);
					if(isset($param[1]))
					{
						$this->call_join($param[0], $param[1]);
					}
					else
					{
						$this->call_join($param[0]);
					}
				}
				else
				{
					$this->call_notice($sender, 'Please specify a channel to join.');
				}
			break;

			// Leave a channel.
			case 'part':
				// Check auths
				if ($this->failnet->auth->authlevel($hostmask) < 5)
				{
					$this->call_privmsg($this->event->source(), $this->failnet->deny());
					return;
				}

				// Check to see if there was a param passed...if so, we check to see if this is from a channel.
				// If it is, then we part the channel it was said in.
				if($text === false && $this->event->fromchannel() === true)
				{
					// Annouce the channel part if we're allowed to speak.
					if($this->failnet->get('speak'))
						$this->call_privmsg($this->event->source(), $this->failnet->get('part_msg'));
					$this->call_part($this->event->source(), $this->failnet->get('quit_msg'));
				}
				elseif($text !== false && $this->event->fromchannel() === true)
				{
					if($this->failnet->is_in($this->failnet->nick, $text))
					{
						// Annouce the channel part if we're allowed to speak.
						if($this->failnet->get('speak'))
							$this->call_privmsg($text, $this->failnet->get('part_msg'));
						$this->call_part($text, $this->failnet->get('part_msg'));
					}
					else
					{
						// I guess we're not in the channel specified.
						$this->call_notice($sender, 'I\'m sorry, but I cannot part a channel I am not in.');
					}
				}
				else
				{
					// We sent this via a private message and did not supply the channel to part.  That was smart.
					$this->call_notice($sender, 'Please specify a channel to part from.');
				}
			break;
		}
	}
}

?>