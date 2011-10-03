<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     irc
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2011 Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Codebite\Yukari\Addon\IRC\Connection;
use \OpenFlame\Framework\Event\Instance as Event;

/**
 * Yukari - IRC Request Argument-mapping class,
 * 	    Provides abstraction of request argument-maps to streamline Yukari's design.
 *
 *
 * @category    Yukari
 * @package     irc
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class RequestMap
{
	/**
	 * @var array - Array containing the arguments map for each IRC command we handle
	 */
	protected $map = array();

	/**
	 * @var array - Array containing the anonymous functions we use to parse and construct our IRC output based on internal events.
	 */
	protected $patterns = array();

	/**
	 * Constructor, instantiates the map and pattern arrays
	 * @return void
	 */
	public function __construct()
	{
		// Declare the arg map.
		$this->map = array(
			'action' => array(
				'target',
				'text',
			),
			'ctcp' => array(
				'target',
				'command',
				'args',
			),
			'ctcp_reply' => array(
				'target',
				'command',
				'args',
			),
			'invite' => array(
				'target',
				'channel',
			),
			'join' => array(
				'channel',
				'key',
			),
			'kick' => array(
				'channel',
				'user',
				'reason',
			),
			'mode' => array(
				'target',
				'flags',
				'args',
			),
			'nick' => array(
				'nick',
			),
			'notice' => array(
				'target',
				'text',
			),
			'part' => array(
				'channel',
				'reason',
			),
			'ping' => array(
				'target',
			),
			'pong' => array(
				'origin',
			),
			'privmsg' => array(
				'target',
				'text',
			),
			'quit' => array(
				'reason',
			),
			'topic' => array(
				'channel',
				'text',
			),
			'whois' => array(
				'target',
			),
			'raw' => array(
				'irc',
			),
		);

		// Declare the anon functions that we'll use to build the raw IRC communication string
		$this->patterns = array(
			'action' => function($target, $text) {
				return sprintf('PRIVMSG %1$s :' . chr(1) . 'ACTION %2$s ' . chr(1), $target, rtrim($text));
			},

			'ctcp' => function($target, $command, $args = NULL) {
				if($args !== NULL)
				{
					return sprintf('PRIVMSG %1$s :' . chr(1) . '%2$s %3$s' . chr(1), $target, strtoupper($command), (is_array($args) ? implode(' ', $args) : $args));
				}
				else
				{
					return sprintf('PRIVMSG %1$s :' . chr(1) . '$2$s' . chr(1), $target, strtoupper($command));
				}
			},

			'ctcp_reply' => function($target, $command, $args = NULL) {
				if($args !== NULL)
				{
					return sprintf('NOTICE %1$s :' . chr(1) . '%2$s %3$s' . chr(1), $target, strtoupper($command), (is_array($args) ? implode(' ', $args) : $args));
				}
				else
				{
					return sprintf('NOTICE %1$s :' . chr(1) . '$2$s' . chr(1), $target, strtoupper($command));
				}
			},

			'invite' => function($target, $channel) {
				return sprintf('INVITE %1$s %2$s', $target, $channel);
			},

			'join' => function($channel, $key = NULL) {
				if($key !== NULL)
				{
					return sprintf('JOIN %1$s :%2$s', $channel, $key);
				}
				else
				{
					return sprintf('JOIN %1$s', $channel);
				}
			},

			'kick' => function($channel, $user, $reason = NULL) {
				if($reason !== NULL)
				{
					return sprintf('KICK %1$s %2$s :%3$s', $channel, $user, $reason);
				}
				else
				{
					return sprintf('KICK %1$s %2$s', $channel, $user);
				}
			},

			'mode' => function($target, $flags, $args = NULL) {
				if($args !== NULL)
				{
					return sprintf('MODE %1$s %2$s %3$s', $target, $flags, (is_array($args) ? implode(' ', $args) : $args));
				}
				else
				{
					return sprintf('MODE %1$s %2$s', $target, $flags);
				}
			},

			'nick' => function($nick) {
				return sprintf('NICK %1$s', $nick);
			},

			'notice' => function($target, $text) {
				return sprintf('NOTICE %1$s :%2$s', $target, rtrim($text));
			},

			'part' => function($channel, $reason = NULL) {
				if($reason !== NULL)
				{
					return sprintf('PART %1$s :%2$s', $channel, $reason);
				}
				else
				{
					return sprintf('PART %1$s', $channel);
				}
			},

			'ping' => function($target) {
				return sprintf('PING %1$s', $target);
			},

			'pong' => function($origin) {
				return sprintf('PONG %1$s', $origin);
			},

			'privmsg' => function($target, $text) {
				return sprintf('PRIVMSG %1$s :%2$s', $target, rtrim($text));
			},

			'quit' => function($reason = NULL) {
				if($reason !== NULL)
				{
					return sprintf('QUIT :%1$s', $reason);
				}
				else
				{
					return 'QUIT';
				}
			},

			'topic' => function($channel, $text = NULL) {
				if($text !== NULL)
				{
					return sprintf('TOPIC %1$s :%2$s', $channel, rtrim($text));
				}
				else
				{
					return sprintf('TOPIC %1$s', $channel);
				}
			},

			'whois' => function($target) {
				return sprintf('WHOIS %1$s', $target);
			},

			'raw' => function($irc) {
				return $irc;
			},
		);
	}

	/**
	 * Get the map of arguments for parsing IRC events with.
	 * @param string $command - The IRC event name.
	 * @return array - The map of arguments to parse the IRC event with.
	 */
	public function getMap($command)
	{
		return (isset($this->map[$command])) ? $this->map[$command] : array();
	}

	/**
	 * Builds the raw IRC to send based on the data stored in the output event.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event containing the data to send.
	 * @return string - The raw IRC to send.
	 */
	public function buildOutput(Event $event)
	{
		// get the event type we're dealing with
		list( , , $event_type) = array_pad(explode('.', $event->getName()), -3, '');

		// build array of params
		$params = array();
		foreach($this->getMap($event_type) as $arg)
		{
			$params[] = ($event->exists($arg)) ? $event->get($arg) : NULL;
		}

		// execute and return the raw IRC string to send.
		return call_user_func_array($this->patterns[$event_type], $params);
	}
}
