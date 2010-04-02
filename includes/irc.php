<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version		2.1.0 DEV
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
 * Failnet - IRC class,
 * 		Used as Failnet's IRC command class, for issuing IRC commands correctly as per RFC guidelines.
 *
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class failnet_irc extends failnet_common
{
	/**
	 * Some methods here (actually, quite a few) borrowed from Phergie.
	 * See /README for more information.
	 */

	/**
	 * Joins a channel.
	 * @param string $channels - Comma-delimited list of channels to join
	 * @param string $keys - Optional comma-delimited list of channel keys
	 * @return void
	 */
	public function join($channel, $key = NULL)
	{
		$args = array($channel);

		if (!empty($key))
			$args[] = $key;

		$this->failnet->socket->send('JOIN', $args);
	}

	/**
	 * Leaves a channel.
	 * @param string $channels - Comma-delimited list of channels to leave
	 * @return void
	 */
	public function part($channel, $reason = NULL)
	{
		$args = array($channel);

		if (!empty($reason))
			$args[] = $reason;

		$this->failnet->socket->send('PART', $args);
	}

	/**
	 * Invites a user to an invite-only channel.
	 * @param string $nick - Nick of the user to invite
	 * @param string $channel - Name of the channel
	 * @return void
	 */
	public function invite($nick, $channel)
	{
		$this->failnet->socket->send('INVITE', array($nick, $channel));
	}

	/**
	 * Obtains a list of nicks of usrs in currently joined channels.
	 * @param string $channels - Comma-delimited list of one or more channels
	 * @return void
	 */
	public function names($channels)
	{
		$this->failnet->socket->send('NAMES', $channels);
	}

	/**
	 * Obtains a list of channel names and topics.
	 * @param string $channels - Comma-delimited list of one or more channels
	 *                         to which the response should be restricted
	 *                         (optional)
	 * @return void
	 */
	public function channels($channels = NULL)
	{
		$this->failnet->socket->send('LIST', $channels);
	}

	/**
	 * Retrieves or changes a channel topic.
	 * @param string $channel - Name of the channel
	 * @param string $topic - New topic to assign (optional)
	 * @return void
	 */
	public function topic($channel, $topic = NULL)
	{
		$args = array($channel);

		if (!empty($topic))
			$args[] = $topic;

		$this->failnet->socket->send('TOPIC', $args);
	}

	/**
	 * Retrieves or changes a channel or user mode.
	 * @param string $target - Channel name or user nick
	 * @param string $mode - New mode to assign (optional)
	 * @return void
	 */
	public function mode($target, $mode = NULL)
	{
		$args = array($target);

		if (!empty($mode))
			$args[] = $mode;

		$this->failnet->socket->send('MODE', $args);
	}

	/**
	 * Changes the client nick.
	 * @param string $nick - New nick to assign
	 * @return void
	 */
	public function nick($nick)
	{
		$this->failnet->socket->send('NICK', $nick);
	}

	/**
	 * Retrieves information about a nick.
	 * @param string $nick - Nick to lookup
	 * @return void
	 */
	public function whois($nick)
	{
		$this->failnet->socket->send('WHOIS', $nick);
	}

	/**
	 * Sends a message to a nick or channel.
	 * @param string $target - Channel name or user nick
	 * @param string $text - Text of the message to send
	 * @return void
	 */
	public function privmsg($target, $text)
	{
		$this->failnet->socket->send('PRIVMSG', array($target, $text));
	}

	/**
	 * Sends a notice to a nick or channel.
	 * @param string $target - Channel name or user nick
	 * @param string $text - Text of the notice to send
	 * @return void
	 */
	public function notice($target, $text)
	{
		$this->failnet->socket->send('NOTICE', array($target, $text));
	}

	/**
	 * Kicks a user from a channel.
	 * @param string $nick - Nick of the user
	 * @param string $channel - Channel name
	 * @param string $reason - Reason for the kick (optional)
	 * @return void
	 */
	public function kick($nick, $channel, $reason = NULL)
	{
		$args = array($nick, $channel);

		if (!empty($reason))
			$args[] = $reason;

		$this->failnet->socket->send('KICK', $args);
	}

	/**
	 * Responds to a server test of client responsiveness.
	 * @param string $daemon - Daemon from which the original request originates
	 * @return void
	 */
	public function pong($daemon)
	{
		$this->failnet->socket->send('PONG', $daemon);
	}

	/**
	 * Sends a CTCP response to a user.
	 * @param string $nick - User nick
	 * @param string $command - Command to send
	 * @param string|array $args - String or array of sequential arguments
	 *        (optional)
	 * @return void
	 */
	private function ctcp_response($nick, $command, $args = NULL)
	{
		if (is_array($args))
			$args = implode(' ', $args);

		$buffer = rtrim(strtoupper($command) . ' ' . $args);

		$this->notice($nick, chr(1) . $buffer . chr(1));
	}

	/**
	 * Sends a CTCP ACTION (/me) command to a nick or channel.
	 * @param string $target - Channel name or user nick
	 * @param string $text - Text of the action to perform
	 * @return void
	 */
	public function action($target, $text)
	{
		$this->privmsg($target, chr(1) . 'ACTION ' . rtrim($text) . ' ' . chr(1));
	}

	/**
	 * Sends a CTCP PING response to a user.
	 * @param string $nick - User nick
	 * @param string $hash - PING hash to use in the handshake
	 * @return void
	 */
	public function ping($nick, $hash)
	{
		$this->ctcp_response($nick, 'PING', $hash);
	}

	/**
	 * Sends a CTCP VERSION response to a user.
	 * @param string $nick - User nick
	 * @param string $version - Version string to send
	 * @return void
	 */
	public function version($nick, $version)
	{
		$this->ctcp_response($nick, 'VERSION', $version);
	}

	/**
	 * Sends a CTCP TIME response to a user.
	 * @param string $user - User nick
	 * @param string $time - Time string to send
	 * @return void
	 */
	public function time($nick, $time)
	{
		$this->ctcp_response($nick, 'TIME', $time);
	}

	/**
	 * Sends a raw command to the server.
	 * @param string $command - Command string to send
	 * @return void
	 */
	public function raw($command)
	{
		$this->failnet->socket->send('RAW', $command);
	}

	/**
	 * Sends a quit command to the server
	 * @param string $reason - The quit reason if any is available
	 * @return void
	 */
	public function quit($reason = NULL)
	{
		$this->failnet->socket->send('QUIT', array($reason));
		$this->failnet->socket->close();
	}
}
