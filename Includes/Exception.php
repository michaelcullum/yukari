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
 * @author      Failnet Project
 * @copyright   (c) 2009 - 2010 -- Failnet Project
 * @license     GNU General Public License, Version 3
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @todo branch out this class into several for each "package"
 *
 */

namespace Failnet;

/**
 * Failnet - Exception class,
 * 	    Extension of the default Exception class, adapted to suit Failnet's needs.
 *
 *
 * @category    Failnet
 * @package     Failnet
 * @author      Failnet Project
 * @license     GNU General Public License, Version 3
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Exception extends \Exception
{
	/**
	 * @var array - Array of "translations" for our various error codes.
	 */
	private static $translations = array();

	const ERR_UNK = 0;

	const ERR_STARTUP_MIN_PHP = 1000;
	const ERR_STARTUP_PHP_SAPI = 1001;
	const ERR_STARTUP_NO_PDO = 1002;
	const ERR_STARTUP_NO_PDO_SQLITE = 1003;
	const ERR_STARTUP_NO_ACCESS_DB_DIR = 1004;

	const ERR_NO_SUCH_CORE_OBJ = 1100;
	const ERR_NO_SUCH_NODE_OBJ = 1101;
	const ERR_NO_SUCH_CRON_OBJ = 1102;

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

	const ERR_CRON_LOAD_FAILED = 3000;
	const ERR_CRON_NO_SUCH_TASK = 3001;
	const ERR_CRON_TASK_ALREADY_LOADED = 3002;
	const ERR_CRON_INVALID_STATE = 3003;

	const ERR_CRON_INVALID_TASK = 3100;
	const ERR_CRON_TASK_STATUS_INVALID = 3101;
	const ERR_CRON_TASK_ACCESS_MANUAL = 3102;
	const ERR_CRON_TASK_ACCESS_ZOMBIE = 3103;

	/**
	 * Exception setup method, loads the error messages up for translation and also performs additional setup if necessary
	 * @return void
	 */
	public static function setup()
	{
		self::$translations = array(
			self::ERR_UNK => 'Unknown exception thrown', // o_O

			self::ERR_STARTUP_MIN_PHP => 'Failnet requires PHP ' . FAILNET_MIN_PHP . ' or better, while the currently installed PHP version is ' . PHP_VERSION,
			self::ERR_STARTUP_PHP_SAPI => 'Failnet must be run in the CLI SAPI',
			self::ERR_STARTUP_NO_PDO => 'Failnet requires the PDO PHP extension to be loaded',
			self::ERR_STARTUP_NO_PDO_SQLITE => 'Failnet requires the PDO_SQLite PHP extension to be loaded',
			self::ERR_STARTUP_NO_ACCESS_DB_DIR => 'Failnet requires the database directory to exist and be readable/writeable',

			self::ERR_NO_SUCH_CORE_OBJ => 'An invalid core object was specified for access: %1$s',
			self::ERR_NO_SUCH_NODE_OBJ => 'An invalid node object was specified for access: %1$s',
			self::ERR_NO_SUCH_CRON_OBJ => 'An invalid cron object was specified for access: %1$s',
			self::ERR_NO_SUCH_PLUGIN_OBJ => 'An invalid cron object was specified for access: %1$s',

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
			self::ERR_SOCKET_UNSUPPORTED_TRANSPORT => 'Transport type "%1$s" is not supported by this PHP installation',

			self::ERR_CRON_LOAD_FAILED => 'Cron system load failed for unknown reason.',
			self::ERR_CRON_NO_SUCH_TASK => 'No class file found for cron task "%1$s"',
			self::ERR_CRON_TASK_ALREADY_LOADED => 'Cron task "%1$s" is already loaded',
			self::ERR_CRON_INVALID_STATE => 'Attempted to set an invalid state on a cron task',

			self::ERR_CRON_INVALID_TASK => 'Invalid cron task "%1$s" specified',
			self::ERR_CRON_TASK_STATUS_INVALID => 'Cron task "%1$s" has an invalid status code [%2$s]',
			self::ERR_CRON_TASK_ACCESS_MANUAL => 'Attempted to automatically run manual cron task "%1$s"',
			self::ERR_CRON_TASK_ACCESS_ZOMBIE => 'Attempted to run zombie cron task "%1$s"',
		);

		// Just in case we extend this class and want to define additional exception messages
		if(method_exists(self, 'extraSetup'))
			self::extraSetup();
	}

	/**
	 * Takes the provided exception code and returns the exception string format
	 * @param integer &$code - The error code we're using
	 * @return string - The desired error string format for sprintf()
	 */
	public static function getTranslation(&$code = 0)
	{
		if(empty(self::$translations))
			self::setup();
		if(!isset(self::$translations[$code]))
			$code = 0;
		return self::$translations[$code];
	}

	/**
	 * Translates the exception message and extracts the exception code from it.
	 * @return void
	 */
	public function translate()
	{
		static $translate;
		if(!$translate)
		{
			// We use ':' in the error message to denote our error codes, as a hackish way to allow us to alter error codes in-flight
			// @note expected string format -- :E:{$error_code}:{$error_message}
			if($this->message[0] === ':')
			{
				$message = substr($this->message, 3);
				$this->code = (int) substr($message, 0, strpos($message, ':'));
				$this->message = substr($message, strpos($message, ':') + 1);
			}
			$translate = true;
		}
	}

	/**
	 * Hooks into __toString() to automatically translate the exception message if we haven't done so already
	 * @return string - Output of \Exception::__toString()
	 */
	public function __toString()
	{
		$this->translate();
		return parent::__toString();
	}
}
