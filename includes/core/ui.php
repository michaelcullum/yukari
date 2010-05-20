<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version		3.0.0 DEV
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @copyright	(c) 2009 - 2010 -- Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
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
 *
 */

namespace Failnet\Core;
use Failnet;


/**
 * Failnet - Terminal UI class,
 * 		Used to handle displaying Failnet's output to a terminal/command prompt.
 *
 *
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class UI extends Common
{
	/**
	 * @var integer - Our current output level
	 */
	public $output_level = 0;

	/**
	 * @var array - Various color codes for use with terminals that support it.
	 */
	protected $fg_colors = array();

	/**
	 * @var array - Various color codes for use with terminals that support it.
	 */
	protected $bg_colors = array();

	/**
	 * @var array - Array of preset color profiles for use with the UI.
	 */
	protected $color_profiles = array();

	/**
	 * @var boolean - Do we want to enable the use of colors in our output?
	 */
	public $enable_colors = false;

	public function __construct()
	{
		if(Bot::core()->config('enable_colors') && $this->checkColorSupport())
		{
			$this->fg_colors = array('black' => '0;30', 'blue' => '0;34', 'green' => '0;32', 'cyan' => '0;36', 'red' => '0;31', 'purple' => '0;35', 'brown' => '0;33', 'yellow' => '1;33', 'white' => '1;37');
			$this->bg_colors = array('black' => '40', 'red' => '41', 'green' => '42', 'yellow' => '43', 'blue' => '44', 'magenta' => '45', 'cyan' => '46', 'light_gray' => '47');
			$this->color_profiles = array(
				'WARNING'	=> array('background' => 'yellow', 'foreground' => 'black', 'bold' => true),
				'ERROR'		=> array('background' => 'red', 'foreground' => 'white', 'bold' => true),
			);
			$this->enable_colors = true;
		}

	}

	/**
	 * Check if ANSI colors can be used.
	 * @return boolean - Does our environment support use of colors in output?
	 */
	protected function checkColorSupport()
	{
		return ((stristr(PHP_OS, 'WIN')) ? @getenv('ANSICON') !== false : function_exists('posix_isatty') && @posix_isatty(STDOUT));
	}

	/**
	 * Colorizes the given text
	 * @param string $string - The string to colorizate.
	 * @param string $profile - Name of the color profile to use on the given string.
	 * @return string - The colorizered string.
	 * @note "typos" intentional.
	 */
	public function addColor($string, $profile)
	{
		if(!isset($this->color_profiles[strtoupper($profile)]) || empty($this->color_profiles[strtoupper($profile)]))
			return $string;

		$profile = $this->color_profiles[strtoupper($profile)];

		$codes = (isset($profile['foreground']) ? $this->fg_colors[$profile['foreground']] . ';' : '');
		$codes .= (isset($profile['background']) ? $this->bg_colors[$profile['background']] . ';' : '');
		$codes .= (isset($profile['bold']) ? '1;' : '');
		return "\033[{$codes}m{$string}\033[0m";
	}

	/**
	 * Method that handles output of all data for the UI.
	 * @return void
	 */
	public function output($data)
	{
		echo ((strrpos($data, PHP_EOL . PHP_EOL) !== false) ? substr($data, 0, strlen($data) - 1) : $data) . PHP_EOL;
	}

	/**
	 * Determine if this message type should be sent with the current output level.
	 * @param const $level - The OUTPUT level constant that we are checking the current output level against.
	 * @return boolean - Whether we should output or not...boolean true if so, boolean false if not.
	 */
	public function level($level)
	{
		return (($level != OUTPUT_RAW) ? ($this->output_level >= $level && $this->output_level !== OUTPUT_RAW) : ($this->output_level === OUTPUT_RAW));
	}

	/**
	 * Method called on init that dumps the startup text for Failnet to output
	 * @return void
	 */
	public function startup()
	{
		if($this->level(OUTPUT_NORMAL))
		{
			$this->output('---------------------------------------------------------------------');
			$this->output('Failnet -- PHP-based IRC Bot version ' . FAILNET_VERSION);
			$this->output('Copyright: (c) 2009 - 2010 -- Obsidian');
			$this->output('License: GNU General Public License - Version 2');
			$this->output('---------------------------------------------------------------------');
			$this->output('Failnet is starting up. Go get yourself a coffee.');
		}
	}

	/**
	 * Method called that dumps Failnet's ready-notice text to output
	 * @return void
	 */
	public function ready()
	{
		if($this->level(OUTPUT_NORMAL))
		{
			$this->output('---------------------------------------------------------------------');
			$this->output('Failnet loaded and ready!');
			$this->output('---------------------------------------------------------------------');
		}
	}

	/**
	 * Method called on shutdown that dumps the shutdown text for Failnet to output
	 * @return void
	 */
	public function shutdown()
	{
		if($this->ui_level(OUTPUT_NORMAL))
		{
			$this->output('---------------------------------------------------------------------');
			$this->output('Failnet shutting down...');
			$this->output('---------------------------------------------------------------------');
		}
	}

	/**
	 * Method called on message being recieved/sent
	 * @return void
	 */
	public function message($data)
	{
		if($this->level(OUTPUT_NORMAL))
		{
			$this->output('[irc] ' . $data);
		}
	}

	/**
	 * Method called when a system event is triggered or occurs in Failnet
	 * @return void
	 */
	public function system($data)
	{
		if($this->level(OUTPUT_DEBUG))
		{
			$this->output('[system] ' . $data);
		}
	}

	/**
	 * Method called when a system event is triggered or occurs in Failnet
	 * @param string $data - The data to display
	 * @return void
	 */
	public function event($data)
	{
		if($this->level(OUTPUT_DEBUG_FULL))
		{
			$this->output('[event] ' . $data);
		}
	}

	/**
	 * Method being called on a PHP notice being thrown
	 * @param string $data - The data to display
	 * @return void
	 */
	public function notice($data)
	{
		if($this->level(OUTPUT_DEBUG))
		{
			$this->output('[php notice] ' . $data);
		}
	}

	/**
	 * Method being called on a PHP warning being thrown
	 * @param string $data - The data to display
	 * @return void
	 */
	public function warning($data)
	{
		if($this->level(OUTPUT_DEBUG))
		{
			$this->output('[php warning] ' . $data);
		}
	}

	/**
	 * Method being called on a PHP error being thrown
	 * @return void
	 */
	public function error($data)
	{
		if($this->level(OUTPUT_DEBUG))
		{
			$this->output('[php error] ' . $data);
		}
	}

	/**
	 * Method being called on debug information being output in Failnet
	 * @return void
	 */
	public function debug($data)
	{
		if($this->level(OUTPUT_DEBUG_FULL))
		{
			$this->output('[debug] ' . $data);
		}
	}

	/**
	 * Method being called on raw IRC protocol information being output in Failnet
	 * @return void
	 */
	public function raw($data)
	{
		if($this->level(OUTPUT_RAW))
		{
			$this->output('[SOCKET] ' . $data);
		}
	}
}
