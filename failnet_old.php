#!/usr/bin/php
<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0
 * SVN ID:		$Id$
 * Copyright:	(c) 2009 - Obsidian
 * License:		http://opensource.org/licenses/gpl-2.0.php  |  GNU Public License v2
 *
 *===================================================================
 *
 * Failnet is based off of and built upon the following work:
 *
 *  PHPBot -- PHP-Based IRC Bot
 *-------------------------------------------------------------------
 * Version:		0.2.5
 * Copyright:	(c) 2009 - Kai Tamkun
 * License:		http://license.youreofftask.com/software.php  |  Kai License (Compatible with GPLv2)
 * Source:		Available at http://youreofftask.com/phpbot/
 *
 *===================================================================
 *
 */

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
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
 * @ignore
 */
define('IN_FAILNET', true);
define('FAILNET_VERSION', '1.0.0'); 

$failnet = new failnet();

// Begin printing info to the terminal window with some general information about Failnet.
echo failnet::HR . PHP_EOL;
echo 'Failnet -- PHP-based IRC Bot version ' . FAILNET_VERSION . ' - $Revision$' . PHP_EOL;
echo 'Copyright: (c) 2009 - Obsidian' . PHP_EOL;
echo 'License: http://opensource.org/licenses/gpl-2.0.php' . PHP_EOL;
echo 'Failnet uses code from PHPBot [ Copyright (c) 2009 Kai Tamkun ]' . PHP_EOL;
echo failnet::HR . PHP_EOL;
echo 'Failnet is starting up. Go get yourself a coffee.' . PHP_EOL;

// Set error handler
echo '- Loading error handler' . PHP_EOL; @set_error_handler('fail_handler');

// Loading DBs, initializing some vars
$actions = array_flip(file('data/actions'));

// Load dictionary file - This fails on Windows systems.
echo '- Loading dictionary (if file is present on OS)' . PHP_EOL; $dict = (@file_exists('/etc/dictionaries-common/words')) ? file('/etc/dictionaries-common/words') : array();

// Load user DB
echo '- Loading user database' . PHP_EOL; $failnet->loaduserdb();

// Adding the core to the modules list and loading help file
$failnet->modules[] = 'core';
$help['core'] = 'Good luck.'; //file_get_contents('data/corehelp'); // This file was just...missing.  O_o

// Load modules
$load = array(
	'simple_html_dom',
	'warfare',
	'slashdot',
	'xkcd',
	'reload',
/*
	'dict',
	'alchemy',
	'notes',
	'markov',
*/
);
echo '- Loading modules' . PHP_EOL;
foreach($load as $item)
{
	if(include 'modules/' . $item . '.php') echo '=-= Loaded "' . $item . '" module' . PHP_EOL;
}

// This is a hack to allow us to restart Failnet if we're running the script through a batch file.
echo '- Removing termination indicator file' . PHP_EOL; if(file_exists('data/restart')) unlink('data/restart');

// Load in the configuration data file
echo '- Loading configuration file for specified IRC server' . PHP_EOL; $failnet->load($argv[1]);

echo '- Loading ignored users list' . PHP_EOL; $failnet->ignore = explode(', ', file_get_contents('data/ignore_users'));

// In case of restart/reload, to prevent 'Nick already in use' (which asplodes everything)
echo 'Preparing to connect...' . PHP_EOL; sleep(2);

// Initiate the beast!  Run, Failnet, RUN!
echo 'Failnet loaded and ready!' . PHP_EOL;
echo 'Connecting to server...' . PHP_EOL;
$failnet->run();

class failnet
{
	/**
	 * Vars for Failnet
	 */

	public $debug = false;
	public $speak = true;
	public $joined = false;

	// Server connection and config vars.
	public $server = '';
	public $port = 6667;
	public $sock;
	public $cycle = 0;

	// Configs for Failnet's authorization and stuff.
	public $owner = '';
	public $nick = '';
	public $pass = '';
	public $user = '';
	public $name = 'Failnet';
	
	// DO NOT SET.
	public $original = '';
	public $chan = '';

	// Currently loaded/joined channels, occupants for each channel, etc.
	public $chans = array();
	public $names = array();
	public $log = array();

	// Currently ignored users.
	public $ignore = array();  // explode(', ', file_get_contents('data/ignore_users'));

	// Authed users.
	public $users = array();

	// Modules list.
	public $modules = array();

	/**
	 * Constants for Failnet.
	 */
	const NL = "\n";
	const TAB = "\t";
	const X01 = "\x01";
	const X02 = "\x02";
	const HR = '---------------------------------------------------------------------';

	/**
	 * Methods for Failnet
	 */

	public function __construct() { }
	
