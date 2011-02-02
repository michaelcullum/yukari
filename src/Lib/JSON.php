<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     lib
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2011 -- Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Yukari\Lib;

/**
 * Yukari - JSON Integration class,
 * 	    Used to provide easy JSON integration and error handling
 *
 *
 * @category    Yukari
 * @package     lib
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
abstract class JSON
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
	 * @return array - The contents of the JSON string/file.
	 *
	 * @throws \RuntimeException
	 */
	public static function decode($json)
	{
		if(is_file($json))
			$json = file_get_contents($json);

		$data = json_decode(preg_replace('#\#.*?' . PHP_EOL . '#', '', $json), true);

		if($data === NULL)
		{
			switch(json_last_error())
			{
				case JSON_ERROR_NONE:
					$error = 'No JSON error';
				break;

				case JSON_ERROR_DEPTH:
					$error = 'Maximum JSON recursion limit reached.';
				break;

				case JSON_ERROR_CTRL_CHAR:
					$error = 'JSON Control character error';
				break;

				case JSON_ERROR_SYNTAX:
					$error = 'JSON syntax error';
				break;

				default:
					$error = 'Unknown JSON error';
				break;
			}

			throw new \RuntimeException($error);
		}

		return $data;
	}
}
