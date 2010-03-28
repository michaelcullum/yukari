<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 2
 * Copyright:	(c) 2009 - 2010 -- Failnet Project
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
 * Class autoloading function, takes in a class name and parses it according to built-in rules.
 * 		Function will automatically strip out the failnet_ prefix if present.
 * 		If the class contains underscores, the autoload function will expect the underscores to be slashes for directories.
 * 		Example being if you load in "failnet_plugin_admin", it will attempt to load the file at /includes/plugins/admin.php
 * @param string $name - Class name to load
 * @return void
 *
function failnet_load_file($name)
{
	// Begin by cleaning the class name of any possible ../. hacks
	$name = basename($name);

	// Now, drop the failnet_ prefix if it is there
	$name = (substr($name, 0, 8) == 'failnet_') ? substr($name, 8) : $name;

	// Replace any underscores with slashes...
	$name = str_replace('_', '/', $name);

	// Now, we try to get the file.
	require FAILNET_ROOT . "includes/{$name}.php";
}
 */

/**
 * Echos a message, and cleans out any extra NL's after the message.
 * 		Also will echo an array of messages properly as well.
 * @param mixed $message - The message or array of messages we want to echo to the terminal.
 * @return void
 */
function display($message)
{
	if(is_array($message))
	{
		foreach($message as $line)
		{
			echo ((strrpos($line, PHP_EOL . PHP_EOL) !== false) ? substr($line, 0, strlen($line) - 1) : $line) . PHP_EOL;
		}
	}
	else
	{
		echo ((strrpos($message, PHP_EOL . PHP_EOL) !== false) ? substr($message, 0, strlen($message) - 1) : $message) . PHP_EOL;
	}
}

/**
 * Throws a fatal and non-recoverable error.
 * @param string $msg - The error message to use
 * @return void
 */
function throw_fatal($msg)
{
	if(file_exists(FAILNET_ROOT . 'data/restart.inc'))
		unlink(FAILNET_ROOT . 'data/restart.inc');
	display('[Fatal Error] ' . $msg);
	display(dump_backtrace());
	sleep(7);
	exit(1);
}

/**
 * Return formatted string for filesizes
 * @param integer $bytes - The number of bytes to convert.
 * @return string - The filesize converted into KiB, MiB, or GiB.
 *
 * @author (c) 2007 phpBB Group
 */
function get_formatted_filesize($bytes)
{
	if ($bytes >= pow(2, 40))
		return round($bytes / 1024 / 1024 / 1024 / 1024, 2) . ' TiB';

	if ($bytes >= pow(2, 30))
		return round($bytes / 1024 / 1024 / 1024, 2) . ' GiB';

	if ($bytes >= pow(2, 20))
		return round($bytes / 1024 / 1024, 2) . ' MiB';

	if ($bytes >= pow(2, 10))
		return round($bytes / 1024, 2) . ' KiB';

	return $bytes . ' B';
}

/**
 * Converts a given integer/timestamp into days, minutes and seconds
 * @param integer $time - The time/integer to calulate the values from
 * @param boolean $last_comma - Should we have a comma between the second to last item of the list and the last, if more than 3 items for time?
 * 									This WAS actually something of debate, for grammar reasons. :P
 * @return string
 */
function timespan($time, $last_comma = false)
{
	$return = array();

	$count = floor($time / 29030400);
	if ($count > 0)
	{
		$return[] = $count . (($count == 1) ? ' year' : ' years');
		$time %= 29030400;
	}

	$count = floor($time / 2419200);
	if ($count > 0)
	{
		$return[] = $count . (($count == 1) ? ' month' : ' months');
		$time %= 2419200;
	}

	$count = floor($time / 604800);
	if ($count > 0)
	{
		$return[] = $count . (($count == 1) ? ' week' : ' weeks');
		$time %= 604800;
	}

	$count = floor($time / 86400);
	if ($count > 0)
	{
		$return[] = $count . (($count == 1) ? ' day' : ' days');
		$time %= 86400;
	}

	$count = floor($time / 3600);
	if ($count > 0)
	{
		$return[] = $count . (($count == 1) ? ' hour' : ' hours');
		$time %= 3600;
	}

	$count = floor($time / 60);
	if ($count > 0)
	{
		$return[] = $count . (($count == 1) ? ' minute' : ' minutes');
		$time %= 60;
	}

	$bigtime = (sizeof($return) ? implode(', ', $return) : '');

	if(!$last_comma)
	{
		if ($time > 0 || count($return) <= 0)
			$bigtime .= (sizeof($return) ? ' and ' : '') . ($time > 0 ? $time : '0') . (($time == 1) ? ' second' : ' seconds');
	}
	else
	{
		if ($time > 0 || count($return) <= 0)
			$bigtime .= (sizeof($return) ? ((sizeof($return) > 1) ? ',' : '') . ' and ' : '') . ($time > 0 ? $time : '0') . (($time == 1) ? ' second' : ' seconds');
	}

	return $bigtime;
}