	public function run()
	{
		$introduced = $command = $forme = false;
		$this->original = $this->nick;
		$this->sock = fsockopen($this->server, $this->port);
		while (!feof($this->sock))
		{
			$srvmsg = rtrim(fgets($this->sock), PHP_EOL);
				
			if (!preg_match('/PRIVMSG|\|auth|\|adduser|\|ident/i', $srvmsg))  // Don't display passwords and stuff. ;)
			{
				if($this->debug) echo $this->cycle . '       ' . $srvmsg . PHP_EOL;
			}
			
			
			if(!$this->joined)
			{
				// Tell the server about us
				if ($this->cycle == 0)
				{
					if (ereg('(Throttled: Reconnecting too fast)', $srvmsg))
					{
						echo 'Couldn\'t connect, let\'s try again.' . PHP_EOL;
						$failnet->terminate(true);
					}
					else
					{
						echo 'Connected to ' . $this->server . ' on port ' . $this->port . PHP_EOL;
						$this->send_server('USER ' . $this->user . ' null null :' . $this->name . PHP_EOL . 'NICK ' . $this->nick);
					}
				}
				if (preg_match('/^\:.+' . preg_quote($this->server, '/') . ' (.+) ' . $this->nick . ' (.+)/', $srvmsg, $matches))
				{
					$this->parse($matches[1], $matches[2]);
				}
					
				// Declare that we are, in fact, a bot.
				if (preg_match('/^:.+' . preg_quote($this->server, '/') . ' 001 ' . $this->nick . ' :Welcome to (.*)/', $srvmsg))
				{
					$this->send_server('MODE ' . $this->nick . ' +B');
				}
	
				// Join channels after MOTD.
				if (ereg('End of /MOTD command', $srvmsg))
				{
					foreach ($this->chans as $chan_)
					{
						$this->send_server('JOIN ' . $chan_);
					}
					$this->joined = true;
				}
			}
			
			/*
			if ($kicked > 0)
			{
				// Restart if kicked
				$failnet->terminate(true);
			}
			*/
	
			// Parsing...
			$str = split(':', $srvmsg, 3);
			$str[1] = split(' ', $str[1]);
			$str[1][0] = split('!', $str[1][0]);
				
			// Play some ping pong with the server
			if (substr($srvmsg, 0, 6)=='PING :')
			{
				$this->send_server('PONG :' . substr($srvmsg, 6));
				$servermsg = 'PING';
			}
			else
			{
				$servermsg = $str[1][1];
			}

			// Fun stuff!
			if ($this->joined)
			{
				if ($str[1][2] != $this->nick && preg_match('/^(.*)KICK ' . preg_quote($str[1][2]) . ' ' . preg_quote($this->nick) . '(.*)$/i', $srvmsg))
				{
					$kicked = $this->cycle;
					if(!$this->debug) echo '-!- Kicked from ' . $str[1][2] . ' by ' . $str[1][0][0] . '!' . PHP_EOL;
					$this->log('--- Kicked from channel "' . $str[1][2] . '" by ' . $str[1][0][0] . ' ---');
					
					// Remove this channel from the joined channels list!
					$chans_ = array_flip($this->chans);
					unset($this->chans[$chans_[$str[1][2]]]);
				}
				if (file_get_contents('data/eternalrampage')=='yesh') rampage(0); // Because I feel evil.
				if (!empty($this->pass)) $this->privmsg('IDENTIFY ' . $this->pass, 'NickServ'); unset($this->pass);
				if (!$introduced)
				{
					sleep(2);
					foreach ($this->chans as $chan_)
					{
						$this->log('--- Joining channel "' . $chan_ . '" ---');
						if(!$this->debug) echo '-!- Joining ' . $chan_ . PHP_EOL;
						$this->privmsg('Let there be faiiiillll!', $chan_);
					}
					$introduced = true;
				}
				if (($servermsg == 'PRIVMSG') && ($str[1][0][0] != $this->nick))
				{
					$this->chan = ($str[1][2] == $this->nick) ? $str[1][0][0] : $str[1][2];
					//echo $str[1][0][0] . PHP_EOL . $str[1][2] . PHP_EOL;
					if (isset($str[3]))
					{
						$str[3] = rtrim($str[3]);
					}
					else
					{
						$str[2] = rtrim($str[2]);
					}
					if (preg_match('/^' . $this->nick . '/i', $str[2]))
					{
						$forme = true;
						$str[2] = preg_replace('/^' . $this->nick . '(|:|,) /i', '', $str[2]);
					}
					else
					{
						$forme = false;
					}
					$command = (preg_match('/^\|/', $str[2])) ? true : false;
					if (!preg_match('/\|adduser|\|auth|\|ident/i', $str[2]))
					{
						echo '<' . $str[1][0][0] . '/' . $this->chan . '> ' . $str[2] . PHP_EOL; // Removes mask, etc.
						$this->add_log($str[2], $str[1][0][0], $this->chan);
					}
					if(!in_array($str[1][0][0], $this->ignore)) // Ignore select users. :D
					{
						if (substr(strtolower($str[2]),0,5)=='|say ' && $this->speak)
						{
							$whattosay = rtrim(substr($str[2],5));
							// We don't want to be kicked.
							$this->privmsg( ($whattosay == 'opme') ? 'I\'m sorry, you\'re not permitted to use me to test kicking.' : $whattosay);
						}
						else
						{
							if ($this->speak && $forme && preg_match('/^shut up$/i', $str[2]))
							{
								$this->choose('Okay, I\'ll shut up for now.', 'Oh, shut up? I can shut up! I always shut up when anybody asks me to shut up, I\'m very shutty-uppy..');
								$this->factoids(0, 0);
							}
							if ($this->speak) $this->checkfact($str[2], $forme, $command, $str[1][0][0]);
							if (!$this->speak && $forme && preg_match('/^come back$/i', $str[2]))
							{
								$this->privmsg('Hooray!');
								$this->factoids(1, 0);
							}
						}
					}
					else
					{
						// 30% chance of telling the ignored user that they're being ignored, if it was a direct factoid.
						if ($forme && rand(0, 9) > 7) $this->privmsg('I\'m ignoring you.', $str[1][0][0]);
					}
				}
			}
			// Stuff to do before the next cycle
			$this->cycle++;
			unset($servermsg, $srvmsg);
			$forme = false;
		}
	}
	
