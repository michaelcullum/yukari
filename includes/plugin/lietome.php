<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 1
 * Copyright:	(c) 2009 - Failnet Project
 * License:		GNU General Public License - Version 2
 *
 *===================================================================
 * 
 */

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-2.0.php>.
 */
 

/**
 * Failnet - Lie to Me plugin,
 * 		This allows users to pull up a random episode of Lie to Me, or even pull up the description for a specified episode.
 * 		Made by request of Hank_the_Cowdog in irc://irc.freenode.net/startrekguide
 * 
 *
 * @package plugins
 * @author Obsidian
 * @copyright (c) 2009 - Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_plugin_lietome extends failnet_plugin_common
{
	/**
	 * @var array - Episodes be here
	 */
	private $episodes = array(
		// Season 1
		1	=> array(
			1	=> 'Season 1, Episode 1 - Pilot - Dr. Lightman and Dr. Foster search for the truth in cases involving a devoutly religious high school student accused of killing a teacher, and a congressman accused of having an affair.',
			2	=> 'Season 1, Episode 2 - Moral Waiver - Lightman\'s team investigates cases involving a staff sergeant accused of raping a soldier, and a star college basketball player accused of taking a bribe from a wealthy booster.',
			3	=> 'Season 1, Episode 3 - A Perfect Score - An investigation into the death of a judge\'s daughter leads Dr. Brightman to the girl\'s prep school, while Dr. Foster investigates a NASA pilot who crashed on a test flight.',
			4	=> 'Season 1, Episode 4 - Love Always - The Lie to Me gang attends a high profile wedding where the father of the groom, a Korean ambassador, has been getting death threats.',
			5	=> 'Season 1, Episode 5 - Unchained - The gang tackles 2 cases -- the suspicious death of a firefighter and the parole of a former gang leader.',
			6	=> 'Season 1, Episode 6 - Do No Harm - Dr. Lightman and Dr. Foster tackle the case of a missing girl. Torres and Loker focus on whether a North Ugandan woman told the truth in her autobiography about the violence in her country.',
			7	=> 'Season 1, Episode 7 - The Best Policy - Dr. Lightman and Torres investigate a drug company affiliated with one of Dr. Lightman\'s college friends. Dr. Foster and Loker help negotiate with Yemen for the release of two young Americans.',
			8	=> 'Season 1, Episode 8 - Depraved Heart - Dr. Lightman and Torres investigate a spate of suicides in young Indian women. We discover that a suicide has helped shape Dr. Lightman. Meanwhile, Dr. Foster and Loker are at odds while they investigate a case of suspected SEC fraud.',
			9	=> 'Season 1, Episode 9 - Life Is Priceless - Dr. Lightman and Dr. Foster help FEMA run a rescue operation on a collapsed construction site. Torres and Loker talk to the girlfriend of an internet billionaire.',
			10	=> 'Season 1, Episode 10 - The Better Half - Dr. Lightman\'s ex-wife shows up, and asks him to tackle an arson case. Torres works the case of a drive by shooting that killed a member of a rapper\'s entourage.',
			11	=> 'Season 1, Episode 11 - Undercover - Dr. Lightman\'s investigation into a questionable police shooting could jeopardize a terrorism investigation, while Eli\'s lie in an earlier case could hurt the firm. Meanwhile, is Dr. Foster\'s husband having an affair as Lightman suspects?',
			12	=> 'Season 1, Episode 12 - Blinded - On behalf of the FBI, the Lightman Group works the case of a copycat serial rapist who blinds his victims. Lightman faces off against the original rapist, a master liar.',
			13	=> 'Season 1, Episode 13 - Sacrifice - The whole Lightman group solves the case of terrorist bombings in the D.C. area. Dr. Foster has escalating problems with her marriage. Torres\'s boyfriend in the Secret Service is in danger.',
		),
		2	=> array(
			1	=> 'Season 2, Episode 1 - The Core of It - Dr. Lightman tries to figure out whether a woman with multiple personalities witnessed a murder, while Torres interrogates a judge who is being considered for a Supreme Court appointment.',
			2	=> 'Season 2, Episode 2 - Truth or Consequences - Zoe gets Dr. Lightman\'s help in a case involving an African-American college athlete accused of statutory rape, and Dr. Foster tries to help a sympathetic mother while investigating a religious leader for the IRS.',
			3	=> 'Season 2, Episode 3 - Control Factor - While on vacation in Mexico, Lightman and Emily become involved in the case of a missing American woman. Back home, Foster investigates tainted blood in D.C.-area hospitals with Lightman\'s rival, Jack Rader.',
			4	=> 'Season 2, Episode 4 - Honey - No description.',
			5	=> 'Season 2, Episode 5 - Grievous Bodily Harm - A face from Lightman’s past pulls him into a dangerous criminal conspiracy, testing his friendship and loyalty. Meanwhile, The Lightman Group investigates a high school student’s homicidal threats.',
		),
	);

	public function cmd_privmsg()
	{
		// Process the command
		$text = $this->event->get_arg('text');
		if(!$this->prefix($text))
			return;

		$cmd = $this->purify($text);
		$sender = $this->event->nick;
		$hostmask = $this->event->gethostmask();
		switch ($cmd)
		{
			// Handle both specific episode inquiries and requests for a random episode
			case 'lietome':
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

				$this->call_privmsg($this->event->source(), $results);
			break;
		}
	}
}

?>