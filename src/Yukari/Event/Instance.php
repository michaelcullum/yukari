<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     event
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

namespace Yukari\Event;

/**
 * Yukari - Event instance,
 * 	    The event instance to dispatch events as.
 *
 *
 * @category    Yukari
 * @package     event
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Instance
{
	/**
	 * @var string - The event name.
	 */
	protected $name = '';

	/**
	 * @var array - Related event data
	 */
	protected $data = array();

	/**
	 * @var mixed - The source of the event, may be null.
	 */
	protected $source;

	/**
	 * Create a new event, used as a one-line shortcut for quickly dispatching events.
	 * @param string $name - The event's name.
	 * @return \Yukari\Event\Instance - The event created.
	 */
	public static function newEvent($name)
	{
		$self = new static();
		$self->setName($name);
		return $self;
	}

	/**
	 * Get the name for the event
	 * @return string - The event's name.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the name for the event.
	 * @param string $name - The name to set.
	 * @return \Yukari\Event\Instance - Provides a fluent interface.
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Get the source of the event.
	 * @return mixed - Returns the source of the event (an object) or NULL.
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * Set the source of the event.
	 * @param mixed $source - The source of the event, must be an object or NULL.
	 * @return \Yukari\Event\Instance - Provides a fluent interface.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setSource($source)
	{
		if($source !== NULL && !is_object($source))
		{
			throw new \InvalidArgumentException('Source provided to event instance must be an object or NULL');
		}

		$this->source = $source;
		return $this;
	}

	/**
	 * Get the array of data attached to the event.
	 * @return array - The array of data attached to this event.
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Set the array of data to attach to this event.
	 * @param array $data - The array of data to attach.
	 * @return \Yukari\Event\Instance - Provides a fluent interface.
	 */
	public function setData(array $data = array())
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * Check if a data point exists in this event.
	 * @param string - The key for the data point to grab.
	 * @return boolean - Does the data point exist?
	 */
	public function dataPointExists($point)
	{
		return array_key_exists($point, $this->data);
	}

	/**
	 * Get a single point of data attached to this event.
	 * @param string - The key for the data point to grab.
	 * @return mixed - The point of data we're looking for
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getDataPoint($point)
	{
		if(!$this->dataPointExists($point))
		{
			throw new \InvalidArgumentException('Invalid event parameter specified');
		}
		return $this->data[$point];
	}

	/**
	 * Attach a single point of data to this event
	 * @param string $point - The key to attach the data under.
	 * @param mixed $value - The data to attach.
	 * @return \Yukari\Event\Instance - Provides a fluent interface.
	 */
	public function setDataPoint($point, $value)
	{
		$this->data[$point] = $value;
		return $this;
	}
}
