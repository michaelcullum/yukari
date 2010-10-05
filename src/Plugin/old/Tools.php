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
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */


/**
 * Failnet - Tools plugin,
 * 		This allows users to use some tools provided by PHP via Failnet.
 *
 *
 * @package plugins
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Damian Bushong
 * @license MIT License
 */
class failnet_plugin_tools extends failnet_plugin_common
{
	public function help(&$name, &$commands)
	{
		$name = 'tools';
		$commands = array(
			'+b64'			=> '+b64 {$text} - (no auth) - Returns the specified text encoded in base64',
			'-b64'			=> '-b64 {$text} - (no auth) - Returns the specified text decoded from base64',
			'+url'			=> '+url {$text} - (no auth) - Returns the specified text URL encoded',
			'-url'			=> '-url {$text} - (no auth) - Returns the specified text URL decoded',
			'+html'			=> '+html {$text} - (no auth) - Returns the specified text with HTML entities properly encoded',
			'-html'			=> '-html {$text} - (no auth) - Returns the specified text with HTML entities decoded',
			'rot13'			=> 'rot13 {$text} - (no auth) - Returns the specified text encoded with ROT13',
			'md5'			=> 'md5 {$text} - (no auth) - Returns the MD5 checksum for the specified text',
			'count'			=> 'count {$text} - (no auth) - Returns the number of characters in the specified text',
			'bytes'			=> 'bytes {$bytes} - (no auth) - Converts the specified number of bytes (or KB, MB, GB, TB) into the lowest common denominator; be sure to specify the size of the measurement in B/KB/MB/GB/TB, otherwise the conversion will fail.',
			'f2c'			=> 'f2c {$temperature} - (no auth) - Returns the temperature in Celsius based on the Fahrenheit temperature specified',
			'c2f'			=> 'c2f {$temperature} - (no auth) - Returns the temperature in Fahrenheit based on the Celsius temperature specified',
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
			// Base64 encoding
			case '+b64':
			case '+64':
			case '+base64':
			case 'base64encode':
				$this->msg('Result: ' . base64_encode($text));
			break;

			case '-b64':
			case '-64':
			case '-base64':
			case 'base64decode':
				$this->msg('Result: ' . base64_decode($text));
			break;

			// URL encoding
			case '+url':
			case 'urlencode':
				$this->msg('Result: ' . rawurlencode($text));
			break;

			case '-url':
			case 'urldecode':
				$this->msg('Result: ' . rawurldecode($text));
			break;

			// HTML entity encoding
			case '+html':
			case 'htmlencode':
				$this->msg('Result: ' . htmlentities($text));
			break;

			case '-html':
			case 'htmldecode':
				$this->msg('Result: ' . html_entity_decode($text));
			break;

			// rot13 encoding
			case 'rot13':
				$this->msg('Result: ' . str_rot13($text));
			break;

			// md5 checksum
			case 'md5':
				$this->msg('Result: ' . md5($text));
			break;

			// Character counting
			case 'count':
				$this->msg('Character count: ' . strlen($text));
			break;

			// Byte multiple conversion
			case 'bytes':
				if(strtoupper(substr($text, -1, 1)) == 'B')
				{
					$end = strtoupper(substr($text, -2, 1));
					if(is_numeric($end))
					{
						$bytes = (int) substr($text, 0, strlen($text) - 1);
						$results = 'Result: ' . get_formatted_filesize($bytes);
					}
					else
					{
						$bytes = (int) substr($text, 0, strlen($text) - 2);
						switch($end)
						{
							case 'K':
								$bytes = $bytes * 1024;
								$results = 'Result: ' . get_formatted_filesize($bytes);
							break;

							case 'M':
								$bytes = $bytes * pow(1024, 2);
								$results = 'Result: ' . get_formatted_filesize($bytes);
							break;

							case 'G':
								$bytes = $bytes * pow(1024, 3);
								$results = 'Result: ' . get_formatted_filesize($bytes);
							break;

							case 'T':
								$bytes = $bytes * pow(1024, 4);
								$results = 'Result: ' . get_formatted_filesize($bytes);
							break;

							default:
								$results = 'Result: Unknown byte multiple';
							break;
						}
					}
				}
				else
				{
					$results = (!is_numeric($text)) ? 'Result: Invalid data provided' : 'Result: ' . get_formatted_filesize((int) $text);
				}

				$this->msg($results);
			break;

			// Temperature conversion
			case 'f2c':
				$this->msg('Result: ' . round((5/9) * ((int) $text - 32), 1));
			break;

			case 'c2f':
				$this->msg('Result: ' . round((9/5) * (int) $text + 32, 1));
			break;
		}
	}
}
