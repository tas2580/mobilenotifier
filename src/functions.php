<?php
/**
*
* @package phpBB Extension - tas2580 Mobile Notifier
* @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
* Class, based on Chat-API (https://github.com/WHAnonymous/Chat-API)
*/

namespace tas2580\mobilenotifier\src;

function wa_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false)
{
	$algorithm = strtolower($algorithm);
	if ( !in_array($algorithm, hash_algos(), true))
	{
		die('PBKDF2 ERROR: Invalid hash algorithm.');
	}
	if ($count <= 0 || $key_length <= 0)
	{
		die('PBKDF2 ERROR: Invalid parameters.');
	}

	$hash_length = strlen(hash($algorithm, "", true));
	$block_count = ceil($key_length / $hash_length);

	$output = "";
	for ($i = 1; $i <= $block_count; $i++)
	{
		$last = $salt . pack("N", $i);
		$last = $xorsum = hash_hmac($algorithm, $last, $password, true);
		for ($j = 1; $j < $count; $j++)
		{
			$xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
		}
		$output .= $xorsum;
	}

	return ($raw_output) ? substr($output, 0, $key_length) : bin2hex(substr($output, 0, $key_length));
}
