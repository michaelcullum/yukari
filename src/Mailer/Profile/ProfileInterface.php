<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     mailer
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

namespace Failnet\Mailer\Profile;
use Failnet\Bot as Bot;

/**
 * Failnet - Replacement engine profile interface,
 * 	    Prototype that defines methods that language package objects must implement.
 *
 * @category    Failnet
 * @package     mailer
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
interface ProfileInterface
{
	public function getReplacementPayload(array $replacements);
	public function getProfileName();
	public function getReplacements();
	public function setReplacement($token, $required, $default = NULL);
}
