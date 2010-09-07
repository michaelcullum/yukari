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
use Failnet as Root;
use Failnet\Bot as Bot;

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
class Cron extends Root\Base
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
	 * @throws Failnet\Core\CronException
	 */
	public function addTask($task_name)
	{
		$task_class = "Failnet\\Cron\\$task_name";
		if(!Bot::getObject('core.autoload')->fileExists($task_class))
			throw new CronException(sprintf('No class file found for cron task "%1$s"', $task_name), CronException::ERR_CRON_NO_SUCH_TASK);

		Bot::getEnvironment()->setObject("cron.$task_name", 'Failnet\\Cron\\' . ucfirst($task_name));
		$this->getTaskQueue($task_name);
	}

	/**
	 * Changes the state of the specified task
	 * @param string $task_name - The name of the task to change the state of
	 * @param integer $status - The state to set the task to (must be a Failnet\TASK_* constant)
	 * @return boolean - Whether or not we were successful
	 *
	 * @throws Failnet\Core\CronException
	 */
	public function toggleTask($task_name, $status)
	{
		if(!in_array($status, array(Root\TASK_ACTIVE, Root\TASK_MANUAL, Root\TASK_ZOMBIE)))
			throw new CronException(sprintf('Attempted to set an invalid state on cron task "%1$s"', $task_name), CronException::ERR_CRON_INVALID_STATE);
		try
		{
			$cron_task = Bot::getObject("cron.$task_name");
			$cron_task->status = $status;
		}
		catch(Root\EnvironmentException $e)
		{
			// Check to see if the cron task even existed.
			if($e->getCode() == Root\EnvironmentException::ERR_ENVIRONMENT_NO_SUCH_OBJECT)
			{
				return false;
			}
			else
			{
				// rethrow the exception if it isn't what we are expecting
				throw new Root\EnvironmentException($e->getMessage(), $e->getCode());
			}
		}
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
			Bot::getObject("cron.$task")->autoRunTask();
		}

	}

	/**
	 * Trigger a task manually
	 * @param string $task_name - The name of the task we want to trigger.
	 * @return mixed - Whatever the task returns.
	 */
	public function triggerTask($task_name)
	{
		return Bot::getObject("cron.$task")->manualRunTask();
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
		$task = Bot::getOption("cron.$task_name");
		if($task->status !== Root\TASK_ACTIVE)
			return false;

		$next_run = (int) $task->getNextRun();
		$this->task_times[$task_name] = $next_run;
		return true;
	}

	/**
	 * Manually run a cron task.
	 * @param string $task - The task to run
	 * @return mixed - Whatever the task returns.
	 */
	public function __invoke($task)
	{
		return Bot::getObject("cron.$task")->manualRunTask();
	}
}

/**
 * Failnet - Subordinate exception class
 *      Extension of the Failnet exception class.
 *
 *
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 * @note reserves 205xx error codes
 */
class CronException extends Root\FailnetException
{
	const ERR_CRON_LOAD_FAILED = 20500;
	const ERR_CRON_NO_SUCH_TASK = 20501;
	const ERR_CRON_TASK_ALREADY_LOADED = 20502;
	const ERR_CRON_INVALID_STATE = 20503;
}
