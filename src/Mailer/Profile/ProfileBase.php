<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     mailer
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

namespace Failnet\Mailer\Profile;
use Failnet\Bot as Bot;

/**
 * Failnet - Replacement engine profile base,
 * 	    Defines common methods and properties for replacement engine profiles to use.
 *
 * @category    Failnet
 * @package     mailer
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
abstract class ProfileBase implements ProfileInterface
{
	/**
	 * @var array - Array of replacements that the profile covers.
	 */
	protected $replacements = array();

	/**
	 * Get the full replacement payload that we'll provide to Swiftmailer's decorator plugin, with support for required replacements.
	 * @param array $replacements - The array of replacements we have provided
	 * @return array - The array of replacements to hand off to Swiftmailer's decorator plugin
	 *
	 * @throws Failnet\Mailer\Profile\ProfileException
	 */
	final public function getReplacementPayload(array $replacements)
	{
		foreach($this->replacements as $token => $replacement_ary)
		{
			// Do we have a replacement not defined?
			if(!isset($replacements[$token]))
			{
				// Enforce required replacements...
				if(isset($replacement_ary['required']) && $replacement_ary['required'] === true)
					throw new ProfileException(); // @todo exception

				// If the default isn't null for this replacement, we'll use it...otherwise we'll leave it alone.
				if(!isset($replacement_ary['default']) || !is_null($replacement_ary['default']))
					$replacements[$token] = $replacement_ary['default'];
			}
		}

		return $replacements;
	}

	/**
	 * Get the name of this replacement engine profile.
	 * @return string - The name of the profile.
	 */
	final public function getProfileName()
	{
		return substr(get_class($this), strrpos(get_class($this), '\\'));
	}

	/**
	 * Get the array of currently registered replacements in this profile.
	 * @return array - The array of registered replacements.
	 */
	final public function getReplacements()
	{
		return $this->replacements;
	}

	/**
	 * Register a new replacement in this profile.
	 * @param string $token - The replacement token to use.
	 * @param boolean $required - Is this replacement required to be specified?
	 * @param mixed $default - The default value to use for the replacement, if $required is not true.
	 * @return void
	 */
	final public function setReplacement($token, $required, $default = NULL)
	{
		// If it's required, just wipe out the default.  We don't need it.
		if($required === true)
			$default = NULL;

		$this->replacements[$token] = array(
			'required'		=> (bool) $required,
			'default'		=> $default,
		);
	}
}
