<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     Yukari
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

namespace Codebite\Yukari;
use \Codebite\Yukari\Kernel;
use \OpenFlame\Framework\Event\Instance as Event;
use \OpenFlame\Framework\Utility\JSON;

/**
 * Yukari - Kernel class,
 *      Used as the static master class that will provides easy access to the Yukari environment.
 *
 *
 * @category    Yukari
 * @package     Yukari
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Daemon
{
	// asdf

	protected $shutdown = false;

	public function init(Event $event)
	{
		// Load our config file
		$args = Kernel::get('argparser');
		$config = (isset($args['config'])) ? "{$args['config']}.json" : 'config.json';
		$config_array = JSON::decode(YUKARI . "/data/config/{$config}");

		foreach($config_array as $name => $value)
		{
			Kernel::setConfig($name, $value);
		}

		// check for missing required configs
		$required_configs = array(
			//'irc.url',
			//'irc.username',
			//'irc.realname',
			//'irc.nickname',
			'yukari.addons',
		);
		foreach($required_configs as $required_config_name)
		{
			if(Kernel::getConfig($required_config_name) === NULL)
			{
				throw new \RuntimeException(sprintf('Required config entry "%s" not defined', $required_config_name));
			}
		}

		$dispatcher = Kernel::get('dispatcher');

		$ui = Kernel::get('yukari.ui');
		$ui->setOutputLevel(Kernel::getConfig('ui.output_level'))
			->registerListeners();

		// Startup message
		$dispatcher->trigger(Event::newEvent('ui.startup'));
		$dispatcher->trigger(Event::newEvent('ui.message.system')
			->set('message', 'Loading the Yukari core'));

		// Create our timezone object and store it for now, along with storing our starting DateTime object.
		$timezone = Kernel::set('yukari.timezone', new \DateTimeZone((Kernel::getConfig('core.timezonestring') ?: 'UTC')));
		Kernel::set('yukari.starttime', new \DateTime('@' . \Codebite\Yukari\START_TIME, $timezone));

		// Load any addons we want.
		$addon_loader = Kernel::set('yukari.addonloader', new \Codebite\Yukari\Addon\Loader());
		foreach(Kernel::getConfig('yukari.addons') as $addon)
		{
			try
			{
				$addon_loader->loadAddon($addon);
				$dispatcher->trigger(\OpenFlame\Framework\Event\Instance::newEvent('ui.message.system')
					->set('message', sprintf('Loaded addon "%s"', $addon)));
			}
			catch(\Exception $e)
			{
				$dispatcher->trigger(\OpenFlame\Framework\Event\Instance::newEvent('ui.message.warning')
					->set('message', sprintf('Failed to load addon "%1$s" - failure message: "%2$s"', $addon, $e->getMessage())));
			}
		}

		if(Kernel::getConfig('yukari.dispatcher.listeners'))
		{
			$dispatcher->trigger(\OpenFlame\Framework\Event\Instance::newEvent('ui.message.system')
				->set('message', 'Registering listeners to event dispatcher'));

			foreach(Kernel::getConfig('yukari.dispatcher.listeners') as $event_name => $listener)
			{
				$listener = explode('->', $listener);
				if(sizeof($listener) > 1)
				{
					$dispatcher->register($event_name, array(Kernel::get($listener[0]), $listener[1]));
				}
				else
				{
					$dispatcher->register($event_name, $listener[0]);
				}
			}
		}

		// Dispatch a startup event
		// This is useful for having a listener registered, waiting for startup to complete before loading in one last thing
		$dispatcher->trigger(Event::newEvent('yukari.startup'));

		// All done!
		$dispatcher->trigger(Event::newEvent('ui.ready'));

		// How fast were we, now?  :3
		$dispatcher->trigger(Event::newEvent('ui.message.debug')
			->set('message', sprintf('Startup complete, took %1$s seconds', (microtime(true) - \Codebite\Yukari\START_MICROTIME))));
	}

	public function exec(Event $event)
	{
		$dispatcher = Kernel::get('dispatcher');

		try
		{
			// Now we go around in endless circles until someone lays down a giant bear trap and catches us.
			while(true)
			{
				$dispatcher->trigger(Event::newEvent('yukari.tick'));

				// If we have a quit event, break out of the loop.
				if($this->shutdown === true)
				{
					break;
				}
			}
		}
		catch(\Exception $e)
		{
			try
			{
				$dispatcher->trigger(Event::newEvent('ui.message.debug')
					->set('message', sprintf('Exception %1$s::%2$s: %3$s', get_class($e), $e->getCode(), $e->getMessage())));
				$dispatcher->trigger(Event::newEvent('ui.message.debug')
					->set('message', sprintf('Stack trace: %s', $e->getTraceAsString())));

				// Dispatch an emergency abort event.
				$dispatcher->trigger(Event::newEvent('yukari.abort'));
			}
			catch(\Exception $e)
			{
				// Another exception?  FFFUUUUUUU--
				// CRASH BANG BOOM.
				printf('Fatal error [%1$s::%2$s] encountered during runtime abort procedure, terminating immediately' . PHP_EOL, get_class($e), $e->getCode());
				printf('Stack trace: %s', $e->getTraceAsString());
				exit(1);
			}
			exit(1);
		}

		// Dispatch a pre-shutdown event.
		$dispatcher->trigger(\OpenFlame\Framework\Event\Instance::newEvent('yukari.shutdown'));

		// Dispatch a daemon-termination event
		$dispatcher->trigger(\OpenFlame\Framework\Event\Instance::newEvent('yukari.terminate'));
	}

	/**
	 * Basic listener that allows an addon or something to trigger a shutdown
	 * @param \OpenFlame\Framework\Event\Instance $event - The event that is triggering the shutdown
	 * @return void
	 */
	public function triggerShutdown(Event $event)
	{
		$this->shutdown = true;
	}
}
