<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     event
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

namespace Failnet\Event\IRC;
use Failnet\Event as Event;

/**
 * Failnet - IRC Event interface,
 * 	    Prototype that defines methods that all IRC events must declare.
 *
 *
 * @category    Failnet
 * @package     event
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
interface IRCInterface extends Event\EventInterface
{
	public function getSource();
	public function getBuffer();
	public function buildCommand();
	public function fromChannel();
}
