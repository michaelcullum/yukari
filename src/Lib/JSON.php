<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     libs
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

namespace Failnet\Lib;
use Failnet as Root;

/**
 * Failnet - JSON Integration class,
 * 	    Used to provide easy JSON integration and error handling
 *
 *
 * @category    Failnet
 * @package     libs
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
abstract class JSON extends Root\Base
{
	/**
	 * Builds a JSON string based on input.
	 * @param mixed $data - The data to cache.
	 * @return string - JSON string.
	 */
	public static function encode($data)
	{
		return json_encode($data);
	}

	/**
	 * Loads a JSON string or file and returns the data held within.
	 * @param string $json - The JSON string or the path of the JSON file to decode.
	 * @param boolean $is_file - Are we loading from a JSON file?
	 * @return array - The contents of the JSON string/file.
	 *
	 * @throws Failnet\Lib\JSONException
	 */
	public static function decode($json, $is_file = true)
	{
		if($is_file)
		{
			if(!file_exists($json))
				throw new JSONException('JSON file does not exist', JSONException::ERR_JSON_NO_FILE);
			$json = file_get_contents($json);
		}

		$data = json_decode(preg_replace('#\#.*?' . PHP_EOL . '#', '', $json), true);

		if($data === NULL)
		{
			switch(json_last_error())
			{
				case JSON_ERROR_NONE:
					$error = 'No error';
					$code = JSONException::ERR_JSON_NO_ERROR;
				break;

				case JSON_ERROR_DEPTH:
					$error = 'Maximum JSON recursion limit reached.';
					$code = JSONException::ERR_JSON_DEPTH;
				break;

				case JSON_ERROR_CTRL_CHAR:
					$error = 'Control character error';
					$code = JSONException::ERR_JSON_CTRL_CHAR;
				break;

				case JSON_ERROR_SYNTAX:
					$error = 'JSON syntax error';
					$code = JSONException::ERR_JSON_SYNTAX;
				break;

				default:
					$error = 'Unknown JSON error';
					$code = JSONException::ERR_JSON_UNKNOWN;
				break;
			}

			throw new JSONException($error, $code);
		}

		return $data;
	}
}

/**
 * Failnet - Subordinate exception class
 *      Extension of the Failnet exception class.
 *
 *
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 *
 * @note reserves 301xx error codes
 */
class JSONException extends Root\FailnetException
{
	const ERR_JSON_NO_FILE = 30100;
	const ERR_JSON_UNKNOWN = 30101;
	const ERR_JSON_NO_ERROR = 30102;
	const ERR_JSON_DEPTH = 30103;
	const ERR_JSON_CTRL_CHAR = 30104;
	const ERR_JSON_SYNTAX = 30105;
}
