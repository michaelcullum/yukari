<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
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

/**
 * Failnet - Event object,
 * 	    A class to define ene of the various event types.
 *
 *
 * @category    Yukari
 * @package     event
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Join extends IRCBase
{
	/**
	 * @var array - Array mapping args for quick setting later
	 */
	protected $map = array(
		'channel',
		'key',
	);

	/**
	 * @var string - Event arg.
	 */
	public $arg_channel = '';

	/**
	 * @var string - Event arg.
	 */
	public $arg_key = '';

	/**
	 * Build the IRC command from the args included
	 * @return string - The raw command to send.
	 */
	public function buildCommand()
	{
		if(!empty($this['key']))
		{
			return sprintf('JOIN %1$s :%2$s', $this['channel'], $this['key']);
		}
		else
		{
			return sprintf('JOIN %1$s', $this['channel']);
		}
	}
}
