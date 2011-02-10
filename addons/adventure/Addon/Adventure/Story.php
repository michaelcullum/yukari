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

namespace Yukari\Addon\Adventure;
use Yukari\Kernel;

/**
 * Yukari - "Choose your own adventure" main object,
 *      Handles Gameplay.  And perhaps gameloss as well.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Story
{
	protected $story_data = array();

	public function prepareDatabase()
	{
		$database = Kernel::get('addon.database');

		if(!$database->tableExists('game_adventure_places'))
			$database->runSchema('game_adventure_story.sql');

		// Get the ID of the last event that a user was at.
		$database->defineQuery('story.getUserLastEvent', function(\PDO $db, $hostmask) {
			$sql = 'SELECT event_id
				FROM game_adventure_story
				WHERE host_string = :host_string';

			$q = $db->prepare($sql);
			$q->bindParam(':host_string', $hostmask, PDO::PARAM_STR);
			$q->execute();
			$result = $q->fetch(PDO::FETCH_ASSOC);
			$q = NULL;

			$event_id = (!empty($result)) ? $result['event_id'] : Kernel::getConfig('story.startpoint');


			return $event_id;
		});
	}

	public function loadStoryFile()
	{
		$story_file = \sfYaml::load(YUKARI . '/data/config/addons/' . Kernel::getConfig('story.story_file'));
		$this->story_data = $story_file['story.data'];
	}

	/**
	 * Register the listeners we need for this addon to work properly.
	 * @return \Yukari\Addon\Commander\Interpreter - Provides a fluent interface.
	 */
	public function registerListeners()
	{
		$dispatcher = Kernel::getDispatcher();
		$dispatcher->register(sprintf('irc.input.command.%s', Kernel::getConfig('story.playcommand')), array(Kernel::get('addon.game.adventure'), 'handlePlayStory'))
			->register(sprintf('irc.input.command.%s', Kernel::getConfig('story.restartcommand')), array(Kernel::get('addon.game.adventure'), 'handleRestartStory'))
			->register(sprintf('irc.input.command.%s', Kernel::getConfig('story.choosecommand')), array(Kernel::get('addon.game.adventure'), 'handleChooseStoryPath'));

		return $this;
	}

	/**
	 * Handles playing the latest chunk of the story.
	 * @param \Yukari\Event\Instance $event - The event to interpret.
	 * @return void
	 */
	public function handlePlayStory(\Yukari\Event\Instance $event)
	{
		$dispatcher = Kernel::getDispatcher();
		$database = Kernel::get('addon.database');

		// asdf

		return $results;
	}

	/**
	 * Handles restarting the story at the beginning.
	 * @param \Yukari\Event\Instance $event - The event to interpret.
	 * @return void
	 */
	public function handleRestartStory(\Yukari\Event\Instance $event)
	{
		$dispatcher = Kernel::getDispatcher();
		$database = Kernel::get('addon.database');

		// asdf

		return $results;
	}

	/**
	 * Handles choosing the path to take in the story.
	 * @param \Yukari\Event\Instance $event - The event to interpret.
	 * @return void
	 */
	public function handleChooseStoryPath(\Yukari\Event\Instance $event)
	{
		$dispatcher = Kernel::getDispatcher();
		$database = Kernel::get('addon.database');

		// asdf

		return $results;
	}

	protected function getCurrentEvent()
	{
		// asdf
	}

	protected function updateEventID(\Yukari\Lib\Hostmask $hostmask, $event_id)
	{
		// asdf
	}

	protected function getEventPaths()
	{
		// asdf
	}
}
