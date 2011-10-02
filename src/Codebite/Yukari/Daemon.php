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
	protected $shutdown = false;

	public function __construct()
	{
		Kernel::registerListener('yukari.init', 0, array($this, 'init'));
		Kernel::registerListener('yukari.exec', 0, array($this, 'exec'));
	}

	/**
	 * Init the yukari daemon
	 * @param Event $event - The event that triggered this method.
	 * @return void
	 */
	public function init(Event $event)
	{
		// Load our config file
		$args = Kernel::get('yukari.argparser');
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

		// Grab the UI here
		$ui = Kernel::get('yukari.ui');

		// Startup message
		Kernel::trigger(Event::newEvent('ui.startup'));
		Kernel::trigger(Event::newEvent('ui.message.system')
			->set('message', 'Loading the Yukari core'));

		// get our start time
		Kernel::get('yukari.starttime');

		// Load any addons we want.
		$addon_loader = Kernel::get('yukari.addonloader');
		foreach(Kernel::getConfig('yukari.addons') as $addon)
		{
			try
			{
				$addon_loader->load($addon);
				Kernel::trigger(Event::newEvent('ui.message.system')
					->set('message', sprintf('Loaded addon "%s"', $addon)));
			}
			catch(\Exception $e)
			{
				Kernel::trigger(Event::newEvent('ui.message.warning')
					->set('message', sprintf('Failed to load addon "%1$s" - failure message: "%2$s"', $addon, $e->getMessage())));
			}
		}

		if(Kernel::getConfig('yukari.dispatcher.listeners'))
		{
			Kernel::trigger(Event::newEvent('ui.message.system')
				->set('message', 'Registering listeners to event dispatcher'));

			foreach(Kernel::getConfig('yukari.dispatcher.listeners') as $event_name => $listener)
			{
				$listener = explode('->', $listener);
				if(sizeof($listener) > 1)
				{
					Kernel::registerListener($event_name, array(Kernel::get($listener[0]), $listener[1]));
				}
				else
				{
					Kernel::registerListener($event_name, $listener[0]);
				}
			}
		}

		// Register the shutdown listener now...
		Kernel::registerListener('yukari.request_shutdown', -20, array($this, 'triggerShutdown'));

		// Dispatch a startup event
		// This is useful for having a listener registered, waiting for startup to complete before loading in one last thing
		Kernel::trigger(Event::newEvent('yukari.ready'));

		// All done!
		Kernel::trigger(Event::newEvent('ui.ready'));

		// How fast were we, now?  :3
		Kernel::trigger(Event::newEvent('ui.message.debug')
			->set('message', sprintf('Startup complete, took %1$s seconds', (microtime(true) - \Codebite\Yukari\START_MICROTIME))));
	}

	public function exec(Event $event)
	{
		// startup event, triggered RIGHT before the loop begins.
		Kernel::trigger(Event::newEvent('yukari.startup'));
		try
		{
			$tick_delay = (Kernel::getConfig('yukari.tickrate') ? (1000000 / ((float) Kernel::getConfig('yukari.tickrate'))) : NULL);

			// Now we go around in endless circles until someone lays down a giant bear trap and catches us.
			if($tick_delay !== NULL)
			{
				Kernel::trigger(Event::newEvent('ui.message.debug')
					->set('message', sprintf('Launching Yukari daemon, using set tickrate with tick interval of %f µs (%d tick/sec)', $tick_delay, Kernel::getConfig('yukari.tickrate'))));

				while(true)
				{
					$_t = microtime(true) + $tick_delay;
					Kernel::trigger(Event::newEvent('yukari.tick'));

					$_tick = microtime(true);
					if($_t - $_tick > 0)
					{
						usleep(($_t) - $_tick);
					}

					// If we have a quit event, break out of the loop.
					if($this->shutdown === true)
					{
						break;
					}
				}
			}
			else
			{
				Kernel::trigger(Event::newEvent('ui.message.debug')
					->set('message', 'Launching Yukari daemon, not using set tickrate'));

				while(true)
				{
					Kernel::trigger(Event::newEvent('yukari.tick'));

					// If we have a quit event, break out of the loop.
					if($this->shutdown === true)
					{
						break;
					}
				}
			}
		}
		catch(\Exception $e)
		{
			try
			{
				Kernel::trigger(Event::newEvent('ui.message.debug')
					->set('message', sprintf('Exception %1$s::%2$s: %3$s', get_class($e), $e->getCode(), $e->getMessage())));
				Kernel::trigger(Event::newEvent('ui.message.debug')
					->set('message', sprintf('Stack trace: %s', $e->getTraceAsString())));

				// Dispatch an emergency abort event.
				Kernel::trigger(Event::newEvent('yukari.abort'));
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
		Kernel::trigger(Event::newEvent('yukari.shutdown'));

		// Dispatch a daemon-termination event
		Kernel::trigger(Event::newEvent('yukari.terminate'));
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
