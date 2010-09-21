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
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Failnet\Cron;
use Failnet as Root;
use Failnet\Bot as Bot;

/**
 * Failnet - Cron task base class,
 * 	    Provides some common methods for cron tasks and implements the cron task interface.
 *
 *
 * @category    Failnet
 * @package     cron
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
abstract class CronBase implements CronInterface
{
	public $status = Root\Core\Cron::TASK_ZOMBIE;

	/**
	 * Automatically run this cron task, and make sure that it should be run in the first place
	 * @return mixed - Whatever $this->runTask() returns in the cron task that extends this class
	 *
	 * @throws Failnet\Cron\CronTaskException
	 */
	final public function autorun()
	{
		if($this->status === Root\Core\Cron::TASK_ZOMBIE)
			throw new CronTaskException(sprintf('Attempted to run zombie cron task "%1$s"', $this->getTaskName()), CronTaskException::ERR_CRON_TASK_ACCESS_ZOMBIE);
		if($this->status === Root\Core\Cron::TASK_MANUAL)
			throw new CronTaskException(sprintf('Attempted to automatically run a manually-triggered cron task "%1$s"', $this->getTaskName()), CronTaskException::ERR_CRON_TASK_ACCESS_MANUAL);
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
	 * @throws Failnet\Cron\CronTaskException
	 */
	final public function __invoke()
	{
		if($this->status === Root\Core\Cron::TASK_ZOMBIE)
			throw new CronTaskException(sprintf('Attempted to run zombie cron task "%1$s"', $this->getTaskName()), CronTaskException::ERR_CRON_TASK_ACCESS_ZOMBIE);
		return $this->runTask();
	}
}

/**
 * Failnet - Cron task interface,
 * 	    Prototype that defines methods that all cron tasks must declare.
 *
 *
 * @category    Failnet
 * @package     cron
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
interface CronInterface
{
	public function getNextRun();
	public function autorun();
	public function getTaskName();
	public function runTask($manual_invoke = true);
	public function __invoke();
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
 * @link        http://github.com/Obsidian1510/Failnet3
 *
 * @note reserves 400xx error codes
 */
class CronTaskException extends Root\FailnetException
{
	const ERR_CRON_TASK_ACCESS_MANUAL = 40000;
	const ERR_CRON_TASK_ACCESS_ZOMBIE = 40001;
}
