<?php
/**
*
* @package phpBB Extension - tas2580 Mobile Notifier
* @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace tas2580\mobilenotifier;

/**
* @ignore
*/
class ext extends \phpbb\extension\base
{

	function disable_step($old_state)
	{
		global $db;
		$sql = 'DELETE FROM ' . USER_NOTIFICATIONS_TABLE . "
			WHERE method = 'notification.method.mobilenotifier'";
		$db->sql_query($sql);
		return parent::enable_step($old_state);
	}
}
