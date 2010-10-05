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

/**
 * Failnet - Event object,
 * 	    A class to define ene of the various event types.
 *
 *
 * @category    Failnet
 * @package     event
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
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
