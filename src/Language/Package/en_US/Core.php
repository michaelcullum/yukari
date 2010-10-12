<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     language
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

namespace Failnet\Language\Package\en_US;
use Failnet\Bot as Bot;
use Failnet\Language as Language;
use Failnet\Language\Package as Package;

/**
 * Failnet - Language package object,
 * 	    Contains a collection of language entries for use within Failnet.
 *
 *
 * @category    Failnet
 * @package     language
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
class Core extends Package\PackageBase implements Package\PackageInterface
{
	protected $locale = 'en-US';

	protected $entries = array(
		'LANGUAGE_VAR'	=> 'Some language variable: %1$s',
	);
}
