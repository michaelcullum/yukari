<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     Failnet
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

namespace Failnet;

/**
 * Error handler function for Failnet.  Modified from the phpBB 3.0.x msg_handler() function.
 * @param integer $errno - Level of the error encountered
 * @param string $msg_text - The error message recieved
 * @param string $errfile - The file that the error was encountered at
 * @param integer $errline - The line that the error was encountered at
 * @return mixed - If suppressed, nothing returned...if not handled, false.
 */
function errorHandler($errno, $msg_text, $errfile, $errline)
{
   // Do not display notices if we suppress them via @
   if (error_reporting() == 0)
	   return;

   // Strip the current directory from the offending file
   $errfile = (!empty($errfile)) ? substr(str_replace(array(__DIR__, '\\'), array('', '/'), $errfile), 1) : '';
   $error = 'in file ' . $errfile . ' on line ' . $errline . ': ' . $msg_text . PHP_EOL;
   $handled = false;

   switch ($errno)
   {
	   case E_NOTICE:
	   case E_STRICT:
	   case E_DEPRECATED:
	   case E_USER_NOTICE:
	   case E_USER_DEPRECATED:
		   $handled = true;
		   Bot::core('ui')->php("notice: $error");
		   file_put_contents(FAILNET . 'data/logs/Error_' . date('m-d-Y', time()) . '.log', date('D m/d/Y - h:i:s A') . ' - [PHP Notice] ' . $error, FILE_APPEND | LOCK_EX);
	   break;

	   case E_WARNING:
	   case E_USER_WARNING:
		   $handled = true;
		   Bot::core('ui')->php("warning: $error");
		   file_put_contents(FAILNET . 'data/logs/Error_' . date('m-d-Y', time()) . '.log', date('D m/d/Y - h:i:s A') . ' - [PHP Warning] ' . $error, FILE_APPEND | LOCK_EX);
	   break;

	   case E_ERROR:
	   case E_USER_ERROR:
		   $handled = true;
		   Bot::core('ui')->php("error: $error");
		   file_put_contents(FAILNET . 'data/logs/Error_' . date('m-d-Y', time()) . '.log', date('D m/d/Y - h:i:s A') . ' - [PHP Error] ' . $error, FILE_APPEND | LOCK_EX);
	   break;
   }

   // Fatal error? DAI.
   if($errno === E_USER_ERROR)
	   Bot::core()->terminate(false); // @todo recode

   // If we notice an error not handled here we pass this back to PHP by returning false
   // This may not work for all php versions
   return ($handled) ? true : false;
}

/**
 * Retrieves the context code from where an error/exception was thrown (as long as file/line are provided) and outputs it.
 * @param string $file - The file where the error/exception occurred.
 * @param string $line - The line where the error/exception occurred.
 * @param integer $context - How many lines of context (above AND below) the troublemaker should we grab?
 * @return string - String containing the perpetrator + context lines for where the error/exception was thrown.
 */
function getErrorContext($file, $line, $context = 3)
{
	$return = array();
	foreach (file($file) as $i => $str)
	{
		if (($i + 1) > ($line - $context))
		{
			if(($i + 1) > ($line + $context))
				break;
			$return[] = $str;
		}
	}

	return $return;
}

/**
 * Return formatted string for filesizes
 * @param integer $bytes - The number of bytes to convert.
 * @return string - The filesize converted into KiB, MiB, or GiB.
 */
function formatFilesize($bytes)
{
	$types = array('TiB', 'GiB', 'MiB', 'KiB');
	$t = (int) sizeof($types);
	for($i = $t; $i > 0; $i--)
	{
		if($bytes >= pow(2, ($i * 10)))
		{
			for($j = $i; $j >= 1; $j--)
				$bytes = $bytes / 1024;
			return round($bytes, 2) . ' ' . $types[$t - $i];
		}
	}
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

	$intervals = array(
		array('year', 29030400),
		array('month', 2419200),
		array('week', 604800),
		array('day', 86400),
		array('hour', 3600),
		array('minute', 60)
	);
	foreach($intervals as $interval)
	{
		$count = floor($time / $interval[1]);
		if($count > 0)
		{
			$return[] = "{$count} {$interval[0]}" . (($count == 1) ? 's' : '');
			$time %= $interval[1];
		}
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
 * @todo update for new framework
 */
function checkuser($user)
{
   if(preg_match('#' . preg_quote(Bot::core()->config('owner'), '#') . '|' . preg_quote(Bot::core()->config('nick'), '#') . '|self#i', $user))
	   return true;
   return false;
}

/**
 * Generate a random string
 * @param integer $length - Length of the random string to generate
 * @return string - The random string.
 */
function unique_string($length = 32)
{
	static $range;
	$rand_string = '';

	if(!$range)
		$range = array_merge(range(0, 9), range('a', 'z'));

	if($length < 1)
		return $rand_string;

	mt_srand((double) microtime() * (9001 + (date('j') * 1000))); // <insert obligatory over-9000 joke here>
	for($i = 1; $i = $length; $i++)
		$rand_string .= $range[mt_rand(0, 35) - 1];

	return $rand_string;
}

/**
 * Converts a delimited string of hostmasks into a regular expression that will match any hostmask in the original string.
 * @param array $list - Array of hostmasks
 * @return string - Regular expression
 *
 * @author Phergie Development Team {@link http://code.assembla.com/phergie/subversion/nodes}
 */
function hostmasksToRegex($list)
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
function parseHostmask($hostmask, &$nick, &$user, &$host)
{
	if (preg_match('/^([^!@]+)!([^@]+)@(.*)$/', $hostmask, $match) > 0)
	{
		list(, $nick, $user, $host) = array_pad($match, 4, NULL);
	}
	else
	{
		$nick = $user = $host = NULL;
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
	$return = array_filter($input, $compare);
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
	foreach($cake as $line)
		Bot::core('ui')->output($line, 'CAKE');
}