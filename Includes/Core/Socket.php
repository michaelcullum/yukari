<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     core
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
 */

namespace Failnet\Core;
use Failnet;
use Failnet\Lib;

/**
 * Failnet - Socket connection handling class,
 * 	    Used as Failnet's connection handler.
 *
 *
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Socket extends Base
{
	/**
	 * @var float - The socket timeout setting
	 */
	private $timeout = 0.1;

	/**
	 * @var stream resource - The stream resource used for communicating with the server
	 */
	public $socket = NULL;

	/**
	 * Initiates a connection with the server.
	 * @return void
	 * @throws Failnet\Exception
	 */
	public function connect()
	{

		// Check to see if the transport method we are using is allowed
		$transport = Bot::core()->config('use_ssl') ? 'ssl' : 'tcp';
		if(!in_array($transport, stream_get_transports()))
			throw new Exception(ex(Exception::ERR_SOCKET_UNSUPPORTED_TRANSPORT, $transport));

		// Establish and configure the socket connection
		$remote = "$transport://" . Bot::core()->config('server') . ':' . Bot::core()->config('port');

		// Try a few times to connect to the server, and if we can't, we dai.
		$attempts = 0;
		do
		{
			if(++$attempts > 5)
				throw new Exception(ex(Exception::ERR_SOCKET_ERROR, array($errno, $errstr)));

			$this->socket = @stream_socket_client($remote, $errno, $errstr);
			if(!$this->socket)
				sleep(5);
		}
		while(!$this->socket);

		stream_set_timeout($this->socket, (int) $this->timeout, (($this->timeout - (int) $this->timeout) * 1000000));

		// Send the server password if one is specified
		if(Bot::core()->config('server_pass'))
			$this->send('PASS', Bot::core()->config('server_pass'));

		// Send user information
		$this->send('USER', array(Bot::core()->config('user'), Bot::core()->config('server'), Bot::core()->config('server'), Bot::core()->config('name')));
		$this->send('NICK', Bot::core()->config('nick'));
	}

	/**
	 * Listens for an event on the current connection.
	 * @return failnet_event_(response|request)|NULL - Event instance if an event was received, NULL otherwise
	 * @throws Failnet\Exception
	 */
	public function get()
	{
		// Check for a new event on the current connection
		$buffer = fgets($this->socket, 512);
		if($buffer === false)
			throw new Exception(ex(Exception::ERR_SOCKET_FGETS_FAILED));

		// If no new event was found, return NULL
		if (empty($buffer))
			return NULL;

		// Strip the trailing newline from the buffer
		$buffer = rtrim($buffer);

		// If the event is from a user...
		if(substr($buffer, 0, 1) == ':')
		{
			// Parse the user hostmask, command, and arguments
			list($prefix, $cmd, $args) = array_pad(explode(' ', ltrim($buffer, ':'), 3), 3, NULL);
			$hostmask = Hostmask::load(((strpos($prefix, '@') !== false) ? $prefix : 'unknown' . ((strpos($prefix, '!') === false) ? '!unknown' : '') . '@' . $prefix));
		}
		else // If the event is from the server...
		{
			// Parse the command and arguments
			list($cmd, $args) = array_pad(explode(' ', $buffer, 2), 2, NULL);
			$hostmask = Hostmask::load('server!server@' . Bot::core()->config('server'));
		}

		// Parse the event arguments depending on the event type
		$cmd = strtolower($cmd);
		switch ($cmd)
		{
			case 'names':
			case 'nick':
			case 'quit':
			case 'ping':
			case 'join':
			case 'error':
				$args = array(ltrim($args, ':'));
			break;

			case 'privmsg':
			case 'notice':
				$ctcp = substr(strstr($args, ':'), 1);
				if (substr($ctcp, 0, 1) === chr(1) && substr($ctcp, -1) === chr(1))
				{
					$ctcp = substr($ctcp, 1, -1);
					$reply = ($cmd == 'notice');
					list($cmd, $args) = array_pad(explode(' ', $ctcp, 2), 2, NULL);
					$cmd = strtolower($cmd);
					switch ($cmd)
					{
						case 'version':
						case 'time':
							if ($reply)
								$args = $ctcp;
						case 'ping':
							if ($reply)
								$cmd .= 'reply';
						case 'action':
							$args = array($hostmask->nick, $args);
						break;

						default:
							$cmd = 'ctcp';
							if ($reply)
								$cmd .= 'reply';
							$args = array($hostmask->nick, $ctcp);
						break;
					}
				}
				else
				{
					$args = $this->args($args, 2);
				}
			break;

			case 'oper':
			case 'topic':
			case 'mode':
				$args = $this->args($args);
			break;

			case 'part':
			case 'kill':
			case 'invite':
				$args = $this->args($args, 2);
			break;

			case 'kick':
				$args = $this->args($args, 3);
			break;

			// Remove the target from responses
			default:
				$args = substr($args, strpos($args, ' ') + 1);
			break;
		}

		// Create, populate, and return an event object
		if(ctype_digit($cmd))
		{
			$event = new Failnet\Event\Response;
			$event->code = $cmd;
			$event->description = $args;
			$event->buffer = $buffer;
		}
		else
		{
			$event = new Failnet\Event\Request;
			$event->type = $cmd;
			$event->arguments = $args;
			if (isset($hostmask))
				$event->hostmask = $hostmask;
			$event->fromchannel = (substr($event->arguments[0], 0, 1) == '#') ? true : false;
			$event->buffer = $buffer;
		}
		return $event;
	}

	/**
	 * Handles construction of command strings and their transmission to the server.
	 * @param string $command - Command to send
	 * @param mixed $args - Optional string or array of sequential arguments
	 * @return string - Command string that was sent
	 * @throws Failnet\Exception
	 */
	public function send($command, $args = '')
	{
		// Require an open socket connection to continue
		if(empty($this->socket))
			throw new Exception(ex(Exception::ERR_SOCKET_NO_CONNECTION));

		$buffer = strtoupper($command);
		// Add arguments
		if(!empty($args))
		{
			// Apply formatting if arguments are passed in as an array
			if (is_array($args))
			{
				$end = count($args) - 1;
				$args[$end] = ':' . $args[$end];
				$args = implode(' ', $args);
			}
			$buffer .= ' ' . $args;
		}

		// Transmit the command over the socket connection
		$attempts = 0;
		do
		{
			if(++$attempts > 5)
				throw new Exception(ex(Exception::ERR_SOCKET_FWRITE_FAILED));

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
		Bot::core('ui')->system('-!- Quitting from server "' . Bot::core()->config('server') . '"');
		Bot::core('log')->add('--- Quitting from server "' . Bot::core()->config('server') . '" ---');

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
