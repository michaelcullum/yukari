<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		3.0.0 DEV
 * Copyright:	(c) 2009 - 2010 -- Failnet Project
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
 * Failnet - Administration plugin,
 * 		This allows the owner or authorized users to control Failnet.
 *
 *
 * @package plugins
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_plugin_admin extends failnet_plugin_common
{
	/**
	 * @var integer - When was the last time we requested a dai?  This is used for dai confirm timeouts.
	 */
	private $dai = 0;

	/**
	 * @var integer - When did we last check for timed out sessions?
	 */
	private $time = 0;

	public function help(&$name, &$commands)
	{
		$name = 'admin';
		$commands = array(
			'chans'			=> 'chans - (authlevel REGISTEREDUSER) - Outputs the channels Failnet is currently inhabiting',
			'start'			=> 'start - (authlevel REGISTEREDUSER) - Outputs when Failnet was launched',
			'uptime'		=> 'uptime - (authlevel REGISTEREDUSER) - Outputs how long Failnet has been running for',
			'memuse'		=> 'memuse - (authlevel REGISTEREDUSER) - Outputs Failnet`s memory usage data',
			'plugins'		=> 'plugins - (authlevel TRUSTEDUSER) - Outputs a list of plugins currently loaded',
			'loaded'		=> 'loaded {$plugin} - (authlevel TRUSTEDUSER) - Checks to see if a specific Failnet plugin has been loaded already or not',
			'load'			=> 'load {$plugin} - (authlevel SUPERADMIN) - Loads a specific Failnet plugin on demand if it is not already loaded',
			'nick'			=> 'nick {$new_nick} - (authlevel ADMIN) - Changes Failnet`s nick to $new_nick',
			'join'			=> 'join {$channel} - (authlevel TRUSTEDUSER) - Instructs Failnet to join channel $channel',
			'part'			=> 'part [{$channel}] - (authlevel TRUSTEDUSER) - Instructs Failnet to leave channel $channel (or if no channel is specified, Failnet will leave the channel it receives the command in)',
			'restart'		=> 'restart - (authlevel ADMIN) - Restarts Failnet',
			'dai'			=> 'dai - (authlevel ADMIN) - Terminates Failnet',
		);
	}

	public function tick()
	{
		// Check for the last time that we did a session purge.
		if($this->time + 10800 <= time())
		{
			$this->time = time();
			$this->failnet->sql('sessions', 'delete_old')->execute(array(':time' => $this->time - 3600));
		}
	}

	public function cmd_privmsg()
	{
		// Process the command
		$text = $this->event->get_arg('text');
		if(!$this->prefix($text))
			return;

		$cmd = $this->purify($text);
		$this->set_msg_args(($this->failnet->config('speak')) ? $this->event->source() : $this->event->hostmask->nick);

		$sender = $this->event->hostmask->nick;
		$hostmask = $this->event->hostmask;
		switch ($cmd)
		{
			// Terminates Failnet
			case 'quit':
			case 'die':
			case 'dai':
				// Check auths
				if ($this->failnet->authorize->authlevel($hostmask) < self::AUTH_ADMIN)
				{
					$this->msg($this->failnet->deny());
					return;
				}

				if(($this->dai + 60) < time())
				{
					$this->dai = time();
					$this->msg('Are you sure? If so, please repeat |dai.');
				}
				else
				{
					// Okay, we've confirmed it.  Time to go to sleep.
					if($this->failnet->config('speak'))
					{
						foreach($this->failnet->chans as $channame => $chan)
						{
							$this->call_privmsg($channame, $this->failnet->config('dai_msg'));
						}
					}
					$this->call_quit(false);
				}
			break;

			// Restart Failnet
			case 'restart':
			case 'reboot':
				// Check auths
				if ($this->failnet->authorize->authlevel($hostmask) < self::AUTH_ADMIN)
				{
					$this->msg($this->failnet->deny());
					return;
				}

				// Let's announce the restart if we've permission to speak.
				if($this->failnet->config('speak'))
				{
					foreach($this->failnet->chans as $channame => $chan)
					{
						$this->call_privmsg($channame, $this->failnet->config('restart_msg'));
					}
				}
				$this->call_quit(true);
			break;

			case 'nick':
				// Check auths
				if ($this->failnet->authorize->authlevel($hostmask) < self::AUTH_ADMIN)
				{
					$this->msg($this->failnet->deny());
					return;
				}

				// Make sure this is a valid IRC usernick
				if(preg_match('#^[a-zA-Z0-9\-\_\[\]\|`]*$#i', $text))
				{
					$this->call_nick($text);
				}
				else
				{
					$this->msg('I\'m sorry, but that is an invalid usernick.');
				}
			break;

			// Join a channel!
			case 'join':
				// Check auths
				if ($this->failnet->authorize->authlevel($hostmask) < self::AUTH_TRUSTEDUSER)
				{
					$this->msg($this->failnet->deny());
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
					$this->msg('Please specify a channel to join.');
				}
			break;

			// Leave a channel.
			case 'part':
				// Check auths
				if ($this->failnet->authorize->authlevel($hostmask) < self::AUTH_TRUSTEDUSER)
				{
					$this->msg($this->failnet->deny());
					return;
				}

				// Check to see if there was a param passed...if so, we check to see if this is from a channel.
				// If it is, then we part the channel it was said in.
				if($text === false && $this->event->fromchannel === true)
				{
					// Annouce the channel part if we're allowed to speak.
					if($this->failnet->config('speak'))
						$this->msg($this->failnet->config('part_msg'));
					$this->call_part($this->event->source(), $this->failnet->config('quit_msg'));
				}
				elseif($text !== false)
				{
					if($this->failnet->server->in_channel($this->failnet->nick, $text))
					{
						// Annouce the channel part if we're allowed to speak.
						if($this->failnet->config('speak'))
							$this->call_privmsg($text, $this->failnet->config('part_msg'));
						$this->call_part($text, $this->failnet->config('quit_msg'));
					}
					else
					{
						// I guess we're not in the channel specified.
						$this->msg('I\'m sorry, but I cannot part a channel I am not in.');
					}
				}
				else
				{
					// We sent this via a private message and did not supply the channel to part.  That was smart.
					$this->msg('Please specify a channel to part from.');
				}
			break;

			// Load a plugin if it isn't already loaded
			case 'load':
				// Check auths
				if ($this->failnet->authorize->authlevel($hostmask) < self::AUTH_SUPERADMIN)
				{
					$this->msg($this->failnet->deny());
					return;
				}

				// Check for empty text
				if($text === false)
				{
					$this->msg('Please specify the plugin to load.');
					return;
				}

				// Check to see if we've loaded that plugin already, and if not load it
				if($this->failnet->plugin('load', $text))
				{
					$this->msg('Plugin loaded successfully.');
				}
				else
				{
					$this->msg('Plugin does not exist or is already loaded.');
				}
			break;

			case 'loaded':
				// Check auths
				if ($this->failnet->authorize->authlevel($hostmask) < self::AUTH_TRUSTEDUSER)
				{
					$this->msg($this->failnet->deny());
					return;
				}

				// Check for empty text
				if($text === false)
				{
					$this->msg('Please specify the plugin to check.');
					return;
				}

				$this->msg(($this->failnet->plugin('loaded', $text)) ? 'Plugin is loaded.' : 'Plugin is not currently loaded.');
			break;

			case 'plugins':
				// Check auths
				if ($this->failnet->authorize->authlevel($hostmask) < self::AUTH_TRUSTEDUSER)
				{
					$this->msg($this->failnet->deny());
					return;
				}

				// Let's build a list of plugins.
				$plugins = implode(', ', $this->failnet->plugins_loaded);
				$this->msg('Plugins: ' . $plugins);
			break;

			// Returns how long Failnet has been running for
			case 'start':
				// Check auths
				if ($this->failnet->authorize->authlevel($hostmask) < self::AUTH_REGISTEREDUSER)
				{
					$this->msg($this->failnet->deny());
					return;
				}

				$this->msg('I\'ve been running since ' . date('l F jS, Y [h:i:s A]', $this->failnet->start));
			break;

			// Returns how long Failnet has been running for
			case 'uptime':
				// Check auths
				if ($this->failnet->authorize->authlevel($hostmask) < self::AUTH_REGISTEREDUSER)
				{
					$this->msg($this->failnet->deny());
					return;
				}

				$this->msg('I\'ve been running for ' . timespan(time() - $this->failnet->start, true));
			break;

			// How much memory is Failnet using?
			case 'memuse':
				// Check auths
				if ($this->failnet->authorize->authlevel($hostmask) < self::AUTH_REGISTEREDUSER)
				{
					$this->msg($this->failnet->deny());
					return;
				}

				$this->msg('Memory use is ' . get_formatted_filesize(memory_get_usage() - $this->failnet->base_mem) . ', and memory peak is ' . get_formatted_filesize(memory_get_peak_usage() -  $this->failnet->base_mem_peak));
			break;

			case 'chans':
				// Check auths
				if ($this->failnet->authorize->authlevel($hostmask) < self::AUTH_REGISTEREDUSER)
				{
					$this->msg($this->failnet->deny());
					return;
				}

				$chans = implode(', ', array_keys($this->failnet->chans));
				$this->msg('Current channels joined are ' . $chans);
			break;

			case 'cake':
			case 'caek':
				cake();
				$this->msg('This was a triumph...');
			break;
		}
	}

	public function cmd_version()
	{
		$this->call_version($this->event->hostmask->nick, 'Failnet PHP IRC Bot v' . FAILNET_VERSION);
	}
}
