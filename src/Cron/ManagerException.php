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
 * @note reserves 205xx error codes
 */
class ManagerException extends Root\FailnetException
{
	const ERR_CRON_LOAD_FAILED = 20500;
	const ERR_CRON_NO_SUCH_TASK = 20501;
	const ERR_CRON_TASK_ALREADY_LOADED = 20502;
	const ERR_CRON_INVALID_STATE = 20503;
}
