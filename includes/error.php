<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0
 * SVN ID:		$Id$
 * Copyright:	(c) 2009 - Obsidian
 * License:		http://opensource.org/licenses/gpl-2.0.php  |  GNU Public License v2
 *
 *===================================================================
 *
 */

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
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
 * @ignore
 */
if(!defined('IN_FAILNET')) exit(1);


/**
 * Failnet - Error handling class,
 * 		Used as Failnet's error handler. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_error extends failnet_common
{
	public function init()
	{
		display('=== Setting main error handler');
			@set_error_handler('fail_handler');
	}

	/**
	 * Error handler function for Failnet.  Modified from the phpBB 3.0.x msg_handler() function.
	 */
	public function fail($errno, $msg_text, $errfile, $errline)
	{
		global $msg_long_text;
	
		// Do not display notices if we suppress them via @
		if (error_reporting() == 0)
		{
			return;
		}
	
		// Message handler is stripping text. In case we need it, we are possible to define long text...
		if (isset($msg_long_text) && $msg_long_text && !$msg_text)
		{
			$msg_text = $msg_long_text;
		}
	
		
		switch ($errno)
		{
			case E_NOTICE:
			case E_WARNING:
			case E_USER_WARNING:
			case E_USER_NOTICE:
			default:
				$error = '[Debug] PHP Notice: in file ' . $errfile . ' on line ' . $errline . ': ' . $msg_text . PHP_EOL; 
				$this->failnet->log->write(self::ERROR_LOG, time(), date('D m/d/Y - h:i:s A') . ' - ' . $error);
				display($error);
				return;
				break;
	
			case E_USER_ERROR:
			case E_PARSE:
			case E_ERROR:
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

?>