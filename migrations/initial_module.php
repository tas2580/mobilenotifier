<?php
/**
*
* @package phpBB Extension - tas2580 Whatsapp Notifier
* @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tas2580\whatsapp\migrations;

class initial_module extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			// Add configs
			array('config.add', array('whatsapp_sender', '')),
			array('config.add', array('whatsapp_password', '')),
			array('config.add', array('whatsapp_status', '')),

			// Add ACP module
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_WHATSAPP_TITLE'
			)),
			array('module.add', array(
				'acp',
				'ACP_WHATSAPP_TITLE',
				array(
					'module_basename'	=> '\tas2580\whatsapp\acp\whatsapp_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'users'	=> array(
					'user_whatsapp'	=> array('VCHAR:255', ''),
				),
			),
		);
	}
	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'users'	=> array(
					'user_whatsapp',
				),
			),
		);
	}
}