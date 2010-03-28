<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * @version:	2.0.0 Alpha 2
 * @copyright:	(c) 2009 - 2010 -- Failnet Project
 * @license:	http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
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
 * Failnet - Error handling class,
 * 		Used as Failnet's error handler.
 *
 *
 * @package core
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

		$error = 'in file ' . $errfile . ' on line ' . $errline . ': ' . $msg_text . PHP_EOL;
		$handled = false;

		switch ($errno)
		{
			case E_NOTICE:
			case E_STRICT:
			case E_DEPRECATED:
			case E_USER_NOTICE:
			case E_USER_DEPRECATED:
				$handled = true;
				$this->failnet->ui->ui_notice($error);
				$this->failnet->log->write('error', time(), date('D m/d/Y - h:i:s A') . ' - [PHP Notice] ' . $error);
			break;

			case E_WARNING:
			case E_USER_WARNING:
				$handled = true;
				$this->failnet->ui->ui_warning($error);
				$this->failnet->log->write('error', time(), date('D m/d/Y - h:i:s A') . ' - [PHP Warning] ' . $error);
			break;

			case E_ERROR:
			case E_USER_ERROR:
				$handled = true;
				$this->failnet->ui->ui_error($error);
				$this->failnet->log->write('error', time(), date('D m/d/Y - h:i:s A') . ' - [PHP Error] ' . $error);
			break;
		}

		if($errno == E_USER_ERROR)
		{
			// Fatal error, so DAI.
			$this->failnet->terminate(false);
		}

		// If we notice an error not handled here we pass this back to PHP by returning false
		// This may not work for all php versions
		return ($handled) ? true : false;
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
			$error = $msg . PHP_EOL;
			$this->failnet->log->write('error', time(), date('D m/d/Y - h:i:s A') . ' - [Internal notice] ' . $error);
			$this->failnet->ui->ui_system('[Internal error] ' . $error);
		}
		else
		{
			$error = $msg . PHP_EOL;
			$this->failnet->log->write('error', time(), date('D m/d/Y - h:i:s A') . ' - [Internal error] ' . $error);
			$this->failnet->ui->ui_system('[Internal error] ' . $error);
			// Fatal error, so DAI.
			$this->failnet->terminate(false);
		}
	}
}