	public function load($srv)
	{
		if(!file_exists('data/config_' . $srv) || !is_readable('data/config_' . $srv))
		{
			$error = '[ERROR] Failed loading configuration file for server "' . $srv . '"';
			log_error($error);
			echo($error) . PHP_EOL;
			$failnet->terminate(false);
		}
		$config = file('data/config_' . $srv);
		foreach($config as &$item)
		{
			$item = explode('::', rtrim($item));
			$key = $item[0];
			if(property_exists(__CLASS__, $key)) $this->$key = $item[1];
		}
		
		echo '- Loading channel autojoin list' . PHP_EOL;	$this->chans = explode(' ', file_get_contents('data/chans_' . $srv));
	}
	
	/**
	 * Raw IRC commands and their handler methods
	 */

	// Send a raw message to the IRC server
	public function send_server($msg, $usenl = true)
	{
		if($this->debug) echo $msg . PHP_EOL;
		fwrite($this->sock, ($usenl) ? $msg . PHP_EOL : $msg);
	}

	// Send a message.
	public function privmsg($msg, $spec = false)
	{
		if(!$this->joined) return;
		if (!empty($msg))
		{
			$where = ($spec) ? $spec : $this->chan;
			$msg = str_replace('\n', PHP_EOL, $msg);
			if (ereg(PHP_EOL, $msg))
			{
				$msgs = split('/\r?[\r\n]/', $msg);
				foreach ($msgs as $msg2)
				{
					$this->add_log($msg2, $this->nick, $where);
					$this->send_server('PRIVMSG ' . $where . ' :' . $msg2);
					if(!$this->debug) echo '<' . $this->nick . '/' . $where . '> ' . $msg2 . PHP_EOL;
				}
			}
			else
			{
				$this->add_log($msg, $this->nick, $where);
				$this->send_server('PRIVMSG ' . $where . ' :' . $msg); // \x0301,08
				if(!$this->debug) echo '<' . $this->nick . '/' . $where . '> ' . $msg . PHP_EOL;
			}
		}
	}

	// Same as privmsg(), but sends an action.
	public function action($msg, $spec = false)
	{
		if(!$this->joined) return;
		$msg = self::X01 . 'ACTION ' . rtrim($msg) . ' '. self::X01;
		$this->add_log($msg, $this->nick, (($spec) ? $spec : $this->chan));
		$this->send_server('PRIVMSG ' . (($spec) ? $spec : $this->chan) . ' :' . $msg . PHP_EOL);
		if(!$this->debug) echo '<' . $this->nick . '/' . $spec . '> ' . $msg . PHP_EOL;
	}
	
	// Change Failnet's IRC nick.
	public function changenick($sender, $newnick)
	{
		if ($this->authlevel($sender) > 4)
		{
			if ($newnick != "_")
			{
				$this->nick = $newnick;
				$this->log('--- Changing nick to "' . $newnick . '" ---');
				$this->send_server('NICK ' . $newnick);
				if(!$this->debug) echo '-!- Changing nick to "' . $newnick . '"' . PHP_EOL;
			}
		}
		else
		{
			$this->deny();
		}
	}

	// Jump into an IRC channel.
	public function join($sender, $newchan)
	{
		if ($this->authlevel($sender) > 4)
		{
			$this->chans[] = $newchan;
			array_unique($this->chans);
			$this->send_server('JOIN ' . $newchan);
			// Write to our log.  ;)
			$this->log('--- Joining channel "' . $newchan . '" ---');
			if(!$this->debug) echo '-!- Joining "' . $newchan . '"' . PHP_EOL;
			$this->privmsg('Let there be faiiiillll!', $newchan);
		}
		else
		{
			$this->deny();
		}
	}

	// Leave an IRC channel.
	public function part($sender, $toleave, $msg = false)
	{
		if ($this->authlevel($sender) > 4)
		{
			foreach ($this->chans as &$chan)
			{
				if ($chan == $toleave) unset($chan);
			}
			$this->privmsg('Bai baiii!', $toleave);
			// Write to our log.  ;)
			$this->log('--- Leaving channel "' . $toleave . '" ---');
			if(!$this->debug) echo '-!- Leaving channel "' . $toleave . '"' . PHP_EOL;
			$this->send_server('PART ' . $toleave . (($msg) ? ' :' . $msg : ''));
		}
		else
		{
			$this->deny();
			return 0;
		}
	}
	
