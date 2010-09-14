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
class CLI extends Root\Base
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
	 * Pull a specific arg that should have been passed to the script, it was sent.
	 * @param string $arg_name - The name of the CLI arg to grab.
	 * @return mixed - NULL if no such arg, the arg if present.
	 */
	public function getArg($arg)
	{
		if(isset($this->args[$arg_name]))
			return $this->args[$arg_name];
		return NULL;
	}

	/**
	 * Aliases to Failnet\Core\CLI->getArg()
	 * @see Failnet\Core\CLI->getArg()
	 */
	public function __invoke($arg)
	{
		return $this->getArg($arg);
	}
}
