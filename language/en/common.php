<?php
/**
*
* @package phpBB Extension - tas2580 Whatsapp Notifier
* @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//
$lang = array_merge($lang, array(
	'NOTIFICATION_METHOD_WHATSAPP'	=> 'Whatsapp',
	'ACP_SETTINGS'					=> 'Settings',
	'ACP_SENDER'						=> 'Sender phone number',
	'ACP_SENDER_EXPLAIN'				=> 'Enter the phone number with country code and without 0 at the beginning from that the Whatsapps should to be sent. <br />Example.: 49123456789',
	'ACP_PASSWORD'					=> 'Password',
	'ACP_PASSWORD_EXPLAIN'			=> 'Enter your Whatsapp Password.',
	'ACP_STATUS'						=> 'Status',
	'ACP_STATUS_EXPLAIN'				=> 'Enter your Whatsapp status.',
	'ACP_IMAGE'						=> 'Avatar',
	'ACP_IMAGE_EXPLAIN'				=> 'Upload an avatar image for your Whatsapp user.',
	'ACP_SUBMIT'						=> 'Update settings',
	'ACP_SAVED'						=> 'Whatsapp settings updated.',
	'WHATSAPP_NR'					=> 'Phone number',
	'WHATSAPP_NR_EXPLAIN'				=> 'Enter your Whatsapp phone number with country code and without 0 at the beginning. <br />Example.: 49123456789'
));