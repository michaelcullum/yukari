<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
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

namespace Yukari;
use Yukari\Lib as Lib;
use Yukari\Event as Event;

/**
 * Yukari - Environment class,
 *      Manages the Failnet environment.
 *
 *
 * @category    Yukari
 * @package     Yukari
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Environment
{
	/**
	 * @var Yukari\Environment - The single environment instance.
	 */
	protected static $instance;

	/**
	 * @var array - Array of various loaded objects
	 */
	protected $objects = array();

	/**
	 * @var array - Array of loaded configuration options
	 */
	protected $config = array();

	/**
	 * Constructor
	 * @return void
	 */
	public function __construct()
	{
		$this->config = array(
			// asdf
		);
	}

	/**
	 * Singleton instance management method, creates the instance of the Environment if it's not available yet, or returns the existing instance
	 * @return Yukari\Environment - The single environment instance.
	 */
	public static function newInstance()
	{
		if(is_null(self::$instance))
			self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Get an object that is currently being stored in the kernel.
	 * @param string $slot - The slot to look in.
	 * @return mixed - NULL if the slot specified is unused, or the object present in the slot specified.
	 */
	public function getObject($slot)
	{
		if(!isset($this->objects[$slot]))
			return NULL;
		return $this->objects[$slot];
	}

	/**
	 * Store an object in the kernel.
	 * @param string $slot - The slot to store the object in.
	 * @param object $object - The object to store.
	 * @return object - The object just set.
	 *
	 * @throws \LogicException
	 */
	public function setObject($slot, $object)
	{
		if(!is_object($object))
			throw new \LogicException('Cannot store non-objects in Yukari kernel');
		$this->objects[$slot] = $object;

		return $this->objects[$slot];
	}

	/**
	 * Get a specified configuration setting from the kernel.
	 * @param string $slot - The configuration setting's slot name.
	 * @return mixed - NULL if the slot specified is unused, or the configuration setting we wanted.
	 */
	public function getConfig($slot)
	{
		if(!isset($this->config[$slot]))
			return NULL;
		return $this->config[$slot];
	}

	/**
	 * Set a configuration setting in the kernel.
	 * @param string $slot - The configuration setting's slot name.
	 * @param mixed $value - The configuration value to set.
	 * @return void
	 */
	public function setConfig($slot, $value)
	{
		$this->config[$slot] = $value;
	}

	/**
	 * Import an array of configuration options into the kernel.
	 * @param array $config_array - The array of options to import.
	 * @return void
	 */
	public function importConfig(array $config_array)
	{
		foreach($config_array as $key => $value)
		{
			if(is_array($this->config[$key]))
			{
				if(!is_array($value))
					$value = array($value);
				$this->config[$key] = array_merge($this->config[$key], $value);
			}
			else
			{
				$this->config[$key] = $value;
			}
		}
	}
















































	public function init()
	{
		// load config file here

		// check for missing required configs
		$required_configs = array(
			// asdf
		);

		foreach($required_configs as $required_config_name)
		{
			if(!isset($this->config[$required_config_name]))
				throw new \RuntimeException(sprintf('Required config entry "%s" not defined', $required_config_name));
		}











		if(!file_exists(YUKARI . 'data/config/') || !is_readable(YUKARI . 'data/config/') || !is_writeable(YUKARI . 'data/config/') || !is_dir(YUKARI . 'Data/Config/'))
			throw new \RuntimeException('Configuration file directory does not exist, or is not readable/writeable');
		// @note if doctrine is used, this code must be removed
		if(!file_exists(YUKARI . 'data/DB/') || !is_readable(YUKARI . 'data/DB/') || !is_writeable(YUKARI . 'data/DB/') || !is_dir(YUKARI . 'data/DB/'))
			throw new \RuntimeException('Database directory does not exist, or is not readable/writeable');

		// Nerf the pyro, then init the Bot with a reference back to the environment.
		Kernel::setEnvironment($this);

		// Create our timezone object and store it for now, along with storing our starting DateTime object.
		$this->setObject('time.timezone', new \DateTimeZone(date_default_timezone_get()));
		$this->setObject('time.start', new \DateTime('now', $this->getObject('core.timezone')));

		// Define the base memory usage here.
		define('Yukari\\BASE_MEMORY', memory_get_usage());

		try
		{
			// Register our autoloader
			$autoloader = Yukari\Autoloader::register();
			$this->setObject('core.autoload', $autoloader);
			//spl_autoload_register(array($this, 'autoloadClass'));

			// Setup our CLI object, and grab any passed args
			$this->setObject('core.cli', new Yukari\CLI\CLIArgs($_SERVER['argv']));
			/* @var Failnet\CLI\CLIArgs */
			$cli = $this->getObject('core.cli');
			define('Yukari\\IN_INSTALL', ($cli['mode'] === 'install') ? true : false);
			define('Yukari\\CONFIG_FILE', ($cli['config'] ? $cli['config'] : 'config.php'));

			if(Yukari\IN_INSTALL)
			{
				// stuff for the dynamic installer goes here
				$this->setObject('core.ui', new Yukari\Install\UI($this->getOption('ui.output_level', 'normal')));

				/* @var Failnet\Install\UI */
				$ui = $this->getObject('core.ui');

				// Fire off the UI's startup text.
				$ui->startup();
				$ui->status('Loading Failnet core objects');

				$ui->system('Loading core.core object');
				$this->setObject('core.core', new Yukari\Install\Core());
			}
			else
			{
				if(!file_exists(FAILNET . 'data/config/' . Failnet\CONFIG_FILE))
					throw new EnvironmentException(sprintf('The configuration file "%1$s" could not be loaded, as it does not exist.', Failnet\CONFIG_FILE), EnvironmentException::ERR_ENVIRONMENT_CONFIG_MISSING);

				// load the config file up next
				$this->loadConfig(Failnet\CONFIG_FILE);

				$this->setObject('core.ui', new Failnet\CLI\UI($this->getOption('ui.output_level', 'normal')));

				/* @var Failnet\CLI\UI */
				$ui = $this->getObject('core.ui');

				// Fire off the UI's startup text.
				$ui->startup();
				$ui->status('Loading the Yukari core system');

				// Start loading stuff.
				$ui->system('Loading internationalization object');
				$this->setObject('core.language', new Failnet\Language\Manager(Bot::getOption('language.file_dir', FAILNET . 'data/language')));
				$ui->system('Loading password hashing library');
				$this->setObject('core.hash', new Failnet\Lib\Hash(8, true));
				$ui->system('Loading event dispatcher');
				$this->setObject('core.dispatcher', new Failnet\Event\Dispatcher());
				$ui->system('Loading session manager');
				$this->setObject('core.session', new Failnet\Session\Manager());

				// Include any extra files that the user has specified
				foreach(Kernel::getOption('environment.extra_files', array()) as $file)
				{
					// Load the file, or asplode if it fails to load
					if(($include = @include($file)) === false)
						throw new EnvironmentException(sprintf('Failed to load extra file "%1$s"', $file), EnvironmentException::ERR_ENVIRONMENT_EXTRA_FILE_LOAD_FAIL);
				}

				// Load our language files
				$ui->system('Loading language files');
				$this->getObject('core.language')->collectEntries();

				// Register our event listeners to the dispatcher
				/* @var $dispatcher Failnet\Event\Dispatcher */
				$dispatcher = $this->getObject('core.dispatcher');
				foreach(Bot::getOption('dispatcher.listeners', array()) as $listener)
					$dispatcher->register($listener['event'], $listener['listener'], (isset($listener['params']) ? $listener['params'] : NULL));

				// Dispatch a startup event
				// This is useful for having a listener registered, waiting for startup to complete before loading in an addon or extra library.
				if($dispatcher->hasListeners('Runtime\\Startup'))
				{
					$trigger = new Event\Runtime\Startup();
					$dispatcher->dispatch($trigger);
				}

				// Load any addons we want.
				$this->setObject('core.addon', new Failnet\Addon\Loader());
				/* @var $addon_loader Failnet\Addon\Loader */
				$addon_loader = $this->getObject('core.addon');
				foreach(Bot::getOption('environment.addons', array()) as $addon)
				{
					try
					{
						$addon_loader->loadAddon($addon);
						$ui->system(sprintf('Loaded addon "%1$s" successfully', $addon));
					}
					catch(Failnet\Addon\LoaderException $e)
					{
						$ui->warning(sprintf('Failed to load addon "%1$s"', $addon));
						$ui->warning(sprintf('Failure message:  %1$s', $e->getMessage()));
					}
				}
			}
		}
		catch(FailnetException $e)
		{
			throw new EnvironmentException(sprintf('Failnet environment initialization encountered a fatal exception (%1$s::%2$s)' . PHP_EOL . 'Exception message: %3$s', get_class($e), $e->getCode(), $e->getMessage()), EnvironmentException::ERR_ENVIRONMENT_LOAD_FAILED, $e);
		}
	}

	/**
	 * Load a specified configuration file
	 * @param string $config -The config file to load, either JSON or PHP.
	 * @return void
	 *
	 * @throws Failnet\EnvironmentException
	 */
	public function loadConfig($config)
	{
		// Grab the file extension of the config file, see if it is PHP or JSON...we'll react appropriately based on which we're working with.
		$file_extension = substr(strrchr($config, '.'), 1);
		if($file_extension == 'php')
		{
			if(($include = @include(FAILNET . "data/config/$config")) === false || !isset($data) || !is_array($data))
				throw new EnvironmentException(sprintf('Failed to load the specified config file "%1$s"', $config), EnvironmentException::ERR_ENVIRONMENT_FAILED_CONFIG_LOAD);
			$this->setOptions($data);
		}
		elseif($file_extension == 'json')
		{
			$data = Lib\JSON::decode(FAILNET . "data/config/$config");
			$this->setOptions($data);
		}
		else
		{
			throw new EnvironmentException('The specified config file\'s file type is not supported', EnvironmentException::ERR_ENVIRONMENT_UNSUPPORTED_CONFIG);
		}
	}

	/**
	 * Run the bot and begin remote server interaction
	 * @return void
	 */
	public function runBot()
	{
		/* @var Failnet\Connection\Socket */
		$socket = $this->getObject('core.socket');
		/* @var Failnet\Event\Dispatcher */
		$dispatcher = $this->getObject('core.dispatcher');
		/* @var Failnet\Cron\Manager */
		$cron = $this->getObject('core.cron');

		// Connect to the remote server, assuming nothing blows up of course.
		$socket->connect();

		// Dispatch a connection event
		if($dispatcher->hasListeners('Runtime\\Connect'))
		{
			$trigger = new Event\Runtime\Connect();
			$dispatcher->dispatch($trigger);
		}

		try
		{
			// Now we go around in endless circles until someone lays down a giant bear trap and catches us.
			while(true)
			{
				$queue = array();
				$quit = NULL;

				$queue = array_merge($cron->runTasks(), $queue);
				$event = $socket->get();
				if($event)
				{
					// Verify that this event type has listeners assigned, then dispatch it to those registered for it.
					if($dispatcher->hasListeners($event->getType()))
						$queue = array_merge($dispatcher->dispatch($event), $queue);
				}

				if(!empty($queue))
				{
					foreach($queue as $outbound)
					{
						// If this is a quit event, we'll quit after all other events have processed.
						if($outbound->getType() === 'IRC\\Quit')
						{
							$quit = $outbound;
							continue;
						}

						// Fire off a predispatch event, to allow listeners to modify events before they are sent.
						// Useful for features like self-censoring.
						if($dispatcher->hasListeners('Runtime\\PreDispatch'))
						{
							$trigger = new Event\Runtime\PreDispatch();
							$trigger['event'] = $outbound;
							$dispatcher->dispatch($trigger);
						}

						// Send off the event!
						$socket->send($outbound);

						// Fire off a postdispatch event, to allow listeners to react to events being sent.
						// Useful for things like logging.
						if($dispatcher->hasListeners('Runtime\\PostDispatch'))
						{
							$trigger = new Event\Runtime\PostDispatch();
							$trigger['event'] = $outbound;
							$dispatcher->dispatch($trigger);
						}
					}

					// If we have a quit event, break out of the loop.
					if($quit)
						break;
				}
			}
		}
		catch(FailnetException $e)
		{
			// @todo do stuff here

			try
			{
				// Dispatch an emergency abort event.
				if($dispatcher->hasListeners('Runtime\\Abort'))
				{
					$trigger = new Event\Runtime\Abort();
					$dispatcher->dispatch($trigger);
				}
			}
			catch(FailnetException $e)
			{
				// Another exception?  FFFUUUUUUU--
				// CRASH BANG BOOM.
				exit;
			}
			exit;
		}

		// Dispatch a pre-shutdown event.
		if($dispatcher->hasListeners('Runtime\\Shutdown'))
		{
			$trigger = new Event\Runtime\Shutdown();
			$dispatcher->dispatch($trigger);
		}

		// Send the quit event.
		$socket->send($quit);

		// @todo handle exit gracefully here
	}
}
