<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     install
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

namespace Failnet\Install;
use Failnet\Lib as Lib;

/**
 * Failnet - Config file generator class,
 *      Generates the config files for Failnet's installer.
 *
 *
 * @category    Yukari
 * @package     install
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Generator extends Base
{
	public function buildHeader()
	{
		return implode(PHP_EOL, array(
			'<' . '?php',
			'/' . '**',
			' *',
			' *===================================================================',
			' *',
			' *  Yukari',
			' *-------------------------------------------------------------------',
			' * @version     3.0.x',
			' * @category    Yukari',
			' * @package     config',
			' * @author      Damian Bushong',
			' * @copyright   (c) 2009 - 2011 -- Damian Bushong',
			' * @license     MIT License',
			' * @link        https://github.com/damianb/yukari',
			' *',
			' *===================================================================',
			' *',
			' * This source file is subject to the MIT license that is bundled',
			' * with this package in the file LICENSE.',
			' *',
			' *' . '/',
			'',
			'/' . '**',
			' *',
			' * This configuration file was automatically generated',
			' * by Failnet.',
			' *',
			' * Modification of this file by hand is highly discouraged.',
			' *',
			' *' . '/',
			'',
		));
	}

	public function buildOptions(array $options)
	{
		foreach($options as $option)
		{
			if($option['value'])
			{
				$type = gettype($option['default']);
				settype($option['value'], $type);
			}
			else
			{
				$option['value'] = (isset($option['previous'])) ? $option['previous'] : $option['default'];
			}
			$return[] = "\t'{$option['key']}' => " . var_export($option['value']) . ',';
		}
		return implode(PHP_EOL, $return);
	}

	public function buildFooter()
	{
		// meh
	}

	public function makeFile()
	{
		// meh
		$content = '';
		$content .= $this->buildHeader();
		$content .= $this->buildOptions(Bot::core()->loadConfig());
	}
}
