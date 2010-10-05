<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     node
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2010 -- Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Failnet\Node;
use Failnet as Root;

/**
 * Failnet - Karma class,
 * 	    Used as Failnet's karma system.
 *
 *
 * @category    Failnet
 * @package     node
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
class Karma extends Base
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
	 * @ignore
	 */
	public function __construct()
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
		Bot::core('db')->armQuery('karma', 'create', 'INSERT INTO karma ( karma_value, term ) VALUES ( :karma, :term )');
		Bot::core('db')->armQuery('karma', 'update', 'UPDATE karma SET karma_value = :karma WHERE LOWER(term) = LOWER(:term)');
		Bot::core('db')->armQuery('karma', 'get', 'SELECT karma_value FROM karma WHERE LOWER(term) = LOWER(:term) LIMIT 1');
	}

	/**
	 * Pull up a term's karma rating.
	 * @param string $term - The karma term to lookup.
	 * @return mixed - NULL if no such karma term, integer of karma value if karma term found.
	 */
	public function getKarma($term)
	{
		// Check within the fixed karma list and karma blacklist.
		if(isset($this->blacklist[$term]))
			return 'I really don\'t know.';
		if(isset($this->fixed[$term]))
			return sprintf($this->fixed[$term], $term);

		Bot::core('db')->useQuery('karma', 'get')->execute(array(':term' => $term));
		$result = Bot::core('db')->useQuery('karma', 'get')->fetch(PDO::FETCH_ASSOC);
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
	public function setKarma($term, $type)
	{
		// Make sure we're playing fair here. :)
		if($type !== self::KARMA_INCREASE && $type !== self::KARMA_DECREASE)
			return false;

		// Check within the fixed karma list and karma blacklist.
		if(isset($this->blacklist[$term]))
			return $this->blacklist[$term];
		if(isset($this->fixed[$term]))
			return 'You\'re not allowed to change karma for that!';

		$amount = $this->getKarma($term);

		if(!is_null($amount))
		{
			Bot::core('db')->useQuery('karma', 'update')->execute(array(':term' => $term, ':karma' => $amount + $type));
		}
		else
		{
			Bot::core('db')->useQuery('karma', 'create')->execute(array(':term' => $term, ':karma' => 0 + $type));
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
