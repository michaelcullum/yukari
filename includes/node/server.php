<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * @version:	3.0.0 DEV
 * @copyright:	(c) 2009 - 2010 -- Failnet Project
 * @license:	http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 *
 *===================================================================
 *
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
 *
 */



/**
 * Failnet - Server syncronization class,
 * 		Used as Failnet's server sync handler.
 *
 *
 * @package nodes
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_node_server extends failnet_common
{
	/**
	 * Specialized init function to allow class construction to be easier.
	 * @see includes/failnet_common#init()
	 * @return void
	 */
	public function init() { }

	/**
	 * Get the userlist of a channel
	 * @param string $chan - Channel name
	 * @return mixed - The user list for the channel or false if we don't have the userlist.
	 */
	public function get_users($channel)
	{
		$channel = trim(strtolower($channel));
		if (isset($this->failnet->chans[$channel]))
			return array_keys($this->failnet->chans[$channel]);
		return false;
	}

	/**
	 * Get a random user in a specified channel
	 * @param string $channel - Channel name
	 * @return mixed - Random user's name, or false if we are not in that channel
	 */
	public function random_user($channel)
	{
		$channel = trim(strtolower($channel));
		if (isset($this->failnet->chans[$channel]))
		{
			while(array_search(($nick = array_rand($this->failnet->chans[$channel], 1)), array('chanserv', 'q', 'l', 's')) !== false) {}
			return $nick;
		}
		return false;
	}

	/**
	 * Checks whether or not a user has a specified status (or if $type is NULL it checks if user is in a specified channel)
	 * @param string $nick - The nick for the user that we are checking
	 * @param string $chan - The channel that we are checking in
	 * @return mixed - Will return false if user is not in the channel or if Failnet is not in the channel, will return boolean true if the user is in the channel and Failnet knows it.
	 */
	public function in_channel($nick, $channel)
	{
		$nick = trim(strtolower($nick));
		$channel = trim(strtolower($channel));
		return (isset($this->failnet->chans[$channel])) ? isset($this->failnet->chans[$channel][$nick]) : false;
	}
}