	// Heheh. /kick a user. :D
	public function kick($sender, $victim, $msg = false, $where = false)
	{
		if ($this->authlevel($sender) > 4)
		{
			if(rand(0, 1))
			{
				$this->choose('Here\'s MY boomstick!', '*PUNT*');
			}
			else
			{
				$this->action('starts to glow');
			}
			// Write to our log.  ;)
			$where = ($where) ? $where : $this->chan;
			sleep(1);
			$this->log('--- Kicking user ' . $victim . ' on channel "' . $where . '" ---');
			if(!$this->debug) echo '-!- Kicking ' . $victim . 'from channel "' . $where . '"' . PHP_EOL;
			$this->send_server('KICK ' . $where . ' ' . $victim . (($msg) ? ' :' . $msg : ''));
		}
		else
		{
			$this->deny();
			return 0;
		}
	}
	
	// Shut down Failnet in case of extreme stupidity.
	public function dai($sender)
	{
		global $sure;
		if ($this->authlevel($sender) > 24)
		{
			if (!empty($sure))
			{
				$this->quit('OH SHI--', false);
			}
			else
			{
				$this->privmsg('Are you sure? If so, please repeat |dai.');
				$sure = true;
			}
		}
		else
		{
			$this->privmsg('You don\'t have the authority to kill me with |dai.');
		}
	}
	
	// Reload Failnet.  Useful if changes are made to the core, and we need him to come back.  :D
	public function reload($sender)
	{
		if ($this->authlevel($sender) > 19)
		{
			$this->quit('ZOMG, BRB!', true);
		}
		else
		{
			$this->deny();
		}
	}
	
	// Wrapper for the IRC quit command
	public function quit($msg = false, $restart = true)
	{
		foreach($this->chans as $chan)
		{
			$this->privmsg($msg, $chan);
		}
		if(!$this->debug) echo '-!- Quitting from server "' . $this->server . '"' . PHP_EOL;
		$this->log('--- Quitting from server "' . $this->server . '" ---');
		$this->send_server('QUIT');
		$this->terminate($restart);
	}
	
	// Terminates Failnet, and restarts it as per params.
	public function terminate($restart = true)
	{
		fclose($this->sock);
		$this->sock = null;
		if($restart)
		{
			// Just a hack to get it to restart through batch, and not terminate.
			file_put_contents('data/restart', 'yesh');
			// Dump the log cache to the file.
			$this->log('--- Restarting Failnet ---', true);
			if(!$this->debug) echo '-!- Restarting Failnet' . PHP_EOL;
			exit(0);
		}
		else
		{
			// Just a hack to get it to truly terminate through batch, and not restart.
			if(file_exists('data/restart')) unlink('data/restart');
			// Dump the log cache to the file.
			$this->log('--- Terminating Failnet ---', true);
			if(!$this->debug) echo '-!- Terminating Failnet' . PHP_EOL;
			exit(1);
		}
	}
	
	public function ctcp($msg, $person = false)
	{
		$this->send_server('PRIVMSG ' . (($person) ? $person : $this->chan) . ' :' . self::X01 . rtrim($msg) . self::X01 . PHP_EOL);
	}
	
	// This does...something...
	public function parse($type, $str)
	{
		//echo 'Parsing (' . $type . ') --> ' . $str . PHP_EOL;
		if ($type == '353')
		{
			preg_match('/^ \= (.+) \:(.+)$/', $str, $m);
			$this->getnames($m);
		}
		
		//! Why is this here?
		if ($type == '001')
		{
			$this->send_server('MODE ' . $this->nick . ' +B');
		}
	}
	
	// Decisions, decisions..
	public function choose($a, $b)
	{
		$this->privmsg((rand(0, 1) ? $a : $b));
	}
	
	/**
	 * Factoid handling methods
	 */

	// Returns multi-dimensional array of factoids.
	public function load_facts($forme = false, $command = false, $sender = '[unknown]')
	{
		if ($forme)
		{
			$facts = array_merge(file('data/factoids_specifically_for_me'), file('data/factoids'));
		}
		elseif ($command)
		{
			$facts = array_merge(file('data/commands'), file('data/factoids'));
		}
		else
		{
			$facts = file('data/factoids');
		}

		$search = array(
			'_nick_',
			'_owner_',
		);
		$replace = array(
			$this->nick,
			$this->owner,
		);
		if ($sender != '[unknown]')
		{
			$search[] = '_sender_';
			$replace[] = $sender;
		}

		foreach ($facts as &$fact)
		{
			$fact = str_replace($search, $replace, $fact);
			$fact = explode(' >> ', $fact);
		}
		return $facts;
	}

