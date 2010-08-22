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
 * Failnet - Karma plugin,
 * 		This allows users to increase or decrease something's karma, and find out its karma.
 *
 *
 * @package plugins
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Damian Bushong
 * @license MIT License
 */
class failnet_plugin_karma extends failnet_plugin_common
{
	public function help(&$name, &$commands)
	{
		$name = 'karma';
		$commands = array(
			'karma'			=> 'karma {$name} - (no auth) - Returns the karma level for the specified user/object/whatever',
		);
	}

	public function cmd_privmsg()
	{
		// Check for karma changes first
		$text = $this->event->get_arg('text');
		$sender = $this->event->hostmask->nick;
		$this->set_msg_args(($this->failnet->config('speak')) ? $this->event->source() : $this->event->hostmask->nick);
		if($this->event->fromchannel === true && $this->failnet->karma->check_word($text))
		{
			$term = strtolower(trim($text));
			$karma_type = substr($term, -2, 2);
			$victim = substr($term, 0, strlen($term) - 2);
			if($karma_type == '++' && $victim != strtolower($sender))
			{
				$results = $this->failnet->karma->set_karma($victim, failnet_karma::KARMA_INCREASE);
				return;
			}
			elseif($karma_type == '--' && $victim != strtolower($sender))
			{
				$results = $this->failnet->karma->set_karma($victim, failnet_karma::KARMA_DECREASE);
				return;
			}
		}

		// Process the command
		if(!$this->prefix($text))
			return;

		$cmd = $this->purify($text);
		switch ($cmd)
		{
			case 'karma':
				// Make sure we're getting the karma for SOMETHING.
				if(empty($text))
				{
					$this->msg($this->event->hostmask->nick . ': You know you DO need to specify something, right?');
					return;
				}

				// Okay, let's get that karma.
				$term = strtolower(trim($text));
				if($this->failnet->karma->check_word($text))
				{
					return;
				}

				$karma = $this->failnet->karma->get_karma($term);
				if(is_null($karma))
				{
					$this->msg(sprintf('%s has a karma of 0.', $term));
				}
				elseif(is_int($karma))
				{
					$this->msg(sprintf('%s has a karma of ' . $karma . '.', $term));
				}
				else
				{
					$this->msg($karma);
				}
			break;
		}
	}
}