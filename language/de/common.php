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
	'ACP_SENDER'						=> 'Absender Nummer',
	'ACP_SENDER_EXPLAIN'				=> 'Gib die Telefon Nummer an von der Whatsapp Benachichtigungen versendet werden.<br />Die Nummer muss in Stiel <b>{Ländervohrwahl}{Vorhwahl ohne 0}{Nummer}</b> angegeben werden.<br />Bsp.: 4917212345678',
	'ACP_PASSWORD'					=> 'Passwort',
	'ACP_PASSWORD_EXPLAIN'			=> 'Gib dein Whatsapp Passwort an. Wie du an das Passwort kommst wird <a href="https://tas2580.net/page/whatsapp_passwort.html">hier</a> beschrieben.',
	'ACP_STATUS'						=> 'Status',
	'ACP_STATUS_EXPLAIN'				=> 'Gib eine Status Meldung für den Whatsapp Kontakt an.',
	'ACP_IMAGE'						=> 'Avatar',
	'ACP_IMAGE_EXPLAIN'				=> 'Lade ein Bild hoch das der Whatsapp Kontakt als Avatar verwendet.',
	'ACP_SUBMIT'						=> 'Einstellungen speichern',
	'ACP_SAVED'						=> 'Die Einstellungen wurden gespeichert.',
	'WHATSAPP_NR'					=> 'Telefon Nummer',
	'WHATSAPP_NR_EXPLAIN'				=> 'Gib die Telefon Nummer an auf der du Whatsapp Benachichtigungen empfangen willst.<br />Die Nummer muss in Stiel <b>{Ländervohrwahl}{Vorhwahl ohne 0}{Nummer}</b> angegeben werden.<br />Bsp.: 4917212345678'
));