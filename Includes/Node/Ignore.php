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
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @todo rebuild so that this pops up before pre-event and kills the event somehow, or prevents reactions within dispatch
 * @todo __construct and not init
 * @todo Bot::core() and not $this->failnet
 * @todo camelCase method names
 *
 */

namespace Failnet\Node;
use Failnet;

/**
 * Failnet - Ignore handling class,
 * 	    Used as Failnet's handler for ignoring users based on hostmasks.
 *
 *
 * @category    Failnet
 * @package     node
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Ignore extends Base
{
	/**
	 * preg_match pattern cache used to check for an ignored user
	 * @var string
	 */
	private $cache = '';

	/**
	 * List of ignored user hostmasks, used to rebuild the preg_match ignore pattern when necessary
	 * @var array
	 */
	private $users = array();

	/**
	 * @ignore
	 */
	public function __construct()
	{
		Bot::core('db')->armQuery('ignore', 'create', 'INSERT INTO ignore ( ignore_date, hostmask ) VALUES ( :timestamp, :hostmask )');
		Bot::core('db')->armQuery('ignore', 'delete', 'DELETE FROM ignore WHERE LOWER(hostmask) = LOWER(:hostmask)');
		Bot::core('db')->armQuery('ignore', 'get_single', 'SELECT * FROM ignore WHERE LOWER(hostmask) = LOWER(:hostmask) LIMIT 1');
		Bot::core('db')->armQuery('ignore', 'get', 'SELECT * FROM ignore');
	}

	/**
	 * Loads up the ignored users list
	 * @return void
	 */
	public function loadIgnore()
	{
		Bot::core('ui')->system('--- Loading ignored users list...');
		Bot::core('db')->useQuery('ignore', 'get')->execute();
		$this->users = Bot::core('db')->useQuery('ignore', 'get')->fetchAll(PDO::FETCH_COLUMN, 0);
		$this->cache = hostmasks_to_regex($this->users);
	}

	/**
	 * Checks to see if the specified hostmask is ignored
	 * @param string $target - The hostmask to check
	 * @return boolean - True if the hostmask is ignored, false if not.
	 */
	public function isIgnored($target)
	{
		return ($this->users) ? preg_match($this->cache, $target) : false;
	}

	/**
	 * Adds the specified target user to the ignored users list.
	 * @param string $hostmask - The sender's hostmask
	 * @param string $target - The target hostmask that should be added to the ignore list
	 * @return boolean True on success, false on hostmask already being ignored
	 */
	public function addIgnore($hostmask, $target)
	{
		// Check to see if this user would already be ignored...
		if(!$this->isIgnored($target))
		{
			// Do that SQL thang
			Bot::core('db')->useQuery('ignore', 'create')->execute(array(':timestamp' => time(), ':hostmask' => $target));

			// Now we need to rebuild the cached PCRE pattern
			$this->users[] = $target;
			$this->cache = hostmasks_to_regex($this->users);
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Removes a specified hostmask pattern from the ignored users list.
	 * @param string $hostmask - The sender's hostmask
	 * @param string $target - The target hostmask to be removed from the ignore list
	 * @return boolean True on success, false on hostmask not within the ignore list
	 */
	public function delIgnore($hostmask, $target)
	{
		// Check to see if this hostmask IS in the ignored list
		if(in_array($target, $this->users))
		{
			// Do that SQL thang
			Bot::core('db')->useQuery('ignore', 'delete')->execute(array(':hostmask' => $target));

			// Now we need to rebuild the cached PCRE pattern
			foreach($this->users as $i => $user)
			{
				if($target === $user)
					unset($this->users[$i]);
			}
			$this->cache = hostmasks_to_regex($this->users);
			return true;
		}
		else
		{
			return false;
		}
	}
}
