<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     install
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

namespace Failnet\Install;
use Failnet;
use Failnet\Core as Core;
use Failnet\Lib as Lib;

/**
 * Failnet - User Interface class,
 *      Handles the prompts and the output shiz for Failnet's installer.
 *
 *
 * @category    Failnet
 * @package     install
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class UI extends Base
{
	/**
	 * Method that handles output of all data for the UI.
	 * @return void
	 */
	public function output($data)
	{
		echo rtrim($data, PHP_EOL) . PHP_EOL;
	}

	public function stdinPrompt($name, $instruction, $prompt, $default)
	{
		// blah
	}

	public function getBool($key, $default)
	{
		// blah
	}

	public function getString($key, $default)
	{
		// blah
	}

	public function getInt($key, $default)
	{
		// blah
	}
}
