<?php

/**
 * Failnet - Plugin command class,
 * 		Used as a direct command handling class for Failnet.
 * 
 *  Heavily borrowed from Phergie.
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
abstract class failnet_plugin_command extends failnet_plugin_common
{
	/**
	 * Cache for command lookups used to confirm that methods exist and 
	 * parameter counts match
	 *
	 * @var array
	 */
	private $methods = array();

	/**
	 * Initialize the methods cache when the bot connects to the server.
	 *
	 * @return void
	 */
	public function cmd_connect()
	{
		$reflector = new ReflectionClass(get_class($this));
		foreach ($reflector->getMethods() as $method)
		{
			$name = $method->getName();
			if (strpos($name, 'call_') === 0)
			{
				$this->methods[strtolower(substr($name, 4))] = array(
					'total' => $method->getNumberOfParameters(),
					'required' => $method->getNumberOfRequiredParameters()
				);
			}
		}
	}

	/**
	 * Parses a given message and, if its format corresponds to that of a
	 * defined command, calls the handler method for that command with any
	 * provided parameters.
	 *
	 * @return void
	 */
	public function cmd_privmsg()
	{
		// Get the content of the message
		$msg = trim($this->event->get_arg('text'));

		if (strpos($msg, $this->failnet->nick) !== 0)
		{
			return;
		}
		else
		{
			$msg = substr($msg, strlen($this->failnet->nick));
		}

		// Separate the command and arguments
		$parsed = preg_split('/\s+/', $msg, 2);
		$cmd = strtolower(array_shift($parsed));
		$args = count($parsed) ? array_shift($parsed) : '';
		$method = 'call_cmd_' . strtolower($cmd); 

		// Check to ensure the command exists
		if (empty($this->methods[$cmd]))
			return;

		// If no arguments are passed...
		if (empty($args))
		{
			// If the method requires no arguments, call it
			if (empty($this->methods[$cmd]['required']))
				$this->$method();

		// If arguments are passed...
		}
		else
		{
			// Parse the arguments
			$args = preg_split('/\s+/', $args, $this->methods[$cmd]['total']);

			// If the minimum arguments are passed, call the method 
			if ($this->methods[$cmd]['required'] <= count($args))
				call_user_func_array(array($this, $method), $args);
		}
	}
}
