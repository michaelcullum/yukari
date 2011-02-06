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
class CTCP extends IRCBase
{
	/**
	 * @var array - Array mapping args for quick setting later
	 */
	protected $map = array(
		'target',
		'command',
		'args',
	);

	/**
	 * @var string - Event arg.
	 */
	public $arg_target = '';

	/**
	 * @var string - Event arg.
	 */
	public $arg_command = '';

	/**
	 * @var array - Event arg.
	 */
	public $arg_args = array();

	/**
	 * Build the IRC command from the args included
	 * @return string - The raw command to send.
	 */
	public function buildCommand()
	{
		if(!empty($this['args']))
		{
			return sprintf('NOTICE %1$s :' . chr(1) . '%2$s %3$s' . chr(1), $this['target'], strtoupper($this['command']), implode(' ', $this['args']));
		}
		else
		{
			return sprintf('NOTICE %1$s :' . chr(1) . '%2$s' . chr(1), $this['target'], strtoupper($this['command']));
		}
	}
}
