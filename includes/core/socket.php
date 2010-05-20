<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version		3.0.0 DEV
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @copyright	(c) 2009 - 2010 -- Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
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
 * Failnet - Socket connection handling class,
 * 		Used as Failnet's connection handler.
 *
 *
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class failnet_socket extends failnet_common
{
	/**
	 * @var integer - The socket timeout setting
	 */
	private $delay = 300;

	/**
	 * @var stream resource - The stream resource used for communicating with the server
	 */
	public $socket = NULL;

	/**
	 * Initiates a connection with the server.
	 * @return void
	 * @throws failnet_exception
	 */
	public function connect()
	{
		// Run indefinitely...
		set_time_limit(0);

		// Check to see if the transport method we are using is allowed
		$transport = failnet::core()->config('use_ssl') ? 'ssl' : 'tcp';
		if(!in_array($transport, stream_get_transports()))
			throw new failnet_exception(failnet_exception::ERR_SOCKET_UNSUPPORTED_TRANSPORT, $transport);

		// Establish and configure the socket connection
		$remote = "$transport://" . failnet::core()->config('server') . ':' . failnet::core()->config('port');
		$this->socket = @stream_socket_client($remote, $errno, $errstr);
		if(!$this->socket)
			throw new failnet_exception(failnet_exception::ERR_SOCKET_ERROR, $errno, $errstr);

		@stream_set_timeout($this->socket, $this->delay);

		// Send the server password if one is specified
		if(failnet::core()->config('server_pass'))
			$this->send('PASS', failnet::core()->config('server_pass'));

		// Send user information
		$this->send('USER', array(failnet::core()->config('user'), failnet::core()->config('server'), failnet::core()->config('server'), failnet::core()->config('name')));
		$this->send('NICK', failnet::core()->config('nick'));
	}

	/**
	 * Listens for an event on the current connection.
	 * @return failnet_event_(response|request)|NULL - Event instance if an event was received, NULL otherwise
	 * @throws failnet_exception
	 */
	public function get()
	{
		// Check for a new event on the current connection
		$buffer = fgets($this->socket, 512);
		if($buffer === false)
			throw new failnet_exception(failnet_exception::ERR_SOCKET_FGETS_FAILED);

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
			$hostmask = failnet_hostmask::load(((strpos($prefix, '@') !== false) ? $prefix : 'unknown' . ((strpos($prefix, '!') === false) ? '!unknown' : '') . '@' . $prefix));
		}
		else // If the event is from the server...
		{
			// Parse the command and arguments
			list($cmd, $args) = array_pad(explode(' ', $buffer, 2), 2, NULL);
			$hostmask = failnet_hostmask::load('server!server@' . failnet::core()->config('server'));
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
			$event = new failnet_event_response;
			$event->code = $cmd;
			$event->description = $args;
			$event->buffer = $buffer;
		}
		else
		{
			$event = new failnet_event_request;
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
	 * @throws failnet_exception
	 */
	public function send($command, $args = '')
	{
		// Require an open socket connection to continue
		if(empty($this->socket))
			throw new failnet_exception(failnet_exception::ERR_SOCKET_NO_CONNECTION);

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
		$success = fwrite($this->socket, $buffer . "\r\n");
		if($success === false)
			throw new failnet_exception(failnet_exception::ERR_SOCKET_FWRITE_FAILED);

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
		failnet::core('ui')->system('-!- Quitting from server "' . failnet::core()->config('server') . '"');
		failnet::core('log')->add('--- Quitting from server "' . failnet::core()->config('server') . '" ---');

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