/**
 * Benchmark function used to get benchmark times for code.
 * @param string $mode - The mode for the benchmark check
 * @param integer &$start - The start time for the benchmarking
 * @return mixed - void if mode is start or print, integer if mode is return
 *
 * @author Deadpool
 */
function benchmark($mode, &$start)
{
	/**
	 * Usage:
	 *
	 * For benchmarking PHP code
	 * <code>
	 * benchmark('start', $start_time);
	 * for (etc.) { $code }
	 * benchmark('print', $start_time);
	 * </code>
	 */
	if($mode == 'start')
	{
		$start = explode(' ', microtime());
		$start = $start[1] + $start[0];
	}
	elseif($mode == 'print' || $mode == 'return')
	{
		$micro = explode(' ', microtime());
		$time = substr(($micro[0] + $micro[1] - $start), 0, 9);

		if ($mode == 'return')
		{
			return $time;
		}
		else
		{
			display($time);
		}
	}
}

/**
 * Generate a backtrace and return it for use elsewhere.
 * @return array - The backtrace results.
 */
function dump_backtrace()
{
	$output = array();
	$backtrace = debug_backtrace();
	$path = fail_realpath(FAILNET_ROOT);
	foreach ($backtrace as $number => $trace)
	{
		// We skip the first one, because it only shows this file/function
		if ($number == 0)
		{
			continue;
		}

		// Strip the current directory from path
		if (empty($trace['file']))
		{
			$trace['file'] = '';
		}
		else
		{
			$trace['file'] = str_replace(array($path, '\\'), array('', '/'), $trace['file']);
			$trace['file'] = substr($trace['file'], 1);
		}
		$args = array();

		// If include/require/include_once is not called, do not show arguments - they may contain sensible information
		if (!in_array($trace['function'], array('include', 'require', 'include_once')))
		{
			unset($trace['args']);
		}
		else
		{
			// Path...
			if (!empty($trace['args'][0]))
			{
				$argument = $trace['args'][0];
				$argument = str_replace(array($path, '\\'), array('', '/'), $argument);
				$argument = substr($argument, 1);
				$args[] = "'{$argument}'";
			}
		}

		$trace['class'] = (!isset($trace['class'])) ? '' : $trace['class'];
		$trace['type'] = (!isset($trace['type'])) ? '' : $trace['type'];

		$output[] = 'FILE: ' . $trace['file'];
		$output[] = 'LINE: ' . ((!empty($trace['line'])) ? $trace['line'] : '');
		$output[] = 'CALL: ' . $trace['class'] . $trace['type'] . $trace['function'] . '(' . ((sizeof($args)) ? implode(', ', $args) : '') . ')';
	}
	return $output;
}

/**
 * Adjust destination path (no trailing slash), and make it safe to use.
 * Ripped from adm/index.php of phpBB 3.0.x
 *
 * @author phpBB Group
 */
function sanitize_filepath($path)
{
	if(substr($path, -1, 1) == '/' || substr($path, -1, 1) == '\\')
	{
		$path = substr($path, 0, -1);
	}

	$path = str_replace(array('../', '..\\', './', '.\\'), '', $path);
	if ($path && ($path[0] == '/' || $path[0] == "\\"))
	{
		$path = '';
	}

	$path = trim($path);

	// Make sure no NUL byte is present...
	if (strpos($path, "\0") !== false || strpos($path, '%00') !== false)
	{
		$path = '';
	}

	// Should be safe now. Return the value...
	return $path;
}

/**
 * @author Chris Smith <chris@project-minerva.org>
 * @copyright 2006 Project Minerva Team
 * @param string $path The path which we should attempt to resolve.
 * @return mixed
 */
