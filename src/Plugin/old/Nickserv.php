<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		3.0.0 DEV
 * Copyright:	(c) 2009 - 2010 -- Damian Bushong
 * License:		MIT License
 *
 *===================================================================
 *
 */

/**
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */


/**
 * Failnet - Nickserv automatic identification plugin,
 * 		If enabled in the config, on end of MOTD we send an identify message to the nickname services bot to identify.
 *
 *
 * @package plugins
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Damian Bushong
 * @license MIT License
 */
class failnet_plugin_nickserv extends failnet_plugin_common
{
	public function cmd_response()
	{
		if($this->event->code !== failnet_event_response::ERR_NICKNAMEINUSE)
			return;

		// If someone else is using our nick, let's GHOST them out of it.  :)
		if($this->failnet->config('nickbot'))
		{
			$this->call_privmsg($this->failnet->config('nickbot'), 'GHOST ' . $this->failnet->config('nick') . ' ' . $this->failnet->config('pass'));
		}
	}

	public function cmd_notice()
	{
		// Check to see if nickserv is asking for authentication, and if so then we'll give it
		if(strtolower($this->event->hostmask->nick) != strtolower($this->failnet->config('nickbot')))
			return;

		if(preg_match('#^.*nickname is (registered|owned)#i', $this->event->get_arg(1)))
		{
			if(!is_null($this->failnet->config('pass')) && $this->failnet->config('pass'))
				$this->call_privmsg($this->failnet->config('nickbot'), 'IDENTIFY ' . $this->failnet->config('pass'));
		}
		elseif(preg_match('#^.*' . $this->failnet->config('nick') . '.* has been killed#i', $this->event->get_arg(1)))
		{
			$this->call_nick($this->failnet->config('nick'));
		}
	}

	public function cmd_nick()
	{
		// If this is our nick being changed, we should react and change it internally.
		if($this->event->hostmask->nick == $this->failnet->config('nick'))
			$this->failnet->nick = $this->event->get_arg('nick');
	}
}