	// Check the factoids
	public function checkfact($tocheck, $forme = false, $command = false, $sender = '[unknown]')
	{
		$done = 0;
		$tocheck = rtrim($tocheck);
		$facts = $this->load_facts($forme, $command, $sender);
		for ($i = 0; $i < sizeof($facts); $i++)
		{
			if ($facts[$i][1] == '(function)')
			{
				if (preg_match('/' . $facts[$i][0] . '/i', $tocheck, $matches))
				{
					for ($j = 2; $j < sizeof($facts[$i]); $j++)
					{
						$facts[$i][$j] = preg_replace('/\["/', '\"', $facts[$i][$j]);
					}
					if (sizeof($facts[$i])>3)
					{
						$usefact = $facts[$i][rand(2, sizeof($facts[$i]) - 1)];
						if (!ereg('_skip_', $usefact))
						{
							eval($usefact);
							$done++;
						}
					}
					else
					{
						eval($facts[$i][2]);
						$done++;
					}
				}
			}
			else
			{
				if (preg_match('/' . $facts[$i][0] . '/i', $tocheck))
				{
					if (sizeof($facts[$i]) > 2)
					{
						$usefact = $facts[$i][rand(1, sizeof($facts[$i]) - 1)];
						if (preg_match('/^\_action\_/', $usefact))
						{
							$this->action(preg_replace('/' . $facts[$i][0] . '/i', preg_replace('/^\_action\_/', '', $usefact), $tocheck));
							$done++;
						}
						else
						{
							if (!ereg('_skip_', $usefact))
							{
								$this->privmsg(preg_replace('/' . $facts[$i][0] . '/i', $usefact, $tocheck));
								$done++;
							}
						}
					}
					else
					{
						if (preg_match('/^\_action\_/', $facts[$i][1]))
						{
							$this->action(preg_replace('/' . $facts[$i][0] . '/i', preg_replace('/^\_action\_/', '', $facts[$i][1]), $tocheck));
							$done++;
						}
						else
						{
							if (!ereg('_skip_', $facts[$i][1]))
							{
								$this->privmsg(preg_replace('/' . $facts[$i][0] . '/i', $facts[$i][1], $tocheck));
								$done++;
							}
						}
					}
				}
			}
		}
		if ($done == 0)
		{
			//privmsg(markov());
		}
	}

	// Enable/disable factoids.
	public function factoids($on = 0, $verbose = 1)
	{
		$this->speak = $on;
		if ($verbose) $this->privmsg(($on) ? 'Factoids on.' : 'Factoids off.');
	}
	
	// Delete a factoid.
	public function delfact($sender, $fact)
	{
		if ($this->authlevel($sender)>6)
		{
			file_put_contents('data/backups/factoids_' . time(), file_get_contents('data/factoids'));
			$lines = file('data/factoids');
			foreach ($lines as &$line)
			{
				$line = explode(' >> ', $line);
				if (strtolower(rtrim($line[0])) == strtolower($fact))
				{
					unset($line);
				}
				else
				{
					$line = implode(' >> ', $line);
				}
			}
			file_put_contents('data/factoids', implode('', $lines));
			$this->privmsg('Okay ' . $sender . ', I have deleted the factoid ' . $fact . ' from the database.');
		}
		else
		{
			$this->deny();
		}
	}
	
	/**
	 * Auth handling methods
	 */
	
	// Instant auth.
	public function instauth($user)
	{
		foreach ($this->users as &$user_row)
		{
			if ($user_row[0] == $user)
			{
				$user_row[3] = 1;
				$this->privmsg('Instant authentication successful.');
				file_put_contents('data/instantauth', 'nope');
			}
		}
	}

	// Auth the sending user.
	public function auth($sender, $pw)
	{
		foreach ($this->users as &$user)
		{
			if ($user[0] == $sender)
			{
				if ($user[2] == sha1($pw))
				{
					$user[3] = 1;
					$this->privmsg('You have been authenticated.');
					return;
				}
				else
				{
					$this->privmsg('Unable to authenticate: incorrect password.');
					return;
				}
			}
		}
		$this->privmsg('Unable to authenticate: not in database.');
	}

	// Load the users DB file initially. :D
	public function loaduserdb()
	{
		$this->users = file('data/users');
		foreach ($this->users as &$user)
		{
			$user = explode('::', rtrim($user));
		}
		// Just a hack to let us do things ourselves.
		$this->users[] = array($this->original, '100', sha1($this->pass), true);
	}

	// Get a user's auth level.
	public function authlevel($person)
	{
		foreach ($this->users as &$user)
		{
			if ($user[0] == $person) return (!empty($user[3])) ? $user[1] : false;
		}
	}

	// Add a new user to the DB file!  YAY!
	public function adduser($nick, $pw)
	{
		foreach ($this->users as &$user)
		{
			if ($user[0] == $nick) return;
		}
		file_put_contents('data/users', PHP_EOL . $nick . '::0::' . sha1($pw), FILE_APPEND);
	}
	
