<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2010 -- Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Failnet;
use Failnet\Lib as Lib;
use Failnet\Event as Event;

/**
 * Failnet - Environment class,
 *      Manages the Failnet environment.
 *
 *
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
class Environment
{
	/**
	 * @var array - The core objects, which will also include the core class.
	 */
	protected $core = array();

	/**
	 * @var array - Array of loaded cron objects
	 */
	protected $cron = array();

	/**
	 * @var array - Array of loaded addon objects
	 */
	protected $addons = array();

	/**
	 * @var array - Array of various loaded objects
	 */
	protected $objects = array();

	/**
	 * @var array - Array of loaded configuration options
	 */
	protected $options = array();

	/**
	 * Constructor
	 * @return void
	 *
	 * @throws Failnet\EnvironmentException
	 */
	public function __construct()
	{
		if(!file_exists(FAILNET . 'data/config/') || !is_readable(FAILNET . 'data/config/') || !is_writeable(FAILNET . 'data/config/') || !is_dir(FAILNET . 'Data/Config/'))
			throw new EnvironmentException('Configuration file directory does not exist, or is not readable/writeable', EnvironmentException::ERR_ENVIRONMENT_NO_ACCESS_CFG_DIR);
		if(!file_exists(FAILNET . 'data/DB/') || !is_readable(FAILNET . 'data/DB/') || !is_writeable(FAILNET . 'data/DB/') || !is_dir(FAILNET . 'data/DB/'))
			throw new EnvironmentException('Database directory does not exist, or is not readable/writeable', EnvironmentException::ERR_ENVIRONMENT_NO_ACCESS_DB_DIR);

		// Check to see if date.timezone is empty in the PHP.ini; if so, set the timezone with some Hax to prevent strict errors.
		if(!ini_get('date.timezone'))
			@date_default_timezone_set(@date_default_timezone_get());

		// Run indefinitely...
		set_time_limit(0);

		// The first chunk always gets in the way, so we drop it.
		array_shift($_SERVER['argv']);

		// nerf the pyro, then init the Bot with a reference back to the environment.
		Bot::init($this);

		try
		{
			// Register our autoloader
			$this->setObject('core.autoload', new Failnet\Autoload());
			spl_autoload_register(array($this, 'autoloadClass'));

			// Setup our CLI object, and grab any passed args
			$this->setObject('core.cli', new Failnet\Core\CLI($_SERVER['argv']));
			/* @var Failnet\Core\CLI */
			$cli = $this->getObject('core.cli');
			define('Failnet\\IN_INSTALL', ($cli['mode'] === 'install') ? true : false);
			define('Failnet\\CONFIG_FILE', ($cli['config'] ? $cli['config'] : 'Config.php'));

			if(Failnet\IN_INSTALL)
			{
				// stuff for the dynamic installer goes here
				$this->setObject('core.ui', new Failnet\Install\UI($this->getOption('ui.output_level', 'normal')));

				/* @var Failnet\Install\UI */
				$ui = $this->getObject('core.ui');

				// Fire off the UI's startup text.
				$ui->startup();
				$ui->status('Loading Failnet core objects');

				$ui->system('Loading core.core object');
				$this->setObject('core.core', new Failnet\Install\Core());
			}
			else
			{
				if(!file_exists(FAILNET . 'Data/Config/' . Failnet\CONFIG_FILE))
					throw new EnvironmentException(sprintf('The configuration file "%1$s" could not be loaded, as it does not exist.', Failnet\CONFIG_FILE), EnvironmentException::ERR_ENVIRONMENT_CONFIG_MISSING);

				// load the config file up next
				$this->loadConfig(Failnet\CONFIG_FILE);

				$this->setObject('core.ui', new Failnet\Core\UI($this->getOption('ui.output_level', 'normal')));

				/* @var Failnet\Core\UI */
				$ui = $this->getObject('core.ui');

				// Fire off the UI's startup text.
				$ui->startup();
				$ui->status('Loading Failnet core objects');

				// Start loading stuff.
				$ui->system('Loading core.core object');
				$this->setObject('core.core', new Failnet\Core\Core());
				$ui->system('Loading core.language object');
				$this->setObject('core.language', new Failnet\Core\Language(Bot::getOption('language.file_dir', FAILNET . 'Data/Language')));
				$ui->system('Loading core.hash object');
				$this->setObject('core.hash', new Failnet\Core\Hash(8, true));
				$ui->system('Loading core.dispatcher object');
				$this->setObject('core.dispatcher', new Failnet\Event\Dispatcher());
				$ui->system('Loading core.session object');
				$this->setObject('core.session', new Failnet\Session\Manager());

				// Include any extra files that the user has specified
				foreach(Bot::getOption('environment.extra_files', array()) as $file)
				{
					// Load the file, or asplode if it fails to load
					if(($include = @include($file)) === false)
						throw new EnvironmentException(sprintf('Failed to load extra file "%1$s"', $file), EnvironmentException::ERR_ENVIRONMENT_EXTRA_FILE_LOAD_FAIL);
				}

				// Load our language files
				$ui->system('Loading language files');
				$this->getObject('core.language')->collectEntries();

				// @todo load nodes here

				// Register our event listeners to the dispatcher
				/* @var Failnet\Core\Dispatcher */
				$dispatcher = $this->getObject('core.dispatcher');
				foreach(Bot::getOption('dispatcher.listeners', array()) as $listener)
					$dispatcher->register($listener['event'], $listener['listener'], (isset($listener['params']) ? $listener['params'] : NULL));
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
		// grab the file extension of the config file, see if it is PHP or JSON...we'll react appropriately based on which we're working with.
		$file_extension = substr(strrchr($config, '.'), 1);
		if($file_extension == 'php')
		{
			if(($include = @include(FAILNET . "Data/Config/$config")) === false || !isset($data) || !is_array($data))
				throw new EnvironmentException('Failed to load the specified config file "%1$s"', EnvironmentException::ERR_ENVIRONMENT_FAILED_CONFIG_LOAD);
			$this->setOptions($data);
		}
		elseif($file_extension == 'json')
		{
			$data = Lib\JSON::decode(FAILNET . "Data/Config/$config");
			$this->setOptions($data);
		}
		else
		{
			throw new EnvironmentException('The specified config file\'s file type is not supported', EnvironmentException::ERR_ENVIRONMENT_UNSUPPORTED_CONFIG);
		}
	}

	/**
	 * Get an object for whatever purpose
	 * @param mixed $object - The object's location and name.  Either an array of format array('type'=>'objecttype','name'=>'objectname'), or a string of format 'objecttype.objectname'
	 * @return mixed - The desired object.
	 *
	 * @throws Failnet\EnvironmentException
	 */
	public function getObject($object)
	{
		// If this is not an array, we need to resolve the object name for something usable.
		if(!is_array($object))
			$object = $this->resolveObject($object);
		list($name, $type) = $object;

		if(property_exists($this, $type))
		{
			if(isset($this->$type[$name]))
				return $this->$type[$name];
		}
		else
		{
			if(isset($this->objects[$type][$name]))
				return $this->objects[$type][$name];
		}
		throw new EnvironmentException(sprintf('The object "%1$s" was unable to be fetched.', "$type.$name"), EnvironmentException::ERR_ENVIRONMENT_NO_SUCH_OBJECT);
	}

	/**
	 * Load an object into the global class.
	 * @param mixed $object - The object's location and name.  Either an array of format array('type'=>'objecttype','name'=>'objectname'), or a string of format 'objecttype.objectname'
	 * @param mixed $value - The object to load.
	 * @return void
	 */
	public function setObject($object, $value)
	{
		if(!is_array($object))
			$object = $this->resolveObject($object);
		list($name, $type) = $object;

		if(property_exists($this, $type))
		{
			if(isset($this->$type[$name]))
				$this->$type[$name] = $value;
		}
		else
		{
			if(isset($this->objects[$type][$name]))
				$this->objects[$type][$name] = $value;
		}
	}

	/**
	 * Remove an object from the global class.
	 * @param mixed $object - The object's location and name.  Either an array of format array('type'=>'objecttype','name'=>'objectname'), or a string of format 'objecttype.objectname'
	 * @return void
	 */
	public function removeObject($object)
	{
		if(!is_array($object))
			$object = $this->resolveObject($object);
		list($name, $type) = $object;

		if(property_exists($this, $type))
		{
			if(isset($this->$type[$name]))
				unset($this->$type[$name]);
		}
		else
		{
			if(isset($this->objects[$type][$name]))
				unset($this->objects[$type][$name]);
		}
	}

	/**
	 * Check to see if an object has been loaded or not
	 * @param mixed $object - The object's location and name.  Either an array of format array('type'=>'objecttype','name'=>'objectname'), or a string of format 'objecttype.objectname'
	 * @return boolean - Do we have this object?
	 */
	public function checkObjectLoaded($object)
	{
		if(!is_array($object))
			$object = $this->resolveObject($object);
		list($name, $type) = $object;

		if(property_exists($this, $type))
			return isset($this->$type[$name]);
		return isset($this->objects[$type][$name]);
	}

	/**
	 * Resolves an object's name
	 * @param string $object - The object's name we want to resolve into a workable array
	 * @return array - The resolved name location for the object.
	 */
	protected function resolveObject($object)
	{
		$object = explode('.', $object, 1);
		$return = array(
			'name' => isset($object[1]) ? $object[1] : $object[0],
			'type' => isset($object[1]) ? $object[0] : 'core',
		);
		return $return;
	}

	/**
	 * Grab an option, or return the default if the option isn't set
	 * @param string $option - The option name to grab.
	 * @param mixed $default - The default value to use if the option is not set.
	 * @param boolean $is_required - Is this option required, or can it flip to the default?
	 * @return mixed - The value of the option we're grabbing, or the default if it's not set.
	 *
	 * @throws Failnet\EnvironmentException
	 */
	public function getOption($option, $default, $is_required = false)
	{
		if(isset($this->options[$option]))
			return $this->options[$option];
		if($is_required)
			throw new EnvironmentException(sprintf('The required option "%1$s" is not set', $option), EnvironmentException::ERR_ENVIRONMENT_OPTION_NOT_SET);
		return $default;
	}

	/**
	 * Set an option to a specified value
	 * @param string $option - The option name to set.
	 * @param mixed $value - The value to set.
	 * @return void
	 */
	public function setOption($option, $value)
	{
		$this->options[$option] = $value;
	}

	/**
	 * Set multiple options at once.
	 * @param array $options - The array of options to set, with the array keys being the option names, and array values being the option values.
	 * @return void
	 */
	public function setOptions(array $options)
	{
		$this->options = array_merge($this->options, $options);
	}

	/**
	 * Autoload a class file up.
	 * @param string $class - The class to load the file for.
	 * @return mixed - Whatever the autoloader's loadFile() call returned.
	 */
	public function autoloadClass($class)
	{
		return $this->getObject('core.autoload')->loadFile($class);
	}

	/**
	 * Run the bot and begin remote server interaction
	 * @return void
	 */
	public function runBot()
	{
		/* @var Failnet\Core\Socket */
		$socket = $this->getObject('core.socket');
		/* @var Failnet\Core\Dispatcher */
		$dispatcher = $this->getObject('core.dispatcher');
		/* @var Failnet\Core\Cron */
		$cron = $this->getObject('core.cron');

		// Dispatch a pre-connection event
		if($dispatcher->hasListeners('Runtime\\Connect'))
		{
			$trigger = new Event\Runtime\Connect();
			$trigger['event'] = $outbound;
			$dispatcher->dispatch($trigger);
		}

		// @todo try/catch block this

		// Connect to the remote server, assuming nothing blows up of course.
		$socket->connect();

		try
		{
			// Now we go around in endless circles until someone lays down a bear trap and catches us.
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
						if($outbound->getType() === 'Quit')
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
				// @todo just terminate immediately.  No sense going on.
			}

			// CRASH BANG BOOM.
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
