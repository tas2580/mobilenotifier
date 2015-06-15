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
	'WHATSAPP_NR_EXPLAIN'				=> 'Select your country code and enter your Whatsapp phone number with without 0 at the beginning. <br />Example.: 123456789',

	// Country Codes
	'CC_AD'	=> 'Andorra',
	'CC_AE'	=> 'Arab Emirates (UAE)',
	'CC_AF'	=> 'Afghanistan',
	'CC_AG'	=> 'Antigua',
	'CC_AI'	=> 'Anguilla',
	'CC_AL'	=> 'Albania',
	'CC_AM'	=> 'Armenia',
	'CC_AO'	=> 'Angola',
	'CC_AR'	=> 'Argentina',
	'CC_AS'	=> 'American Samoa',
	'CC_AT'	=> 'Austria',
	'CC_AU'	=> 'Australia',
	'CC_AW'	=> 'Aruba',
	'CC_AZ'	=> 'Azerbaijan',
	'CC_BA'	=> 'Bosnia and Herzegovina',
	'CC_BB'	=> 'Barbados',
	'CC_BD'	=> 'Bangladesh',
	'CC_BE'	=> 'Belgium',
	'CC_BF'	=> 'Burkina Faso',
	'CC_BG'	=> 'Bulgaria',
	'CC_BH'	=> 'Bahrain',
	'CC_BI'	=> 'Burundi',
	'CC_BJ'	=> 'Benin',
	'CC_BL'	=> 'Saint Barthélemy',
	'CC_BM'	=> 'Bermuda',
	'CC_BN'	=> 'Brunei',
	'CC_BO'	=> 'Bolivia',
	'CC_BQ'	=> 'Bonaire Island Country',
	'CC_BR'	=> 'Brazil',
	'CC_BS'	=> 'Bahamas',
	'CC_BT'	=> 'Bhutan',
	'CC_BW'	=> 'Botswana',
	'CC_BY'	=> 'Belarus',
	'CC_BZ'	=> 'Belize',
	'CC_CA'	=> 'Canada',
	'CC_CD'	=> 'Democratic Republic of Congo',
	'CC_CF'	=> 'Central African Republic',
	'CC_CG'	=> 'Congo',
	'CC_CH'	=> 'Switzerland',
	'CC_CI'	=> 'Côte d’Ivoire',
	'CC_CK'	=> 'Cook Islands',
	'CC_CL'	=> 'Chile',
	'CC_CM'	=> 'Cameroon',
	'CC_CN'	=> 'China',
	'CC_CO'	=> 'Colombia',
	'CC_CR'	=> 'Costa Rica',
	'CC_CU'	=> 'Kuba',
	'CC_CV'	=> 'Cape Verde',
	'CC_CW'	=> 'Curaçao',
	'CC_CY'	=> 'Cyprus',
	'CC_CZ'	=> 'Czech Republic',
	'CC_DE'	=> 'Germany',
	'CC_DJ'	=> 'Djibouti',
	'CC_DK'	=> 'Denmark',
	'CC_DM'	=> 'Dominica',
	'CC_DO'	=> 'Dominican Republic',
	'CC_DZ'	=> 'Algeria',
	'CC_EC'	=> 'Ecuador',
	'CC_EE'	=> 'Estonia',
	'CC_EG'	=> 'Egypt',
	'CC_EH'	=> 'Western Sahara',
	'CC_ER'	=> 'Eritrea',
	'CC_ES'	=> 'Spain',
	'CC_ET'	=> 'Ethiopia',
	'CC_FK'	=> 'Falkland Islands',
	'CC_FI'	=> 'Finland',
	'CC_FJ'	=> 'Fiji',
	'CC_FO'	=> 'Faroe Islands',
	'CC_FM'	=> 'Federated States of Micronesia',
	'CC_FR'	=> 'France',
	'CC_GA'	=> 'Gabon',
	'CC_GB'	=> 'United Kingdom',
	'CC_GD'	=> 'Grenada',
	'CC_GE'	=> 'Georgia',
	'CC_GF'	=> 'French Guiana',
	'CC_GG'	=> 'Guernsey',
	'CC_GH'	=> 'Ghana',
	'CC_GI'	=> 'Gibraltar',
	'CC_GL'	=> 'Greenland',
	'CC_GM'	=> 'Gambia',
	'CC_GN'	=> 'Guinea',
	'CC_GQ'	=> 'Equatorial Guinea',
	'CC_GP'	=> 'Guadeloupe',
	'CC_GR'	=> 'Greece',
	'CC_GT'	=> 'Guatemala',
	'CC_GU'	=> 'Guam',
	'CC_GW'	=> 'Guinea-Bissau',
	'CC_GY'	=> 'Guyana',
	'CC_HK'	=> 'Hong kong',
	'CC_HN'	=> 'Honduras',
	'CC_HR'	=> 'Croatia',
	'CC_HT'	=> 'Haiti',
	'CC_HU'	=> 'Hungary',
	'CC_ID'	=> 'Indonesia',
	'CC_IE'	=> 'Ireland',
	'CC_IL'	=> 'Israel',
	'CC_IM'	=> 'Isle of Man',
	'CC_IN'	=> 'India',
	'CC_IO'	=> 'British Indian Ocean Territory',
	'CC_IQ'	=> 'Iraq',
	'CC_IR'	=> 'Iran',
	'CC_IS'	=> 'Iceland',
	'CC_IT'	=> 'Italy',
	'CC_JE'	=> 'Jersey',
	'CC_JM'	=> 'Jamaica',
	'CC_JO'	=> 'Jordan',
	'CC_JP'	=> 'Japan',
	'CC_KE'	=> 'Kenya',
	'CC_KG'	=> 'Kyrgyzstan',
	'CC_KH'	=> 'Cambodia',
	'CC_KI'	=> 'Kiribati',
	'CC_KM'	=> 'Comoros',
	'CC_KN'	=> 'St. Kitts and Nevis',
	'CC_KP'	=> 'North Korea',
	'CC_KR'	=> 'South korea',
	'CC_KW'	=> 'Kuwait',
	'CC_KY'	=> 'Cayman Islands',
	'CC_KZ'	=> 'Kazakhstan',
	'CC_LA'	=> 'Laos',
	'CC_LB'	=> 'Lebanon',
	'CC_LC'	=> 'St. Lucia',
	'CC_LI'	=> 'Liechtenstein',
	'CC_LK'	=> 'Sri Lanka',
	'CC_LR'	=> 'Liberia',
	'CC_LS'	=> 'Lesotho',
	'CC_LT'	=> 'Lithuania',
	'CC_LU'	=> 'Luxembourg',
	'CC_LV'	=> 'Latvia',
	'CC_LY'	=> 'Libya',
	'CC_MA'	=> 'Morocco',
	'CC_MC'	=> 'Monaco',
	'CC_MD'	=> 'Moldova',
	'CC_ME'	=> 'Montenegro',
	'CC_MF'	=> 'St. Martin',
	'CC_MG'	=> 'Madagascar',
	'CC_MH'	=> 'Marshall Islands',
	'CC_MK'	=> 'Macedonia',
	'CC_ML'	=> 'Mali',
	'CC_MM'	=> 'Myanmar (Burma)',
	'CC_MN'	=> 'Mongolia',
	'CC_MO'	=> 'Macau',
	'CC_MP'	=> 'Northern Mariana Islands',
	'CC_MQ'	=> 'Martinique',
	'CC_MR'	=> 'Mauritania',
	'CC_MS'	=> 'Montserrat',
	'CC_MT'	=> 'Malta',
	'CC_MU'	=> 'Mauritius',
	'CC_MV'	=> 'Maldives',
	'CC_MW'	=> 'Malawi',
	'CC_MX'	=> 'Mexico',
	'CC_MY'	=> 'Malaysia',
	'CC_MZ'	=> 'Mozambique',
	'CC_NA'	=> 'Namibia',
	'CC_NC'	=> 'New Caledonia',
	'CC_NE'	=> 'Niger',
	'CC_NF'	=> 'Norfolk Islands',
	'CC_NG'	=> 'Nigeria',
	'CC_NI'	=> 'Nicaragua',
	'CC_NL'	=> 'Netherlands',
	'CC_NO'	=> 'Norway',
	'CC_NP'	=> 'Nepal',
	'CC_NR'	=> 'Nauru',
	'CC_NU'	=> 'Niue',
	'CC_NZ'	=> 'New Zealand',
	'CC_OM'	=> 'Oman',
	'CC_PA'	=> 'Panama',
	'CC_PE'	=> 'Peru',
	'CC_PF'	=> 'French Polynesia',
	'CC_PG'	=> 'Papua New Guinea',
	'CC_PH'	=> 'Philippines',
	'CC_PK'	=> 'Pakistan',
	'CC_PL'	=> 'Poland',
	'CC_PM'	=> 'St. Pierre and Miquelon',
	'CC_PR'	=> 'Puerto Rico',
	'CC_PS'	=> 'Palestine',
	'CC_PT'	=> 'Portugal',
	'CC_PW'	=> 'Belau',
	'CC_PY'	=> 'Paraguay',
	'CC_QA'	=> 'Katar',
	'CC_RE'	=> 'Reunion',
	'CC_RO'	=> 'Romania',
	'CC_RS'	=> 'Serbia',
	'CC_RU'	=> 'Russia',
	'CC_RW'	=> 'Rwanda',
	'CC_SA'	=> 'Saudi-Arabia',
	'CC_SB'	=> 'Solomon Islands',
	'CC_SC'	=> 'Seychelles',
	'CC_SD'	=> 'Sudan',
	'CC_SE'	=> 'Sweden',
	'CC_SG'	=> 'Singapore',
	'CC_SH'	=> 'St. Helena',
	'CC_SI'	=> 'Slovenia',
	'CC_SK'	=> 'Slovakia',
	'CC_SL'	=> 'Sierra Leone',
	'CC_SM'	=> 'San Marino',
	'CC_SN'	=> 'Senegal',
	'CC_SO'	=> 'Somalia',
	'CC_SR'	=> 'Suriname',
	'CC_SS'	=> 'South Sudan',
	'CC_ST'	=> 'Sao Tome and Principe',
	'CC_SV'	=> 'El Salvador',
	'CC_SX'	=> 'St. Maarten',
	'CC_SY'	=> 'Syria',
	'CC_SZ'	=> 'Swaziland',
	'CC_TC'	=> 'Turks and Caicos Islands',
	'CC_TD'	=> 'Chad',
	'CC_TG'	=> 'Togo',
	'CC_TH'	=> 'Thailand',
	'CC_TJ'	=> 'Tajikistan',
	'CC_TK'	=> 'Tokelau',
	'CC_TL'	=> 'Timor-Leste',
	'CC_TM'	=> 'Turkmenistan',
	'CC_TN'	=> 'Tunisia',
	'CC_TO'	=> 'Tonga',
	'CC_TR'	=> 'Turkey',
	'CC_TT'	=> 'Trinidad und Tobago',
	'CC_TV'	=> 'Tuvalu',
	'CC_TW'	=> 'Taiwan',
	'CC_TZ'	=> 'Tanzania',
	'CC_UA'	=> 'Ukraine',
	'CC_UG'	=> 'Uganda',
	'CC_US'	=> 'United States of America',
	'CC_UY'	=> 'Uruguay',
	'CC_UZ'	=> 'Uzbekistan',
	'CC_VA'	=> 'Vatican City',
	'CC_VC'	=> 'Saint Vincent and the Grenadines',
	'CC_VE'	=> 'Venezuela',
	'CC_VG'	=> 'British Virgin Islands',
	'CC_VI'	=> 'United States Virgin Islands',
	'CC_VN'	=> 'Vietnam',
	'CC_VU'	=> 'Vanuatu',
	'CC_WF'	=> 'Wallis und Futuna',
	'CC_WS'	=> 'Samoa',
	'CC_XK'	=> 'Kosovo (Republic of)',
	'CC_YE'	=> 'Yemen',
	'CC_YT'	=> 'Mayotte',
	'CC_ZA'	=> 'South Africa',
	'CC_ZM'	=> 'Zambia',
	'CC_ZW'	=> 'Zimbabwe',

));