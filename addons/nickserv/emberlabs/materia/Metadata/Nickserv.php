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
use \Codebite\Yukari\Kernel;
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
class Nickserv extends \emberlabs\materia\Metadata\MetadataBase
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
	protected $name = 'Nickserv';

	/**
	 * @var string - The addon's description.
	 */
	protected $description = 'Automatically identifies to nickserv upon MOTD end';

	/**
	 * Hooking method for addon metadata objects, called to initialize the addon after the dependency check has been passed.
	 * @return void
	 */
	public function initialize()
	{
		Kernel::registerListener('irc.input.response.RPL_ENDOFMOTD', 0, function(Event $event) {
			$network = $event->get('network');
			$nickserv = Kernel::get('irc.stack')->getNetworkOption($network, 'nickserv');

			if(!$nickserv || empty($nickserv['nick']) || empty($nickserv['ident']))
			{
				return;
			}

			$pattern = $nickserv['ident_format'] ?: "IDENTIFY %s";

			$return = Event::newEvent('irc.output.privmsg')
				->set('target', $nickserv['nick'])
				->set('text', sprintf($pattern, $nickserv['ident']));

			return $return;
		});
	}

	/**
	 * Hooking method for addon metadata objects for executing own code on pre-load dependency check.
	 * @return boolean - Does the addon pass the dependency check?
	 */
	public function checkDependencies()
	{
		$this->loadAddonDependency('irc.stack', 'irc');

		return true;
	}
}
