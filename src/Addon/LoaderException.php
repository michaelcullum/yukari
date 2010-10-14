<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     Failnet
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

namespace Failnet\Addon;
use Failnet\Bot as Bot;

/**
 * Failnet - Subordinate exception class
 *      Extension of the Failnet exception class.
 *
 *
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 *
 * @note reserves 208xx error codes
 */
class LoaderException extends Failnet\FailnetException
{
	const ERR_METADATA_FILE_MISSING = 20800;
	const ERR_METADATA_CLASS_MISSING = 20801;
	const ERR_METADATA_NOT_BASE_CHILD = 20802;
	const ERR_METADATA_NOT_INTERFACE_CHILD = 20803;
	const ERR_METADATA_MINIMUM_TARGET_NOT_MET = 20804;
	const ERR_METADATA_CUSTOM_DEPENDENCY_FAIL = 20805;
}
