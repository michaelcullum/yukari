<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.1.0 DEV
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
 * Failnet - Plugin base class,
 * 		Used as the common base class for all of Failnet's plugin class files
 *
 *
 * @package plugins
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
abstract class failnet_plugin_common extends failnet_common
{
/**
 * Common properties
 */

	/**
	 * @var object - Current event instance being processed
	 */
	public $event;

	/**
	 * @var array - Queue of events initiated by the plugin in response to the current event being processed
	 */
	public $events = array();

	/**
	 * @var string - Who shall recieve our messages, if we're using $this->msg ?
	 */
	public $msg_recipient = '';

/**
 * Commonly used plugin methods
 */
	/**
	 * Handler method for plugin load
	 * @return void
	 */
	public function init()
	{
		if(isset($this->failnet->help))
		{
			$this->help($name, $commands);
			if(!empty($name) && !empty($commands))
				$this->failnet->help->collect($name, $commands);
		}
	}

	/**
	 * Checks to see if this is has the proper command prefix for this message
	 * @param string $text - The message to check
	 * @return boolean - Is it using the command prefix?
	 */
	public function prefix($text)
	{
		return (substr($text, 0, strlen($this->failnet->config('cmd_prefix'))) == $this->failnet->config('cmd_prefix'));
	}

	/**
	 * Cleans up the text, returns the actual command being entered, and modifies the entered text also.
	 * @param string &$text - The text to process
	 * @return string - The command to use
	 */
	public function purify(&$text)
	{
		$cmd = $text = substr($text, 1);
		$text = (strpos($text, ' ') !== false) ? substr($text, strpos($text, ' ') + 1) : false;
		return ($text !== false) ? substr($cmd, 0, strpos($cmd, ' ')) : $cmd;
	}

	/**
	 * Sets the arguments for any messages sent in response to events encountered via privmsg.
	 * @param string $recipient - The destination of the messages.
	 * @return void
	 */
	public function set_msg_args($recipient)
	{
		$this->msg_recipient = $recipient;
	}

	/**
	 * A simple wrapper to clean up message sending.
	 * @param string $message - The message to send.
	 * @return void
	 */
	public function msg($message)
	{
		$this->call_privmsg($this->msg_recipient, $message);
	}

/**
 * Handler methods (methods that are intended to be overridden by plugins)
 */

	/**
	 * Handler method for building the dynamic help information
	 * @param string &$name - The name of the plugin.
	 * @param array &$commands - The array of help items, with an entry for each command.
	 */
	public function help(&$name, &$commands)
	{
		$name = '';
		$commands = array();
	}

	/**
	 * Callback dispatched before connections are checked for new events, allowing for the execution of logic that does not require an event to occur.
	 * @return void
	 */
	public function tick() { }

	/**
	 * Callback dispatched right before commands are to be dispatched to the server, allowing plugins to mutate, remove, or reorder events.
	 * @param array &$events - Events to be dispatched
	 * @return void
	 */
	public function pre_dispatch(array &$events) { }

	/**
	 * Callback dispatched right after commands are dispatched to the server,
	 * informing plugins of what events were sent in and in what order.
	 * @param array $events Events that were dispatched
	 * @return void
	 */
	public function post_dispatch(array $events) { }

	/**
	 * Callback dispatched before a handler is called for the current event based on its type.
	 * @return void
	 */
	public function pre_event() { }

	/**
	 * Callback dispatched after a handle is called for the current event based on its type.
	 * @return void
	 */
	public function post_event() { }

	/**
	 * Handler for when the bot connects to the current server.
	 * @return void
	 */
	public function cmd_connect() { }

	/**
	 * Handler for when the bot disconnects from the current server.
	 * @return void
	 */
	public function cmd_disconnect() { }

	/**
	 * Handler for when the client session is about to be terminated.
	 * @return void
	 */
	public function cmd_quit() { }

	/**
	 * Handler for when a user joins a channel.
	 * @return void
	 */
	public function cmd_join() { }

	/**
	 * Handler for when a user leaves a channel.
	 * @return void
	 */
	public function cmd_part() { }

	/**
	 * Handler for when a user sends an invite request.
	 * @return void
	 */
	public function cmd_invite() { }

	/**
	 * Handler for when a user obtains operator privileges.
	 * @return void
	 */
	public function cmd_oper() { }

	/**
	 * Handler for when a channel topic is viewed or changed.
	 * @return void
	 */
	public function cmd_topic() { }

	/**
	 * Handler for when a user or channel mode is changed.
	 * @return void
	 */
	public function cmd_mode() { }

	/**
	 * Handler for when the server prompts the client for a nick.
	 * @return void
	 */
	public function cmd_nick() { }

	/**
	 * Handler for when a message is received from a channel or user.
	 * @return void
	 */
	public function cmd_privmsg() { }

	/**
	 * Handler for when an action is received from a channel or user
	 * @return void
	 */
	public function cmd_action() { }

	/**
	 * Handler for when a notice is received.
	 * @return void
	 */
	public function cmd_notice() { }

	/**
	 * Handler for when a user is kicked from a channel.
	 * @return void
	 */
	public function cmd_kick() { }

	/**
	 * Handler for when the server or a user checks the client connection to ensure activity.
	 * @return void
	 */
	public function cmd_ping() { }

	/**
	 * Handler for when the server sends a CTCP TIME request.
	 * @return void
	 */
	public function cmd_time() { }

	/**
	 * Handler for when the server sends a CTCP VERSION request.
	 * @return void
	 */
	public function cmd_version() { }

	/**
	 * Handler for the reply to a CTCP PING request.
	 * @return void
	 */
	public function cmd_pingreply() { }

	/**
	 * Handler for the reply to a CTCP TIME request.
	 * @return void
	 */
	public function cmd_timereply() { }

	/**
	 * Handler for the reply to a CTCP VERSION request.
	 * @return void
	 */
	public function cmd_versionreply() { }

	/**
	 * Handler for unrecognized CTCP requests.
	 * @return void
	 */
	public function cmd_ctcp() { }

	/**
	 * Handler for unrecognized CTCP responses.
	 * @return void
	 */
	public function cmd_ctcpreply() { }

	/**
	 * Handler for raw requests from the server.
	 * @return void
	 */
	public function cmd_raw() { }

	/**
	 * Handler for when the server sends a kill request.
	 * @return void
	 */
	public function cmd_kill() { }

	/**
	 * Handler for when a server response is received to a client-issued command.
	 * @return void
	 */
	public function cmd_response() { }

	/**
	 * Provides cmd_* methods
	 * @param string $name Name of the method called
	 * @param array $arguments Arguments passed in the call
	 * @return void
	 */
	public function __call($name, array $arguments)
	{
		if(substr($name, 0, 5) == 'call_')
		{
			$type = substr($name, 5);
			if(defined('failnet_event_command::TYPE_' . strtoupper($type)))
			{
				$request = new failnet_event_command();
				$request->load_data($this, $type, $arguments);
				$this->events[] = $request;
			}
		}
		else
		{
			return parent::__call($name, $arguments);
		}
	}
}
