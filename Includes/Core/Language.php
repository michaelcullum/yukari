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
use Failnet;

/**
 * Failnet - Language class,
 * 	    Collects and provides access to all of the language entries for Failnet.
 *
 *
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Language extends Base
{
	protected $entries = array();

	public function __construct()
	{
		$this->collectEntries(FAILNET_ROOT . 'Data/Language/');
	}

	protected function collectEntries($lang_path)
	{
		// meh
	}

	public function getEntry($key)
	{
		// blah
		// use func_get_args, func_num_args in this
	}

	public function setEntry($key, $value)
	{
		// yahoooooo
	}

	public function setEntries(array $entries)
	{
		// wheee
	}
}
