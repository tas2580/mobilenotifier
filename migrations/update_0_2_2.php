<?php
/**
*
* @package phpBB Extension - tas2580 Mobile Notifier
* @copyright (c) 2016 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tas2580\mobilenotifier\migrations;

class update_0_2_2 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return array(
			'\tas2580\usermap\migrations\initial_module',
		);
	}

	public function update_data()
	{
		return array(
			// Add ACP module
			array('module.add', array(
				'acp',
				'ACP_MOBILENOTIFIER_TITLE',
				array(
					'module_basename'	=> '\tas2580\mobilenotifier\acp\mobilenotifier_module',
					'modes'				=> array('debug'),
				),
			)),
		);
	}
}
