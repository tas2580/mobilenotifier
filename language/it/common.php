<?php
/**
*
* @package phpBB Extension - tas2580 Mobile Notifier
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
	'NOTIFICATION_METHOD_MOBILENOTIFIER'	=> 'WhatsApp®',
	'ACP_SETTINGS'						=> 'Impostazioni',
	'ACP_SENDER'							=> 'Il numero di telefono del mittente',
	'ACP_SENDER_EXPLAIN'					=> 'Inserisci il tuo numero cellulare di Whatsapp con il tuo codice paese (italia è 39) e senza il prefisso 0 iniziale dove Whatsapp dovrebbe inviare i messaggi. <br />Esempio.: 39123456789',
	'ACP_PASSWORD'						=> 'Password',
	'ACP_PASSWORD_EXPLAIN'				=> 'Inserisci la tua Password Whatsapp.',
	'ACP_STATUS'							=> 'Stato',
	'ACP_STATUS_EXPLAIN'					=> 'Inserisci il tuo stato di Whatsapp.',
	'ACP_IMAGE'							=> 'Avatar',
	'ACP_IMAGE_EXPLAIN'					=> 'Carica un immagine avatar per il tuo utente Whatsapp.',
	'ACP_SUBMIT'							=> 'Aggiorna impostazioni',
	'ACP_SAVED'							=> 'Impostazioni Whatsapp aggiornate!',
	'WHATSAPP_NR'						=> 'Numero cellulare',
	'WHATSAPP_NR_EXPLAIN'					=> 'Inserisci il tuo numero cellulare di Whatsapp con il tuo codice paese (italia è 39) e senza il prefisso 0 iniziale. <br />Esempio.: 39123456789'
));
