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

namespace Failnet\Event\Runtime;
use Failnet\Bot as Bot;
use Failnet\Event as Event;

/**
 * Failnet - Runtime Event base class,
 * 	    Base class that all Runtime events must extend.
 *
 *
 * @category    Failnet
 * @package     event
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
abstract class RuntimeBase extends Event\EventBase implements Event\EventInterface
{
	/**
	 * @var boolean - Can this event be sent externally?
	 */
	protected $sendable = false;

	/**
	 * @var array - Array mapping args for quick setting later
	 */
	protected $map = array(
		'time',
	);

	/**
	 * @var DateTime - Event arg.
	 */
	public $arg_time;

	/**
	 * Constructor
	 * @return void
	 */
	public function __construct()
	{
		$this->arg_time = new DateTime('now', Bot::getObject('core.timezone'));
	}
}
