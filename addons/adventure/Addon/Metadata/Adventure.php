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
class Adventure extends \Yukari\Addon\Metadata\MetadataBase
{
	/**
	 * @var string - The addon's version.
	 */
	protected $version = 'extra';

	/**
	 * @var string - The addon's author information.
	 */
	protected $author = 'Damian Bushong';

	/**
	 * @var string - The addon's name.
	 */
	protected $name = 'Choose your own Adventure';

	/**
	 * @var string - The addon's description.
	 */
	protected $description = 'IRC game addon, mimicks the classic "Choose your own adventure" games.';

	/**
	 * Hooking method for addon metadata objects, called to initialize the addon after the dependency check has been passed.
	 * @return void
	 */
	public function initialize()
	{
		$configs = array(
			'story.story_file'		=> 'game_adventure.yml',
			'story.playcommand'		=> 'story',
			'story.restartcommand'	=> 'restartstory',
			'story.choosecommand'	=> 'storyoption',
			'story.startpoint'		=> 0,
			'story.say_event_id'	=> false,
		);
		foreach($configs as $config_name => $config_value)
		{
			if(Kernel::getConfig($config_name))
				Kernel::setConfig($config_name, $config_value);
		}

		$adventure = Kernel::set('addon.game.adventure', new \Yukari\Addon\Adventure\Story());
		$adventure->prepareDatabase()->loadStoryFile()->registerListeners();
	}

	/**
	 * Hooking method for addon metadata objects for executing own code on pre-load dependency check.
	 * @return boolean - Does the addon pass the dependency check?
	 */
	public function checkDependencies()
	{
		if(!Kernel::get('addon.commander') || !Kernel::get('addon.database'))
			return false;
		return true;
	}
}
