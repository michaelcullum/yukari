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
use \OpenFlame\Framework\Event\Instance as Event;

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
class ManagerStack
{
	protected $managers = array();

	public function __construct($networks)
	{
		foreach($networks as $network => $properties)
		{
			$manager = new \Codebite\Yukari\Addon\IRC\Manager($network);

			foreach($properties as $property => $value)
			{
				$manager->set($property, $value);
			}

			$this->managers[$network] = $manager;
		}
	}

	public function getNetworkOption($network, $option)
	{
		return $this->managers[$network]->get($option);
	}

	public function setNetworkOption($network, $option, $value)
	{
		return $this->managers[$network]->set($option, $value);
	}

	public function registerListeners()
	{
		Kernel::registerListener('yukari.tick', 0, array($this, 'tick'));
	}

	public function tick(Event $tick)
	{
		foreach($this->managers as $k => $manager)
		{
			try
			{
				$manager->tickHook($tick);
			}
			catch(DeadConnectionException $e)
			{
				unset($this->managers[$k]);
			}
		}
	}
}
