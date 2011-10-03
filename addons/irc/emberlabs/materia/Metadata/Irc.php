<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     addon
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

namespace emberlabs\materia\Metadata;
use Codebite\Yukari\Kernel;
use \OpenFlame\Framework\Dependency\Injector;
use \OpenFlame\Framework\Event\Instance as Event;

/**
 * Yukari - Addon metadata object,
 *      Provides some information regarding the addon.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Irc extends \emberlabs\materia\Metadata\MetadataBase
{
	/**
	 * @var string - The addon's version.
	 */
	protected $version = 'core';

	/**
	 * @var string - The addon's author information.
	 */
	protected $author = 'Damian Bushong';

	/**
	 * @var string - The addon's name.
	 */
	protected $name = 'IRC';

	/**
	 * @var string - The addon's description.
	 */
	protected $description = 'Provides basic support for the IRC protocol.';

	/**
	 * @var boolean - Has this addon been initialized? (it MAY be loaded multiple times)
	 */
	protected static $initialized = false;

	protected $manager;

	/**
	 * Hooking method for addon metadata objects, called to initialize the addon after the dependency check has been passed.
	 * @return void
	 */
	public function initialize()
	{
		$injector = Injector::getInstance();

		$networks = Kernel::getConfig('irc.networks');
		foreach($networks as $network => $properties)
		{
			$manager = new \Codebite\Yukari\Addon\IRC\Manager($network);

			foreach($properties as $property => $value)
			{
				$manager->set($property, $value);
			}

			$this->manager[$network] = $manager;
		}
	}

	protected function setInjectors()
	{
		$injector = Injector::getInstance();

		$injector->setInjector('irc.ui', function() {
			return \Codebite\Yukari\Addon\IRC\Environment\Display();
		});

		$injector->setInjector('irc.socket', function() {
			return function() {
				return \Codebite\Yukari\Addon\IRC\Connection\Socket();
			};
		});

		$injector->setInjector('irc.request_map', function() {
			return new \Codebite\Yukari\Connection\RequestMap();
		});

		$injector->setInjector('irc.response_map', function() {
			return new \Codebite\Yukari\Connection\ResponseMap();
		});
	}

	protected function setListeners()
	{
		Kernel::get('irc.ui')->registerListeners();
	}

	/**
	 * Hooking method for addon metadata objects for executing own code on pre-load dependency check.
	 * @return boolean - Does the addon pass the dependency check?
	 */
	public function checkDependencies()
	{
		// This addon has no dependencies.
		return true;
	}
}
