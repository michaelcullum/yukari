<?php


class failnet_event_request implements ArrayAccess
{

	/**
	 * Event types
	 */
	const TYPE_NICK = 'nick';
	const TYPE_WHOIS = 'whois';
	const TYPE_QUIT = 'quit';
	const TYPE_JOIN = 'join';
	const TYPE_KICK = 'kick';
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
	 * Mapping of event types to their named parameters
	 *
	 * @var array
	 */
	protected static $map = array(

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
		)

	);
	
	
	/**
	 * Host name for the originating server or user
	 *
	 * @var string
	 */
	public $host;

	/**
	 * Username of the user from which the event originates
	 *
	 * @var string
	 */
	public $username;

	/**
	 * Nick of the user from which the event originates
	 *
	 * @var string
	 */
	public $nick;

	/**
	 * Request type, which can be compared to the TYPE_* class constants
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Arguments included with the message
	 *
	 * @var array
	 */
	public $arguments;

	/**
	 * The raw buffer that was sent by the server
	 *
	 * @var string
	 */
	public $buffer;

	/**
	 * Returns the channel name or user nick representing the source of the
	 * event.
	 *
	 * @return string
	 */
	public function source()
	{
		if (substr($this->arguments[0], 0, 1) == '#')
			return $this->arguments[0];
		return $this->nick;
	}

	/**
	 * Returns whether or not the event occurred within a channel.
	 *
	 * @return TRUE if the event is in a channel, FALSE otherwise
	 */
	public function fromchannel()
	{
		return (substr($this->getSource(), 0, 1) == '#');
	}
	
	/**
	 * Returns a single specified argument for the request.
	 *
	 * @param mixed $argument Integer position (starting from 0) or the
	 *        equivalent string name of the argument from self::$_map
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
	 * @return int|null Integer position of the argument or NULL if no 
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

?>