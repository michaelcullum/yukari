<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version		3.0.0 DEV
 * @category	Yukari
 * @package		lib
 * @author		Damian Bushong
 * @copyright	(c) 2009 - 2011 -- Damian Bushong
 * @license		MIT License
 * @link		https://github.com/damianb/yukari
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Yukari\Lib;

/**
 * Yukari - Password hashing framework,
 * 		Used as Yukari's password hashing system.
 *
 * @package lib
 * @version Version 0.1 / slightly modified for Yukari (using $Y$ as hash type identifier, and using hash() + SHA512 instead of MD5)
 *
 * Portable PHP password hashing framework.
 *
 * Written by Solar Designer <solar at openwall.com> in 2004-2006 and placed in
 * the public domain.
 *
 * There's absolutely no warranty.
 *
 * The homepage URL for this framework is:
 *
 *	http://www.openwall.com/phpass/
 *
 * Please be sure to update the Version line if you edit this file in any way.
 * It is suggested that you leave the main version number intact, but indicate
 * your project name (after the slash) and add your own revision information.
 *
 * Please do not change the "private" password hashing method implemented in
 * here, thereby making your hashes incompatible.  However, if you must, please
 * change the hash type identifier (the "$P$") to something different.
 *
 * Obviously, since this code is in the public domain, the above are not
 * requirements (there can be none), but merely suggestions.
 */
class Hash
{
	public $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	public $iteration_count_log2;
	public $random_state;

	public function __construct($iteration_count_log2 = 8)
	{
		if ($iteration_count_log2 < 8 || $iteration_count_log2 > 31)
			$iteration_count_log2 = 8;
		$this->iteration_count_log2 = $iteration_count_log2;

		$this->random_state = microtime() . getmypid();
	}

	public function get_random_bytes($count)
	{
		$output = '';
		if (($fh = @fopen('/dev/urandom', 'rb')))
		{
			$output = fread($fh, $count);
			fclose($fh);
		}

		if (strlen($output) < $count)
		{
			$output = '';
			for ($i = 0; $i < $count; $i += 16)
			{
				$this->random_state = hash('sha256', microtime() . $this->random_state);
				$output .= pack('H*', hash('sha256', $this->random_state));
			}
			$output = substr($output, 0, $count);
		}

		return $output;
	}

	public function encode64($input, $count)
	{
		$output = '';
		$i = 0;
		do
		{
			$value = ord($input[$i++]);
			$output .= $this->itoa64[$value & 0x3f];
			if ($i < $count)
				$value |= ord($input[$i]) << 8;
			$output .= $this->itoa64[($value >> 6) & 0x3f];
			if ($i++ >= $count)
				break;
			if ($i < $count)
				$value |= ord($input[$i]) << 16;
			$output .= $this->itoa64[($value >> 12) & 0x3f];
			if ($i++ >= $count)
				break;
			$output .= $this->itoa64[($value >> 18) & 0x3f];
		}
		while ($i < $count);

		return $output;
	}

	public function gensalt_private($input)
	{
		$output = '$Y$';
		$output .= $this->itoa64[min($this->iteration_count_log2 + 5, 30)];
		$output .= $this->encode64($input, 6);

		return $output;
	}

	public function crypt_private($password, $setting)
	{
		$output = '*0';
		if (substr($setting, 0, 2) == $output)
			$output = '*1';

		if (substr($setting, 0, 3) != '$Y$')
			return $output;

		$count_log2 = strpos($this->itoa64, $setting[3]);
		if ($count_log2 < 7 || $count_log2 > 30)
			return $output;

		$count = 1 << $count_log2;

		$salt = substr($setting, 4, 8);
		if (strlen($salt) != 8)
			return $output;

		$hash = hash('sha512', $salt . $password, TRUE);
		do
		{
			$hash = hash('sha512', $hash . $password, TRUE);
		}
		while (--$count);

		$output = substr($setting, 0, 12);
		$output .= $this->encode64($hash, 64);

		return $output;
	}

	public function hashPassword($password)
	{
		$random = '';

		if (strlen($random) < 6)
			$random = $this->get_random_bytes(6);
		$hash = $this->crypt_private($password, $this->gensalt_private($random));
		if (strlen($hash) == 98)
			return $hash;

		# Returning '*' on error is safe here, but would _not_ be safe
		# in a crypt(3)-like function used _both_ for generating new
		# hashes and for validating passwords against existing hashes.
		return '*';
	}

	public function check($password, $stored_hash)
	{
		$hash = $this->crypt_private($password, $stored_hash);
		if ($hash[0] == '*')
			$hash = crypt($password, $stored_hash);

		return $hash == $stored_hash;
	}
}