	// Ignore a user.
	public function ignore($sender, $victim)
	{
		if ($this->authlevel($sender) > 9)
		{
			if(!in_array($victim, $this->ignore))
			{
				$this->ignore[] = $victim; 
				file_put_contents('data/ignore_users', implode(', ', $this->ignore));
				$this->privmsg('User "' . $victim . '" is now ignored.'); 
			}
			else
			{
					$this->privmsg('User "' . $victim . '" is already ignored.');
			}
		}
		else
		{
			$this->deny();
		}
	}
	
	// 	// Unignore a user.
	public function unignore($sender, $victim)
	{
		if ($this->authlevel($sender) > 9)
		{
			foreach($this->ignore as $id => &$user)
			{
				if($user == $victim) unset($this->ignore[$id]);
			}
			file_put_contents('data/ignore_users', implode(', ', $this->ignore));
			$this->privmsg('User "' . $victim . '" is no longer ignored.');
		}
		else
		{
			$this->deny();
		}
	}
	
	// Gets the ignore file (re)loads the ignore list.
	public function loadignore($sender)
	{
		if ($this->authlevel($sender) > 9)
		{
			$this->ignore = explode(', ', file_get_contents('data/ignore_users')); 
			$this->privmsg('Reloaded ignore list.');
		}
		else
		{
			$this->deny();
		}
	}

	// The the user to GTFO.
	public function deny()
	{
		$rand = rand(0, 9);
		switch ($rand)
		{
			case 0:
			case 1:
				$this->privmsg('No.');
				break;
			case 2:
			case 3:
				$this->privmsg('Uhm, no.');
				break;
			case 4:
			case 5:
				$this->privmsg('Hells no!');
				break;
			case 6:
			case 7:
			case 8:
				$this->privmsg('HELL NOEHS!');
				break;
			case 9:
				$this->privmsg('The number you are dialing is not available at this time.');
				break;
		}
	}

	// Load the channel occupant list.
	public function getnames($m)
	{
		$people = explode(' ', $m[2]);
		if (empty($this->names[$m[1]]))
		{
			$this->names[$m[1]] = $people;
		}
		else
		{
			$in = array();
			foreach ($people as $person)
			{
				foreach ($this->names[$m[1]] as $name)
				{
					if ($name == $person)
					{
						$in[$person] = 1;
						last;
					}
				}
				if (!$in[$person])
				{
					$this->names[$m[1]][] = $person;
				}
			}
		}
	}

	// Get a random user in the channel.
	public function namein($of = '')
	{
		if (empty($of)) $of = $this->chan;
		return (!empty($this->names[$of])) ? array_rand($this->names[$of]) : 'someone';
	}
	
	// Debug function.  O_o
	public function debug($txt)
	{
		echo self::HR . PHP_EOL . self::TAB . $txt . PHP_EOL . self::HR . PHP_EOL;
	}

	// Heartthunder function.  XD
	public function heartthunder($theirspaces, $theirnick, $str='<3 thunder')
	{
		return str_repeat(' ', ((strlen($theirspaces) + strlen($theirnick)) - strlen($this->nick))) . $str;
	}
	
	// Are we directing this at our owner or ourself?
	// This is best to avoid humilation if we're using an agressive factoid.  ;)
	public function checkuser($user)
	{
		return ($user != $this->owner && $user != $this->nick && !preg_match('/self/i', $user)) ? true : false;
	}
	
	// Check the logs for something..
	public function searchlog($txt)
	{
		$log = file('log');
		foreach ($log as &$line)
		{
			$line = rtrim($line);
			if ((preg_match('/' . preg_quote($txt, '/') . '/i', $line)) && (!preg_match('/\|log/i', $line))) $this->privmsg($line);
		}
	}

	// Write an event to the log.
	public function add_log($log, $sender, $where = false)
	{
		if(preg_match('/^IDENTIFY (.*)/i', $log)) $log = 'IDENTIFY ***removed***';
		$log = (preg_match('/' . PHP_EOL . '(| )$/i', $log)) ? substr($log, 0, strlen($log) - 1) : $log;
		$this->log(@date('D m/d/Y - h:i:s A') . ' <' . $sender . (($where) ? '/' . $where : false) . '> ' . preg_replace('/^' . self::X01 . 'ACTION (.+)' . self::X01 . '$/', '*'. $sender . ' $1' . '*', $log));
	}
	
	// Write something to the log.
	public function log($msg, $dump = false)
	{
		$this->log[] = $msg;
		if($dump == true || sizeof($this->log) > 10)
		{
			$log_msg = '';
			$log_msg = PHP_EOL . implode(PHP_EOL, $this->log);
			$this->log = array();
			file_put_contents('log', $log_msg, FILE_APPEND);
		}
	}
}

/**
 * Error handler function for Failnet.  Modified from the phpBB 3.0.x msg_handler() function.
 */
