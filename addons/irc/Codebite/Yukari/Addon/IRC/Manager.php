<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     connection
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2011 Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Codebite\Yukari\Addon\IRC;
use Codebite\Yukari\Kernel;

/**
 * Yukari - Connection manager class,
 * 	    Used as the IRC connection instance manager.
 *
 *
 * @category    Yukari
 * @package     connection
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Manager
{
	public $socket;

	protected $name = '';

	protected $network = '';

	public function __construct($network)
	{
		$seeder = Kernel::get('seeder');

		$this->network = (string) $network;
		$this->name = $seeder->getRandomString(12, (string) $network);

		$this->socket = Kernel::get('irc.socket')
			->setManager($this);
	}

	public function get($option)
	{
		return Kernel::getConfig(sprintf('irc_%s.%s', $this->name, $option));
	}

	public function set($option, $value)
	{
		return Kernel::setConfig(sprintf('irc_%s.%s', $this->name, $option), $value);
	}

	public function connect()
	{
		// asdf
	}

	public function tickHook()
	{
		// asdf
	}
}
