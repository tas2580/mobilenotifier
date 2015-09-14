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

class rc4
{
	private $s;
	private $i;
	private $j;

	public function __construct($key, $drop)
	{
		$this->s = range(0, 255);
		for ($i = 0, $j = 0; $i < 256; $i++)
		{
			$k = ord($key{$i % strlen($key)});
			$j = ($j + $k + $this->s[$i]) & 255;
			$this->swap($i, $j);
		}

		$this->i = 0;
		$this->j = 0;
		$this->cipher(range(0, $drop), 0, $drop);
	}

	public function cipher($data, $offset, $length)
	{
		$out = $data;
		for ($n = $length; $n > 0; $n--)
		{
			$this->i = ($this->i + 1) & 0xff;
			$this->j = ($this->j + $this->s[$this->i]) & 0xff;
			$this->swap($this->i, $this->j);
			$d = ord($data{$offset});
			$out[$offset] = chr($d ^ $this->s[($this->s[$this->i] + $this->s[$this->j]) & 0xff]);
			$offset++;
		}

		return $out;
	}

	protected function swap($i, $j)
	{
		$c = $this->s[$i];
		$this->s[$i] = $this->s[$j];
		$this->s[$j] = $c;
	}
}
