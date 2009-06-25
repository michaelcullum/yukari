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
 * Copyright:	(c) 2009 - Obsidian
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
 * Failnet - Connection status detection plugin,
 * 		Used to ping the server periodically to ensure that the client connection has not been dropped. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_plugin_ping extends failnet_plugin_common
{
	/**
	 * Timestamp for the last instance in which an event was received
	 *
	 * @var int
	 */
	private $last_event;

	/**
	* Timestamp for the last instance in which a PING was sent
	*
	* @var int
	*/
	private $last_ping;

	/**
	* Initialize event timestamps upon connecting to the server.
	*
	* @return void
	*/
	public function call_connect()
	{
		$this->last_event = time();
		$this->last_ping = NULL;
	}

	/**
	* Updates the timestamp since the last received event when a new event 
	* arrives.
	*
	* @return void
	*/
	public function pre_event()
	{
		$this->last_event = time();
	}

	/**
	* Clears the ping time if a reply is received.
	*
	* @return void
	*/
	public function cmd_pingreply()
	{
		$this->last_ping = NULL;
	}

	/**
	* Performs a self ping if the event threshold has been exceeded or 
	* issues a termination command if the ping theshold has been exceeded. 
	*
	* @return void
	*/
	public function tick()
	{
		$time = time();
		
		if(!empty($this->last_ping) && $time - $this->last_ping > $this->failnet->get('ping_timeout'))
		{
			$this->failnet->log->add('--- Ping timeout, restarting Failnet ---');
			$this->failnet->terminate(true);
		}
		elseif($time - $this->last_event > $this->failnet->get('ping_wait'))
		{
			$this->last_ping = time();
			$this->call_ping($this->failnet->get('nick')';
		}
	}
}
