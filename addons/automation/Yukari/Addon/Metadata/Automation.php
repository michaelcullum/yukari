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
		$ctcp_lambda = function(\OpenFlame\Framework\Event\Instance $event) {
			if(strtolower($event->getDataPoint('command')) === 'version')
			{
				return \OpenFlame\Framework\Event\Instance::newEvent('irc.output.ctcp_reply')
					->setData(array(
						'command' 	=> 'version',
						'target'	=> $event->getDataPoint('hostmask')->getNick(),
						'args'		=> sprintf('Yukari IRC Bot - %s', Kernel::getBuildNumber()),
					));
			}
			elseif(strtolower($event->getDataPoint('command')) === 'ping')
			{
				if(!$event->dataPointExists('args') || $event->getDataPoint('args') === NULL)
					return NULL;

				return \OpenFlame\Framework\Event\Instance::newEvent('irc.output.ctcp_reply')
					->setData(array(
						'command' 	=> 'ping',
						'target'	=> $event->getDataPoint('hostmask')->getNick(),
						'args'		=> $event->getDataPoint('args'),
					));
			}
			else
			{
				return NULL;
			}
		};
		$dispatcher->register('irc.input.ctcp', $ctcp_lambda, array(), -10); // use -10 priority for medium-high listener priority

		// Respond to server pings
		$ping_lambda = function(\OpenFlame\Framework\Event\Instance $event) {
			return \OpenFlame\Framework\Event\Instance::newEvent('irc.output.pong')
					->setDataPoint('origin', $event->getDataPoint('target'));
		};
		$dispatcher->register('irc.input.ping', $ping_lambda, array(), -10); // use -10 priority for medium-high listener priority
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
