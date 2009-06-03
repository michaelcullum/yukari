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
if(!defined('IN_FAILNET')) return;


/**
 * Failnet - Logging handling class,
 * 		Used as Failnet's logging handler. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_logs extends failnet_common
{
	private $log = array();
	
	// Build a log message...
	public function log($log, $who, $where = false)
	{
		if(preg_match('/^IDENTIFY (.*)/i', $log)) $log = 'IDENTIFY ***removed***';
		$log = (preg_match('/' . self::NL . '(| )$/i', $log)) ? substr($log, 0, strlen($log) - 1) : $log;
		$log = preg_replace('/^' . self::X01 . 'ACTION (.+)' . self::X01 . '$/', '*'. $who . ' $1' . '*', $log);
		$this->add(self::USER_LOG, time(), @date('D m/d/Y - h:i:s A') . ' - <' . $who . (($where) ? '/' . $where : false) . '> ' . $log);
	}
	
	// Add an entry to the queue of user logs...
	public function add($type, $time, $msg)
	{
		$this->log[] = $msg;
		if($dump == true || sizeof($this->log) > 10)
		{
			$log_msg = '';
			$log_msg = self::NL . implode(self::NL, $this->log);
			$this->log = array();
			$this->write($type, $time, $log_msg);
		}
	}
	
	// Directly add an entry to the logs.  Useful for if we want to write to the error logs. ;)
	public function write($type, $time, $msg)
	{
		return file_put_contents('logs/' . $type . '_log_' . @date('m-d-Y', $time) . '.log', $msg, FILE_APPEND);
	}
	
	// Nuke the log file!
	public function wipe($type, $time)
	{
		return unlink('logs/' . $type . '_log_' . @date('m-d-Y', $time) . '.log');
	}
}

?>