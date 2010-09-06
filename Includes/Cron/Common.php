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
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Failnet\Cron;
use Failnet as Root;

/**
 * Failnet - Cron task common class,
 * 	    Common class which defines the required methods that each cron task must implement, and provides a singular base for tasks.
 *
 *
 * @category    Failnet
 * @package     cron
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
abstract class Common extends Base
{
	public $status = TASK_ZOMBIE;

	// @todo document all
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
