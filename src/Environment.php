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
	 * @var boolean - Should we trigger a bot shutdown?
	 */
	protected $shutdown = false;

	/**
	 * Constructor
	 * @return void
	 */
	public function __construct()
	{
		$this->config = array(
			'language.default_locale'	=> 'en-US',
			'ui.output_level'			=> 'normal',
			'environment.addons'		=> array(),
			'core.timezonestring'		=> date_default_timezone_get(),
			'server.port'				=> 6667,
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
		return $value;
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

	public function triggerShutdown(\Yukari\Event\Instance $event)
	{
		if($event->getName() === 'system.shutdown')
			$this->shutdown = true;
	}














































	public function init()
	{
		try
		{
			// load config file here, load CLI args parser
			$cli = Kernel::set('core.cli', new \Yukari\CLI\CLIArgs($_SERVER['argv']));
			$config = (isset($cli['config'])) ? $cli['config'] : 'config.yml';

			// Make sure that the config file is usable.
			if(!file_exists(YUKARI . "/data/config/{$config}") || !is_readable(YUKARI . "/data/config/{$config}"))
				throw new \RuntimeException('Configuration file directory does not exist, or is not readable/writeable');

			// Load the configs
			Kernel::importConfig(\sfYaml::load(YUKARI . "/data/config/{$config}"));

			// check for missing required configs
			$required_configs = array(
				'language.file_dir',
				'ui.output_level',

				'irc.url',
				'irc.username',
				'irc.realname',
				'irc.nickname',
			);

			foreach($required_configs as $required_config_name)
			{
				if(!isset($this->config[$required_config_name]))
					throw new \RuntimeException(sprintf('Required config entry "%s" not defined', $required_config_name));
			}

			// Load up the event dispatcher for the very basic core functionality
			$dispatcher = Kernel::set('core.dispatcher', new \Yukari\Event\Dispatcher());

			// Instantiate the UI object
			$ui = Kernel::set('core.ui', new \Yukari\CLI\UI());
			$ui->setOutputLevel(Kernel::getConfig('ui.output_level'))
				->registerListeners();

			// Startup message
			$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'ui.startup'));
			$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'ui.message.system')
				->setDataPoint('message', 'Loading the Yukari core'));

			// Create our timezone object and store it for now, along with storing our starting DateTime object.
			$timezone = Kernel::set('core.timezone', new \DateTimeZone(Kernel::getConfig('core.timezonestring')));
			Kernel::set('core.starttime', new \DateTime('now', $timezone));

			// Define the base memory usage here.
			define('Yukari\\BASE_MEMORY', memory_get_usage());

			// Load the language manager
			$language = Kernel::set('core.language', new \Yukari\Language\Manager());
			$language->setPath(YUKARI . Kernel::getConfig('language.file_dir'))
				->collectEntries();

			// Load the password hashing library
			$hash = Kernel::set('lib.hash', new \Yukari\Lib\Hash());

			// Load the session manager
			$session = Kernel::set('core.session', new \Yukari\Session\Manager());

			// Connect to the database
			// @todo PDO code here

			// Load any addons we want.
			$addon_loader = Kernel::set('core.addonloader', new \Yukari\Addon\Loader());
			foreach(Kernel::getConfig('environment.addons') as $addon)
			{
				try
				{
					$addon_loader->loadAddon($addon);
					$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'ui.message.system')
						->setDataPoint('message', sprintf('Loaded addon "%s"', $addon)));
				}
				catch(\Exception $e)
				{
					$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'ui.message.warning')
						->setDataPoint('message', sprintf('Failed to load addon "%1$s" - failure message: "%2$s"', $addon, $e->getMessage())));
				}
			}

			$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'ui.message.system')
						->setDataPoint('message', 'Registering listeners to event dispatcher'));
			foreach(Kernel::getConfig('dispatcher.listeners') as $event_name => $listener)
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

			// Dispatch a startup event
			// This is useful for having a listener registered, waiting for startup to complete before loading in one last thing
			$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'runtime.startup'));
		}
		catch(\Exception $e)
		{
			throw new \RuntimeException(sprintf('Yukari environment initialization encountered a fatal exception (%1$s::%2$s)' . PHP_EOL . 'Exception message: %3$s', get_class($e), $e->getCode(), $e->getMessage()), $e);
		}






		if(!file_exists(YUKARI . 'data/config/') || !is_readable(YUKARI . 'data/config/') || !is_writeable(YUKARI . 'data/config/') || !is_dir(YUKARI . 'Data/Config/'))
			throw new \RuntimeException('Configuration file directory does not exist, or is not readable/writeable');
		// @note if doctrine is used, this code must be removed
		if(!file_exists(YUKARI . 'data/DB/') || !is_readable(YUKARI . 'data/DB/') || !is_writeable(YUKARI . 'data/DB/') || !is_dir(YUKARI . 'data/DB/'))
			throw new \RuntimeException('Database directory does not exist, or is not readable/writeable');

		// Nerf the pyro, then init the Bot with a reference back to the environment.
		//Kernel::setEnvironment($this);

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

				//$this->setObject('core.ui', new Failnet\CLI\UI($this->getOption('ui.output_level', 'normal')));

				/* @var Failnet\CLI\UI */
				$ui = $this->getObject('core.ui');

				// Fire off the UI's startup text.
				$ui->startup();
				$ui->status('Loading the Yukari core system');

				// Start loading stuff.
				$ui->system('Loading internationalization object');
				$this->setObject('core.language', new Failnet\Language\Manager(YUKARI . Kernel::getConfig('language.file_dir', '/data/language')));
				$ui->system('Loading password hashing library');
				$this->setObject('core.hash', new Failnet\Lib\Hash(8));
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
				$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'runtime.startup'));

				// Load any addons we want.
				$this->setObject('core.addon', new Failnet\Addon\Loader());
				/* @var $addon_loader Failnet\Addon\Loader */
				$addon_loader = $this->getObject('core.addon');
				foreach(Bot::getOption('environment.addons', array()) as $addon)
				{
					try
					{
						$addon_loader->loadAddon($addon);
						$ui->system(sprintf('Loaded addon "%s" successfully', $addon));
					}
					catch(Failnet\Addon\LoaderException $e)
					{
						$ui->warning(sprintf('Failed to load addon "%s"', $addon));
						$ui->warning(sprintf('Failure message:  %s', $e->getMessage()));
					}
				}
			}
		}
		catch(\Exception $e)
		{
			throw new \RuntimeException(sprintf('Yukari environment initialization encountered a fatal exception (%1$s::%2$s)' . PHP_EOL . 'Exception message: %3$s', get_class($e), $e->getCode(), $e->getMessage()), $e);
		}
	}

	/**
	 * Load a specified configuration file
	 * @param string $config -The config file to load, either JSON or PHP.
	 * @return void
	 *
	 * @throws Failnet\EnvironmentException
	 * @deprecated
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
		/* @var \Yukari\Connection\Socket */
		$socket = Kernel::get('core.socket');
		/* @var \Yukari\Event\Dispatcher */
		$dispatcher = Kernel::get('core.dispatcher');

		// Hook up the shutdown listener here real quick
		$dispatcher->register('system.shutdown', array(Kernel::getEnvironment(), 'triggerShutdown'));

		// Connect to the remote server, assuming nothing blows up of course.
		$socket->connect();

		// Dispatch a connection event
		$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'runtime.connect'));

		try
		{
			// Now we go around in endless circles until someone lays down a giant bear trap and catches us.
			while(true)
			{
				$queue = array();

				// Fire off a tick event.
				$queue = array_merge($dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'runtime.tick')), $queue);

				// Grab an event from the socket
				$event = $socket->get();

				// If we got one, we process the event we received
				if($event)
					$queue = array_merge($dispatcher->trigger($event), $queue);

				if(!empty($queue))
				{
					foreach($queue as $outbound)
					{
						// Fire off a predispatch event, to allow listeners to modify events before they are sent.
						// Useful for features like self-censoring.
						$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'runtime.predispatch')
							->setDataPoint('response', $outbound));

						// Send off the event!
						$socket->send($outbound);

						// Fire off a postdispatch event, to allow listeners to react to events being sent.
						// Useful for things like logging.
						$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'runtime.postdispatch')
							->setDataPoint('response', $outbound));
					}

					// If we have a quit event, break out of the loop.
					if($this->shutdown === true)
						break;
				}
			}
		}
		catch(\Exception $e)
		{
			// @todo do stuff here

			try
			{
				// Dispatch an emergency abort event.
				$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'runtime.abort'));
			}
			catch(\Exception $e)
			{
				// Another exception?  FFFUUUUUUU--
				// CRASH BANG BOOM.
				exit;
			}
			exit;
		}

		// Dispatch a pre-shutdown event.
		$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'runtime.shutdown'));

		// Send a quit event, handle exit gracefully.
		$socket->send(sprintf('QUIT :Yukari IRC Bot - %s', Kernel::getBuildNumber()));
		$socket->close();
	}
}
