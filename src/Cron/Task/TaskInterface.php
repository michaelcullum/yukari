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

namespace Failnet\Cron\Task;
use Failnet\Bot as Bot;
use Failnet\Cron as Cron;

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
interface TaskInterface
{
	public function getNextRun();
	public function autorun();
	public function getTaskName();
	public function runTask($manual_invoke = true);
	public function __invoke();
}
