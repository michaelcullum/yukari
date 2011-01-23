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
 * Failnet - Subordinate exception class
 *      Extension of the Failnet exception class.
 *
 *
 * @category    Yukari
 * @package     Yukari
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 * @note reserves 400xx error codes
 */
class TaskException extends Failnet\FailnetException
{
	const ERR_CRON_TASK_ACCESS_MANUAL = 40000;
	const ERR_CRON_TASK_ACCESS_ZOMBIE = 40001;
}
