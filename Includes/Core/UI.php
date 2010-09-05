<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     core
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
 */

namespace Failnet\Core;
use Failnet as Root;


/**
 * Failnet - Terminal UI class,
 * 	    Used to handle displaying Failnet's output to a terminal/command prompt.
 *
 *
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class UI extends Root\Base
{
	/**
	 * @var integer - Our current output level
	 */
	protected $output_level = 0;

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
	protected $enable_colors = false;

	public function __construct($output_level = 'normal')
	{
		if(Root\Bot::getOption('ui.enable_colors', false) && $this->checkColorSupport())
		{
			$this->fg_colors = array('black' => '30', 'blue' => '34', 'green' => '32', 'cyan' => '36', 'red' => '31', 'purple' => '35', 'brown' => '33', 'yellow' => '33', 'white' => '37');
			$this->bg_colors = array('black' => '40', 'red' => '41', 'green' => '42', 'yellow' => '43', 'blue' => '44', 'magenta' => '45', 'cyan' => '46', 'light_gray' => '47');
			$this->color_profiles = array(
				'STATUS'	=> array('background' => 'black', 'foreground' => 'blue'),
				'CAKE'		=> array('background' => 'black', 'foreground' => 'yellow', 'bold' => true),
				'INFO'		=> array('foreground' => 'cyan', 'bold' => true),
				'WARNING'	=> array('background' => 'yellow', 'foreground' => 'black', 'bold' => true),
				'ERROR'		=> array('background' => 'red', 'foreground' => 'white', 'bold' => true),
			);
			$this->enable_colors = true;
		}

		if(!in_array($output_level, array('silent', 'normal', 'debug', 'debug_full', 'raw', 'spam')))
			throw new UIException(); // @todo exception

		$this->output_level = constant('Failnet\\OUTPUT_' . strtoupper($output_level));
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

		$codes = '';
		$codes .= (isset($profile['foreground']) ? "\033[" . (isset($profile['bold']) ? '1;' : '') . $this->fg_colors[$profile['foreground']] . 'm' : '');
		$codes .= (isset($profile['background']) ? "\033[" . $this->bg_colors[$profile['background']] . 'm' : '');

		return "{$codes}{$string}\033[0m";
	}

	/**
	 * Method that handles output of all data for the UI.
	 * @param string $data - The string to output
	 * @param string $color - The color profile to use for output, if we want to use one.
	 * @return void
	 */
	public function output($data, $color = NULL)
	{
		$data = rtrim($data, PHP_EOL);
		if(is_null($color))
		{
			echo str_pad($data, 80) . PHP_EOL;
		}
		else
		{
			echo $this->addColor(str_pad($data, 80), $color) . PHP_EOL;
		}
	}

	/**
	 * Determine if this message type should be sent with the current output level.
	 * @param const $level - The OUTPUT level constant that we are checking the current output level against.
	 * @return boolean - Whether we should output or not...boolean true if so, boolean false if not.
	 */
	public function level($level)
	{
		if($level === Root\OUTPUT_RAW)
		{
			return ($this->output_level === Root\OUTPUT_RAW);
		}
		else
		{
			return ($this->output_level >= $level && $this->output_level !== Root\OUTPUT_RAW);
		}
	}

	/**
	 * Method called on startup that dumps the startup text for Failnet to output
	 * @return void
	 */
	public function startup()
	{
		if($this->level(Root\OUTPUT_NORMAL))
		{
			$this->output('===================================================================', 'STATUS');
			$this->output('', 'STATUS');
			$this->output('  Failnet -- PHP-based IRC Bot', 'STATUS');
			$this->output('---------------------------------------------------------------------', 'STATUS');
			$this->output('@version:      ' . Root\FAILNET_VERSION, 'STATUS');
			$this->output('@copyright:    (c) 2009 - 2010 -- Damian Bushong', 'STATUS');
			$this->output('@license:      MIT License', 'STATUS');
			$this->output('', 'STATUS');
			$this->output('===================================================================', 'STATUS');
			$this->output('', 'STATUS');
			$this->output('This program is subject to the MIT license that is bundled', 'STATUS');
			$this->output('with this package in the file LICENSE.', 'STATUS');
			$this->output('', 'STATUS');
			$this->output('---------------------------------------------------------------------', 'STATUS');
			$this->output('Failnet is starting up. Go get yourself a coffee.', 'STATUS');
		}
	}

	/**
	 * Method called that dumps Failnet's ready-notice text to output
	 * @return void
	 */
	public function ready()
	{
		if($this->level(Root\OUTPUT_NORMAL))
		{
			$this->output('---------------------------------------------------------------------', 'STATUS');
			$this->output('Failnet loaded and ready!', 'STATUS');
			$this->output('---------------------------------------------------------------------', 'STATUS');
		}
	}

	/**
	 * Method called on shutdown that dumps the shutdown text for Failnet to output
	 * @return void
	 */
	public function shutdown()
	{
		if($this->level(Root\OUTPUT_NORMAL))
		{
			$this->output('---------------------------------------------------------------------', 'STATUS');
			$this->output('Failnet shutting down...', 'STATUS');
			$this->output('---------------------------------------------------------------------', 'STATUS');
		}
	}

	/**
	 * Method called on message being recieved/sent
	 * @return void
	 */
	public function message($data)
	{
		if($this->level(Root\OUTPUT_NORMAL))
		{
			$this->output('[irc] ' . $data);
		}
	}

	/**
	 * Method called when a low-level system event is triggered or occurs in Failnet
	 * @return void
	 */
	public function status($data)
	{
		if($this->level(Root\OUTPUT_NORMAL))
		{
			$this->output('[system] ' . $data);
		}
	}

	/**
	 * Method called when a system event is triggered or occurs in Failnet
	 * @return void
	 */
	public function system($data)
	{
		if($this->level(Root\OUTPUT_DEBUG))
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
		if($this->level(Root\OUTPUT_DEBUG_FULL))
		{
			$this->output('[event] ' . $data);
		}
	}

	/**
	 * Method called on a notice being thrown
	 * @param string $data - The data to display
	 * @return void
	 */
	public function notice($data)
	{
		if($this->level(Root\OUTPUT_DEBUG))
		{
			$this->output('[notice] ' . $data);
		}
	}

	/**
	 * Method called on a warning being issued
	 * @param string $data - The data to display
	 * @return void
	 */
	public function warning($data)
	{
		if($this->level(Root\OUTPUT_DEBUG))
		{
			$this->output('[warning] ' . $data, 'WARNING');
		}
	}

	/**
	 * Method called on an error being encountered
	 * @param string $data - The data to display
	 * @return void
	 */
	public function error($data)
	{
		if($this->level(Root\OUTPUT_DEBUG))
		{
			$this->output('[error] ' . $data, 'ERROR');
		}
	}

	/**
	 * Method that is called when a PHP issue pops up (notice, warning, etc.)
	 * @param string $data - The data to display
	 * @return void
	 */
	public function php($data)
	{
		if($this->level(Root\OUTPUT_DEBUG))
		{
			$this->output('[php] ' . $data, 'ERROR');
		}
	}

	/**
	 * Method called on debug information being output in Failnet
	 * @return void
	 */
	public function debug($data)
	{
		if($this->level(Root\OUTPUT_DEBUG_FULL))
		{
			$this->output('[debug] ' . $data);
		}
	}

	/**
	 * Method called on raw IRC protocol information being output in Failnet
	 * @return void
	 */
	public function raw($data)
	{
		if($this->level(Root\OUTPUT_RAW))
		{
			$this->output('[SOCKET] ' . $data);
		}
	}
}
