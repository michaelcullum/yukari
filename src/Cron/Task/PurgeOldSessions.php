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

namespace Failnet\Cron;

/**
 * Failnet - Expired session purge cron task class,
 * 	    Cleans out old, dead session data on a regular basis.
 *
 *
 * @category    Yukari
 * @package     cron
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class PurgeOldSessions extends Common
{
	public $status = TASK_ACTIVE;

	// blah
}