function fail_handler($errno, $msg_text, $errfile, $errline)
{
	global $msg_long_text, $failnet;

	// Do not display notices if we suppress them via @
	if (error_reporting() == 0)
	{
		return;
	}

	// Message handler is stripping text. In case we need it, we are possible to define long text...
	if (isset($msg_long_text) && $msg_long_text && !$msg_text)
	{
		$msg_text = $msg_long_text;
	}

	
	switch ($errno)
	{
		case E_NOTICE:
		case E_WARNING:
		case E_USER_WARNING:
		case E_USER_NOTICE:
		default:
			$error = '[Debug] PHP Notice: in file ' . $errfile . ' on line ' . $errline . ': ' . $msg_text . PHP_EOL;
			if($failnet->joined && $failnet->debug) $failnet->privmsg(@date('D m/d/Y - h:i:s A') . ' ' . $error, $failnet->owner); 
			log_error(@date('D m/d/Y - h:i:s A') . ' ' . $error); echo $error . PHP_EOL;
			return;
			break;

		case E_USER_ERROR:
		case E_PARSE:
		case E_ERROR:
			$error = '[ERROR] PHP Fatal Error: in file ' . $errfile . ' on line ' . $errline . ': ' . $msg_text . PHP_EOL;
			if($failnet->joined && $failnet->debug) $failnet->privmsg(@date('D m/d/Y - h:i:s A') . ' ' . $error, $failnet->owner);
			log_error(@date('D m/d/Y - h:i:s A') . ' ' . $error); echo $error . PHP_EOL;
			// Fatal error, so DAI.
			$failnet->terminate(false);
			break;
	}

	// If we notice an error not handled here we pass this back to PHP by returning false
	// This may not work for all php versions
	return false;
}

function log_error($msg)
{
	file_put_contents('error_log', $msg, FILE_APPEND);
}

/**
 * Commands/functions/methods that just shouldn't be part of the Failnet core class.
 */

// Randomly grabs an action from the array of available actions, unsets it from the array, and returns the action.
function getaction()
{
	$a = array_rand(failnet::$actions);
	unset(failnet::$actions[$a]);
	return $a;
}

// Be annoying then restart, or restart and repeat infinitely.
function rampage($exit = 0, $eternal = 0)
{
	global $failnet;
	if ($eternal)
	{
		file_put_contents('data/eternalrampage', 'yesh');
	}
	$failnet->factoids(0, 0);
	$failnet->action('goes on a rampage');
	sleep(2);
	$failnet->privmsg('AAAAAAAAA');
	for ($i = 0; $i < 5; $i++)
	{
		$failnet->action($failnet->getaction());
		sleep(3);
	}
	$failnet->action('dies.');
	sleep(1);
	$failnet->terminate($exit);
}

// THE PAAAAIN! Results in death for Failnet due to excess flood.
function silentdeath()
{
	global $failnet;
	$failnet->privmsg('This death is NOT silent. LOLOLOLOLOLOLOLOLOLOLOLOLOLOLOLOLOLOL');
	usleep(500000);
	silentdeath();
}

function dict()
{
	global $dict, $failnet;
	if(@sizeof($dict))
	{
		$failnet->privmsg($dict[array_rand($dict)]);
	}
}

function remove_p($s,$lower=0) {
	if ($lower) return strtolower(preg_replace('/[^a-zA-Z0-9-\s]/', '', $s));
	return preg_replace('/[^a-zA-Z0-9]/', '', $s);
}

/**
 * Table stuffs.  O_o
 */

