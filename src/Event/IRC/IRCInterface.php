<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     event
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

namespace Failnet\Event\IRC;
use Failnet\Event as Event;

/**
 * Failnet - IRC Event interface,
 * 	    Prototype that defines methods that all IRC events must declare.
 *
 *
 * @category    Yukari
 * @package     event
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
interface IRCInterface extends Event\EventInterface
{
	public function getSource();
	public function getBuffer();
	public function buildCommand();
	public function fromChannel();
}
