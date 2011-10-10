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

namespace Codebite\Yukari\Addon\Admin;
use \Codebite\Yukari\Kernel;
use \Codebite\Yukari\Addon\IRC\Internal\DeadConnectionException;
use \OpenFlame\Framework\Event\Instance as Event;

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
	 * @return \Codebite\Yukari\Addon\Admin\Basic - Provides a fluent interface.
	 */
	public function registerListeners()
	{
		Kernel::registerListener('irc.input.command.join', 0, array($this, 'handleJoinCommand'));
		Kernel::registerListener('irc.input.command.part', 0, array($this, 'handlePartCommand'));
		//Kernel::registerListener('irc.input.command.kick', 0, array($this, 'handleKickCommand'));
		Kernel::registerListener('irc.input.command.op', 0, array($this, 'handleSetUserChannelOp'));
		Kernel::registerListener('irc.input.command.deop', 0, array($this, 'handleSetUserChannelDeop'));
		Kernel::registerListener('irc.input.command.voice', 0, array($this, 'handleSetUserChannelVoice'));
		Kernel::registerListener('irc.input.command.devoice', 0, array($this, 'handleSetUserChannelDevoice'));
		Kernel::registerListener('irc.input.command.listaddons', 0, array($this, 'handleListAddonsCommand'));
		//Kernel::registerListener('irc.input.command.addoninfo', 0, array($this, 'handleAddonInfoCommand')) // @todo write this command
		Kernel::registerListener('irc.input.command.loadaddon', 0, array($this, 'handleLoadAddonCommand'));
		Kernel::registerListener('irc.input.command.versioncheck', 0, array($this, 'handleVersionCheckCommand'));
		Kernel::registerListener('irc.input.command.uptime', 0, array($this, 'handleUptimeCommand'));
		Kernel::registerListener('irc.input.command.quit', 0, array($this, 'handleQuitCommand'));
		Kernel::registerListener('irc.input.command.shutdown', 0, array($this, 'handleShutdownCommand'));

		return $this;
	}

	/**
	 * Handles the bot being told to join a specific channel.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleJoinCommand(Event $event)
	{
		// Check auths first
		if(!$this->checkAuthentication($event->get('hostmask')))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			$highlight = (!$event->get('is_private')) ? $event->get('hostmask')->getNick() . ':' : '';
			if(substr($event->get('text'), 0, 1) !== '#')
			{
				$results[] = Event::newEvent('irc.output.privmsg')
					->set('target', $event->get('target'))
					->set('text', sprintf('%1$s Invalid channel specified.', $highlight));

				return $results;
			}
			else
			{
				$join_params = explode(' ', $event->get('text'), 2);
				$join = Event::newEvent('irc.output.join')
					->set('channel', $join_params[0]);

				if(isset($join_params[1]))
				{
					$join->set('key', $join_params[1]);
				}

				return array($join);
			}
		}
	}

	/**
	 * Handles the bot being told to part a specific channel.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handlePartCommand(Event $event)
	{
		// Check auths first
		if(!$this->checkAuthentication($event->get('hostmask')))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			$highlight = (!$event->get('is_private')) ? $event->get('hostmask')->getNick() . ':' : '';
			if(substr($event->get('text'), 0, 1) !== '#')
			{
				$results[] = Event::newEvent('irc.output.privmsg')
					->set('target', $event->get('target'))
					->set('text', sprintf('%1$s Invalid channel specified.', $highlight));

				return $results;
			}
			else
			{
				$part_params = explode(' ', $event->get('text'), 2);
				$part = Event::newEvent('irc.output.part')
					->set('channel', $part_params[0]);

				if(isset($part_params[1]))
				{
					$part->set('reason', $part_params[1]);
				}

				return array($part);
			}
		}
	}

	/*
	public function handleKickCommand(Event $event)
	{
		// asdf
	}
	*/

	public function handleSetUserChannelOp($event)
	{
		$this->handleSetUserChannelMode($event, '+o');
	}

	public function handleSetUserChannelDeop($event)
	{
		$this->handleSetUserChannelMode($event, '-o');
	}

	public function handleSetUserChannelVoice($event)
	{
		$this->handleSetUserChannelMode($event, '+v');
	}

	public function handleSetUserChannelDevoice($event)
	{
		$this->handleSetUserChannelMode($event, '-v');
	}

	/**
	 * Handles the bot being told set a channel-specific user mode.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event instance.
	 * @param string $mode - The mode flag to set.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleSetUserChannelMode(Event $event, $mode)
	{
		// Check auths first
		if(!$this->checkAuthentication($event->get('hostmask')))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			$highlight = (!$event->get('is_private')) ? $event->get('hostmask')->getNick() . ':' : '';

			// Make sure an invalid username isn't being provided.
			if(preg_match('#[\!\#\@]#i', $event->get('text')))
			{
				$results[] = Event::newEvent('irc.output.privmsg')
					->set('target', $event->get('target'))
					->set('text', sprintf('%1$s Invalid nickname specified.', $highlight));

				return $results;
			}
			else
			{
				// send the mode command
				$params = explode(' ', $event->get('text'), 2);

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
					if($event->get('is_private'))
					{
						$results[] = Event::newEvent('irc.output.privmsg')
							->set('target', $event->get('target'))
							->set('text', sprintf('%1$s No target channel specified.', $highlight));

						return $results;
					}

					$channel = $event->get('target');
					$user = $params[0];
				}
				$results[] = Event::newEvent('irc.output.mode')
					->set('target', $channel)
					->set('flags', $mode)
					->set('args', $user);

				return $results;
			}
		}
	}

	/**
	 * Handles the bot being told to list all loaded addons.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleListAddonsCommand(Event $event)
	{
		// Check auths first
		if(!$this->checkAuthentication($event->get('hostmask')))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			$addon_loader = Kernel::get('yukari.addonloader');

			foreach($addon_loader as $metadata)
			{
				$addon_list[] = sprintf('%1$s [%2$s]', $metadata->getName(), $metadata->getVersion());
			}

			// Had to get hackish with word wrapping, can't rely on wordwrap() here.
			$response = array(0 => array());
			$i = 0;
			foreach($addon_list as $addon)
			{
				$len = strlen($addon);
				if(strlen(implode(', ', $response[$i])) + $len + 2 > 300)
				{
					$i++;
				}
				$response[$i][] = $addon;
			}

			$results = array();
			$highlight = (!$event->get('is_private')) ? $event->get('hostmask')->getNick() . ':' : '';
			foreach($response as $line)
			{
				$line = implode(', ', $line);
				$results[] = Event::newEvent('irc.output.privmsg')
					->set('target', $event->get('target'))
					->set('text', sprintf('%1$s %2$s.', $highlight, $line));
			}

			return $results;
		}
	}

	/**
	 * Handles the bot being told to provide information about a loaded addon.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 *
	public function handleAddonInfoCommand(Event $event)
	{
		// Check auths first
		if(!$this->checkAuthentication($event->get('hostmask')))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			// asdf
		}
	}
	 */

	/**
	 * Handles the bot being told to load a specific addon.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleLoadAddonCommand(Event $event)
	{
		// Check auths first
		if(!$this->checkAuthentication($event->get('hostmask')))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			$highlight = (!$event->get('is_private')) ? $event->get('hostmask')->getNick() . ':' : '';

			try
			{
				$addon = $event->get('text');
				// Alphanumeric addon names only, we don't want any sneaky stuff going on.
				if(!preg_match('#^([0-9a-z]+)$#i', $event->get('text')))
				{
					throw new \RuntimeException('Unacceptable addon name provided');
				}

				$addon_loader = Kernel::get('yukari.addonloader');
				$addon_loader->load($addon);

				// Display a message in the UI.
				Kernel::trigger(Event::newEvent('ui.message.system')
					->set('message', sprintf('Loaded addon "%s"', $addon)));

				$results[] = Event::newEvent('irc.output.privmsg')
					->set('target', $event->get('target'))
					->set('text', sprintf('%1$s Loaded addon "%2$s" successfully.', $highlight, $addon));
			}
			catch(\Exception $e)
			{
				// Display a message in the UI saying stuff asploded
				Kernel::trigger(Event::newEvent('ui.message.warning')
					->set('message', sprintf('Failed to load addon "%1$s" - failure message: "%2$s"', $addon, $e->getMessage())));

				$results[] = Event::newEvent('irc.output.privmsg')
					->set('target', $event->get('target'))
					->set('text', sprintf('%1$s Failed to load addon "%2$s".', $highlight, $addon));
			}

			return $results;
		}
	}

	/**
	 * Handles the bot being told to check to see if a newer build is available.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleVersionCheckCommand(Event $event)
	{
		// Check auths first
		if(!$this->checkAuthentication($event->get('hostmask')))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			$highlight = (!$event->get('is_private')) ? $event->get('hostmask')->getNick() . ':' : '';
			$installed_build = (int) substr(Kernel::getBuildNumber(), 6);

			// if the build number is "DEV", it's a dev build, so we can't treat it as a normal build.  As such, version check must fail here.
			if($installed_build == 'DEV')
			{
				$results[] = Event::newEvent('irc.output.privmsg')
					->set('target', $event->get('target'))
					->set('text', sprintf('%1$s Cannot check for new build; a DEV build is currently installed.', $highlight));
				return $results;
			}

			// Get the latest build number.
			$latest_build_number = rtrim(@file_get_contents(self::BUILD_NUMBER_URL));

			// If the return value was false, an empty string, or a non integer...something went wrong.
			if($latest_build_number === false || $latest_build_number == '' || !ctype_digit($latest_build_number))
			{
				$results[] = Event::newEvent('irc.output.privmsg')
					->set('target', $event->get('target'))
					->set('text', sprintf('%1$s Failed to get the latest build number.', $highlight));
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

				$results[] = Event::newEvent('irc.output.privmsg')
					->set('target', $event->get('target'))
					->set('text', sprintf('%1$s %2$s.', $highlight, $status));
				return $results;
			}
		}
	}

	/**
	 * Handles the bot being asked how long its been up.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleUptimeCommand(Event $event)
	{
		// Check auths first
		if(!$this->checkAuthentication($event->get('hostmask')))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			$current_time = time();

			$time_diff = $current_time - \Codebite\Yukari\START_TIME;
			$diff_string = \Codebite\Yukari\timespan($time_diff);

			$highlight = (!$event->get('is_private')) ? $event->get('hostmask')->getNick() . ':' : '';
			$results[] = Event::newEvent('irc.output.privmsg')
				->set('target', $event->get('target'))
				->set('text', sprintf('%1$s I have been running for %2$s.', $highlight, $diff_string));

			return $results;
		}
	}

	/**
	 * Handles the bot being told to quit.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleQuitCommand(Event $event)
	{
		// Check auths first
		if(!$this->checkAuthentication($event->get('hostmask')))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			throw new DeadConnectionException();

			return;
		}
	}

	/**
	 * Handles the bot being told to shutdown.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleShutdownCommand(Event $event)
	{
		// Check auths first
		if(!$this->checkAuthentication($event->get('hostmask')))
		{
			return $this->handleCommandRefusal($event);
		}
		else
		{
			Kernel::trigger(Event::newEvent('yukari.request_shutdown'));

			return;
		}
	}

	/**
	 * Handles the bot refusing to obey a command due to authentication failure.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event instance.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleCommandRefusal(Event $event)
	{
		$highlight = (!$event->get('is_private')) ? $event->get('hostmask')->getNick() . ':' : '';
		$results[] = Event::newEvent('irc.output.privmsg')
			->set('target', $event->get('target'))
			->set('text', sprintf('%1$s You are not authorized to use this command.', $highlight));

		return $results;
	}

	/**
	 * Checks to see if the originator of the event is authorized to use the command.
	 * @param \Codebite\Yukari\Addon\IRC\Connection\Hostmask $event - The hostmask object of the event originator.
	 * @return boolean - Is the sender authorized to use the command?
	 */
	public function checkAuthentication(\Codebite\Yukari\Addon\IRC\Connection\Hostmask $hostmask)
	{
		$event = Kernel::trigger(Event::newEvent('acl.check_allowed')
			->set('hostmask', $hostmask), \OpenFlame\Framework\Event\Dispatcher::TRIGGER_MANUALBREAK);

		$auth = $event->getReturns();
		$auth = array_shift($auth);

		if($auth !== NULL)
		{
			return $auth;
		}
		else
		{
			// if we don't have a result, assume we don't have a reliable ACL addon in place and deny access.
			return false;
		}
	}
}
