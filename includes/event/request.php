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
 * Failnet - IRC request event handling class
 *
 *
 * @package connection
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 *
 * @see http://www.irchelp.org/irchelp/rfc/chapter4.html
 */
class failnet_event_request implements ArrayAccess
{
	/**
	 * Constants for Failnet.
	 */
	const HR = '---------------------------------------------------------------------';
	const ERROR_LOG = 'error';
	const USER_LOG = 'user';

	/**
	 * Event types
	 */
	const TYPE_NICK = 'nick';
	const TYPE_WHOIS = 'whois';
	const TYPE_QUIT = 'quit';
	const TYPE_JOIN = 'join';
	const TYPE_KICK = 'kick';
	const TYPE_INVITE = 'invite';
	const TYPE_PART = 'part';
	const TYPE_MODE = 'mode';
	const TYPE_TOPIC = 'topic';
	const TYPE_PRIVMSG = 'privmsg';
	const TYPE_NOTICE = 'notice';
	const TYPE_PONG = 'pong';
	const TYPE_ACTION = 'action';
	const TYPE_PING = 'ping';
	const TYPE_TIME = 'time';
	const TYPE_VERSION = 'version';
	const TYPE_RAW = 'raw';

	/**
	 * @var array - Mapping of event types to their named parameters
	 */
	protected static $map = array(

		self::TYPE_NICK => array(
			'nick' => 0
		),

		self::TYPE_QUIT => array(
			'message' => 0
		),

		self::TYPE_JOIN => array(
			'channel' => 0
		),

		self::TYPE_KICK => array(
			'channel' => 0,
			'user'    => 1,
			'comment' => 2
		),

		self::TYPE_INVITE => array(
			'user' => 0,
			'channel' => 1,
		),

		self::TYPE_PART => array(
			'channel' => 0,
			'message' => 1
		),

		self::TYPE_MODE => array(
			'target'  => 0,
			'mode'    => 1,
			'limit'   => 2,
			'user'    => 3,
			'banmask' => 4
		),

		self::TYPE_TOPIC => array(
			'channel' => 0,
			'topic'   => 1
		),

		self::TYPE_PRIVMSG => array(
			'receiver' => 0,
			'text'     => 1
		),

		self::TYPE_NOTICE => array(
			'nickname' => 0,
			'text'     => 1
		),

		self::TYPE_ACTION => array(
			'target' => 0,
			'action' => 1
		),

		self::TYPE_RAW => array(
			'message' => 0
		),

	);


	/**
	 * @var object failnet_hostmask - Hostmask for the originating server or user
	 */
	public $hostmask;

	/**
	 * @var boolean - Is this event from a channel?
	 */
	public $fromchannel = false;

	/**
	 * @var string - Request type, which can be compared to the TYPE_* class constants
	 */
	public $type;

	/**
	 * @var array - Arguments included with the message
	 */
	public $arguments;

	/**
	 * @var string - The raw buffer that was sent by the server
	 */
	public $buffer;

	/**
	 * Constructor
	 * @return void
	 */
	public function __construct() { }

	public function __call($name, array $arguments)
	{
		trigger_error('Call to undefined method "' . $name . '" in class "' . __CLASS__ . '"', E_USER_WARNING);
	}

	/**
	 * Returns the channel name or user nick representing the source of the event.
	 *
	 * @return string
	 */
	public function source()
	{
		return ($this->fromchannel) ? $this->arguments[0] : $this->hostmask->nick;
	}

	/**
	 * Returns whether or not the event occurred within a channel.
	 *
	 * @return TRUE if the event is in a channel, FALSE otherwise
	 */
	public function fromchannel()
	{
		$trace = debug_backtrace();
		trigger_error('Depreciated method failnet_event_request::fromchannel() called (the property failnet_event_request::fromchannel should be used instead) in ' . $trace[0]['file'] .' on line ' . $trace[0]['line'], E_USER_NOTICE);
		return $this->fromchannel;
	}

	/**
	 * Returns a single specified argument for the request.
	 *
	 * @param mixed $argument Integer position (starting from 0) or the
	 *        equivalent string name of the argument from self::$map
	 * @return string
	 */
	public function get_arg($argument)
	{
		$argument = $this->resolve_arg($argument);
		if ($argument !== NULL)
			return $this->arguments[$argument];
		return NULL;
	}

	/**
	 * Resolves an argument specification to an integer position.
	 *
	 * @param mixed $argument Integer position (starting from 0) or the
	 *        equivalent string name of the argument from self::$_map
	 * @return int|NULL Integer position of the argument or NULL if no
	 *         corresponding argument was found
	 */
	private function resolve_arg($argument)
	{
		if (isset($this->arguments[$argument]))
		{
			return $argument;
		}
		else
		{
			$argument = strtolower($argument);
			if (isset(self::$map[$this->type][$argument]) && isset($this->arguments[self::$map[$this->type][$argument]]))
				return self::$map[$this->type][$argument];
		}
		return NULL;
	}

	/**
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset)
	{
		return ($this->resolve_arg($offset) !== NULL);
	}

	/**
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset)
	{
		return $this->get_arg($offset);
	}

	/**
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value)
	{
		$offset = $this->resolve_arg($offset);
		if ($offset !== NULL)
			$this->arguments[$offset] = $value;
	}

	/**
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset)
	{
		if ($offset = $this->resolve_arg($offset))
			unset($this->arguments[$offset]);
	}
}
