<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     language
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

namespace Yukari\Language\Package;

/**
 * Yukari - Language package interface,
 *      Prototype that defines methods that language package objects must implement.
 *
 *
 * @category    Yukari
 * @package     language
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
interface PackageInterface extends \Countable, \Iterator
{
	public function toJSON();
	public function getLocale();
}
