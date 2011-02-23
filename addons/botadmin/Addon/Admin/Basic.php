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
 * Yukari - Basic bot administration object,
 *      Hooks onto incoming admin commands, authenticates the sender, and either performs a task or tells the user to screw off.
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
	 * @const - URL providing the latest available build number for Yukari.
	 */
	const BUILD_NUMBER_URL = 'https://github.com/damianb/yukari/raw/master/build/bin_number.txt';

	/**
	 * Register the listeners we need for this addon to work properly.
	 * @return \Yukari\Addon\Admin\Basic - Provides a fluent interface.
	 */
	public function registerListeners()
	{
		$dispatcher = Kernel::getDispatcher();
		$dispatcher->register('irc.input.command.join', array(Kernel::get('addon.botadmin'), 'handleJoinCommand'))
			->register('irc.input.command.part', array(Kernel::get('addon.botadmin'), 'handlePartCommand'))
			//->register('irc.input.command.kick', array(Kernel::get('addon.botadmin'), 'handleKickCommand'))
			->register('irc.input.command.op', array(Kernel::get('addon.botadmin'), 'handleSetUserChannelMode'), array('+o'))
			->register('irc.input.command.deop', array(Kernel::get('addon.botadmin'), 'handleSetUserChannelMode'), array('-o'))
			->register('irc.input.command.voice', array(Kernel::get('addon.botadmin'), 'handleSetUserChannelMode'), array('+v'))
			->register('irc.input.command.devoice', array(Kernel::get('addon.botadmin'), 'handleSetUserChannelMode'), array('-v'))
			->register('irc.input.command.listaddons', array(Kernel::get('addon.botadmin'), 'handleListAddonsCommand'))
			//->register('irc.input.command.addoninfo', array(Kernel::get('addon.botadmin'), 'handleAddonInfoCommand')) // @todo write this command
			->register('irc.input.command.loadaddon', array(Kernel::get('addon.botadmin'), 'handleLoadAddonCommand'))
			->register('irc.input.command.versioncheck', array(Kernel::get('addon.botadmin'), 'handleVersionCheckCommand'))
			->register('irc.input.command.quit', array(Kernel::get('addon.botadmin'), 'handleQuitCommand'));

		return $this;
	}

	/**
	 * Handles the bot being told to join a specific channel.
	 * @param \Yukari\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
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
				$results[] = \Yukari\Event\Instance::newEvent('irc.output.privmsg')
					->setDataPoint('target', $event['target'])
					->setDataPoint('text', sprintf('%1$s Invalid channel specified.', $highlight));

				return $results;
			}
			else
			{
				$join_params = explode(' ', $event['text'], 2);
				$join = \Yukari\Event\Instance::newEvent('irc.output.join')
					->setDataPoint('channel', $join_params[0]);

				if(isset($join_params[1]))
					$join->setDataPoint('key', $join_params[1]);

				return array($join);
			}
		}
	}

	/**
	 * Handles the bot being told to part a specific channel.
	 * @param \Yukari\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
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
				$results[] = \Yukari\Event\Instance::newEvent('irc.output.privmsg')
					->setDataPoint('target', $event['target'])
					->setDataPoint('text', sprintf('%1$s Invalid channel specified.', $highlight));

				return $results;
			}
			else
			{
				$part_params = explode(' ', $event['text'], 2);
				$part = \Yukari\Event\Instance::newEvent('irc.output.part')
					->setDataPoint('channel', $part_params[0]);

				if(isset($part_params[1]))
					$part->setDataPoint('reason', $part_params[1]);

				return array($part);
			}
		}
	}

	public function handleKickCommand(\Yukari\Event\Instance $event)
	{
		// asdf
	}

	/**
	 * Handles the bot being told set a channel-specific user mode.
	 * @param \Yukari\Event\Instance $event - The event instance.
	 * @param string $mode - The mode flag to set.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
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

			// Make sure an invalid username isn't being provided.
			if(preg_match('#[\!\#\@]#i', $event['text']))
			{
				$results[] = \Yukari\Event\Instance::newEvent('irc.output.privmsg')
					->setDataPoint('target', $event['target'])
					->setDataPoint('text', sprintf('%1$s Invalid nickname specified.', $highlight));

				return $results;
			}
			else
			{
				// send the mode command
				$params = explode(' ', $event['text'], 2);

				// if the user wants to specify a channel, let them do so...and then fall back to the current channel if no channel is specified
				if($params[0][0] === '#')
				{
					list($channel, $user) = $params;
				}
				elseif(isset($params[1]) && $params[1][0] === '#')
				{
					list($user, $channel) = $params;
				}
				else
				{
					// if this was a private command, we must derp at the sender.
					if($event['is_private'])
					{
						$results[] = \Yukari\Event\Instance::newEvent('irc.output.privmsg')
							->setDataPoint('target', $event['target'])
							->setDataPoint('text', sprintf('%1$s No target channel specified.', $highlight));

						return $results;
					}

					$channel = $event['target'];
					$user = $params[0];
				}
				$results[] = \Yukari\Event\Instance::newEvent('irc.output.mode')
					->setDataPoint('target', $channel)
					->setDataPoint('flags', $mode)
					->setDataPoint('args', $user);

				return $results;
			}
		}
	}

	/**
	 * Handles the bot being told to list all loaded addons.
	 * @param \Yukari\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleListAddonsCommand(\Yukari\Event\Instance $event)
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
				$results[] = \Yukari\Event\Instance::newEvent('irc.output.privmsg')
					->setDataPoint('target', $event['target'])
					->setDataPoint('text', sprintf('%1$s %2$s.', $highlight, $line));
			}

			return $results;
		}
	}

	/**
	 * Handles the bot being told to provide information about a loaded addon.
	 * @param \Yukari\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleAddonInfoCommand(\Yukari\Event\Instance $event)
	{
		// Check auths first
		if(!$this->checkAuthentication($event['hostmask']))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			// asdf
		}
	}

	/**
	 * Handles the bot being told to load a specific addon.
	 * @param \Yukari\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleLoadAddonCommand(\Yukari\Event\Instance $event)
	{
		$dispatcher = Kernel::getDispatcher();

		// Check auths first
		if(!$this->checkAuthentication($event['hostmask']))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			$highlight = (!$event['is_private']) ? $event['hostmask']['nick'] . ':' : '';

			try
			{
				$addon = $event['text'];
				// Alphanumeric addon names only, we don't want any sneaky stuff going on.
				if(!preg_match('#^([0-9a-z]+)$#i', $event['text']))
					throw new \RuntimeException('Unacceptable addon name provided');

				$addon_loader = Kernel::get('core.addonloader');
				$addon_loader->loadAddon($addon);

				// Display a message in the UI.
				$dispatcher->trigger(\Yukari\Event\Instance::newEvent('ui.message.system')
					->setDataPoint('message', sprintf('Loaded addon "%s"', $addon)));

				$results[] = \Yukari\Event\Instance::newEvent('irc.output.privmsg')
					->setDataPoint('target', $event['target'])
					->setDataPoint('text', sprintf('%1$s Loaded addon "%2$s" successfully.', $highlight, $addon));
			}
			catch(\Exception $e)
			{
				// Display a message in the UI saying stuff asploded
				$dispatcher->trigger(\Yukari\Event\Instance::newEvent('ui.message.warning')
					->setDataPoint('message', sprintf('Failed to load addon "%1$s" - failure message: "%2$s"', $addon, $e->getMessage())));

				$results[] = \Yukari\Event\Instance::newEvent('irc.output.privmsg')
					->setDataPoint('target', $event['target'])
					->setDataPoint('text', sprintf('%1$s Failed to load addon "%2$s".', $highlight, $addon));
			}

			return $results;
		}
	}

	/**
	 * Handles the bot being told to check to see if a newer build is available.
	 * @param \Yukari\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleVersionCheckCommand(\Yukari\Event\Instance $event)
	{
		// Check auths first
		if(!$this->checkAuthentication($event['hostmask']))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			$highlight = (!$event['is_private']) ? $event['hostmask']['nick'] . ':' : '';
			$installed_build = (int) substr(Kernel::getBuildNumber(), 6);

			// if the build number is "DEV", it's a dev build, so we can't treat it as a normal build.  As such, version check must fail here.
			if($installed_build == 'DEV')
			{
				$results[] = \Yukari\Event\Instance::newEvent('irc.output.privmsg')
					->setDataPoint('target', $event['target'])
					->setDataPoint('text', sprintf('%1$s Cannot check for new build; a DEV build is currently installed.', $highlight));
				return $results;
			}

			// Get the latest build number.
			$latest_build_number = rtrim(@file_get_contents(self::BUILD_NUMBER_URL));

			// If the return value was false, an empty string, or a non integer...something went wrong.
			if($latest_build_number === false || $latest_build_number == '' || !ctype_digit($latest_build_number))
			{
				$results[] = \Yukari\Event\Instance::newEvent('irc.output.privmsg')
					->setDataPoint('target', $event['target'])
					->setDataPoint('text', sprintf('%1$s Failed to get the latest build number.', $highlight));
				return $results;
			}
			else
			{
				$latest_build_number = (int) $latest_build_number;

				// Do some comparisons to see if we're on the latest build.
				if($latest_build_number <= $installed_build)
				{
					$status = 'Yukari is up to date';
				}
				elseif($latest_build_number > $installed_build)
				{
					$status = sprintf('Yukari build %1$d is available; currently running build %2$d', $latest_build_number, $installed_build);
				}

				$results[] = \Yukari\Event\Instance::newEvent('irc.output.privmsg')
					->setDataPoint('target', $event['target'])
					->setDataPoint('text', sprintf('%1$s %2$s.', $highlight, $status));
				return $results;
			}
		}
	}

	/**
	 * Handles the bot being told to quit.
	 * @param \Yukari\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
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
			$dispatcher->trigger(\Yukari\Event\Instance::newEvent('system.shutdown'));

			return NULL;
		}
	}

	/**
	 * Handles the bot refusing to obey a command due to authentication failure.
	 * @param \Yukari\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleCommandRefusal(\Yukari\Event\Instance $event)
	{
		$highlight = (!$event['is_private']) ? $event['hostmask']['nick'] . ':' : '';
		$results[] = \Yukari\Event\Instance::newEvent('irc.output.privmsg')
			->setDataPoint('target', $event['target'])
			->setDataPoint('text', sprintf('%1$s You are not authorized to use this command.', $highlight));

		return $results;
	}

	/**
	 * Checks to see if the originator of the event is authorized to use the command.
	 * @param \Yukari\Lib\Hostmask $event - The hostmask object of the event originator.
	 * @return boolean - Is the sender authorized to use the command?
	 */
	public function checkAuthentication(\Yukari\Lib\Hostmask $hostmask)
	{
		$dispatcher = Kernel::getDispatcher();
		$auth = $dispatcher->cleanTrigger(\Yukari\Event\Instance::newEvent('acl.check_allowed')
			->setDataPoint('hostmask', $hostmask));
		if(isset($auth[0]))
		{
			return $auth[0];
		}
		else
		{
			// if we don't have a result, assume we don't have a reliable ACL addon in place and deny access.
			return false;
		}
	}
}
