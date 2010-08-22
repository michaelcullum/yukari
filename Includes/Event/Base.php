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
class EventBase extends Failnet\Base implements EventInterface
{
	/**
	 * @var Failnet\Lib\Hostmask - The hostmask for the originating server or user
	 */
	public $origin;

	/**
	 * @var boolean - Was this recieved from a channel perspective?
	 */
	public $from_channel = false;

	/**
	 * @var string - The raw buffer of the event
	 */
	public $buffer = '';

	/**
	 * @var array - The map of the various arguments for the event
	 */
	protected $map = array();

	/**
	 * Grabs this event object's event type
	 * @return string - The current event's type
	 */
	public function getType()
	{
		$class = get_class($this);
		return substr($class, strrpos($class, '\\'));
	}

	/**
	 * Sets the value of a certain arg to the specified value
	 * @param $arg_number - The arg number to set
	 * @param $arg_value - The value of the arg to set
	 * @return boolean - True if successful, false if no such arg to set.
	 */
	public function setArgNumber($arg_number, $arg_value)
	{
		if(!isset($this->map[$arg_number]))
			return false;
		$this->{'arg_' . $this->map[$arg_number]} = $arg_value;
		return true;
	}

	public function getBuffer()
	{
		return (!isset($this->buffer)) ? $this->buildCommand() : $this->buffer;
	}
}

interface EventInterface
{
	public function getType();
	public function setArgNumber($arg_number, $arg_value);
	public function getSource();
	public function getBuffer();
	public function buildCommand();
}