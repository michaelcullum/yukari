<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 1
 * Copyright:	(c) 2009 - 2010 -- Failnet Project
 * License:		GNU General Public License - Version 2
 *
 *===================================================================
 *
 */

/**
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
 */



/**
 * Failnet - Error handling class,
 * 		Used as Failnet's error handler. 
 * 
 *
 * @package logs
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_error extends failnet_common
{
	/**
	 * Specialized init function to allow class construction to be easier.
	 * @see includes/failnet_common#init()
	 * @return void
	 */
	public function init() { }

	/**
	 * Error handler function for Failnet.  Modified from the phpBB 3.0.x msg_handler() function.
	 * @param integer $errno - Level of the error encountered 
	 * @param string $msg_text - The error message recieved
	 * @param string $errfile - The file that the error was encountered at
	 * @param integer $errline - The line that the error was encountered at
	 * @return mixed - If suppressed, nothing returned...if not handled, false.
	 */
	public function fail($errno, $msg_text, $errfile, $errline)
	{
		global $msg_long_text;

		// Do not display notices if we suppress them via @
		if (error_reporting() == 0)
			return;

		// Message handler is stripping text. In case we need it, we are possible to define long text...
		if (isset($msg_long_text) && $msg_long_text && !$msg_text)
			$msg_text = $msg_long_text;
		
		// Strip the current directory from the offending file
		if (empty($errfile))
		{
			$errfile = '';
		}
		else
		{
			$errfile = str_replace(array(fail_realpath(FAILNET_ROOT), '\\'), array('', '/'), $errfile);
			$errfile = substr($errfile, 1);
		}

		switch ($errno)
		{
			case E_NOTICE:
			case E_WARNING:
			case E_STRICT:
			case E_DEPRECIATED:
			case E_USER_WARNING:
			case E_USER_NOTICE:
			case E_USER_DEPRECIATED:
			default:
				$error = '[Debug] PHP Notice: in file ' . $errfile . ' on line ' . $errline . ': ' . $msg_text . PHP_EOL; 
				$this->failnet->log->write(self::ERROR_LOG, time(), date('D m/d/Y - h:i:s A') . ' - ' . $error);
				display($error);
				return;
				break;
	
			case E_USER_ERROR:
				$error = '[ERROR] PHP Error: in file ' . $errfile . ' on line ' . $errline . ': ' . $msg_text . PHP_EOL;
				$this->failnet->log->write(self::ERROR_LOG, time(), date('D m/d/Y - h:i:s A') . ' - ' . $error);
				display($error);
				// Fatal error, so DAI.
				$this->failnet->terminate(false);
				break;
		}

		// If we notice an error not handled here we pass this back to PHP by returning false
		// This may not work for all php versions
		return false;
	}

	/**
	 * Manually throw an error.
	 * @param strinv $msg - The error message
	 * @param boolean $is_fatal - Is it a fatal error?
	 * @return void
	 */
	public function error($msg, $is_fatal = false)
	{
		if(!$is_fatal)
		{
			$error = '[Debug] ' . $msg . PHP_EOL; 
			$this->failnet->log->write(self::ERROR_LOG, time(), date('D m/d/Y - h:i:s A') . ' - ' . $error);
			display($error);
		}
		else
		{
			$error = '[ERROR] ' . $msg . PHP_EOL;
			$this->failnet->log->write(self::ERROR_LOG, time(), date('D m/d/Y - h:i:s A') . ' - ' . $error);
			display($error);
			// Fatal error, so DAI.
			$this->failnet->terminate(false);
		}
	}
}

