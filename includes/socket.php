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
if(!defined('IN_FAILNET')) return;


/**
 * Failnet - Socket connection handling class,
 * 		Used as Failnet's connection handler. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_socket extends failnet_common
{
	/**
	 * Some methods here (actually, quite a few) borrowed from Phergie.
	 * See /README for details.
	 */
	
	private $delay = 50000;
	private $socket;

	/**
	 * Supporting method to parse event argument strings where the last 
	 * argument may contain a colon.
	 *
	 * @param string $args Argument string to parse
	 * @param int $count Optional maximum number of arguments
	 * @return array Array of argument values
	 */
	private function args($args, $count = -1)
	{
		return preg_split('/ :?/S', $args, $count);
	}
	
	/**
	 * Listens for an event on the current connection.
	 *
	 * @return failnet_event_(response|request)|null - Event instance if an event was 
	 *         received, NULL otherwise
	 */
	public function get()
	{
		// Check for a new event on the current connection
		$buffer = fgets($this->_socket, 512);

		// If no new event was found, return NULL
		if (empty($buffer))
		{
			return null;
		}

		// Strip the trailing newline from the buffer
		$buffer = rtrim($buffer);

		// If debugging mode is enabled, output the received event
		if ($this->failnet->debug)
			display($buffer);

		// If the event is from a user...
		if (substr($buffer, 0, 1) == ':')
		{
			// Parse the user hostmask, command, and arguments
			list($prefix, $cmd, $args) = array_pad(explode(' ', ltrim($buffer, ':'), 3), 3, null);
			preg_match('/^([^!@]+)!(?:[ni]=)?([^@]+)@([^ ]+)/', $prefix, $match);
			list(, $nick, $user, $host) = array_pad($match, 4, null);

		
		}
		else // If the event is from the server...
		{
			// Parse the command and arguments
			list($cmd, $args) = array_pad(explode(' ', $buffer, 2), 2, null);
		}

		// Parse the event arguments depending on the event type
		$cmd = strtolower($cmd);
		switch ($cmd) {
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
				if (substr($ctcp, 0, 1) === self::X01 && substr($ctcp, -1) === self::X01) {
					$ctcp = substr($ctcp, 1, -1);
					$reply = ($cmd == 'notice');
					list($cmd, $args) = array_pad(explode(' ', $ctcp, 2), 2, null);
					$cmd = strtolower($cmd);
					switch ($cmd) {
						case 'version':
						case 'time':
							if ($reply)
								$args = $ctcp;
						case 'ping':
							if ($reply)
								$cmd .= 'Reply';
						case 'action':
							$args = array($nick, $args);
						break;

						default:
							$cmd = 'ctcp';
							if ($reply)
								$cmd .= 'Reply';
							$args = array($nick, $ctcp);
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
		if (ctype_digit($cmd))
		{
			$event = new failnet_event_response;
			$event->code = strtolower($cmd);
			$event->description = $args;
			$event->buffer = $buffer;
		}
		else
		{
			$event = new failnet_event_request;
			$event->type = $cmd;
			$event->arguments = $args;
			if (isset($user))
			{
				$event->host = $host;
				$event->username = $user;
				$event->nick = $nick;
			}
			$event->buffer = $buffer;
		}
		return $event;
	}
	
	/**
	 * Initiates a connection with the server.
	 *
	 * @return void
	 */
	public function connect()
	{
		// Listen for input indefinitely
		set_time_limit(0);

		// Establish and configure the socket connection
		$remote = 'tcp://' . $this->failnet->server . ':' . $this->failnet->port;
		$this->socket = @stream_socket_client($remote, $errno, $errstr);
		if (!$this->socket)
			$this->failnet->error->error('Unable to connect to server: socket error ' . $errno . ' : ' . $errstr, true);
		
		@stream_set_timeout($this->socket, $this->delay);

		// Send the password if one is specified
		if (!empty($this->server_password))
			$this->send('PASS', $this->server_password);

		// Send user information
		$this->send('USER', array($this->user, $this->server, $this->server, $this->name));

		$this->send('NICK', $this->nick); 
	}
	
	/**
	 * Handles construction of command strings and their transmission to the 
	 * server.
	 *
	 * @param string $command Command to send
	 * @param string|array $args Optional string or array of sequential 
	 *        arguments
	 * @return string Command string that was sent 
	 */
	private function send($command, $args = '')
	{
		// Require an open socket connection to continue
		if (empty($this->socket))
			$this->failnet->error->error('failnet_connection::connect() must be called first', true);

		$buffer = strtoupper($command);
		// Add arguments
		if (!empty($args))
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
		fwrite($this->socket, $buffer . PHP_EOL);

		// If debugging mode is enabled, output the transmitted command
		if ($this->failnet->debug)
			display($buffer);

		// Return the command string that was transmitted
		return $buffer;
	}
	
	/**
	 * Terminates the connection with the server.
	 *
	 * @param string $reason Reason for connection termination (optional)
	 * @return void
	 */
	public function quit($reason = NULL)
	{
		// Send a QUIT command to the server
		$this->send('QUIT', $reason);

		// Terminate the socket connection
		fclose($this->socket);
		$this->socket = NULL;
	}
}

?>