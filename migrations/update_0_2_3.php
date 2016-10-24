<?php
/**
*
* @package phpBB Extension - tas2580 Mobile Notifier
* @copyright (c) 2016 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tas2580\mobilenotifier\migrations;

class update_0_2_3 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return array(
			'\tas2580\usermap\migrations\update_0_2_2',
		);
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'users'	=> array(
					'user_allow_whatsapp'	=> array('BOOL', 0),
				),
			),
		);
	}
	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'users'	=> array(
					'user_allow_whatsapp',
				),
			),
		);
	}
}
