<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version		3.0.0 DEV
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @copyright	(c) 2009 - 2010 -- Failnet Project
 * @license		GNU General Public License, Version 3
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
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
 */

namespace Failnet\Core;
use Failnet;

/**
 * Failnet - Cron core class,
 * 		Manages the cron system, handles tasks, etc.
 *
 *
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @license		GNU General Public License, Version 3
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Cron extends Base
{
	public $last_event = 0;

	public $task_times = array();

	public function __construct()
	{
		// meh
	}

	public function addTask($task_name)
	{

		if(Autoload::fileExists('Failnet\\Cron\\' . ucfirst($task_name)))
		{
			// meh
		}
		else
		{
			throw new Exception(ex(Exception::ERR_CRON_LOAD_FAILED), $task_name);
		}
	}

	public function toggleTask() { }

	public function runTasks() { }

	public function triggerTask() { }
}
