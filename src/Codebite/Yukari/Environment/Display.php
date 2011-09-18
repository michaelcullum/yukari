<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     environment
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

namespace Codebite\Yukari\Environment;
use \Codebite\Yukari\Kernel;
use \OpenFlame\Framework\Event\Instance as Event;


/**
 * Yukari - Terminal display class,
 * 	    Used to handle displaying Yukari's output to a terminal/command prompt.
 *
 *
 * @category    Yukari
 * @package     environment
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Display
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
	protected $fg_colors = array('black' => '30', 'blue' => '34', 'green' => '32', 'cyan' => '36', 'red' => '31', 'purple' => '35', 'brown' => '33', 'yellow' => '33', 'white' => '37');

	/**
	 * @var array - Various color codes for use with terminals that support it.
	 */
	protected $bg_colors = array('black' => '40', 'red' => '41', 'green' => '42', 'yellow' => '43', 'blue' => '44', 'magenta' => '45', 'cyan' => '46', 'light_gray' => '47');

	/**
	 * @var array - Array of preset color profiles for use with the UI.
	 */
	protected $color_profiles = array(
		'STATUS'	=> array('background' => 'black', 'foreground' => 'blue'),
		'CAKE'		=> array('background' => 'black', 'foreground' => 'yellow', 'bold' => true),
		'INFO'		=> array('foreground' => 'cyan', 'bold' => true),
		'WARNING'	=> array('background' => 'yellow', 'foreground' => 'black', 'bold' => true),
		'ERROR'		=> array('background' => 'red', 'foreground' => 'white', 'bold' => true),
	);

	/**
	 * @var boolean - Do we want to enable the use of colors in our output?
	 */
	protected $enable_colors = false;

	/**
	 * Constructor
	 * @param string $output_level - The output level to use
	 * @return void
	 */
	public function __construct($output_level = 'normal')
	{
		if(Kernel::getConfig('ui.enable_colors') && $this->checkColorSupport())
		{
			$this->enable_colors = true;
		}
	}


	/**
	 * Set the output level
	 * @param string $output_level - The output level.
	 * @return \Codebite\Yukari\Environment\Display - Provides a fluent interface.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setOutputLevel($output_level)
	{
		// Make sure the output level is valid
		if(!in_array($output_level, array('silent', 'normal', 'debug', 'debug_full', 'raw', 'spam')))
		{
			throw new \InvalidArgumentException(sprintf('Invalid UI output level "%1$s" specified', $output_level));
		}

		$this->output_level = constant('Codebite\\Yukari\\CLI\\UI::OUTPUT_' . strtoupper($output_level));

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
		{
			return $string;
		}

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
	public function output($level, $color, $data, $params = NULL)
	{
		if(!$this->level($level))
		{
			return;
		}

		$data = rtrim($data, PHP_EOL);

		if($params !== NULL)
		{
			if(!is_array($params))
			{
				$params = array($params);
			}

			$data = vsprintf($data, $params);
		}
		if($color === NULL)
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
		if($level === self::OUTPUT_RAW)
		{
			return ($this->output_level === self::OUTPUT_RAW);
		}
		else
		{
			return ($this->output_level >= $level && $this->output_level !== self::OUTPUT_RAW);
		}
	}

	/**
	 * Register our listeners in the event dispatcher.
	 * @return \Codebite\Yukari\Environment\Display - Provides a fluent interface.
	 */
	public function registerListeners()
	{
		$dispatcher = Kernel::get('dispatcher');

		// Register our load of UI listeners
		$dispatcher->register('ui.startup', array($this, 'displayStartup'));
		$dispatcher->register('ui.ready', array($this, 'displayReady'));
		$dispatcher->register('ui.shutdown', array($this, 'displayShutdown'));
		$dispatcher->register('ui.message.status', array($this, 'displayStatus'));
		$dispatcher->register('ui.message.system', array($this, 'displaySystem'));
		$dispatcher->register('ui.message.event', array($this, 'displayEvent'));
		$dispatcher->register('ui.message.notice', array($this, 'displayNotice'));
		$dispatcher->register('ui.message.warning', array($this, 'displayWarning'));
		$dispatcher->register('ui.message.error', array($this, 'displayError'));
		$dispatcher->register('ui.message.php', array($this, 'displayPHP'));
		$dispatcher->register('ui.message.debug', array($this, 'displayDebug'));
		$dispatcher->register('ui.message.raw', array($this, 'displayRaw'));

		$dispatcher->register('ui.message.irc', array($this, 'displayIRC'));

		// Display IRC going-ons
		$dispatcher->register('irc.input.action', function(Event $event) use($dispatcher) {
			$dispatcher->trigger(Event::newEvent('ui.message.irc')
				->set('message', sprintf('<- [%2$s] *** %1$s %3$s', $event->get('hostmask')->getNick(), $event->get('target'), $event->get('text'))));
		});
		$dispatcher->register('irc.input.privmsg', function(Event $event) use($dispatcher) {
			$dispatcher->trigger(Event::newEvent('ui.message.irc')
				->set('message', sprintf('<- [%2$s] <%1$s> %3$s', $event->get('hostmask')->getNick(), $event->get('target'), $event->get('text'))));
		});
		$dispatcher->register('irc.input.notice', function(Event $event) use($dispatcher) {
			$dispatcher->trigger(Event::newEvent('ui.message.irc')
				->set('message', sprintf('<- [%2$s] <%1$s NOTICE>  %3$s', $event->get('hostmask')->getNick(), $event->get('target'), $event->get('text'))));
		});

		// Display channel happenings.
		$dispatcher->register('irc.input.join', function(Event $event) use($dispatcher) {
			$dispatcher->trigger(Event::newEvent('ui.message.irc')
				->set('message', sprintf('<- %1$s (%2$s@%3$s) has joined %4$s', $event->get('hostmask')->getNick(), $event->get('hostmask')->getUsername(), $event->get('hostmask')->getHost(), $event->get('channel'))));
		});
		$dispatcher->register('irc.input.part', function(Event $event) use($dispatcher) {
			if($event->get('reason') !== NULL)
			{
				$dispatcher->trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- %1$s (%2$s@%3$s) has left %4$s [Reason: %5$s]', $event->get('hostmask')->getNick(), $event->get('hostmask')->getUsername(), $event->get('hostmask')->getHost(), $event->get('channel'), $event->get('reason'))));
			}
			else
			{
				$dispatcher->trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- %1$s (%2$s@%3$s) has left %4$s', $event->get('hostmask')->getNick(), $event->get('hostmask')->getUsername(), $event->get('hostmask')->getHost(), $event->get('channel'))));
			}
		});
		$dispatcher->register('irc.input.kick', function(Event $event) use($dispatcher) {
			if($event->get('reason') !== NULL)
			{
				$dispatcher->trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- %1$s kicked %2$s %3$s [Reason: %4$s]', $event->get('hostmask')->getNick(), $event->get('user'), $event->get('channel'), $event->get('reason'))));
			}
			else
			{
				$dispatcher->trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- %1$s kicked %2$s from %3$s', $event->get('hostmask')->getNick(), $event->get('user'), $event->get('channel'))));
			}
		});
		$dispatcher->register('irc.input.quit', function(Event $event) use($dispatcher) {
			if(!$event->exists('args') || $event->get('args') === NULL)
			{
				$dispatcher->trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- %1$s (%2$s@%3$s) has quit [Reason: %4$s]', $event->get('hostmask')->getNick(), $event->get('hostmask')->getUsername(), $event->get('hostmask')->getHost(), $event->get('reason'))));
			}
			else
			{
				$dispatcher->trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- %1$s (%2$s@%3$s) has quit', $event->get('hostmask')->getNick(), $event->get('hostmask')->getUsername(), $event->get('hostmask')->getHost())));
			}
		});

		// Display CTCP requests and replies
		$dispatcher->register('irc.input.ctcp', function(Event $event) use($dispatcher) {
			if(!$event->exists('args') || $event->get('args') === NULL)
			{
				$dispatcher->trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- <%1$s> CTCP %2$s - %3$s', $event->get('hostmask')->getNick(), $event->get('command'), $event->get('args'))));
			}
			else
			{
				$dispatcher->trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- <%1$s> CTCP %2$s', $event->get('hostmask')->getNick(), $event->get('command'))));
			}
		});
		$dispatcher->register('irc.input.ctcp_reply', function(Event $event) use($dispatcher) {
			if(!$event->exists('args') || $event->get('args') === NULL)
			{
				$dispatcher->trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- <%1$s> CTCP-REPLY %2$s - %3$s', $event->get('hostmask')->getNick(), $event->get('command'), $event->get('args'))));
			}
			else
			{
				$dispatcher->trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- <%1$s> CTCP-REPLY %2$s', $event->get('hostmask')->getNick(), $event->get('command'))));
			}
		});

		// Display our responses
		$dispatcher->register('runtime.postdispatch', function(Event $event) use($dispatcher) {
			$response = $event->get('event');
			switch($response->getName())
			{
				case 'irc.output.action':
					$dispatcher->trigger(Event::newEvent('ui.message.irc')
						->set('message', sprintf('-> [%1$s] *** %2$s', $response->get('target'), $response->get('text'))));
				break;

				case 'irc.output.ctcp':
					if($response->exists('args') && $response->get('args') !== NULL)
					{
						$dispatcher->trigger(Event::newEvent('ui.message.irc')
							->set('message', sprintf('-> [%1$s] CTCP %2$s - %3$s', $response->get('target'), $response->get('command'), $response->get('args'))));
					}
					else
					{
						$dispatcher->trigger(Event::newEvent('ui.message.irc')
							->set('message', sprintf('-> [%1$s] CTCP %2$s', $response->get('target'), $response->get('command'))));
					}
				break;

				case 'irc.output.ctcp_reply':
					if($response->exists('args') && $response->get('args') !== NULL)
					{
						$dispatcher->trigger(Event::newEvent('ui.message.irc')
							->set('message', sprintf('-> [%1$s] CTCP-REPLY %2$s - %3$s', $response->get('target'), $response->get('command'), $response->get('args'))));
					}
					else
					{
						$dispatcher->trigger(Event::newEvent('ui.message.irc')
							->set('message', sprintf('-> [%1$s] CTCP-REPLY %2$s', $response->get('target'), $response->get('command'))));
					}
				break;

				case 'irc.output.privmsg':
					$dispatcher->trigger(Event::newEvent('ui.message.irc')
						->set('message', sprintf('-> [%1$s] %2$s', $response->get('target'), $response->get('text'))));
				break;

				case 'irc.output.notice':
					$dispatcher->trigger(Event::newEvent('ui.message.irc')
						->set('message', sprintf('-> [%1$s NOTICE] %2$s', $response->get('target'), $response->get('text'))));
				break;

				default:
					return NULL;
				break;
			}
		});

		return $this;
	}

	/**
	 * Method called on startup that dumps the startup text for Yukari to output
	 * @param \OpenFlame\Framework\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayStartup(Event $event)
	{
		$language = Kernel::get('language');

		$this->output(self::OUTPUT_NORMAL, 'STATUS', '===================================================================');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '  Yukari IRC Bot');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '-------------------------------------------------------------------');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '@version:      %s', array(Kernel::getBuildNumber()));
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '@copyright:    (c) 2009 - 2011 Damian Bushong');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '@license:      MIT License');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '===================================================================');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', 'This program is subject to the MIT license that is bundled');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', 'with this package in the file LICENSE.');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '-------------------------------------------------------------------');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '%s', $language->getEntry('DISPLAY_STARTUP'));
	}

	/**
	 * Method called that dumps Yukari's ready-notice text to output
	 * @param \OpenFlame\Framework\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayReady(Event $event)
	{
		$language = Kernel::get('language');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '-------------------------------------------------------------------');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '%s', $language->getEntry('DISPLAY_READY'));
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '-------------------------------------------------------------------');
	}

	/**
	 * Method called on shutdown that dumps the shutdown text for Yukari to output
	 * @param \OpenFlame\Framework\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayShutdown(Event $event)
	{
		$language = Kernel::get('language');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '-------------------------------------------------------------------');
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '%s', $language->getEntry('DISPLAY_SHUTDOWN'));
		$this->output(self::OUTPUT_NORMAL, 'STATUS', '-------------------------------------------------------------------');
	}

	/**
	 * Method called on message being received/sent
	 * @param \OpenFlame\Framework\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayIRC(Event $event)
	{
		$this->output(self::OUTPUT_NORMAL, '', '[irc] %s', $event->get('message'));
	}

	/**
	 * Method called when a low-level system event is triggered or occurs in Yukari
	 * @param \OpenFlame\Framework\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayStatus(Event $event)
	{
		$this->output(self::OUTPUT_NORMAL, '', '[system] %s', $event->get('message'));
	}

	/**
	 * Method called when a system event is triggered or occurs in Yukari
	 * @param \OpenFlame\Framework\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displaySystem(Event $event)
	{
		$this->output(self::OUTPUT_DEBUG, '', '[system] %s', $event->get('message'));
	}

	/**
	 * Method called when a system event is triggered or occurs in Yukari
	 * @param \OpenFlame\Framework\Event\Instance $event - The event that is triggering the output.
	 * @param string $data - The data to display
	 * @return void
	 *
	 * @note Intended for debugging use only.
	 */
	public function displayEvent(Event $event)
	{
		$this->output(self::OUTPUT_DEBUG_FULL, '', '[event] %s', $event->get('message'));
	}

	/**
	 * Method called on a notice being thrown
	 * @param \OpenFlame\Framework\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayNotice(Event $event)
	{
		$this->output(self::OUTPUT_DEBUG, '', '[notice] %s', $event->get('message'));
	}

	/**
	 * Method called on a warning being issued
	 * @param \OpenFlame\Framework\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayWarning(Event $event)
	{
		$this->output(self::OUTPUT_DEBUG, 'WARNING', '[warning] %s', $event->get('message'));
	}

	/**
	 * Method called on an error being encountered
	 * @param \OpenFlame\Framework\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayError(Event $event)
	{
		$this->output(self::OUTPUT_DEBUG, 'ERROR', '[error] %s', $event->get('message'));
	}

	/**
	 * Method that is called when a PHP issue pops up (notice, warning, etc.)
	 * @param \OpenFlame\Framework\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayPHP(Event $event)
	{
		$this->output(self::OUTPUT_DEBUG, 'ERROR', '[php] %s', $event->get('message'));
	}

	/**
	 * Method called on debug information being output in Yukari
	 * @param \OpenFlame\Framework\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 *
	 * @note Intended for debugging use only.
	 */
	public function displayDebug(Event $event)
	{
		$this->output(self::OUTPUT_DEBUG, '', '[debug] %s', $event->get('message'));
	}

	/**
	 * Method called on raw IRC protocol information being output in Yukari
	 * @param \OpenFlame\Framework\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 *
	 * @note Intended for debugging use only.
	 */
	public function displayRaw(Event $event)
	{
		$this->output(self::OUTPUT_RAW, '', '[SOCKET] %s', $event->get('message'));
	}
}
