<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
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
abstract class Instance implements \ArrayAccess
{
	protected $name = '';

	protected $data = array();

	protected $source;

	public function __construct($source, $name, array $data = array())
	{
		$this->setSource($source)->setName($name)->setData($data);
	}

	public static function newEvent($source, $name)
	{
		return new static($source, $name);
	}

	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function getSource()
	{
		return $this->source;
	}

	public function setSource($source)
	{
		if($source !== NULL && !is_object($source))
			throw new \InvalidArgumentException('Source provided to event instance must be an object or NULL');

		$this->source = $source;
		return $this;
	}

	public function getData()
	{
		return $this->data;
	}

	public function setData(array $data = array())
	{
		$this->data = $data;
		return $this;
	}

	public function getDataPoint($point)
	{
		return $this->offsetGet($point);
	}

	public function setDataPoint($point, $value)
	{
		$this->offsetSet($point, $value);
		return $this;
	}

	/**
	 * ArrayAccess methods
	 */

	/**
	 * Check if an "array" offset exists in this object.
	 * @param mixed $offset - The offset to check.
	 * @return boolean - Does anything exist for this offset?
	 */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->data);
	}

	/**
	 * Get an "array" offset for this object.
	 * @param mixed $offset - The offset to grab from.
	 * @return mixed - The value of the offset, or null if the offset does not exist.
	 */
	public function offsetGet($offset)
	{
		if(!$this->offsetExists($offset))
			throw new \InvalidArgumentException('Invalid event parameter specified');
		return $this->data[$offset];
	}

	/**
	 * Set an "array" offset to a certain value, if the offset exists
	 * @param mixed $offset - The offset to set.
	 * @param mixed $value - The value to set to the offset.
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->data[$offset] = $value;
	}

	/**
	 * Unset an "array" offset.
	 * @param mixed $offset - The offset to clear out.
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}
}
