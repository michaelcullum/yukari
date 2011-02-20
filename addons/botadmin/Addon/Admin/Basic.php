<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     addon
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

namespace Yukari\Addon\Admin;
use Yukari\Kernel;

/**
 * Yukari - Commander addon interpreter object,
 *      Checks incoming privmsg events and sees if they are commands intended for the bot, and if so extended events are issued accordingly.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Basic
{
	/**
	 * Register the listeners we need for this addon to work properly.
	 * @return \Yukari\Addon\Admin\Basic - Provides a fluent interface.
	 */
	public function registerListeners()
	{
		$dispatcher = Kernel::getDispatcher();
		$dispatcher->register('irc.input.command.join', array(Kernel::get('addon.botadmin'), 'handleJoinCommand'))
			->register('irc.input.command.part', array(Kernel::get('addon.botadmin'), 'handlePartCommand'))
			->register('irc.input.command.op', array(Kernel::get('addon.botadmin'), 'handleSetUserChannelMode'), array('+o'))
			->register('irc.input.command.deop', array(Kernel::get('addon.botadmin'), 'handleSetUserChannelMode'), array('-o'))
			->register('irc.input.command.voice', array(Kernel::get('addon.botadmin'), 'handleSetUserChannelMode'), array('+v'))
			->register('irc.input.command.devoice', array(Kernel::get('addon.botadmin'), 'handleSetUserChannelMode'), array('-v'))
			->register('irc.input.command.listaddons', array(Kernel::get('addon.botadmin'), 'handleListaddonsCommand'))
			->register('irc.input.command.quit', array(Kernel::get('addon.botadmin'), 'handleQuitCommand'));

		return $this;
	}

	public function handleJoinCommand(\Yukari\Event\Instance $event)
	{
		// Check auths first
		if(!$this->checkAuthentication($event['hostmask']))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			$highlight = (!$event['is_private']) ? $event['hostmask']['nick'] . ':' : '';
			if($event['text'][0] !== '#')
			{
				$results[] = \Yukari\Event\Instance::newEvent(null, 'irc.output.privmsg')
					->setDataPoint('target', $event['target'])
					->setDataPoint('text', sprintf('%1$s Invalid channel specified.', $highlight));

				return $results;
			}
			else
			{
				$join_params = explode(' ', $event['text'], 2);
				$join = \Yukari\Event\Instance::newEvent(null, 'irc.output.join')
					->setDataPoint('channel', $join_params[0]);

				if(isset($join_params[1]))
					$join->setDataPoint('key', $join_params[1]);

				return $join;
			}
		}
	}

	public function handlePartCommand(\Yukari\Event\Instance $event)
	{
		// Check auths first
		if(!$this->checkAuthentication($event['hostmask']))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			$highlight = (!$event['is_private']) ? $event['hostmask']['nick'] . ':' : '';
			if($event['text'][0] !== '#')
			{
				$results[] = \Yukari\Event\Instance::newEvent(null, 'irc.output.privmsg')
					->setDataPoint('target', $event['target'])
					->setDataPoint('text', sprintf('%1$s Invalid channel specified.', $highlight));

				return $results;
			}
			else
			{
				$part_params = explode(' ', $event['text'], 2);
				$part = \Yukari\Event\Instance::newEvent(null, 'irc.output.part')
					->setDataPoint('channel', $part_params[0]);

				if(isset($part_params[1]))
					$part->setDataPoint('reason', $part_params[1]);

				return $part;
			}
		}
	}

	public function handleKickCommand(\Yukari\Event\Instance $event)
	{
		// asdf
	}

	public function handleSetUserChannelMode(\Yukari\Event\Instance $event, $mode)
	{
		// Check auths first
		if(!$this->checkAuthentication($event['hostmask']))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			$highlight = (!$event['is_private']) ? $event['hostmask']['nick'] . ':' : '';
			if(preg_match('#[\!\#\@]#i', $event['text']))
			{
				$results[] = \Yukari\Event\Instance::newEvent(null, 'irc.output.privmsg')
					->setDataPoint('target', $event['target'])
					->setDataPoint('text', sprintf('%1$s Invalid nickname specified.', $highlight));

				return $results;
			}
			else
			{
				// send the mode command
				$params = explode(' ', $event['text'], 2);

				// if the user wants to specify a channel, let them do so...and then fall back to the current channel if no channel is specified
				if($params[0][0] !== '#')
				{
					list($channel, $user) = $params;
				}
				elseif(isset($params[1]) && $params[1][0] !== '#')
				{
					list($user, $channel) = $params;
				}
				else
				{
					// if this was a private command, we must derp at the sender.
					if($event['is_private'])
					{
						$results[] = \Yukari\Event\Instance::newEvent(null, 'irc.output.privmsg')
							->setDataPoint('target', $event['target'])
							->setDataPoint('text', sprintf('%1$s No target channel specified specified.', $highlight));

						return $results;
					}

					$channel = $event['target'];
					$user = $params[0];
				}
				$results[] = \Yukari\Event\Instance::newEvent(null, 'irc.output.mode')
					->setDataPoint('target', $channel)
					->setDataPoint('flags', $mode)
					->setDataPoint('args', $user);

				return $results;
			}
		}
	}

	public function handleListaddonsCommand(\Yukari\Event\Instance $event)
	{
		// Check auths first
		if(!$this->checkAuthentication($event['hostmask']))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			$addon_loader = Kernel::get('core.addonloader');

			foreach($addon_loader as $metadata)
				$addon_list[] = sprintf('%1$s [%2$s]', $metadata->getName(), $metadata->getVersion());

			// Had to get hackish with word wrapping, can't rely on wordwrap() here.
			$response = array(0 => array());
			$i = 0;
			foreach($addon_list as $addon)
			{
				$len = strlen($addon);
				if(strlen(implode(', ', $response[$i])) + $len + 2 > 300)
					$i++;
				$response[$i][] = $addon;
			}

			$results = array();
			$highlight = (!$event['is_private']) ? $event['hostmask']['nick'] . ':' : '';
			foreach($response as $line)
			{
				$line = implode(', ', $line);
				$results[] = \Yukari\Event\Instance::newEvent(null, 'irc.output.privmsg')
					->setDataPoint('target', $event['target'])
					->setDataPoint('text', sprintf('%1$s %2$s.', $highlight, $line));
			}

			return $results;
		}
	}

	public function handleQuitCommand(\Yukari\Event\Instance $event)
	{
		// Check auths first
		if(!$this->checkAuthentication($event['hostmask']))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			$dispatcher = Kernel::getDispatcher();
			$dispatcher->trigger(\Yukari\Event\Instance::newEvent(null, 'system.shutdown'));

			return NULL;
		}
	}

	public function handleCommandRefusal(\Yukari\Event\Instance $event)
	{
		$highlight = (!$event['is_private']) ? $event['hostmask']['nick'] . ':' : '';
		$results[] = \Yukari\Event\Instance::newEvent(null, 'irc.output.privmsg')
			->setDataPoint('target', $event['target'])
			->setDataPoint('text', sprintf('%1$s You are not authorized to use this command.', $highlight));

		return $results;
	}

	public function checkAuthentication(\Yukari\Lib\Hostmask $hostmask)
	{
		$dispatcher = Kernel::getDispatcher();
		$auth = $dispatcher->trigger(\Yukari\Event\Instance::newEvent(null, 'acl.check_allowed')
			->setDataPoint('hostmask', $hostmask));
		if(!is_array($auth) || !isset($auth[0]) || $auth[0] === 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}
