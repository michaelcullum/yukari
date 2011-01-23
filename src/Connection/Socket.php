<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
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

namespace Failnet\Connection;
use Failnet\Bot as Bot;
use Failnet\Lib as Lib;

/**
 * Failnet - Socket connection handling class,
 * 	    Used as Failnet's connection handler.
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
	private $timeout = 0.1;

	/**
	 * @var stream resource - The stream resource used for communicating with the server
	 */
	protected $socket = NULL;

	/**
	 * Initiates a connection with the server.
	 * @return void
	 *
	 * @throws Failnet\Connection\SocketException
	 */
	public function connect()
	{
		// Check to see if the transport method we are using is allowed
		$transport = Bot::getOption('socket.use_ssl', false) ? 'ssl' : 'tcp';
		if(!in_array($transport, stream_get_transports()))
			throw new SocketException(sprintf('', $transport), SocketException::ERR_SOCKET_UNSUPPORTED_TRANSPORT);

		// Establish and configure the socket connection
		$remote = "$transport://" . Bot::getOption('server.server_uri', '', true) . ':' . Bot::getOption('server.port', 6667);

		// Try a few times to connect to the server, and if we can't, we dai.
		$attempts = 0;
		do
		{
			if(++$attempts > 5)
				throw new SocketException(sprintf('Socket error encountered: [%1$s] %2$s', $errno, $errstr), SocketException::ERR_SOCKET_ERROR);

			$this->socket = @stream_socket_client($remote, $errno, $errstr);
			if(!$this->socket)
				sleep(5);
		}
		while(!$this->socket);

		stream_set_timeout($this->socket, (int) $this->timeout, (($this->timeout - (int) $this->timeout) * 1000000));

		// Send the server password if one is specified
		if(Bot::getOption('socket.server_pass', ''))
			$this->send('PASS', Bot::getOption('server.server_pass', ''));

		// Send user information
		$this->send('USER', array(Bot::getOption('server.username', 'Failnet'), Bot::getOption('server.server_uri', '', true), Bot::getOption('server.server_uri', '', true), Bot::getOption('server.realname', 'Failnet')));
		$this->send('NICK', Bot::getOption('socket.nickname', '', true));
	}

	/**
	 * Listens for an event on the current connection.
	 * @return Failnet\Event\EventBase - Event instance if an event was received, NULL otherwise
	 *
	 * @throws Failnet\Connection\SocketException
	 */
	public function get()
	{
		// Check for a new event on the current connection
		$attempts = 0;
		do
		{
			if(++$attempts > 5)
				throw new SocketException('fgets() call failed, socket connection lost', SocketException::ERR_SOCKET_FGETS_FAILED);

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
			$hostmask = new Lib\Hostmask('server', Bot::getOption('server.server_uri', '', true), $prefix);
		}
		else
		{
			// Parse the command and arguments
			$hostmask = Lib\Hostmask::load($prefix);
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
			$event = new Failnet\Event\IRC\Response();
			$event['code'] = $cmd;
			$event['description'] = $args;

		}
		else
		{
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
	 * @param Failnet\Event\EventBase $event - Event to send.
	 * @return string - Command string that was sent
	 *
	 * @throws Failnet\Connection\SocketException
	 */
	public function send(Failnet\Event\EventBase $event)
	{
		// Require an open socket connection to continue
		if(empty($this->socket))
			throw new SocketException('Cannot send to server, no connection present', SocketException::ERR_SOCKET_NO_CONNECTION);

		// Make sure this event can be sent in the first place.
		if(!$event->sendable())
			throw new SocketException('Attempt to send unsendable event failed', SocketException::ERR_SOCKET_SEND_UNSENDABLE_EVENT);

		// Get the buffer to write.
		$buffer = $event->buildCommand();

		// Transmit the command over the socket connection
		$attempts = 0;
		do
		{
			if(++$attempts > 5)
				throw new SocketException('fwrite() call failed, socket connection lost', SocketException::ERR_SOCKET_FWRITE_FAILED);

			$success = @fwrite($this->socket, $buffer . "\r\n");
		}
		while(!$success);

		// Return the command string that was transmitted
		return $buffer;
	}

	/**
	 * Terminates the connection with the server.
	 * @param string $reason - Reason for connection termination (optional)
	 * @return void
	 */
	public function close()
	{
		Bot::getObject('core.ui')->system('-!- Quitting from server "' . Bot::getOption('server.server_uri', '', true) . '"');
		Bot::core('log')->add('--- Quitting from server "' . Bot::core()->config('server') . '" ---'); // @todo rewrite

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
	private function args($args, $count = -1)
	{
		return preg_split('/ :?/S', $args, $count);
	}
}