function _realpath($path)
{
	// Now to perform funky shizzle

	// Switch to use UNIX slashes
	$path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
	$path_prefix = '';

	// Determine what sort of path we have
	if (is_absolute($path))
	{
		$absolute = true;

		if ($path[0] == '/')
		{
			// Absolute path, *NIX style
			$path_prefix = '';
		}
		else
		{
			// Absolute path, Windows style
			// Remove the drive letter and colon
			$path_prefix = $path[0] . ':';
			$path = substr($path, 2);
		}
	}
	else
	{
		// Relative Path
		// Prepend the current working directory
		if (function_exists('getcwd'))
		{
			// This is the best method, hopefully it is enabled!
			$path = str_replace(DIRECTORY_SEPARATOR, '/', getcwd()) . '/' . $path;
			$absolute = true;
			if (preg_match('#^[a-z]:#i', $path))
			{
				$path_prefix = $path[0] . ':';
				$path = substr($path, 2);
			}
			else
			{
				$path_prefix = '';
			}
		}
		else if (isset($_SERVER['SCRIPT_FILENAME']) && !empty($_SERVER['SCRIPT_FILENAME']))
		{
			// Warning: If chdir() has been used this will lie!
			// Warning: This has some problems sometime (CLI can create them easily)
			$path = str_replace(DIRECTORY_SEPARATOR, '/', dirname($_SERVER['SCRIPT_FILENAME'])) . '/' . $path;
			$absolute = true;
			$path_prefix = '';
		}
		else
		{
			// We have no way of getting the absolute path, just run on using relative ones.
			$absolute = false;
			$path_prefix = '.';
		}
	}

	// Remove any repeated slashes
	$path = preg_replace('#/{2,}#', '/', $path);

	// Remove the slashes from the start and end of the path
	$path = trim($path, '/');

	// Break the string into little bits for us to nibble on
	$bits = explode('/', $path);

	// Remove any . in the path, renumber array for the loop below
	$bits = array_values(array_diff($bits, array('.')));

	// Lets get looping, run over and resolve any .. (up directory)
	for ($i = 0, $max = sizeof($bits); $i < $max; $i++)
	{
		// @todo Optimise
		if ($bits[$i] == '..' )
		{
			if (isset($bits[$i - 1]))
			{
				if ($bits[$i - 1] != '..')
				{
					// We found a .. and we are able to traverse upwards, lets do it!
					unset($bits[$i]);
					unset($bits[$i - 1]);
					$i -= 2;
					$max -= 2;
					$bits = array_values($bits);
				}
			}
			else if ($absolute) // ie. !isset($bits[$i - 1]) && $absolute
			{
				// We have an absolute path trying to descend above the root of the filesystem
				// ... Error!
				return false;
			}
		}
	}

	// Prepend the path prefix
	array_unshift($bits, $path_prefix);

	$resolved = '';

	$max = sizeof($bits) - 1;

	// Check if we are able to resolve symlinks, Windows cannot.
	$symlink_resolve = (function_exists('readlink')) ? true : false;

	foreach ($bits as $i => $bit)
	{
		if (@is_dir("$resolved/$bit") || ($i == $max && @is_file("$resolved/$bit")))
		{
			// Path Exists
			if ($symlink_resolve && is_link("$resolved/$bit") && ($link = readlink("$resolved/$bit")))
			{
				// Resolved a symlink.
				$resolved = $link . (($i == $max) ? '' : '/');
				continue;
			}
		}
		else
		{
			// Something doesn't exist here!
			// This is correct realpath() behaviour but sadly open_basedir and safe_mode make this problematic
			// return false;
		}
		$resolved .= $bit . (($i == $max) ? '' : '/');
	}

	// @todo If the file exists fine and open_basedir only has one path we should be able to prepend it
	// because we must be inside that basedir, the question is where...
	// @internal The slash in is_dir() gets around an open_basedir restriction
	if (!@file_exists($resolved) || (!is_dir($resolved . '/') && !is_file($resolved)))
	{
		return false;
	}

	// Put the slashes back to the native operating systems slashes
	$resolved = str_replace('/', DIRECTORY_SEPARATOR, $resolved);

	// Check for DIRECTORY_SEPARATOR at the end (and remove it!)
	if (substr($resolved, -1) == DIRECTORY_SEPARATOR)
	{
		return substr($resolved, 0, -1);
	}

	return $resolved; // We got here, in the end!
}

/**
 * Realpath function set for generating a clean realpath.
 * Borrowed from phpBB 3.0.x
 *
 * @author (c) 2007 phpBB Group
 */
