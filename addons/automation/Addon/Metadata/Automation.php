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

namespace Yukari\Addon\Metadata;
use Yukari\Kernel;

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
class Automation extends \Yukari\Addon\Metadata\MetadataBase
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
	protected $name = 'Automation';

	/**
	 * @var string - The addon's description.
	 */
	protected $description = 'Provides basic automation for handling of standard IRC events.';

	/**
	 * Hooking method for addon metadata objects, called to initialize the addon after the dependency check has been passed.
	 * @return void
	 */
	public function initialize()
	{
		$dispatcher = Kernel::getDispatcher();

		// Respond to CTCP VERSION and CTCP PING (if a valid argument for the CTCP was provided)
		$dispatcher->register('irc.input.ctcp', function(\Yukari\Event\Instance $event) {
			if(strtolower($event['command']) === 'version')
			{
				return \Yukari\Event\Instance::newEvent('irc.output.ctcp_reply')
					->setDataPoint('target', $event['hostmask']['nick'])
					->setDataPoint('command', 'version')
					->setDataPoint('args', sprintf('Yukari IRC Bot - %s', Kernel::getBuildNumber()));
			}
			elseif(strtolower($event['command']) === 'ping')
			{
				if($event['args'] === NULL)
					return NULL;

				return \Yukari\Event\Instance::newEvent('irc.output.ctcp_reply')
					->setDataPoint('target', $event['hostmask']['nick'])
					->setDataPoint('command', 'ping')
					->setDataPoint('args', $event['args']);
			}
			else
			{
				return NULL;
			}
		});

		// Respond to server pings
		$dispatcher->register('irc.input.ping', function(\Yukari\Event\Instance $event) {
			return \Yukari\Event\Instance::newEvent('irc.output.pong')
					->setDataPoint('origin', $event['target']);
		});
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
