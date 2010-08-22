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
class Notice extends Failnet\Event\EventBase
{
	/**
	 * @var array - Array mapping args for quick setting later
	 */
	protected $map = array(
		'target',
		'text'
	);

	/**
	 * @var string - Target arg for the NOTICE event type.
	 */
	public $arg_target = '';

	/**
	 * @var string - Text arg for the NOTICE event type.
	 */
	public $arg_text = '';

	/**
	 * Build the IRC command from the args included
	 * @return string - The raw command to send.
	 */
	public function buildCommand()
	{
		return 'NOTICE ' . $this->arg_target . ' :' . $this->arg_text;
	}
}
