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
	/**
	 * @var array - The array of story data loaded from the YAML file.
	 */
	protected $story_data = array();

	/**
	 * Prepare the database and define the necessary queries.
	 * @return \Yukari\Addon\Adventure\Story - Provides a fluent interface
	 */
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

			if(empty($result))
			{
				$event_id = Kernel::getConfig('story.startpoint');

				$sql = 'INSERT INTO game_adventure_story
					( host_string, event_id )
					VALUES ( :host_string, :event_id )';

				$q = $db->prepare($sql);
				$q->bindParam(':host_string', $hostmask, PDO::PARAM_STR);
				$q->bindParam(':event_id', $event_id, PDO::PARAM_STR);
				$q->execute();
			}
			else
			{
				$event_id = $result['event_id'];
			}

			return $event_id;
		});

		// Update the event ID for the current user.
		$database->defineQuery('story.updateUserLastEvent', function(\PDO $db, $hostmask, $event_id) {
			$sql = 'UPDATE game_adventure_story
				SET event_id = :event_id
				WHERE host_string = :host_string';

			$q = $db->prepare($sql);
			$q->bindParam(':host_string', $hostmask, PDO::PARAM_STR);
			$q->bindParam(':event_id', $event_id, PDO::PARAM_STR);
            $result = $q->execute();

			$q = NULL;

			return $result;
		});

		return $this;
	}

	/**
	 * Load the YAML file containing the story for the adventure.
	 * @return \Yukari\Addon\Adventure\Story - Provides a fluent interface
	 */
	public function loadStoryFile()
	{
		$story_file = \sfYaml::load(YUKARI . '/data/config/addons/' . Kernel::getConfig('story.story_file'));
		$this->story_data = $story_file['story.data'];

		return $this;
	}

	/**
	 * Register the listeners we need for this addon to work properly.
	 * @return \Yukari\Addon\Adventure\Story - Provides a fluent interface.
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
	 * @return array - Array of messages to send.
	 */
	public function handlePlayStory(\Yukari\Event\Instance $event)
	{
		$event_id = $this->getCurrentEvent($event['hostmask']);

		return $this->buildStoryEvent($event, $event_id);
	}

	/**
	 * Handles restarting the story at the beginning.
	 * @param \Yukari\Event\Instance $event - The event to interpret.
	 * @return array - Array of messages to send.
	 */
	public function handleRestartStory(\Yukari\Event\Instance $event)
	{
		$event_id = Kernel::getConfig('story.startpoint');
		$this->updateEventID($event['hostmask'], $event_id);

		return $this->buildStoryEvent($event, $event_id);
	}

	/**
	 * Handles choosing the path to take in the story.
	 * @param \Yukari\Event\Instance $event - The event to interpret.
	 * @return array - Array of messages to send.
	 */
	public function handleChooseStoryPath(\Yukari\Event\Instance $event)
	{
		$event_id = $this->getCurrentEvent($event['hostmask']);
		$paths = $this->getEventPaths($event_id);

		if(!isset($paths[$event['text']]))
		{
			$results[] = \Yukari\Event\Instance::newEvent(null, 'irc.output.privmsg')
				->setDataPoint('target', $event['target'])
				->setDataPoint('text', sprintf('%1$s: Invalid option specified.', $event['target']));

			return $results;
		}
		else
		{
			$next_event_id = $paths[$event['text']]['event'];
			$this->updateEventID($event['hostmask'], $next_event_id);
			return $this->buildStoryEvent($event, $next_event_id);
		}
	}

	/**
	 * Handles creation of output for our specified story event.
	 * @param \Yukari\Event\Instance $event - The event to interpret.
	 * @return array - Array of messages to send.
	 */
	protected function buildStoryEvent(\Yukari\Event\Instance $event, $event_id)
	{
		$event_text = wordwrap($this->story_data[$event_id]['text'], 300, "\n");

		$results = array();
		// Explodie the message!
		foreach(explode("\n", $event_text) as $line)
		{
			$results[] = \Yukari\Event\Instance::newEvent(null, 'irc.output.privmsg')
				->setDataPoint('target', $event['target'])
				->setDataPoint('text', sprintf('%1$s: %2$s', $event['hostmask']['nick'], $line));
		}

		// If we have paths, we'll want to let the sucker know what varieties of doom^W^W^W^H options they have.
		if(isset($this->story_data[$event_id]['paths']))
		{
			$results[] = \Yukari\Event\Instance::newEvent(null, 'irc.output.privmsg')
				->setDataPoint('target', $event['target'])
				->setDataPoint('text', sprintf('%1$s: You have %2$s options to choose from...do you:', $event['hostmask']['nick'], count($this->story_data[$event_id]['paths'])));

			// WHAT DO
			foreach($this->story_data[$event_id]['paths'] as $path_id => $path)
			{
				$path_text = explode("\n", wordwrap($path['text'], 300, "\n"));
				$first = true;
				foreach($path_text as $line)
				{
					// We want to only show the "option xyz" bit if it's the first line about it.
					if($first === true)
					{
						$results[] = \Yukari\Event\Instance::newEvent(null, 'irc.output.privmsg')
							->setDataPoint('target', $event['target'])
							->setDataPoint('text', sprintf('%1$s: option "%2$s": %3$s', $event['hostmask']['nick'], $path_id, $line));
						$first = false;
					}
					else
					{
						$results[] = \Yukari\Event\Instance::newEvent(null, 'irc.output.privmsg')
							->setDataPoint('target', $event['target'])
							->setDataPoint('text', sprintf('%1$s: (...) %2$s', $event['hostmask']['nick'], $line));
					}
				}
			}
		}

		return $results;
	}

	/**
	 * Get the current event for the specified user (by way of their hostmask).
	 * @param \Yukari\Lib\Hostmask $hostmask - The hostmask to use for current event lookup.
	 * @return array - Array containing the event data for the last event the user encountered.
	 *
	 */
	protected function getCurrentEvent(\Yukari\Lib\Hostmask $hostmask)
	{
		$database = Kernel::get('addon.database');

		return $this->story_data[$database->query('story.getUserLastEvent', sprintf('%1$s@%2$s', $hostmask['username'], $hostmask['host']))];
	}

	/**
	 * Update the last encountered event ID for the specified user.
	 * @param \Yukari\Lib\Hostmask $hostmask - The hostmask to update the last encountered event ID for.
	 * @param integer $event_id - The event ID to set as the last encountered event.
	 * @return void
	 */
	protected function updateEventID(\Yukari\Lib\Hostmask $hostmask, $event_id)
	{
		$database = Kernel::get('addon.database');

		$database->query('story.updateUserLastEvent', sprintf('%1$s@%2$s', $hostmask['username'], $hostmask['host']), $event_id);
	}

	/**
	 * Get the possible choice paths for the specified event.
	 * @param integer $event_id - The event to lookup choice paths for.
	 * @return array - The array of paths that can be taken from the specified event.
	 */
	protected function getEventPaths($event_id)
	{
		return $this->story_data[$event_id]['paths'];
	}
}
