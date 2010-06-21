<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		3.0.0 DEV
 * Copyright:	(c) 2009 - 2010 -- Damian Bushong
 * License:		MIT License
 *
 *===================================================================
 *
 */

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


/**
 * Failnet - Firefly plugin,
 * 		This allows users to pull up a random episode of Firefly, or even pull up the description for a specified episode.
 *
 *
 * @package plugins
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Damian Bushong
 * @license MIT License
 */
class failnet_plugin_firefly extends failnet_plugin_common
{
	/**
	 * @var array - Flans unite!
	 */
	private $episodes = array(
		// Season 1
		1	=> array(
			1	=> 'Episode 1 - Serenity - Malcolm Reynolds is a veteran and the captain of Serenity. He and his crew are smuggling goods, but they need to pick up some passengers for extra money. However, not all the passengers are what they seem. ',
			2	=> 'Episode 2 - The Train Job - The crew of Serenity takes on a train heist commissioned by a crime lord. They steal the goods, only to find it is medicine that is desperately needed by the town. ',
			3	=> 'Episode 3 - Bushwhacked - Serenity is pulled in by an Alliance cruiser while investigating a spaceship that was attacked by Reavers. Simon and River must hide to prevent capture, while something is wrong with the lone survivor of the attacked spaceship. ',
			4	=> 'Episode 4 - Shindig - Inara attends a formal society dance, only to find Malcolm there as well, attempting to set up a smuggling job. Mal comes to blows with Inara\'s conceited date and finds himself facing a duel with a renowned swordsman, and only one night to learn how to fence. ',
			5	=> 'Episode 5 - Safe - Mal must choose which crew members to save when one is gravely wounded and two others are kidnapped. Simon finds an uneasy haven in a remote village, but River\'s uncanny perceptions jeopardize the Tams\' temporary safety.',
			6	=> 'Episode 6 - Our Mrs. Reynolds - As an unexpected reward for an unpaid job, Mal finds himself married to a naïve, subservient young woman named Saffron. The crew are amused at his discomfort and Book lectures him on propriety, but things are not as smoothly straightforward as they thought them to be. ',
			7	=> 'Episode 7 - Jaynestown - Returning to a planet where he ran into some serious trouble years ago, Jayne discovers that he\'s become a local folk legend. Mal decides to use this entertaining distraction to complete a job, but some unfinished business may derail his plans. ',
			8	=> 'Episode 8 - Out of Gas - After Serenity suffers a catastrophe that leaves her crew with only hours of oxygen, flashbacks show how Mal and Zoe acquired Serenity and assembled their motley band. ',
			9	=> 'Episode 9 - Ariel - Hard up for cash, Serenity takes on a job from Simon: help him get a thorough diagnostic of River in return for the opportunity to loot the vast medical stores of an Alliance hospital on central world Ariel. But River\'s pursuers are hot on their trail, and they receive some unexpected inside help. ',
			10	=> 'Episode 10 - War Stories - Angered at Zoe\'s unshakable war connection to Mal, Wash demands a shot at a field assignment. Unfortunately, crime lord Niska chooses this moment to exact a brutal vengeance for Mal\'s failure to complete an earlier job. ',
			11	=> 'Episode 11 - Trash - Saffron returns to plague Serenity with a scheme to steal a rare antique weapon from a wealthy landowner. Unfortunately for Mal, she neglects to mention just how she came across the information needed to break into the landowner\'s home. ',
			12	=> 'Episode 12 - The Message - A former Independence soldier who had served with Mal and Zoe returns in a dramatic manner, with a vicious Alliance officer chasing after him for some unusual smuggled goods. ',
			13	=> 'Episode 13 - Heart of Gold - A Companion-trained friend of Inara\'s who runs a brothel calls for help from Serenity when a local bigwig reveals his intentions to take "his" baby from the girl he impregnated. ',
			14	=> 'Episode 14 - Objects in Space - Serenity encounters a ruthlessly professional bounty hunter, Jubal Early, who will stop at nothing to retrieve River. But River, feeling unwelcome on the ship, takes a novel approach to escaping from the long arm of the Alliance. ',
		),
		/**
		 * One day....one day...
		 *
		2	=> array(

		),
		*/
	);

	public function help(&$name, &$commands)
	{
		$name = 'firefly';
		$commands = array(
			'firefly'		=> 'firefly s{$season}e{$episode} - (no auth) - Returns an episodes info if an episode is specified; if no season/episode is specified, returns data for a random episode',
		);
	}

	public function cmd_privmsg()
	{
		// Process the command
		$text = $this->event->get_arg('text');
		if(!$this->prefix($text))
			return;

		$cmd = $this->purify($text);
		$this->set_msg_args(($this->failnet->config('speak')) ? $this->event->source() : $this->event->hostmask->nick);

		$sender = $this->event->hostmask->nick;
		$hostmask = $this->event->hostmask;
		switch ($cmd)
		{
			// Handle both specific episode inquiries and requests for a random episode
			case 'firefly':
				if(!empty($text))
				{
					if(preg_match('#^S([0-9]+)E([0-9]+)$#i', $text, $matches))
					{
						if(!isset($this->episodes[$matches[1]]) || !isset($this->episodes[$matches[1]][$matches[2]]))
						{
							$results = 'No such episode exists';
						}
						else
						{
							$results = $this->episodes[$matches[1]][$matches[2]];
						}
					}
					else
					{
						$results = 'Invalid data provided';
					}

				}
				else
				{
					$rand = array_rand($this->episodes);
					$results = $this->episodes[$rand][array_rand($this->episodes[$rand])];
				}

				$this->msg($results);
			break;
		}
	}
}
