<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     language
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2010 -- Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Failnet\Language\Package;
use Failnet\Bot as Bot;
use Failnet\Lib as Lib;
use Failnet\Language as Language;

/**
 * Failnet - Language package interface,
 *      Prototype that defines methods that language package objects must implement.
 *
 *
 * @category    Failnet
 * @package     language
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
interface PackageInterface extends \Countable, \Iterator
{
	public function buildJSON();
}
