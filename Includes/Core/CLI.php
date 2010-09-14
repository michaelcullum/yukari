<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     core
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

namespace Failnet\Core;
use Failnet as Root;


/**
 * Failnet - CLI handling object,
 * 	    Used to provide access to parameters passed to Failnet via argv.
 *
 *
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class CLI extends Root\Base implements \ArrayAccess
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
	 *
	 * @copyright   (c) 2010 Sam Thompson
	 * @author      Sam Thompson
	 * @license     MIT License
	 * @note        This code generously provided by a friend of mine, Sam Thompson.  Kudos!
	 */
	public function loadArgs(array $args)
	{
		foreach($args as $i => $val)
		{
			if($val[0] === '-')
			{
				if($val[1] === '-')
				{
					$separator = strpos($val, '=');
					if($separator !== false)
					{
						$this->args[substr($val, 2, $separator - 2)] = substr($val, $separator + 1);
					}
					else
					{
						$this->args[substr($val, 2)] = true;
					}
				}
				else
				{
					$this->args[substr($val, 1)] = true;
				}
			}
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