if (!function_exists('realpath'))
{
	/**
	* A wrapper for realpath
	* @ignore
	*/
	function fail_realpath($path)
	{
		return _realpath($path);
	}
}
else
{
	/**
	* A wrapper for realpath
	*/
	function fail_realpath($path)
	{
		$realpath = realpath($path);

		// Strangely there are provider not disabling realpath but returning strange values. :o
		// We at least try to cope with them.
		if ($realpath === $path || $realpath === false)
		{
			return _realpath($path);
		}

		// Check for DIRECTORY_SEPARATOR at the end (and remove it!)
		if (substr($realpath, -1) == DIRECTORY_SEPARATOR)
		{
			$realpath = substr($realpath, 0, -1);
		}

		return $realpath;
	}
}

/**
 * Retrieve contents from remotely stored file
 *
 * @author (c) 2007 phpBB Group
 */
function get_remote_file($host, $directory, $filename, &$errstr, &$errno, $port = 80, $timeout = 10)
{
	if ($fsock = @fsockopen($host, $port, $errno, $errstr, $timeout))
	{
		@fputs($fsock, "GET $directory/$filename HTTP/1.1\r\n");
		@fputs($fsock, "HOST: $host\r\n");
		@fputs($fsock, "Connection: close\r\n\r\n");

		$file_info = '';
		$get_info = false;

		while (!@feof($fsock))
		{
			if ($get_info)
			{
				$file_info .= @fread($fsock, 1024);
			}
			else
			{
				$line = @fgets($fsock, 1024);
				if ($line == "\r\n")
				{
					$get_info = true;
				}
				else if (stripos($line, '404 not found') !== false)
				{
					$errstr = 'ERROR 404 FILE NOT FOUND: ' . $filename;
					return false;
				}
			}
		}
		@fclose($fsock);
	}
	else
	{
		if ($errstr)
		{
			return false;
		}
		else
		{
			// If fsock is disabled, would we even be able to run Failnet?
			$errstr = 'fsock() is disabled';
			return false;
		}
	}

	return $file_info;
}

/**
 * Checks to see if the installed version is current.
 * @link http://code.assembla.com/failnet/git/node/blob/master/develop/version.txt The version check file
 *
 * @author (c) 2007 phpBB Group
 */
function check_version(&$up_to_date, &$latest_version, &$announcement_url)
{
	// Check the version, load out remote version check file!
	$errstr = '';
	$errno = 0;
	$info = get_remote_file('code.assembla.com', '/failnet/git/node/blob/master/develop', 'version.txt', $errstr, $errno);
	if ($info === false)
	{
		trigger_error($errstr, E_USER_WARNING);
	}
	$info = explode("\n", $info);
	$latest_version = trim($info[0]);
	$announcement_url = htmlspecialchars(trim($info[1]));
	$up_to_date = (!version_compare(str_replace('rc', 'RC', strtolower(FAILNET_VERSION)), str_replace('rc', 'RC', strtolower($latest_version)), '<'));
}

/**
 * Deny function...
 * @return string - The deny message to use. :3
 */
function deny_message()
{
	$rand = rand(0, 9);
	switch($rand)
	{
		case 0:
		case 1:
			return 'No.';
		break;
		case 2:
		case 3:
			return 'Uhm, no.';
		break;
		case 4:
		case 5:
			return 'Hells no!';
			break;
		case 6:
		case 7:
		case 8:
			return 'HELL NOEHS!';
		break;
		case 9:
			return 'The number you are dialing is not available at this time.';
		break;
	}
}

/**
 * Are we directing this at our owner or ourself?
 * This is best to avoid humilation if we're using an agressive command.  ;)
 * @param string $user - The user to check.
 * @return boolean - Are we targeting the owner or ourself?
 */
function checkuser($user)
{
   if(preg_match('#' . preg_quote(failnet::core()->config('owner'), '#') . '|' . preg_quote(failnet::core()->config('nick'), '#') . '|self#i', $user))
	   return true;
   return false;
}

/**
* Return unique id
* @param string $extra additional entropy
* @return string - The unique ID
*
* @author (c) 2007 phpBB Group
*/
function unique_id($extra = 'c')
{
	static $dss_seeded = false;

	$rand_seed = failnet::core()->config('rand_seed');
	$last_rand_seed = failnet::core()->config('last_rand_seed');

	$val = md5($rand_seed . microtime());
	$rand_seed = md5($rand_seed . $val . $extra);

	if($dss_seeded !== true && ($last_rand_seed < time() - rand(1,10)))
	{
		failnet::core()->sql('config', 'update')->execute(array(':name' => 'rand_seed', ':value' => $rand_seed));
		failnet::core()->settings['rand_seed'] = $rand_seed;
		$last_rand_seed = time();
		failnet::core()->sql('config', 'update')->execute(array(':name' => 'last_rand_seed', ':value' => $last_rand_seed));
		failnet::core()->settings['last_rand_seed'] = $last_rand_seed;
		$dss_seeded = true;
	}

	return substr($val, 4, 16);
}

