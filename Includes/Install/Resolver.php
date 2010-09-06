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
use Failnet as Root;
use Failnet\Core as Core;
use Failnet\Lib as Lib;

/**
 * Failnet - Dependency processing and resolution class,
 *      Processes dependencies to detect broken or unsupported packages.
 *
 *
 * @category    Failnet
 * @package     install
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Resolver extends Base
{
	public $dependencies = array();

	public function loadFile($namespace, $version, array $dependencies = array())
	{
		// meh
	}

	public function resolveFile($namespace)
	{
		// blah
	}

	public function checkBroken($namespace)
	{
		// snork
	}
}
