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

abstract class CronBase implements CronInterface
{
	// task constants need defined here

	public $status = TASK_ZOMBIE;

	final public function autorun()
	{
		if($this->status === TASK_ZOMBIE)
			throw new Exception(ex(Exception::ERR_CRON_TASK_ACCESS_ZOMBIE)); // @todo exceptions
		if($this->status === TASK_MANUAL)
			throw new Exception(ex(Exception::ERR_CRON_TASK_ACCESS_MANUAL));
		return $this->runTask(false);
	}

	/**
	 * Manually trigger a cron task
	 */
	final public function __invoke()
	{
		return $this->runTask();
	}
}

interface CronInterface
{
	public function getNextRun();
	public function autorun();
	public function runTask($manual_invoke = true);
	public function __invoke();
}
