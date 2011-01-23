<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     mailer
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

namespace Failnet\Mailer\Profile;
use Failnet\Bot as Bot;

/**
 * Failnet - Replacement engine profile interface,
 * 	    Prototype that defines methods that language package objects must implement.
 *
 * @category    Yukari
 * @package     mailer
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
interface ProfileInterface
{
	public function getReplacementPayload(array $replacements);
	public function getProfileName();
	public function getReplacements();
	public function setReplacement($token, $required, $default = NULL);
}
