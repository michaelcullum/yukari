<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
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
 * Failnet - Channel residence tracking plugin,
 * 		Used to track what channels Failnet is in, and the users inhabiting them. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_plugin_channels extends failnet_plugin_common
{
	const FOUNDER = 32;
	const ADMIN = 16;
	const OP = 8;
	const HALFOP = 4;
	const VOICE = 2;
	const REGULAR = 1;
	
	public function cmd_response()
	{
		switch($this->event->code)
		{
			case failnet_event_response::RPL_ENDOFNAMES:
				$chanargs = explode(' ', $this->event->arguments);
				if($this->failnet->speak)
					// Only do the intro message if we're allowed to speak.  
					$this->call_privmsg($chanargs[1], $this->failnet->get('intro_msg'));
			break;
			
			case failnet_event_response::RPL_NAMREPLY:
				$desc = preg_split('/[@*=]\s*/', $this->event->description, 2);
				list($chan, $users) = array_pad(explode(' :', trim($desc[1])), 2, null);
				$users = explode(' ', trim($users));
				foreach($users as $user)
				{
					if (empty($user)) 
						continue;
		
					$flag = self::REGULAR;
					if (substr($user, 0, 1) === '~')
					{
						$user = substr($user, 1);
						$flag |= self::FOUNDER;
					}
					if (substr($user, 0, 1) === '&')
					{
						$user = substr($user, 1);
						$flag |= self::ADMIN;
					}
					if (substr($user, 0, 1) === '@')
					{
						$user = substr($user, 1);
						$flag |= self::OP;
					}
					if (substr($user, 0, 1) === '%')
					{
						$user = substr($user, 1);
						$flag |= self::HALFOP;
					}
					if (substr($user, 0, 1) === '+')
					{
						$user = substr($user, 1);
						$flag |= self::VOICE;
					}
		
					$this->failnet->chans[trim(strtolower($chan))][trim(strtolower($user))] = $flag;
				}
			break;
		}
	}
	
	/**
	 * Tracks mode changes.
	 *
	 * @return void
	 */
	public function cmd_mode()
	{
		if (count($this->event->arguments) != 3)
			return;
		
		$chan = $this->event->get_arg('target');
		$modes = $this->event->get_arg('mode');
		$nick = $this->event->get_arg(2);

		if (preg_match('/(?:\+|-)[hov+-]+/i', $modes))
		{
			$chan = trim(strtolower($chan));
			$modes = str_split(trim(strtolower($modes)), 1);
			$nick = trim(strtolower($nick));
			while ($char = array_shift($modes))
			{
				switch ($char)
				{
					case '+':
						$mode = '+';
					break;

					case '-':
						$mode = '-';
					break;

					case 'q':
						if ($mode == '+')
						{
							$this->failnet->chans[$chan][$nick] |= self::FOUNDER;
						}
						elseif ($mode == '-')
						{
							$this->failnet->chans[$chan][$nick] ^= self::FOUNDER;
						}
					break;

					case 'a':
						if ($mode == '+')
						{
							$this->failnet->chans[$chan][$nick] |= self::ADMIN;
						}
						elseif ($mode == '-')
						{
							$this->failnet->chans[$chan][$nick] ^= self::ADMIN;
						}
					break;

					case 'o':
						if ($mode == '+')
						{
							$this->failnet->chans[$chan][$nick] |= self::OP;
						}
						elseif ($mode == '-')
						{
							$this->failnet->chans[$chan][$nick] ^= self::OP;
						}
					break;

					case 'h':
						if ($mode == '+')
						{
							$this->failnet->chans[$chan][$nick] |= self::HALFOP;
						}
						elseif ($mode == '-')
						{
							$this->failnet->chans[$chan][$nick] ^= self::HALFOP;
						}
					break;

					case 'v':
						if ($mode == '+')
						{
							$this->failnet->chans[$chan][$nick] |= self::VOICE;
						}
						elseif ($mode == '-')
						{
							$this->failnet->chans[$chan][$nick] ^= self::VOICE;
						}
					break;
				}
			}
		}
	}
	
	public function cmd_kick()
	{
		if($this->event->nick != $this->failnet->get('nick'))
		{
			if (isset($this->failnet->chans[trim(strtolower($this->event->get_arg('channel')))][trim(strtolower($this->event->nick))]))
				unset($this->failnet->chans[trim(strtolower($this->event->get_arg('channel')))][trim(strtolower($this->event->nick))]);
		}
		else
		{
			foreach($this->failnet->chans as $key => $channel)
			{
				if($channel == $this->event->get_arg('channel'))
				{
					unset($this->failnet->chans[$key]);
					return;
				}
			}
		}
	}
	
	public function cmd_part()
	{
		if($this->event->get_arg('user') != $this->failnet->get('nick'))
		{
			if (isset($this->failnet->chans[trim(strtolower($this->event->get_arg('channel')))][trim(strtolower($this->event->nick))]))
				unset($this->failnet->chans[trim(strtolower($this->event->get_arg('channel')))][trim(strtolower($this->event->nick))]);
		}
		else
		{
			foreach($this->failnet->chans as $key => $channel)
			{
				if($channel == $this->event->get_arg('channel'))
				{
					unset($this->failnet->chans[$key]);
					return;
				}
			}
		}
	}

	public function cmd_join()
	{
		$this->failnet->chans[trim(strtolower($this->event->get_arg('channel')))][trim(strtolower($this->event->nick))] = self::REGULAR;
	}

	public function cmd_quit()
	{
		foreach($this->failnet->chans as $channame => $chan)
		{
			if(isset($chan[trim(strtolower($this->event->nick))]))
				unset($this->failnet->chans[$channame][trim(strtolower($this->event->nick))]);
		}
	}

	public function cmd_nick()
	{
		foreach($this->failnet->chans as $channame => $chan)
		{
			if(isset($chan[trim(strtolower($this->event->nick))]))
			{
				$data = $chan[trim(strtolower($this->event->nick))];
				unset($this->failnet->chans[$channame][trim(strtolower($this->event->nick))]);
				$this->failnet->chans[$channame][trim($this->event->get_arg('nick'))] = $data;
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
		
		// Make sure we're asking this in channel, or that we have additional params for the channel.
		$param = explode(' ', $text);
		if(!$this->event->fromchannel() && !isset($param[1]))
		{
			$this->call_notice($sender, 'Please specify the channel name to check within.');
			return;
		}
		elseif($this->event->fromchannel() && !isset($param[1]))
		{
			// If in channel and no channel param (we don't want to overwrite a specified channel),
			// 		we assume it is for this channel
			$param[1] = $this->event->source();
		}
		
		// And let's choose a command.
		switch ($cmd)
		{
			case 'isfounder':
				$this->call_notice($sender, $this->failnet->is_founder($param[0], $param[1]) ? 'Yep, they\'re a founder.' : 'Nope, they aren\'t a founder.');
			break;

			case 'isadmin':
				$this->call_notice($sender, $this->failnet->is_admin($param[0], $param[1]) ? 'Yep, they\'re an admin.' : 'Nope, they aren\'t an admin.');
			break;

			case 'isop':
				$this->call_notice($sender, $this->failnet->is_op($param[0], $param[1]) ? 'Yep, they\'re an op.' : 'Nope, they aren\'t an op.');
			break;

			case 'ishalfop':
				$this->call_notice($sender, $this->failnet->is_halfop($param[0], $param[1]) ? 'Yep, they\'re a halfop.' : 'Nope, they aren\'t a halfop.');
			break;

			case 'isvoice':
				$this->call_notice($sender, $this->failnet->is_voice($param[0], $param[1]) ? 'Yep, they have voice.' : 'Nope, they don\'t have voice.');
			break;

			case 'isin':
				$this->call_notice($sender, $this->failnet->is_in($param[0], $param[1]) ? 'Yep, they\'re in here.' : 'Nope, they aren\'t in here.');
			break;
		}
	}
}

?>