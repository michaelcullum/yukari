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

	public function handleOpCommand(\Yukari\Event\Instance $event)
	{
		// asdf
	}

	public function handleDeopCommand(\Yukari\Event\Instance $event)
	{
		// asdf
	}
	public function handleVoiceCommand(\Yukari\Event\Instance $event)
	{
		// asdf
	}

	public function handleDevoiceCommand(\Yukari\Event\Instance $event)
	{
		// asdf
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
			$response = array(0 => '');
			$i = 0;
			foreach($addon_list as $addon)
			{
				$len = strlen($addon);
				if(strlen($response[$i]) + $len > 300)
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
