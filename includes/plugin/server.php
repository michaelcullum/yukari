<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 1
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
 * Failnet - Server communication plugin,
 * 		Used to track what channels Failnet is in, the users inhabiting them, along with various other default server interactions.
 *
 *
 * @package plugins
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_plugin_server extends failnet_plugin_common
{
	public function cmd_response()
	{
		switch($this->event->code)
		{
			case failnet_event_response::RPL_ENDOFNAMES:
				$chanargs = explode(' ', $this->event->description);

				// Only do the intro message if we're allowed to speak.
				if($this->failnet->speak)
					$this->call_privmsg($chanargs[0], $this->failnet->config('intro_msg'));
			break;

			case failnet_event_response::RPL_NAMREPLY:
				$desc = preg_split('/[@*=]\s*/', $this->event->description, 2);
				list($chan, $users) = array_pad(explode(' :', trim($desc[1])), 2, null);
				$users = explode(' ', trim($users));
				//$current_time = time();
				foreach($users as $user)
				{
					if (empty($user))
						continue;

					$chan = trim(strtolower($chan));
					$user = trim(trim(strtolower($user)), '~&@%+');

					$this->failnet->chans[$chan][$user] = true;
				}
			break;
		}
	}

	public function cmd_kick()
	{
		// Check to see if it was us that got kicked.
		if($this->event->hostmask->nick != $this->failnet->get('nick'))
		{
			$chan = trim(strtolower($this->event->get_arg('channel')));
			$nick = trim(strtolower($this->event->hostmask->nick));

			if (isset($this->failnet->chans[$chan][$nick]))
				unset($this->failnet->chans[$chan][$nick]);
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
			$chan = trim(strtolower($this->event->get_arg('channel')));
			$nick = trim(strtolower($this->event->hostmask->nick));

			if (isset($this->failnet->chans[$chan][$nick]))
				unset($this->failnet->chans[$chan][$nick]);
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
		$chan = trim(strtolower($this->event->get_arg('channel')));
		$nick = trim(strtolower($this->event->hostmask->nick));

		$this->failnet->chans[$chan][$nick] = true;
	}

	public function cmd_quit()
	{
		$chan = trim(strtolower($this->event->get_arg('channel')));
		$nick = trim(strtolower($this->event->hostmask->nick));

		foreach($this->failnet->chans as $channame => $chan)
		{
			if(isset($chan[$nick]))
				unset($this->failnet->chans[$channame][$nick]);
		}
	}

	public function cmd_nick()
	{
		$nick = trim(strtolower($this->event->hostmask->nick));
		$new_nick = trim(strtolower($this->event->get_arg('nick')));

		foreach($this->failnet->chans as $channame => $chan)
		{
			if(isset($chan[$nick]))
			{
				$data = $chan[$nick];
				unset($this->failnet->chans[$channame][$nick]);
				$this->failnet->chans[$channame][$new_nick] = $data;
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
		$this->set_msg_args(($this->failnet->get('speak')) ? $this->event->source() : $this->event->hostmask->nick);

		$sender = $this->event->hostmask->nick;
		$hostmask = $this->event->hostmask;
		switch($cmd)
		{
			case 'isin':
				if($text === false && $this->event->fromchannel() === true)
				{
					list($victim, $channel) = array($text, $this->event->source());
				}
				elseif($text !== false)
				{
					list($victim, $channel) = explode(' ', $text);
				}

				$this->msg($this->failnet->server->in_channel($param[0], $channel) ? 'Yep, I see them.' : 'Nope, I don\'t see them.');
			break;
		}
	}

	/**
	 * Critical method here...this replies to pings to the server, and keeps the connection alive.
	 */
	public function cmd_ping()
	{
		if(isset($this->event->arguments[1]))
		{
			$this->call_ping($this->event->arguments[0], $this->event->arguments[1]);
		}
		else
		{
			$this->call_pong($this->event->arguments[0]);
		}
	}
}
