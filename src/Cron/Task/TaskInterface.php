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
 * Failnet - Cron task interface,
 * 	    Prototype that defines methods that all cron tasks must declare.
 *
 *
 * @category    Yukari
 * @package     cron
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
interface TaskInterface
{
	public function getNextRun();
	public function autorun();
	public function getTaskName();
	public function runTask($manual_invoke = true);
	public function __invoke();
}
