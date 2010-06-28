<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     install
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

namespace Failnet\Install;
use Failnet;
use Failnet\Core as Core;
use Failnet\Lib as Lib;

/**
 * Failnet - User Interface class,
 *      Handles the prompts and the output shiz for Failnet's installer.
 *
 *
 * @category    Failnet
 * @package     install
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class UI extends Core\UI
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
}
