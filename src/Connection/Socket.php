<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     connection
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2011 -- Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Yukari\Connection;
use Yukari\Kernel;

/**
 * Yukari - Socket connection handling class,
 * 	    Used as Yukari's connection handler.
 *
 *
 * @category    Yukari
 * @package     connection
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Socket
{
	/**
	 * @var float - The socket timeout setting
	 */
	protected $timeout = 0.1;

	/**
	 * @var stream resource - The stream resource used for communicating with the server
	 */
	protected $socket = NULL;

	/**
	 * Initiates a connection with the server.
	 * @return void
	 *
	 * @throws \RuntimeException
	 */
	public function connect()
	{
		// Check to see if the transport method we are using is allowed
		$transport = (Kernel::getConfig('socket.use_ssl') == true) ? 'ssl' : 'tcp';
		if(!in_array($transport, stream_get_transports()))
			throw new \RuntimeException(sprintf('Unsupported transport "%s" specified', $transport));

		// Establish and configure the socket connection
		$remote = sprintf('%1$s://%2$s:%3$s', $transport, Kernel::getConfig('irc.url'), Kernel::getConfig('irc.port'));

		// Try a few times to connect to the server, and if we can't, we dai.
		$attempts = 0;
		do
		{
			if(++$attempts > 5)
				throw new \RuntimeException(sprintf('Socket error encountered: [%1$s] %2$s', $errno, $errstr));

			$this->socket = @stream_socket_client($remote, $errno, $errstr);
			if(!$this->socket)
				sleep(5);
		}
		while(!$this->socket);

		stream_set_timeout($this->socket, (int) $this->timeout, (($this->timeout - (int) $this->timeout) * 1000000));

		// Send the server password if one is specified
		if(Kernel::getConfig('irc.password'))
			$this->send(sprintf('PASS %s', Kernel::getConfig('irc.password')));

		// Send user information
		$this->send(sprintf('USER %1$s %2$s %3$s :%4$s', Kernel::getConfig('irc.username'), Kernel::getConfig('irc.url'), Kernel::getConfig('irc.url'), Kernel::getConfig('irc.realname')));
		$this->send(sprintf('NICK %s', Kernel::getConfig('irc.nickname')));
	}

	/**
	 * Listens for an event on the current connection.
	 * @return \Yukari\Event\Instance - Event instance if an event was received, NULL otherwise
	 *
	 * @throws \RuntimeException
	 */
	public function get()
	{
		// Check for a new event on the current connection
		$attempts = 0;
		do
		{
			if(++$attempts > 5)
				throw new \RuntimeException('fgets() call failed, socket connection lost');

			$buffer = fgets($this->socket, 512);
		}
		while($buffer === false);


		// Strip the trailing newline from the buffer
		$buffer = rtrim($buffer);

		// If no new event was found we will just return NULL
		if (empty($buffer))
			return NULL;

		$prefix = '';
		if(substr($buffer, 0, 1) == ':')
		{
			$chunks = explode(' ', $buffer, 3);
			$prefix = substr(array_shift($chunks), 1);
			$buffer = implode(' ', $chunks);
		}

		list($cmd, $args) = array_pad(explode(' ', $buffer, 2), 2, NULL);

		// Parse the hostmask.
		if(strpos($prefix, '@') === false)
		{
			$hostmask = new \Yukari\Lib\Hostmask('server', Kernel::getConfig('irc.url'), $prefix);
		}
		else
		{
			// Parse the command and arguments
			$hostmask = \Yukari\Lib\Hostmask::load($prefix);
		}

		// Parse the event arguments depending on the event type
		// @todo rewrite for proper parsing using the new event objects
		$cmd = strtolower($cmd);
		switch ($cmd)
		{
			case 'names':
			case 'nick':
			case 'quit':
			case 'ping':
			case 'pong':
			case 'error':
				$args = array_filter(array(ltrim($args, ':')));
			break;

			case 'privmsg':
			case 'notice':
				$args = $this->args($args, 2);
				list($source, $ctcp) = $args;
				if (substr($ctcp, 0, 1) === chr(1) && substr($ctcp, -1) === chr(1))
				{
					$ctcp = substr($ctcp, 1, -1);
					$reply = ($cmd == 'notice');
					list($cmd, $args) = array_pad(explode(' ', $ctcp, 2), 2, array());
					$cmd = strtolower($cmd);
					switch ($cmd)
					{
						case 'version':
						case 'time':
						case 'finger':
						case 'ping':
							if($reply)
								$args = array($args);
						break;
						case 'action':
							$args = array($source, $args);
						break;
					}
				}
				else
				{
					$args = $this->args($args, 2);
				}
			break;

			case 'topic':
			case 'part':
			case 'invite':
			case 'join':
				$args = $this->args($args, 2);
			break;

			case 'kick':
			case 'mode':
				$args = $this->args($args, 3);
			break;

			// Remove the target from responses
			default:
				$args = substr($args, strpos($args, ' ') + 2);
			break;
		}

		// Create, populate, and return an event object
		if(ctype_digit($cmd))
		{
			$event = new \Yukari\Event\IRC\Response();
			$event['code'] = $cmd;
			$event['description'] = $args;

		}
		else
		{
			// @todo rewrite for new event stuff
			$event_class = 'Failnet\\Event\\IRC\\' . ucfirst($cmd);
			$event = new $event_class();
			$event['type'] = $cmd;
			$event['arguments'] = $args;
			if(isset($hostmask))
				$event['hostmask'] = $hostmask;
			$event->channel = $arguments[0];
		}
		$event->buffer = $buffer;

		return $event;
	}

	/**
	 * Handles construction of command strings and their transmission to the server.
	 * @param \Yukari\Event\Instance $event - Event to send.
	 * @return string - Command string that was sent
	 *
	 */
	public function sendEvent(\Yukari\Event\Instance $event)
	{
		// Get the buffer to write.
		$buffer = $event->buildCommand();
		return $this->send($buffer);
	}

	/**
	 * Sends a string of data to the server
	 * @param string $data - The data to send to the server.
	 * @return string - The command string that was sent.
	 *
	 * @throws \LogicException
	 * @throws \RuntimeException
	 */
	public function send($data)
	{
		// Require an open socket connection to continue
		if(empty($this->socket))
			throw new \LogicException('Cannot send to server, no connection present');

		// Make sure this event can be sent in the first place.
		if(!$event->sendable())
			throw new \LogicException('Attempt to send unsendable event failed');

		// Transmit the command over the socket connection
		$attempts = 0;
		do
		{
			if(++$attempts > 5)
				throw new \RuntimeException('fwrite() call failed, socket connection lost');

			$success = @fwrite($this->socket, "{$data}\r\n");

			// slight delay to keep from flooding the server with send requests...we should be polite after all.  :)
			if(!$success)
				usleep(500);
		}
		while(!$success);

		// Return the command string that was transmitted
		return $data;
	}

	/**
	 * Terminates the connection with the server.
	 * @return void
	 */
	public function close()
	{
		if($this->socket === NULL)
			return;

		$dispatcher = Kernel::getDispatcher();
		$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'ui.message.system')
			->setDataPoint('message', sprintf('Quitting from server "%1$s"', Kernel::getConfig('irc.url'))));

		// Terminate the socket connection
		fclose($this->socket);
		$this->socket = NULL;
	}

	/**
	 * Supporting method to parse event argument strings where the last argument may contain a colon.
	 * @param string $args - Argument string to parse
	 * @param integer $count - Optional maximum number of arguments
	 * @return array - Array of argument values
	 */
	final public function args($args, $count = -1)
	{
		return preg_split('/ :?/S', $args, $count);
	}
}
