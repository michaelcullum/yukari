<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     core
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

namespace Yukari\CLI;
use Yukari\Kernel;


/**
 * Yukari - Terminal UI class,
 * 	    Used to handle displaying Yukari's output to a terminal/command prompt.
 *
 *
 * @category    Yukari
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class UI
{
	// Output levels
	const OUTPUT_SILENT = 0;
	const OUTPUT_NORMAL = 1;
	const OUTPUT_DEBUG = 2;
	const OUTPUT_DEBUG_FULL = 3;
	const OUTPUT_RAW = 4;
	const OUTPUT_SPAM = 4; // ;D

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

	/**
	 * Constructor
	 * @param string $output_level - The output level to use
	 * @return void
	 *
	 * @throws Failnet\CLI\UIException
	 */
	public function __construct($output_level = 'normal')
	{
		if(Kernel::getConfig('ui.enable_colors') && $this->checkColorSupport())
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

		// Make sure the output level is valid
		if(!in_array($output_level, array('silent', 'normal', 'debug', 'debug_full', 'raw', 'spam')))
			throw new \InvalidArgumentException(sprintf('Invalid UI output level "%1$s" specified', $output_level));

		$this->output_level = constant('Yukari\\CLI\\UI::OUTPUT_' . strtoupper($output_level));
	}

	/**
	 * Register our listeners in the event dispatcher.
	 * @return \Yukari\CLI\UI - Provides a fluent interface.
	 */
	public function registerListeners()
	{
		$dispatcher = Kernel::getDispatcher();
		$dispatcher->register('ui.startup', array(Kernel::get('core.ui'), 'displayStartup'))
			->register('ui.ready', array(Kernel::get('core.ui'), 'displayReady'))
			->register('ui.shutdown', array(Kernel::get('core.ui'), 'displayShutdown'))
			->register('ui.message.irc', array(Kernel::get('core.ui'), 'displayIRC'))
			->register('ui.message.status', array(Kernel::get('core.ui'), 'displayStatus'))
			->register('ui.message.system', array(Kernel::get('core.ui'), 'displaySystem'))
			->register('ui.message.event', array(Kernel::get('core.ui'), 'displayEvent'))
			->register('ui.message.notice', array(Kernel::get('core.ui'), 'displayNotice'))
			->register('ui.message.warning', array(Kernel::get('core.ui'), 'displayWarning'))
			->register('ui.message.error', array(Kernel::get('core.ui'), 'displayError'))
			->register('ui.message.php', array(Kernel::get('core.ui'), 'displayPHP'))
			->register('ui.message.debug', array(Kernel::get('core.ui'), 'displayDebug'))
			->register('ui.message.raw', array(Kernel::get('core.ui'), 'displayRaw'));

		return $this;
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
		if($level === \Yukari\CLI\UI::OUTPUT_RAW)
		{
			return ($this->output_level === \Yukari\CLI\UI::OUTPUT_RAW);
		}
		else
		{
			return ($this->output_level >= $level && $this->output_level !== \Yukari\CLI\UI::OUTPUT_RAW);
		}
	}

	/**
	 * Method called on startup that dumps the startup text for Yukari to output
	 * @param \Yukari\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayStartup(\Yukari\Event\Instance $event)
	{
		if($event->getName() !== 'ui.startup')
			return;

		if($this->level(\Yukari\CLI\UI::OUTPUT_NORMAL))
		{
			$this->output('===================================================================', 'STATUS');
			$this->output('', 'STATUS');
			$this->output('  Yukari IRC Bot', 'STATUS');
			$this->output('---------------------------------------------------------------------', 'STATUS');
			$this->output('@build:        ' . Kernel::getBuildNumber(), 'STATUS');
			$this->output('@copyright:    (c) 2009 - 2011 -- Damian Bushong', 'STATUS');
			$this->output('@license:      MIT License', 'STATUS');
			$this->output('', 'STATUS');
			$this->output('===================================================================', 'STATUS');
			$this->output('', 'STATUS');
			$this->output('This program is subject to the MIT license that is bundled', 'STATUS');
			$this->output('with this package in the file LICENSE.', 'STATUS');
			$this->output('', 'STATUS');
			$this->output('---------------------------------------------------------------------', 'STATUS');
			$this->output('Yukari is starting up. Go get yourself a coffee.', 'STATUS');
		}
	}

	/**
	 * Method called that dumps Yukari's ready-notice text to output
	 * @param \Yukari\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayReady(\Yukari\Event\Instance $event)
	{
		if($event->getName() !== 'ui.ready')
			return;

		if($this->level(\Yukari\CLI\UI::OUTPUT_NORMAL))
		{
			$this->output('---------------------------------------------------------------------', 'STATUS');
			$this->output('Yukari loaded and ready!', 'STATUS');
			$this->output('---------------------------------------------------------------------', 'STATUS');
		}
	}

	/**
	 * Method called on shutdown that dumps the shutdown text for Yukari to output
	 * @param \Yukari\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayShutdown(\Yukari\Event\Instance $event)
	{
		if($event->getName() !== 'ui.shutdown')
			return;

		if($this->level(\Yukari\CLI\UI::OUTPUT_NORMAL))
		{
			$this->output('---------------------------------------------------------------------', 'STATUS');
			$this->output('Yukari shutting down...', 'STATUS');
			$this->output('---------------------------------------------------------------------', 'STATUS');
		}
	}

	/**
	 * Method called on message being received/sent
	 * @param \Yukari\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayIRC(\Yukari\Event\Instance $event)
	{
		if($event->getName() !== 'ui.message.irc')
			return;

		if($this->level(\Yukari\CLI\UI::OUTPUT_NORMAL))
		{
			$this->output('[irc] ' . $event->getDataPoint('message'));
		}
	}

	/**
	 * Method called when a low-level system event is triggered or occurs in Yukari
	 * @param \Yukari\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayStatus(\Yukari\Event\Instance $event)
	{
		if($event->getName() !== 'ui.message.status')
			return;

		if($this->level(\Yukari\CLI\UI::OUTPUT_NORMAL))
		{
			$this->output('[system] ' . $event->getDataPoint('message'));
		}
	}

	/**
	 * Method called when a system event is triggered or occurs in Yukari
	 * @param \Yukari\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displaySystem(\Yukari\Event\Instance $event)
	{
		if($event->getName() !== 'ui.message.system')
			return;

		if($this->level(\Yukari\CLI\UI::OUTPUT_DEBUG))
		{
			$this->output('[system] ' . $event->getDataPoint('message'));
		}
	}

	/**
	 * Method called when a system event is triggered or occurs in Yukari
	 * @param \Yukari\Event\Instance $event - The event that is triggering the output.
	 * @param string $data - The data to display
	 * @return void
	 *
	 * @note Intended for debugging use only.
	 */
	public function displayEvent(\Yukari\Event\Instance $event)
	{
		if($event->getName() !== 'ui.message.event')
			return;

		if($this->level(\Yukari\CLI\UI::OUTPUT_DEBUG_FULL))
		{
			$this->output('[event] ' . $event->getDataPoint('message'));
		}
	}

	/**
	 * Method called on a notice being thrown
	 * @param \Yukari\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayNotice(\Yukari\Event\Instance $event)
	{
		if($event->getName() !== 'ui.message.notice')
			return;

		if($this->level(\Yukari\CLI\UI::OUTPUT_DEBUG))
		{
			$this->output('[notice] ' . $event->getDataPoint('message'));
		}
	}

	/**
	 * Method called on a warning being issued
	 * @param \Yukari\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayWarning(\Yukari\Event\Instance $event)
	{
		if($event->getName() !== 'ui.message.warning')
			return;

		if($this->level(\Yukari\CLI\UI::OUTPUT_DEBUG))
		{
			$this->output('[warning] ' . $event->getDataPoint('message'), 'WARNING');
		}
	}

	/**
	 * Method called on an error being encountered
	 * @param \Yukari\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayError(\Yukari\Event\Instance $event)
	{
		if($event->getName() !== 'ui.message.error')
			return;

		if($this->level(\Yukari\CLI\UI::OUTPUT_DEBUG))
		{
			$this->output('[error] ' . $event->getDataPoint('message'), 'ERROR');
		}
	}

	/**
	 * Method that is called when a PHP issue pops up (notice, warning, etc.)
	 * @param \Yukari\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayPHP(\Yukari\Event\Instance $event)
	{
		if($event->getName() !== 'ui.message.php')
			return;

		if($this->level(\Yukari\CLI\UI::OUTPUT_DEBUG))
		{
			$this->output('[php] ' . $event->getDataPoint('message'), 'ERROR');
		}
	}

	/**
	 * Method called on debug information being output in Yukari
	 * @param \Yukari\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 *
	 * @note Intended for debugging use only.
	 */
	public function displayDebug(\Yukari\Event\Instance $event)
	{
		if($event->getName() !== 'ui.message.debug')
			return;

		if($this->level(\Yukari\CLI\UI::OUTPUT_DEBUG_FULL))
		{
			$this->output('[debug] ' . $event->getDataPoint('message'));
		}
	}

	/**
	 * Method called on raw IRC protocol information being output in Yukari
	 * @param \Yukari\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 *
	 * @note Intended for debugging use only.
	 */
	public function displayRaw(\Yukari\Event\Instance $event)
	{
		if($event->getName() !== 'ui.message.raw')
			return;

		if($this->level(\Yukari\CLI\UI::OUTPUT_RAW))
		{
			$this->output('[SOCKET] ' . $event->getDataPoint('message'));
		}
	}
}
