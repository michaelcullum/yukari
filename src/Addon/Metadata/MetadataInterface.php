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
 * Failnet - Addon metadata interface,
 *      Prototype that defines methods that addon metadata objects must implement.
 *
 *
 * @category    Failnet
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
interface MetadataInterface
{
	public function __construct();
	public function getVersion();
	public function getAuthor();
	public function getAddonName();
	public function getDescription();
	public function getTargetVersion();
	public function meetsTargetVersion();
	public function buildInstallPrompt();

	public function initialize();
	public function checkDependencies();
}
