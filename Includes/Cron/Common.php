<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     cron
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2010 -- Damian Bushong
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
 */

namespace Failnet\Cron;
use Failnet;

/**
 * Failnet - Cron task common class,
 * 	    Common class which defines the required methods that each cron task must implement, and provides a singular base for tasks.
 *
 *
 * @category    Failnet
 * @package     cron
 * @author      Damian Bushong
 * @license     GNU General Public License, Version 3
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
abstract class Common extends Base
{
	public $status = TASK_ZOMBIE;

	abstract public function getNextRun();

	public function autoRunTask()
	{
		if($this->status === TASK_ZOMBIE)
			throw new Exception(ex(Exception::ERR_CRON_TASK_ACCESS_ZOMBIE));
		if($this->status === TASK_MANUAL)
			throw new Exception(ex(Exception::ERR_CRON_TASK_ACCESS_MANUAL));
		return $this->runTask(false);
	}

	public function manualRunTask()
	{
		if($this->status === TASK_ZOMBIE)
			throw new Exception(ex(Exception::ERR_CRON_TASK_ACCESS_ZOMBIE));
		return $this->runTask(true);
	}

	abstract protected function runTask($manual_call);
}