function renamerow($table, $row, $newname) {
	if (!is_file("tables/$table")) {
		return false;
	}
	$final = simplexml_load_file("tables/$table");
	foreach ($final->row as $foo) {
		if ($row == (string)$foo['name']) {
			$foo['name'] = $newname;
		}
	}
	$fh = fopen("tables/$table", 'w');
	if (flock($fh, LOCK_EX)) {
		$return = fwrite($fh, trim(preg_replace('/\/\>'."\n".'/', ' />'."\n", preg_replace('/'."\t\n".'/', "\n", str_replace("\n\t\n", "\n", preg_replace('/'."\t".'\<table/', '<table', preg_replace('/\<\/table\>/', "\n</table>", preg_replace("/\n\</", "\n\t<", str_replace("\n\n", "\n", $final->asXML())))))))));
		flock($fh, LOCK_UN);
		fclose($fh);
		return $return;
	} else {
		return false;
	}
}
function deleterow($table, $row) {
	if (!is_file("tables/$table")) {
		return false;
	}
	$final = simplexml_load_file("tables/$table");
	$i=0;
	foreach ($final->row as $foo) {
		if ($row == (string)$foo['name']) {
			unset($final->row[$i]);
		}
		$i++;
	}
	$fh = fopen("tables/$table", 'w');
	if (flock($fh, LOCK_EX)) {
		$return = fwrite($fh, trim(preg_replace('/\/\>'."\n".'/', ' />'."\n", preg_replace('/'."\t\n".'/', "\n", str_replace("\n\t\n", "\n", preg_replace('/'."\t".'\<table/', '<table', preg_replace('/\<\/table\>/', "\n</table>", preg_replace("/\n\</", "\n\t<", str_replace("\n\n", "\n", $final->asXML())))))))));
		flock($fh, LOCK_UN);
		fclose($fh);
		return $return;
	} else {
		return false;
	}
}
function rows($table) {
	if (!is_file("tables/$table")) {
		return false;
	}
	$table = simplexml_load_file("tables/$table");
	foreach ($table->row as $foo) {
		$return[] = (string)$foo['name'];
	}
	return $return;
}
function read($table, $row) {
	if (!is_file("tables/$table")) {
		return false;
	}
	$table = simplexml_load_file("tables/$table");
	foreach ($table->row as $foo) {
		if ($foo['name']==$row) {
			$bar = $foo;
		}
	}
	if (!$bar) {
		return false;
	}
	if ($bar['type']=='array') {
		$baz = explode(', ', $bar['data']);
		foreach ($baz as &$quack) {
			$quack = explode('||', $quack);
			foreach($quack as &$meow) {
				$meow = base64_decode($meow);
			}
		}
		return $baz;
	} elseif($bar['type']=='list') {
		$baz = explode(', ', $bar['data']);
		foreach ($baz as &$quack) {
			$quack = base64_decode($quack);
		}
		return $baz;
	} else {
		return base64_decode($bar['data']);
	}
}
function write($table, $row, $data, $dontoverwrite=0) {
	if (!is_dir('tables')) {
		mkdir('tables');
	}
	$type = 'other';
	if (is_array($data)) {
		$type = 'list';
		foreach ($data as $key => $val) {
			if (!is_numeric($key)) {
				$type = 'array';
				last;
			}
		}
	}
	if ($type == 'array') {
		$i = 0;
		foreach ($data as $key=>$val) {
			if ($i > 0) {
				$dat.= ', ';
			}
			$dat .= base64_encode($key).'||'.base64_encode($val);
			$i++;
		}
	} elseif($type == 'list') {
		$i = 0;
		foreach ($data as $bagel) {
			if ($i > 0) {
				$dat.= ', ';
			}
			$dat .= base64_encode($bagel);
			$i++;
		}
	} else {
		$dat = base64_encode($data);
	}
	if ((!is_file("tables/$table"))||(filesize("tables/$table")==0)) {
		touch("tables/$table");
		$fh = fopen("tables/$table", 'r');
		if (flock($fh, LOCK_EX)) {
			fwrite($fh, "tables/$table", <<<FOO
<?xml version="1.0"?>
<table>
	<row name="$row" type="$type" data="$dat" />
</table>
FOO
			);
			flock($fh, LOCK_UN);
			fclose($fh);
		} else {
			return false;
		}
	} else {
		$xml = simplexml_load_file("tables/$table");
		foreach ($xml->row as $foo) {
			if ($foo['name'] == $row) {
				if ($dontoverwrite) {
					return false;
				}
				$foo['data'] = $dat;
				$foo['type'] = $type;
				$z=1;
			}
		}
		if (!$z) {
			$newrow = $xml->addChild('row');
			$newrow->addAttribute('name', $row);
			$newrow->addAttribute('type', $type);
			$newrow->addAttribute('data', $dat);
		}
		$final = $xml->asXML();
		$final = trim(preg_replace('/\/\>'."\n".'/', ' />'."\n", preg_replace('/'."\t\n".'/', "\n", str_replace("\n\t\n", "\n", preg_replace('/'."\t".'\<table/', '<table', preg_replace('/\<\/table\>/', "\n</table>", preg_replace("/\n\</", "\n\t<", str_replace("\n\n", "\n", $final))))))));
		$fh=fopen("tables/$table", 'w');
		if (flock($fh, LOCK_EX)) {
			fwrite($fh, $final);
			flock($fh, LOCK_UN);
			fclose($fh);
		} else {
			return false;
		}
	}
}

/*
 function calc($sender, $a) {
 global $store;
 if (authlevel($sender)>1) {
 eval('privmsg('.$a.'); $store = '.$a.';');
 } else {
 deny();
 }
 }
 */

/*
 function pws ($string) {
 $string = explode(')(', $string);
 $i = 0;
 foreach ($string as &$bits) {
 $bits = str_replace(array(')', '('), '', $bits);
 $bits = split('[x\^]', $bits);
 $p[$i] = bcmul($bits[0], bcpow($bits[1], $bits[2]));
 $i++;
 }
 $r = bcmul($p[0], $p[1]); // $r --> result
 if (is_nan($r)) {
 $r = 'DOES NOT COMPUTE';
 } elseif (is_infinite($r)) {
 if ($r > 0) {
 $r = 'IT\'S WAAAY OVER 9000! (infinity)';
 } else {
 $r = 'IT\'S UNDER -9000! (-infinity)';
 }
 } elseif ($r > 9000) {
 $r = 'IT\'S OVER 9000! (which is '.$r.')';
 }
 privmsg($r);
 }
 */

/*
 function shmeval($sender, $a) {
 if (authlevel(substr($sender,0,strlen($sender)-1))>1) {
 eval($a);
 } else {
 deny();
 }
 }
 */

/*
 function power($a, $b) {
 global $store;
 $a = eregi_replace('s', $store, $a);
 echo $a;
 }
 */

/*
 function store($a) {
 global $store;
 $store = $a;
 }
 */

?>