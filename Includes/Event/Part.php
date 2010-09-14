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
class Part extends Failnet\Event\EventBase
{
	/**
	 * @var array - Array mapping args for quick setting later
	 */
	protected $map = array(
		'channel',
		'reason'
	);

	/**
	 * @var string - Event arg.
	 */
	public $arg_channel = '';

	/**
	 * @var string - Event arg.
	 */
	public $arg_reason = '';

	/**
	 * Build the IRC command from the args included
	 * @return string - The raw command to send.
	 */
	public function buildCommand()
	{
		if(!empty($this['reason']))
		{
			return sprintf('PART %1$s :%2$s', $this['channel'], $this['reason']);
		}
		else
		{
			return sprintf('PART %1$s', $this['channel']);
		}
	}
}
