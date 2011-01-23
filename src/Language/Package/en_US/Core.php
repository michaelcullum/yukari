<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     language
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

namespace Failnet\Language\Package\en_US;
use Failnet\Bot as Bot;
use Failnet\Language as Language;
use Failnet\Language\Package as Package;

/**
 * Failnet - Language package object,
 * 	    Contains a collection of language entries for use within Failnet.
 *
 *
 * @category    Yukari
 * @package     language
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Core extends Package\PackageBase implements Package\PackageInterface
{
	protected $locale = 'en-US';

	protected $entries = array(
		'NOT_AUTHED'			=> 'You are not authorized to use that command.',
		'RUNNING_VERSION'		=> 'Failnet IRC Bot v%1$s',
	);
}