/**
 * Converts a delimited string of hostmasks into a regular expression that will match any hostmask in the original string.
 * @param array $list - Array of hostmasks
 * @return string - Regular expression
 *
 * @author Phergie Development Team {@link http://code.assembla.com/phergie/subversion/nodes}
 */
function hostmasks_to_regex($list)
{
	static $hmask_find, $hmask_repl;
	if(empty($hmask_find))
		$hmask_find = array('\\', '^', '$', '.', '[', ']', '|', '(', ')', '?', '+', '{', '}');
	if(empty($hmask_repl))
		$hmask_repl = array('\\\\', '\\^', '\\$', '\\.', '\\[', '\\]', '\\|', '\\(', '\\)', '\\?', '\\+', '\\{', '\\}');

	$patterns = array();

	foreach($list as $hostmask)
	{
		// Find out which chars are present in the config mask and exclude them from the regex match
		$excluded = '';
		if (strpos($hostmask, '!') !== false)
		{
			$excluded .= '!';
		}
		if (strpos($hostmask, '@') !== false)
		{
			$excluded .= '@';
		}

		// Escape regex meta characters
		$hostmask = str_replace($hmask_find, $hmask_repl, $hostmask);

		// Replace * so that they match correctly in a regex
		$patterns[] = str_replace('*', ($excluded === '' ? '.*' : '[^' . $excluded . ']*'), $hostmask);
	}

	return ('#^' . implode('|', $patterns) . '$#iS');
}

/**
 * Parses a IRC hostmask and sets nick, user and host bits.
 * @param string $hostmask - Hostmask to parse
 * @param string &$nick - Container for the nick
 * @param string &$user - Container for the username
 * @param string &$host - Container for the hostname
 * @return void
 *
 * @author Phergie Development Team {@link http://code.assembla.com/phergie/subversion/nodes}
 */
function parse_hostmask($hostmask, &$nick, &$user, &$host)
{
	if (preg_match('/^([^!@]+)!([^@]+)@(.*)$/', $hostmask, $match) > 0)
	{
		list(, $nick, $user, $host) = array_pad($match, 4, NULL);
	}
	else
	{
		$nick = NULL;
		$user = NULL;
		$host = NULL;
	}
}

/**
 * Based on the function at http://php.net/manual/en/function.array-filter.php#89432 by "Craig", it allows separation of values based on a callback function
 * @param array &$input - The array to process, also this will be filled with array values that were evaluated as boolean FALSE via the compare callback
 * @param callback $compare - Function name that we will use to check each value
 * @return array - The vars that match in the strict comparison
 *
 * @note Kudos to cs278 for the function redesign...like ZOMG so much nicer!
 */
function array_split(&$input, $compare)
{
	$return = array_filter($input, $callback);
	$input = array_diff($input, $return);
	return $return;
}

/**
 * This function is a lie.
 * @return void
 */
function cake()
{
	$cake = array(
	'                                          ',
	'                                          ',
	'              ,:/+/-                      ',
	'              /M/              .,-=;//;-  ',
	'         .:/= ;MH/,    ,=/+%$XH@MM#@:     ',
	'        -$##@+$###@H@MMM#######H:.    -/H#',
	'   .,H@H@ X######@ -H#####@+-     -+H###@X',
	'    .,@##H;      +XM##M/,     =%@###@X;-  ',
	'  X%-  :M##########$.    .:%M###@%:       ',
	'  M##H,   +H@@@$/-.  ,;$M###@%,          -',
	'  M####M=,,---,.-%%H####M$:          ,+@##',
	'  @##################@/.         :%H##@$- ',
	'  M###############H,         ;HM##M$=     ',
	'  #################.    .=$M##M$=         ',
	'  ################H..;XM##M$=          .:+',
	'  M###################@%=           =+@MH%',
	'  @###############M/.           =+H#X%=   ',
	'  =+M#############M,        -/X#X+;.      ',
	'    .;XM#########H=     ,/X#H+;,          ',
	'      .=+HM########M+/+HM@+=.             ',
	'          ,:/%XM####H/.                   ',
	'               ,.:=-.                     ',
	'                                          ',
	'                                          ',
	);
	display($cake);
}
