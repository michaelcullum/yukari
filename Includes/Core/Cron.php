<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2010 -- Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Failnet\Core;
use Failnet;

/**
 * Failnet - Cron core class,
 * 	    Manages the cron system, handles tasks, etc.
 *
 *
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Cron extends Base
{
	//public $last_event = 0;

	/**
	 * @var array - The list of times when each task is scheduled to run next
	 */
	public $task_times = array();

	/**
	 * Loads a task and prepares it for use
	 * @param string $task_name - The name of the task to load
	 * @return void
	 * @throws Failnet\Exception
	 */
	public function addTask($task_name)
	{
		if(!Autoload::fileExists('Failnet\\Cron\\' . ucfirst($task_name)))
			throw new Exception(ex(Exception::ERR_CRON_LOAD_FAILED, $task_name));
		Bot::setCron($task_name, 'Failnet\\Cron\\' . ucfirst($task_name));
		$this->getTaskQueue($task_name);
	}

	/**
	 * Changes the state of the specified task
	 * @param string $task_name - The name of the task to change the state of
	 * @param integer $status - The state to set the task to (must be a TASK_* constant)
	 * @return boolean - Whether or not we were successful
	 * @throws Failnet\Exception
	 */
	public function toggleTask($task_name, $status)
	{
		if(!in_array($status, array(TASK_ACTIVE, TASK_MANUAL, TASK_ZOMBIE)))
			throw new Exception(ex(Exception::ERR_CRON_INVALID_STATE));
		if(!Bot::checkCronLoaded($task_name))
			return false;
		Bot::cron($task_name)->status = $status;
		return true;
	}

	/**
	 * Automatically run all necessary tasks
	 * @return void
	 */
	public function runTasks()
	{
		// run all tasks that can be run automatically
		asort($this->task_times, SORT_NUMERIC);
		foreach($this->task_times as $task => $time)
		{
			if($time > time())
				continue;
			Bot::cron($task)->autoRunTask();
		}

	}

	/**
	 * Trigger a task manually
	 * @param string $task_name - The name of the task we want to trigger.
	 * @return mixed - Whatever the task returns.
	 */
	public function triggerTask($task_name)
	{
		return Bot::cron($task_name)->manualRunTask();
	}

	/**
	 * Get the next time a task must be run, so long as it is setup to be automatically run
	 * @param string $task_name - The name of the task to check
	 * @return boolean - Whether or not the specified task can be automatically run.
	 */
	public function getTaskQueue($task_name)
	{
		// get the next time a task must be run
		// If we're a zombie task or a manual task, we should not be queued into the task list.
		if(Bot::cron($task_name)->status !== TASK_ACTIVE)
			return false;

		$next_run = (int) Bot::cron($task_name)->getNextRun();
		$this->task_times[$task_name] = $next_run;
		return true;
	}
}
