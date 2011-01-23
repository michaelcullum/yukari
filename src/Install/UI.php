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
use Failnet\CLI as CLI;
use Failnet\Lib as Lib;

/**
 * Failnet - User Interface class,
 *      Handles the prompts and the output shiz for Failnet's installer.
 *
 *
 * @category    Yukari
 * @package     install
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class UI extends CLI\UI
{
	/**
	 * @var array - Array of valid strings that can be used as input for boolean true values.
	 */
	protected $bool_yes_vals = array('y' => true, '1' => true, 't' => true, 'yes' => true, 'enable' => true, 'true' => true, 'on' => true);

	/**
	 * @var array - Array of valid strings that can be used as input for boolean false values.
	 */
	protected $bool_no_vals = array('n' => false, '0' => false, 'f' => false, 'no' => false, 'disable' => false, 'false' => false, 'off' => false);

	/**
	 * Builds a prompt for information from STDIN, so we can ask the user for something.
	 * @param string $instruction - The instruction text to provide the user so they know what we're asking.
	 * @param mixed $default - The default value for the question, may be of any type.
	 * @param string $prompt - The prompt text.
	 * @return mixed - The user input directly from STDIN, with the ending PHP_EOL stripped.
	 */
	public function stdinPrompt($instruction, $default, $prompt)
	{
		if($instruction)
			$this->output($instruction);

		if($prompt)
			echo $prompt . ' ';

		$input = rtrim(fgets(STDIN), PHP_EOL);
		return (!$input) ? $default : $input;
	}

	/**
	 * Get a boolean value answer from the user.
	 * @param string $instruction - The instruction text to provide the user so they know what we're asking.
	 * @param boolean $default - The default value for the question.
	 * @param string $prompt - The prompt text.
	 * @return boolean - Desired user input.
	 */
	public function getBool($instruction, $default, $prompt = 'y/n')
	{
		$values = array_merge($this->bool_yes_vals, $this->bool_no_vals);

		// Nag the user for a usable answer
		$validates = false;
		do
		{
			$input = strtolower($this->stdinPrompt($instruction, (boolean) $default, $prompt));
			$validates = isset($values[$input]);
			if(!$validates)
				$this->output('Invalid response', 'ERROR');
		}
		while(!$validates);

		return (boolean) $values[$input];
	}

	/**
	 * Get a multiple-choice value answer from the user.
	 * @param string $instruction - The instruction text to provide the user so they know what we're asking.
	 * @param boolean $default - The default value for the question.
	 * @param array $choices - The choices available.
	 * @return string - Desired user input.
	 */
	public function getMulti($instruction, $default, array $choices)
	{
		$prompt = implode(', ', $choices);

		// Nag the user for a usable answer
		$validates = false;
		do
		{
			$input = strtolower($this->stdinPrompt($instruction, (boolean) $default, $prompt));
			$validates = in_array($input, $choices);
			if(!$validates)
				$this->output('Invalid response', 'ERROR');
		}
		while(!$validates);

		return (boolean) $input;
	}

	/**
	 * Get a string value answer from the user.
	 * @param string $instruction - The instruction text to provide the user so they know what we're asking.
	 * @param string $default - The default value for the question.
	 * @param string $prompt - The prompt text.
	 * @return string - Desired user input.
	 */
	public function getString($instruction, $default, $prompt)
	{
		return (string) $this->stdinPrompt($instruction, (string) $default, $prompt);
	}

	/**
	 * Get a integer value answer from the user.
	 * @param string $instruction - The instruction text to provide the user so they know what we're asking.
	 * @param integer $default - The default value for the question.
	 * @param string $prompt - The prompt text.
	 * @return integer - Desired user input.
	 */
	public function getInt($instruction, $default, $prompt)
	{
		return (int) $this->stdinPrompt($instruction, (int) $default, $prompt);
	}

	/**
	 * Method called on startup that dumps the startup text for Failnet to output
	 * @return void
	 */
	public function startup()
	{
		if($this->level(OUTPUT_NORMAL))
		{
			$this->output('===================================================================', 'STATUS');
			$this->output('', 'STATUS');
			$this->output('  Yukari', 'STATUS');
			$this->output('---------------------------------------------------------------------', 'STATUS');
			$this->output('@version:      ' . FAILNET_VERSION, 'STATUS');
			$this->output('@copyright:    (c) 2009 - 2010 -- Damian Bushong', 'STATUS');
			$this->output('@license:      MIT License', 'STATUS');
			$this->output('', 'STATUS');
			$this->output('===================================================================', 'STATUS');
			$this->output('', 'STATUS');
			$this->output('This program is subject to the MIT license that is bundled', 'STATUS');
			$this->output('with this package in the file LICENSE.', 'STATUS');
			$this->output('', 'STATUS');
			$this->output('---------------------------------------------------------------------', 'STATUS');
			$this->output('The Failnet installer is loading, please wait.', 'STATUS');
		}
	}
}
