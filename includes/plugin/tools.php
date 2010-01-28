<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 1
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
 * Failnet - Tools plugin,
 * 		This allows users to use some tools provided by PHP via Failnet. 
 * 
 *
 * @package plugins
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_plugin_tools extends failnet_plugin_common
{

	public function cmd_privmsg()
	{
		// Process the command
		$text = $this->event->get_arg('text');
		if(!$this->prefix($text))
			return;

		$cmd = $this->purify($text);
		$sender = $this->event->hostmask->nick;
		$hostmask = $this->event->hostmask;
		switch ($cmd)
		{
			// Base64 encoding
			case '+b64':
			case '+64':
			case '+base64':
			case 'base64encode':
				$this->call_privmsg($this->event->source(), 'Result: ' . base64_encode($text));
			break;

			case '-b64':
			case '-64':
			case '-base64':
			case 'base64decode':
				$this->call_privmsg($this->event->source(), 'Result: ' . base64_decode($text));
			break;

			// URL encoding
			case '+url':
			case 'urlencode':
				$this->call_privmsg($this->event->source(), 'Result: ' . rawurlencode($text));
			break;

			case '-url':
			case 'urldecode':
				$this->call_privmsg($this->event->source(), 'Result: ' . rawurldecode($text));
			break;

			// HTML entity encoding
			case '+html':
			case 'htmlencode':
				$this->call_privmsg($this->event->source(), 'Result: ' . htmlentities($text));
			break;

			case '-html':
			case 'htmldecode':
				$this->call_privmsg($this->event->source(), 'Result: ' . html_entity_decode($text));
			break;

			// rot13 encoding
			case 'rot13':
				$this->call_privmsg($this->event->source(), 'Result: ' . str_rot13($text));
			break;

			// md5 checksum
			case 'md5':
				$this->call_privmsg($this->event->source(), 'Result: ' . md5($text));
			break;

			// Character counting
			case 'count':
				$this->call_privmsg($this->event->source(), 'Character count: ' . strlen($text));
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

				$this->call_privmsg($this->event->source(), $results);
			break;

			// Temperature conversion
			case 'f2c':
				$this->call_privmsg($this->event->source(), 'Result: ' . round((5/9) * ((int) $text - 32), 1));
			break;

			case 'c2f':
				$this->call_privmsg($this->event->source(), 'Result: ' . round((9/5) * (int) $text + 32, 1));
			break;
		}
	}
}

