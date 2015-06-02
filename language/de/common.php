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

$lang = array_merge($lang, array(
	'NOTIFICATION_METHOD_WHATSAPP'	=> 'Whatsapp',
	'ACP_SETTINGS'					=> 'Einstellungen',
	'ACP_NICKNAME'					=> 'Nickname',
	'ACP_NICKNAME_EXPLAIN'				=> 'Gib einen Nicknamen an der zum versenden der Whatsapp Nachichten benutzt wird.',
	'ACP_SENDER'						=> 'Absender Nummer',
	'ACP_SENDER_EXPLAIN'				=> 'Gib die Telefon Nummer an von der Whatsapp Benachichtigungen versendet werden.<br />Die Nummer muss in Stiel <b>{Ländervohrwahl}{Vorhwahl ohne 0}{Nummer}</b> angegeben werden.<br />Bsp.: 4917212345678',
	'ACP_PASSWORD'					=> 'Passwort',
	'ACP_PASSWORD_EXPLAIN'			=> 'Gib dein Whatsapp Passwort an.',
	'ACP_SUBMIT'						=> 'Einstellungen speichern',
	'WHATSAPP_NR'					=> 'Telefon Nummer',
	'WHATSAPP_NR_EXPLAIN'				=> 'Gib die Telefon Nummer an auf der du Whatsapp Benachichtigungen empfangen willst.<br />Die Nummer muss in Stiel <b>{Ländervohrwahl}{Vorhwahl ohne 0}{Nummer}</b> angegeben werden.<br />Bsp.: 4917212345678'
));