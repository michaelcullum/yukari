<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     install
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

namespace Failnet\Install;
use Failnet\Lib as Lib;

/**
 * Failnet - Dependency processing and resolution class,
 *      Processes dependencies to detect broken or unsupported packages.
 *
 *
 * @category    Yukari
 * @package     install
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
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
