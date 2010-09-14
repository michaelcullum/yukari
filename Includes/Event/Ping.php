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
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Failnet\Event;

/**
 * Failnet - Event object,
 * 	    A class to define ene of the various event types.
 *
 *
 * @category    Failnet
 * @package     event
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Ping extends Failnet\Event\CTCP
{
	/**
	 * @var string - Event arg.
	 */
	public $arg_hash = '';

	/**
	 * Build the IRC command from the args included
	 * @return string - The raw command to send.
	 */
	public function buildCommand()
	{
		$this['command'] = 'PING';

		if(!empty($this['hash']))
			$this['args'][] = $this['hash'];

		parent::buildCommand();
	}
}