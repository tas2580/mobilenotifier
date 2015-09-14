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

class KeyStream
{
	private $rc4;
	private $seq;
	private $macKey;

	public function __construct($key, $macKey)
	{
		$this->rc4    = new rc4($key, 768);
		$this->macKey = $macKey;
	}

	public static function GenerateKeys($password, $nonce)
	{
		$array  = array("key", "key", "key", "key");
		$array2 = array(1, 2, 3, 4);
		$nonce .= '0';
		for ($j = 0; $j < count($array); $j++)
		{
			$nonce[(strlen($nonce) - 1)] = chr($array2[$j]);
			$foo = wa_pbkdf2("sha1", $password, $nonce, 2, 20, true);
			$array[$j] = $foo;
		}
		return $array;
	}

	public function DecodeMessage($buffer, $macOffset, $offset, $length)
	{
		$mac = $this->computeMac($buffer, $offset, $length);
		//validate mac
		for ($i = 0; $i < 4; $i++)
		{
			$foo = ord($buffer[$macOffset + $i]);
			$bar = ord($mac[$i]);
			if ($foo !== $bar)
			{
				return false;
			}
		}
		return $this->rc4->cipher($buffer, $offset, $length);
	}

	public function EncodeMessage($buffer, $macOffset, $offset, $length)
	{
		$data = $this->rc4->cipher($buffer, $offset, $length);
		$mac = $this->computeMac($data, $offset, $length);
		return substr($data, 0, $macOffset) . substr($mac, 0, 4) . substr($data, $macOffset + 4);
	}

	private function computeMac($buffer, $offset, $length)
	{
		$hmac = hash_init("sha1", HASH_HMAC, $this->macKey);
		hash_update($hmac, substr($buffer, $offset, $length));
		$array = chr($this->seq >> 24) . chr($this->seq >> 16) . chr($this->seq >> 8) . chr($this->seq);
		hash_update($hmac, $array);
		$this->seq++;
		return hash_final($hmac, true);
	}
}
