<?php
/**
*
* @package phpBB Extension - tas2580 Mobile Notifier
* @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tas2580\mobilenotifier\acp;

class mobilenotifier_info
{
	function module()
	{
		return array(
			'filename'		=> '\tas2580\mobilenotifier\mobilenotifier_module',
			'title'			=> 'ACP_MOBILENOTIFIER_TITLE',
			'version'		=> '0.1.2',
			'modes'		=> array(
				'settings'		=> array(
					'title'		=> 'ACP_MOBILENOTIFIER_TITLE',
					'auth'	=> 'ext_tas2580/mobilenotifier && acl_a_board',
					'cat'		=> array('ACP_MOBILENOTIFIER_TITLE')
				),
			),
		);
	}
}
