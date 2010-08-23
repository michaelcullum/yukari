<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     event
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

namespace Failnet\Event;

/**
 * Failnet - Event object,
 * 	    A class to define ene of the various event types.
 *
 *
 * @category    Failnet
 * @package     event
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Part extends Failnet\Event\EventBase
{
	/**
	 * @var array - Array mapping args for quick setting later
	 */
	protected $map = array(
		'channel',
		'reason'
	);

	/**
	 * @var string - Channel arg for the PART event type.
	 */
	public $arg_channel = '';

	/**
	 * @var string - Reason arg for the PART event type.
	 */
	public $arg_reason = '';

	/**
	 * Grab the event's buffer, useful when sending events
	 * @return string - The event buffer.
	 */
	public function getBuffer()
	{
		return 'PART ' . $this->arg_channel . (!empty($this->arg_reason)) ? ' :' . $this->arg_reason : '';
	}
}
