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
use \Codebite\Yukari\Addon\IRC\Internal\DeadConnectionException;
use \Codebite\Yukari\Addon\IRC\Internal\LostConnectionException;
use \Codebite\Yukari\Kernel;
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

	protected $properties = array();

	protected $connections = array();

	public function __construct($networks)
	{
		$this->properties = $networks;
		foreach($this->properties as $network => $properties)
		{
			$this->connectNetwork($network, $properties);
		}
	}

	public function registerListeners()
	{
		Kernel::registerListener('yukari.tick', 0, array($this, 'tick'));
	}

	public function connectNetwork($network)
	{
		$manager = new \Codebite\Yukari\Addon\IRC\Manager($network);

		foreach($this->properties[$network] as $property => $value)
		{
			$manager->set($property, $value);
		}

		$this->managers[$network] = $manager;
		$this->connections[$network] = 1;

		return $manager;
	}

	public function getNetworkOption($network, $option)
	{
		return $this->managers[$network]->get($option);
	}

	public function setNetworkOption($network, $option, $value)
	{
		return $this->managers[$network]->set($option, $value);
	}

	public function upConnectionCount($network)
	{
		if(isset($this->connections[$network]))
		{
			$this->connections[$network]++;
		}
		else
		{
			$this->connections[$network] = 1;
		}
	}

	public function resetConnectionCount($network)
	{
		$this->connections[$network] = 0;

		return $this;
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
				$manager->disconnect();
				unset($this->managers[$k]);
			}
			catch(LostConnectionException $e)
			{
				// handle dropped connections, and attempt to reconnect
				$network = $manager->getNetwork();

				$this->upConnectionCount($network);
				$manager->disconnect();
				unset($this->managers[$k]);

				if($this->connections[$network] <= 3)
				{
					$manager = $this->connectNetwork($network);
					$manager->tickHook($tick);
				}
			}
		}
	}
}
