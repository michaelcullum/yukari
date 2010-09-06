<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     node
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2010 -- Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @todo camelCase method names
 * @todo convert for new framework
 *
 */

namespace Failnet\Node;
use Failnet as Root;

/**
 * Failnet - Server syncronization class,
 * 	    Used as Failnet's server sync handler.
 *
 *
 * @category    Failnet
 * @package     node
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Server extends Base
{
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
