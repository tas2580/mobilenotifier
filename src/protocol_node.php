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

class protocol_node
{
	private $tag;
	private $attributeHash;
	private $children;
	private $data;
	private static $cli = null;

	/**
	 * check if call is from command line
	 * @return bool
	 */
	private static function isCli()
	{
		if (self::$cli === null)
		{
			self::$cli = (php_sapi_name() == "cli") ? true : false;
		}
		return self::$cli;
	}

	/**
	 * @return string
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @return string
	 */
	public function getTag()
	{
		return $this->tag;
	}

	/**
	 * @return string[]
	 */
	public function getAttributes()
	{
		return $this->attributeHash;
	}

	/**
	 * @return protocol_node[]
	 */
	public function getChildren()
	{
		return $this->children;
	}

	public function __construct($tag, $attributeHash, $children, $data)
	{
		$this->tag           = $tag;
		$this->attributeHash = $attributeHash;
		$this->children      = $children;
		$this->data          = $data;
	}

	/**
	 * @param string $indent
	 * @param bool   $isChild
	 * @return string
	 */
	public function nodeString($indent = "", $isChild = false)
	{
		//formatters
		$lt = "<";
		$gt = ">";
		$nl = "\n";
		if (!self::isCli())
		{
			$lt = "&lt;";
			$gt = "&gt;";
			$nl = "<br />";
			$indent = str_replace(" ", "&nbsp;", $indent);
		}

		$ret = $indent . $lt . $this->tag;
		if ($this->attributeHash != null)
		{
			foreach ($this->attributeHash as $key => $value)
			{
				$ret .= " " . $key . "=\"" . $value . "\"";
			}
		}
		$ret .= $gt;
		if (strlen($this->data) > 0)
		{
			$ret .= (strlen($this->data) <= 1024) ? $this->data : " " . strlen($this->data) . " byte data";
		}
		if ($this->children)
		{
			$ret .= $nl;
			$foo = array();
			foreach ($this->children as $child)
			{
				$foo[] = $child->nodeString($indent . "  ", true);
			}
			$ret .= implode($nl, $foo) . $nl . $indent;
		}
		$ret .= $lt . "/" . $this->tag . $gt;

		if (!$isChild)
		{
			$ret .= $nl;
			if (!self::isCli())
			{
				$ret .= $nl;
			}
		}

		return $ret;
	}

	/**
	 * @param $attribute
	 * @return string
	 */
	public function getAttribute($attribute)
	{
		return (isset($this->attributeHash[$attribute])) ? $this->attributeHash[$attribute] : '';
	}


	//get children supports string tag or int index
	/**
	 * @param $tag
	 * @return protocol_node
	 */
	public function getChild($tag)
	{
		$ret = null;
		if ($this->children)
		{
			if (is_int($tag))
			{
				return (isset($this->children[$tag])) ? $this->children[$tag] : null;
			}
			foreach ($this->children as $child)
			{
				if (strcmp($child->tag, $tag) == 0)
				{
					return $child;
				}
				$ret = $child->getChild($tag);
				if ($ret)
				{
					return $ret;
				}
			}
		}

		return null;
	}
}
