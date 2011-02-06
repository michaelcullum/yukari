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
class Kick extends IRCBase
{
	/**
	 * @var array - Array mapping args for quick setting later
	 */
	protected $map = array(
		'channel',
		'user',
		'reason',
	);

	/**
	 * @var string - Event arg.
	 */
	public $arg_channel = '';

	/**
	 * @var string - Event arg.
	 */
	public $arg_user = '';

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
			return sprintf('KICK %1$s %2$s :%3$s', $this['channel'], $this['user'], $this['reason']);
		}
		else
		{
			return sprintf('KICK %1$s %2$s', $this['channel'], $this['user']);
		}

	}
}
