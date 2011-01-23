<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     cron
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2011 -- Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Failnet\Cron\Task;
use Failnet\Bot as Bot;
use Failnet\Cron as Cron;

/**
 * Failnet - Cron task base class,
 * 	    Provides some common methods for cron tasks and implements the cron task interface.
 *
 *
 * @category    Yukari
 * @package     cron
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
abstract class TaskBase implements TaskInterface
{
	public $status = Cron\Manager::TASK_ZOMBIE;

	/**
	 * Automatically run this cron task, and make sure that it should be run in the first place
	 * @return mixed - Whatever $this->runTask() returns in the cron task that extends this class
	 *
	 * @throws Failnet\Cron\Task\TaskException
	 */
	final public function autorun()
	{
		if($this->status === Cron\Manager::TASK_ZOMBIE)
			throw new TaskException(sprintf('Attempted to run zombie cron task "%1$s"', $this->getTaskName()), TaskException::ERR_CRON_TASK_ACCESS_ZOMBIE);
		if($this->status === Cron\Manager::TASK_MANUAL)
			throw new TaskException(sprintf('Attempted to automatically run a manually-triggered cron task "%1$s"', $this->getTaskName()), TaskException::ERR_CRON_TASK_ACCESS_MANUAL);
		return $this->runTask(false);
	}

	/**
	 * Get the name of this cron task
	 * @return string - The name of the cron task
	 */
	final public function getTaskName()
	{
		$class = get_class($this);
		return substr($class, strrpos($class, '\\'));
	}

	/**
	 * Manually trigger a cron task
	 * @return mixed - Whatever $this->runTask() returns in the cron task that extends this class
	 *
	 * @throws Failnet\Cron\Task\TaskException
	 */
	final public function __invoke()
	{
		if($this->status === Cron\Manager::TASK_ZOMBIE)
			throw new TaskException(sprintf('Attempted to run zombie cron task "%1$s"', $this->getTaskName()), TaskException::ERR_CRON_TASK_ACCESS_ZOMBIE);
		return $this->runTask();
	}
}
