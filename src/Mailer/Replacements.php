<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     mailer
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

namespace Failnet\Mailer;
use Failnet\Bot as Bot;

/**
 * Failnet - Swiftmailer Decorator Replacements override,
 * 	    Provides on-the-fly decorator replacement access for Swiftmailer's Decorator plugin.
 *
 * @category    Yukari
 * @package     mailer
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Replacements implements Swift_Plugins_Decorator_Replacements
{
	/**
	 * @var Failnet\Mailer\Profile\ProfileBase - The replacement profile to use.
	 */
	protected $profile;

	/**
	 * @var array - The replacements to use.
	 */
	protected $replacements = array();

	/**
	 * Required method, used by the Swiftmailer Decorator Plugin to grab the necessary replacements for a certain email address.
	 * @param string $address - The address to pull up the replacements for.
	 * @return array - The replacements to use for that address.
	 */
	public function getReplacementsFor($address)
	{
		/**
		 * We use Failnet\Mailer\Profile\ProfileBase->getReplacementPayload() here so that any replacements not fulfilled are replaced
		 * by their default values, or an exception is thrown accordingly (depending on if the replacement is marked as required).
		 */
		return $this->profile->getReplacementPayload($this->replacements[hash('md5', $address)]);
	}

	/**
	 * Get the replacement profile currently being used.
	 * @return mixed - NULL if no profile has been registered, or an object extending Failnet\Mailer\Profile\ProfileBase if a profile has been registered.
	 */
	public function getProfile()
	{
		return $this->profile;
	}

	/**
	 * Set the replacement profile that we will use.
	 * @param Failnet\Mailer\Profile\ProfileBase $profile - The profile to use.
	 * @return void
	 */
	public function setProfile(Failnet\Mailer\Profile\ProfileBase $profile)
	{
		$this->profile = $profile;
	}

	/**
	 * Set the replacements to use for the email address specified.
	 * @param string $address - The email address to associate the replacements with.
	 * @param array $replacements - The replacements to use.
	 * @return void
	 */
	public function setReplacements($address, array $replacements)
	{
		if(filter_var($address, FILTER_VALIDATE_EMAIL) === false)
			throw new ReplacementsException(sprintf('Failed to set replacements, invalid email address "%1$s" specified', $address), ReplacementsException::ERR_INVALID_EMAIL);

		$this->replacements[hash('md5', $address)] = $replacements;
	}

	/**
	 * Reset the replacement profile and clear out all replacement data, to prepare for a different replacement profile's use
	 * @return void
	 */
	public function resetProfile()
	{
		$this->profile = NULL;
		$this->replacements = array();
	}
}
