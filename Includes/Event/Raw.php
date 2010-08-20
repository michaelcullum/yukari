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
 * Failnet - Event base class,
 * 	    Base class that all events must extend.
 *
 *
 * @category    Failnet
 * @package     event
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Raw extends Failnet\Event\EventBase
{
	/**
	 * @var array - Array mapping args for quick setting later
	 */
	public $map = array(
		'message'
	);

	/**
	 * @var string - Message arg for the RAW event type.
	 */
	public $arg_message = '';

	/**
	 * Grab the event's buffer, useful when sending events
	 * @return string - The event buffer.
	 */
	public function getBuffer()
	{
		return $this->arg_message;
	}
}
