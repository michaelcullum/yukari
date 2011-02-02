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

namespace Yukari\Language\Package\en_US;

/**
 * Yukari - Language package object,
 * 	    Contains a collection of language entries for use within Yukari.
 *
 *
 * @category    Yukari
 * @package     language
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Core extends \Yukari\Language\Package\PackageBase implements \Yukari\Language\Package\PackageInterface
{
	protected $locale = 'en-US';

	protected $entries = array(
		'NOT_AUTHED'			=> 'You are not authorized to use that command.',
		'RUNNING_VERSION'		=> 'Yukari IRC Bot v%1$s',
	);
}
