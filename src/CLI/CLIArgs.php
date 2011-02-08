<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     cli
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

namespace Yukari\CLI;


/**
 * Yukari - CLI handling object,
 * 	    Used to provide access to parameters passed to Yukari via argv.
 *
 *
 * @category    Yukari
 * @package     cli
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class CLIArgs implements \ArrayAccess
{
	/**
	 * @var array - The args loaded.
	 */
	protected $args = array();

	/**
	 * Constructor
	 * @param array $args - Array of CLI args to load and parse
	 * @return void
	 */
	public function __construct(array $args)
	{
		$this->loadArgs($args);
	}

	/**
	 * Load up the CLI args and parse them.
	 * @param array $args - An array of CLI args to load and parse
	 * @return void
	 */
	public function loadArgs(array $args)
	{
		foreach($args as $i => $val)
		{
			$result = preg_match('#\-\-?([a-z0-9]+[a-z0-9\-_]*)(=([a-z0-9]+[a-z0-9\-_]*))?#i', $val, $matches);
			if(!$result)
				continue;
			list(, $setting, , $value) = array_pad($matches, 4, true);
			$this->args[$setting] = $value;
		}
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
		return isset($this->args[$offset]);
	}

	/**
	 * Get an "array" offset for this object.
	 * @param mixed $offset - The offset to grab from.
	 * @return mixed - The value of the offset, or null if the offset does not exist.
	 */
	public function offsetGet($offset)
	{
		return isset($this->args[$offset]) ? $this->args[$offset] : NULL;
	}

	/**
	 * Set an "array" offset to a certain value, if the offset exists
	 * @param mixed $offset - The offset to set.
	 * @param mixed $value - The value to set to the offset.
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->args[$offset] = $value;
	}

	/**
	 * Unset an "array" offset.
	 * @param mixed $offset - The offset to clear out.
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->args[$offset]);
	}
}
