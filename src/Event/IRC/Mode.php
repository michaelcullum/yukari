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
class Mode extends IRCBase
{
	/**
	 * @var array - Array mapping args for quick setting later
	 */
	protected $map = array(
		'target',
		'mode',
		'limit',
		'user',
		'banmask',
	);

	/**
	 * @var string - Event arg.
	 */
	public $arg_target = '';

	/**
	 * @var string - Event arg.
	 */
	public $arg_mode = '';

	/**
	 * @var string - Event arg.
	 */
	public $arg_limit = '';

	/**
	 * @var string - Event arg.
	 */
	public $arg_user = '';

	/**
	 * @var string - Event arg.
	 */
	public $arg_banmask = '';

	/**
	 * Build the IRC command from the args included
	 * @return string - The raw command to send.
	 */
	public function buildCommand()
	{
		// asdf
		// return sprintf('')
		// @todo look up RFC on mode command, attempt to piece together a sprintf() pattern that will work for this
	}
}
