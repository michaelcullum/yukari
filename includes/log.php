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
 * Copyright:	(c) 2009 - Failnet Project
 * License:		GNU General Public License - Version 2
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
 * Failnet - Logging handling class,
 * 		Used as Failnet's logging handler. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_log extends failnet_common
{
	/**
	 * Queue of logs to be written
	 * @var array
	 */
	private $log = array();
	
	/**
	 * Initiator method
	 * @see includes/failnet_common#init()
	 */
	public function init()
	{
		$this->add('--- Starting Failnet ---', true);
	}
	
	/**
	 * Build a log message...
	 * @param string $log - The message/action to log
	 * @param string $who - Who sent the message? 
	 * @param mixed $where - What was the recipient? A channel, or ourselves (as in, /msg)
	 * @param boolean $is_action - Is this an action?
	 * @return void
	 */
	public function log($log, $who, $where = false, $is_action = false)
	{
		if(preg_match('/^IDENTIFY (.*)/i', $log)) $log = 'IDENTIFY ***removed***';
		$log = (preg_match('/' . PHP_EOL . '(| )$/i', $log)) ? substr($log, 0, strlen($log) - 1) : $log;
		if(!$is_action)
		{
			$this->add(date('D m/d/Y - h:i:s A') . " - <{$who}" . (($where) ? '/' . $where : false) . "> {$log}");
		}
		else
		{
			$this->add(date('D m/d/Y - h:i:s A') . " - <{$who}" . (($where) ? '/' . $where : false) . "> *** {$who} {$log}");
		}
	}
	
	/**
	 * Add an entry to the queue of user logs...
	 * @param string $msg - The entry to add
	 * @param boolean $dump - Should we immediately dump all log entries into the log file after adding this to the quue? 
	 * @return void
	 */
	public function add($msg, $dump = false)
	{
		$this->log[] = $msg;
		if($dump === true || sizeof($this->log) > $this->failnet->get('log_queue'))
		{
			$log_msg = '';
			$log_msg = implode(PHP_EOL, $this->log). PHP_EOL;
			$this->log = array();
			$this->write(self::USER_LOG, time(), $log_msg);
		}
	}
	
	/**
	 * Directly add an entry to the logs.  Useful for if we want to write to the error logs. ;)
	 * @param string $type - The type of log to write to
	 * @param integer $time - The current UNIX timestamp
	 * @param string $msg - The message to write
	 * @return boolean - Whether the write was successful or not.
	 */
	public function write($type, $time, $msg)
	{
		return file_put_contents(FAILNET_ROOT . "logs/{$type}_log_" . date('m-d-Y', $time) . '.log', $msg, FILE_APPEND | LOCK_EX);
	}

	/**
	 * Nuke the log file!
	 * @param string $type - The type of log file to remove
	 * @param integer $time - The timestamp for the day of the log file
	 * @return boolean - Was the delete successful? 
	 */
	public function wipe($type, $time)
	{
		return @unlink(FAILNET_ROOT . "logs/{$type}_log_" . date('m-d-Y', $time) . '.log');
	}
}

?>