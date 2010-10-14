<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     addon
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

namespace Failnet\Addon\Metadata;
use Failnet\Bot as Bot;
use Failnet\Lib as Lib;

/**
 * Failnet - Addon metadata base class,
 *      Defines common methods and properties for addon metadata objects to use.
 *
 *
 * @category    Failnet
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
abstract class MetadataBase implements MetadataInterface
{
	/**
	 * @var string - The addon's version.
	 */
	protected $version = '';

	/**
	 * @var string - The addon's author information.
	 */
	protected $author = '';

	/**
	 * @var string - The addon's name.
	 */
	protected $name = '';

	/**
	 * @var string - The addon's description.
	 */
	protected $description = '';

	/**
	 * @var string - The addon's minimum target Failnet version.
	 */
	protected $target_version = '';

	/**
	 * @ignore - preventing use of __construct on metadata objects
	 */
	final public function __construct() { }

	/**
	 * Get the version stamp of the addon.
	 * @return string - The version stamp of the addon.
	 */
	final public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Get the author for the addon.
	 * @return string - The author data for the addon.
	 */
	final public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * Get the name of the addon.
	 * @return string - The addon's name.
	 */
	final public function getAddonName()
	{
		return $this->name;
	}

	/**
	 * Get this addon's description
	 * @return string - The addon description.
	 */
	final public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Get the minimum target Failnet version for this addon.
	 * @return string - The minimum target Failnet version for this addon.
	 */
	final public function getTargetVersion()
	{
		return $this->target_version;
	}

	/**
	 * Determine if the Failnet version we are running meets the minimum version requirement of this addon.
	 * @return boolean - True if we meet the minimum version, false if we do not.
	 */
	final public function meetsTargetVersion()
	{
		return version_compare(Failnet\FAILNET_VERSION, $this->getTargetVersion(), '>');
	}

	/**
	 * Builds the installation information data, best used with the CLI UI.
	 * @return array - Array of lines containing installation information
	 */
	final public function buildInstallPrompt()
	{
		return array(
			sprintf('Addon name:        %1$s', $this->getAddonName()),
			sprintf('Addon author:      %1$s', $this->getAuthor()),
			sprintf('Addon version:     %1$s (requires Failnet %2$s minimum)', $this->getVersion(), $this->getTargetVersion()),
			sprintf('Addon description: %1$s', $this->getDescription()),
		);
	}

	/**
	 * Hooking method for addon metadata objects, called to initialize the addon after the dependency check has been passed.
	 * @return void
	 */
	public function initialize() { }

	/**
	 * Hooking method for addon metadata objects for executing own code on pre-load dependency check.
	 * @return boolean - Does the addon pass the dependency check?
	 */
	public function checkDependencies() { }
}
