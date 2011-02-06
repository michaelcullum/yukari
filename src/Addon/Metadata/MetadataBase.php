<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     addon
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

namespace Yukari\Addon\Metadata;
use Yukari\Kernel;

/**
 * Yukari - Addon metadata base class,
 *      Defines common methods and properties for addon metadata objects to use.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
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
