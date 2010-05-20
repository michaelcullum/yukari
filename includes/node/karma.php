<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * @version:	3.0.0 DEV
 * @copyright:	(c) 2009 - 2010 -- Failnet Project
 * @license:	http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 *
 *===================================================================
 *
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
 *
 */



/**
 * Failnet - Karma class,
 * 		Used as Failnet's karma system.
 *
 *
 * @package nodes
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_node_karma extends failnet_common
{
	/**
	 * Items that have a fixed karma rating, along with what the fixed rating is.
	 * @var array
	 */
	private $fixed = array();

	/**
	 * Blacklisted items that we'll not give karma for.
	 * @var array
	 */
	private $blacklist = array();

	/**
	 * Karma constants
	 */
	const KARMA_INCREASE = 1;
	const KARMA_DECREASE = -1;

	/**
	 * Specialized init function to allow class construction to be easier.
	 * @see includes/failnet_common#init()
	 * @return void
	 */
	public function init()
	{
		if(!defined('M_EULER'))
            define('M_EULER', '0.57721566490153286061');

		// Set our fixed karma entries
		$this->fixed = array(
			'failnet'			=> '%s is too awesome for karma',
			'failnet2'			=> '%s is too awesome for karma',
			'pi'				=> '%s has a karma of ' . M_PI,
			'Π'					=> '%s has a karma of ' . M_PI,
			'π'					=> '%s has a karma of ' . M_PI,
			'chucknorris'		=> '%s has a karma of [WARNING] Integer out of range',
			'chuck_norris'		=> '%s has a karma of [WARNING] Integer out of range',
			'c'					=> '%s has a karma of 299,792,458 m/s',
			'e'					=> '%s has a karma of ' . M_E,
			'euler'				=> '%s has a karma of ' . M_EULER,
			'mole'				=> '%s has a karma of 6.02214e23 molecules',
			'avogadro'			=> '%s has a karma of 6.02214e23 molecules',
			'spoon'				=> '%s has no karma, because there is no spoon',
			'4chan'				=> '%s has a karma of over NINE THOUSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
			'mc^2'				=> '%s has a karma of E',
			'mc2'				=> '%s has a karma of E',
			'mc²'				=> '%s has a karma of E',
			'i'					=> '%s can haz big karma',
		);

		$this->blacklist = array(
            '*' 				=> 'I\'m sorry, but you can\'t change the karma for everything.',
            'all'				=> 'I\'m sorry, but you can\'t change the karma for everything.',
            'everything'		=> 'I\'m sorry, but you can\'t change the karma for everything.',
			'everyone'			=> 'I\'m sorry, but you can\'t change the karma for everything.',
        );

		// Add in our prepared SQL statements. ;)
		$this->sql('karma', 'create', 'INSERT INTO karma ( karma_value, term ) VALUES ( :karma, :term )');
		$this->sql('karma', 'update', 'UPDATE karma SET karma_value = :karma WHERE LOWER(term) = LOWER(:term)');
		$this->sql('karma', 'get', 'SELECT karma_value FROM karma WHERE LOWER(term) = LOWER(:term) LIMIT 1');
	}

	/**
	 * Pull up a term's karma rating.
	 * @param string $term - The karma term to lookup.
	 * @return mixed - NULL if no such karma term, integer of karma value if karma term found.
	 */
	public function get_karma($term)
	{
		// Check within the fixed karma list and karma blacklist.
		if(isset($this->blacklist[$term]))
			return 'I really don\'t know.';
		if(isset($this->fixed[$term]))
			return sprintf($this->fixed[$term], $term);

		$this->failnet->sql('karma', 'get')->execute(array(':term' => $term));
		$result = $this->failnet->sql('karma', 'get')->fetch(PDO::FETCH_ASSOC);
		if(!$result)
			return NULL;

		return (int) $result['karma_value'];
	}

	/**
	 * Change the karma value for a certain term
	 * @param string $term - The term to change the karma for
	 * @param string $type - Whether or not we should increase or decrease the karma for this term
	 * @return boolean - Whether or not the karma change was successful
	 */
	public function set_karma($term, $type)
	{
		// Make sure we're playing fair here. :)
		if($type !== self::KARMA_INCREASE && $type !== self::KARMA_DECREASE)
			return false;

		// Check within the fixed karma list and karma blacklist.
		if(isset($this->blacklist[$term]))
			return $this->blacklist[$term];
		if(isset($this->fixed[$term]))
			return 'You\'re not allowed to change karma for that!';

		$amount = $this->get_karma($term);
		if(!is_null($amount))
		{
			$this->failnet->sql('karma', 'update')->execute(array(':term' => $term, ':karma' => $amount + $type));
		}
		else
		{
			$this->failnet->sql('karma', 'create')->execute(array(':term' => $term, ':karma' => 0 + $type));
		}

		return true;
	}

	/**
	 * Checks to see if a phrase is a karma request
	 * @param string $term - The term to check for eligibility
	 * @return boolean - Whether or not the term meets our pattern for karma
	 */
	public function check_word($term)
	{
		return preg_match('#^[a-zA-Z0-9\+\-\_\[\]\|`]+[\+\+|\-\-]$#i', $term);
	}
}
