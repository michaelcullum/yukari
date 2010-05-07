<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version		2.1.0 DEV
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @copyright	(c) 2009 - 2010 -- Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-2.0.php>.
 *
 */


/**
 * Failnet - Exception class,
 * 		Extension of the default Exception class, adapted to suit Failnet's needs.
 *
 *
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class failnet_exception extends Exception
{
	/**
	 * @var array - Array of "translations" for our various error codes.
	 */
	private $translations = array();

	const ERR_STARTUP_MIN_PHP = 1000;
	const ERR_STARTUP_PHP_SAPI = 1001;
	const ERR_STARTUP_NO_PDO = 1002;
	const ERR_STARTUP_NO_PDO_SQLITE = 1003;
	const ERR_STARTUP_NO_ACCESS_DB_DIR = 1004;

	const ERR_NO_SUCH_CORE_OBJ = 1100;
	const ERR_NO_SUCH_NODE_OBJ = 1101;
	
	// @todo add message
	const ERR_AUTOLOAD_CLASS_INVALID = 1200;
	const ERR_AUTOLOAD_NO_FILE = 1201;

	const ERR_NO_CONFIG = 2000;
	const ERR_INVALID_VIRTUAL_STORAGE_SLOT = 2001;

	const ERR_PDO_EXCEPTION = 2100;
	const ERR_INVALID_PREP_QUERY = 2101;

	const ERR_REGISTER_HOOK_BAD_CLASS = 2200;
	const ERR_REGISTER_HOOK_BAD_HOOK_TYPE = 2201;

	const ERR_UNDEFINED_METHOD_CALL = 2300;
	
	const ERR_SOCKET_ERROR = 2400;
	const ERR_SOCKET_FGETS_FAILED = 2401;
	const ERR_SOCKET_FWRITE_FAILED = 2402;
	const ERR_SOCKET_NO_CONNECTION = 2403;
	const ERR_SOCKET_UNSUPPORTED_TRANSPORT = 2404;

	/**
	 * Exception setup method, loads the error messages up for translation and also performs additional setup if necessary
	 * @return void
	 */
	public function setup()
	{
		$this->translations = array(
			self::ERR_STARTUP_MIN_PHP => 'Failnet ' . FAILNET_VERSION . ' requires PHP ' . FAILNET_MIN_PHP . ' or better, while the currently installed PHP version is ' . PHP_VERSION,
			self::ERR_STARTUP_PHP_SAPI => 'Failnet must be run in the CLI SAPI',
			self::ERR_STARTUP_NO_PDO => 'Failnet requires the PDO PHP extension to be loaded',
			self::ERR_STARTUP_NO_PDO_SQLITE => 'Failnet requires the PDO_SQLite PHP extension to be loaded',
			self::ERR_STARTUP_NO_ACCESS_DB_DIR => 'Failnet requires the database directory to exist and be readable/writeable',

			self::ERR_NO_SUCH_CORE_OBJ => 'An invalid core object was specified for access: %1$s',
			self::ERR_NO_SUCH_NODE_OBJ => 'An invalid node object was specified for access: %1$s', // @todo FIXME
			
			self::ERR_AUTOLOAD_CLASS_INVALID => 'Invalid class contained within file %1$s',
			self::ERR_AUTOLOAD_NO_FILE => 'No class file found for class %1$s',

			self::ERR_NO_CONFIG => 'Specified Failnet configuration file not found',
			self::ERR_INVALID_VIRTUAL_STORAGE_SLOT => 'Undefined virtual-storage property accessed: %1$s',

			self::ERR_PDO_EXCEPTION => 'Database exception thrown: %1$s',
			self::ERR_INVALID_PREP_QUERY => 'The specified prepared PDO query was not found',

			self::ERR_REGISTER_HOOK_BAD_CLASS => 'An invalid class was specified for registering a hook with: %1$s',
			self::ERR_REGISTER_HOOK_BAD_HOOK_TYPE => 'An invalid hook type was specified during hook registration',

			self::ERR_UNDEFINED_METHOD_CALL => 'Call to undefined method - %2$s::%1$s',
			
			self::ERR_SOCKET_ERROR => 'Unable to connect to server: socket error %1$s : %2$s',
			self::ERR_SOCKET_FGETS_FAILED => 'fgets() failed, socket connection lost',
			self::ERR_SOCKET_FWRITE_FAILED => 'fwrite() failed, socket connection lost',
			self::ERR_SOCKET_NO_CONNECTION => 'Cannot send to server - no connection present',
			self::ERR_SOCKET_UNSUPPORTED_TRANSPORT => 'Transport type "%1$s" is not supported by this PHP installation'
		);

		// Just in case we extend this class and want to define additional exception messages
		if(method_exists($this, 'extraSetup'))
			$this->extraSetup();
	}

	/**
	 * Error translation method, takes them pesky error numbers and gives you something you can actually use!
	 * @return object returns itself (i.e. $this) for use with the method Exception::__toString()
	 */
	public function translate()
	{
		if(!sizeof($this->translations))
			$this->setup();

		if(isset($this->code))
			$message = $this->code;
		$this->code = (int) $this->message;
		$this->message = '[Error ' . $this->code . ']' . (isset($message)) ? sprintf($this->translations[$this->message], (!is_array($message) ? array($message) : $message)) : $this->translations[$message];

		// We return $this so that one may make use of Exception::__toString() directly after calling this method
		// so, pretty much... echo failnet_exception::translate() should work nicely
		return $this;
	}
}
