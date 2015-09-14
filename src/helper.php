<?php
/**
*
* @package phpBB Extension - tas2580 Mobile Notifier
* @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/


namespace tas2580\mobilenotifier\src;

class helper
{
	/* @var wa */
	protected $wa;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user\user */
	protected $user;

	/* @var \phpbb\request\request */
	protected $request;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string php_ext */
	protected $php_ext;

	/**
	* Constructor
	*
	* @param \phpbb\config\config			$config				Config Object
	* @param \phpbb\user					$user				User object
	* @param \phpbb\request\request			$request				Request object
	* @param string						$phpbb_root_path		phpbb_root_path
	* @param string						$php_ext				php_ext
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\user $user, \phpbb\request\request $request, $phpbb_root_path, $php_ext)
	{

		$this->config = $config;
		$this->user = $user;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		require_once($this->phpbb_root_path . 'ext/tas2580/mobilenotifier/src/functions.' . $this->php_ext);
		require_once($this->phpbb_root_path . 'ext/tas2580/mobilenotifier/src/keystream.' . $this->php_ext);
		require_once($this->phpbb_root_path . 'ext/tas2580/mobilenotifier/src/tokenmap.' . $this->php_ext);
		require_once($this->phpbb_root_path . 'ext/tas2580/mobilenotifier/src/protocol_node.' . $this->php_ext);
		require_once($this->phpbb_root_path . 'ext/tas2580/mobilenotifier/src/writer.' . $this->php_ext);
		require_once($this->phpbb_root_path . 'ext/tas2580/mobilenotifier/src/reader.' . $this->php_ext);
		require_once($this->phpbb_root_path . 'ext/tas2580/mobilenotifier/src/rc4.' . $this->php_ext);
		require_once($this->phpbb_root_path . 'ext/tas2580/mobilenotifier/src/whatsapp.' . $this->php_ext);
	}

	/*
	 * Send a message to a whatsapp user
	 *
	 * @param	string	$dst		Phone number of the reciver
	 * @param	string	$msg	The message to send
	 */
	public function send($dst, $msg)
	{
		$this->_connect();
		$cc_array = $this->_country_code();
		$cc = substr($dst, 0, 2);
		$whatsapp = substr($dst, 2);
		$this->wa->send($cc_array[$cc][1] . $whatsapp, $msg);
	}

	/*
	 * Update your Whatsapp status
	 *
	 * @param	string	$status	Your new status
	 */
	public function update_status($status)
	{
		$this->_connect();
		$this->wa->set_status($status);
	}

	/*
	 * Update your Whatsapp avatar
	 *
	 * @param	string	$pic		path to the picture
	 */
	public function update_picture($pic)
	{
		$this->_connect();
		$this->wa->set_picture($pic);
	}

	/*
	 * Connect to Whatsapp
	 */
	private function _connect()
	{
		if (is_object($this->wa ))
		{
			return;
		}

		$this->wa = new whatsapp($this->config['whatsapp_sender'], '');
		$this->wa->connect();
		$this->wa->login($this->config['whatsapp_password']);
	}

	/*
	 * Generate the contry code select options
	 *
	 * @param	string	$sel		Selected CC
	 */
	public function cc_select($sel)
	{
		$options = '';
		$cc_array = $this->_country_code();
		foreach ($cc_array as $cc => $data)
		{
			$selected = ($cc == strtoupper($sel)) ? ' selected="selected"' : '';
			$options .= '<option' . $selected . ' value="' . $cc . '">' . $data[0] . ' (+' . $data[1]  . ')</option>';
		}
		return $options;
	}

	/**
	 * Try to get the country code from the users hostneme
	 *
	 * @return	string	country code
	 */
	public function get_cc()
	{
		$ip = $this->request->server('REMOTE_ADDR', '');
		$host = strtolower(gethostbyaddr($ip));

		// PECL geoip installed? Lets do it the easy way
		if (function_exists('geoip_country_code_by_name'))
		{
			$cc = geoip_country_code_by_name($host);
		}
		else
		{
			// Get the CC from hostname and translate some providers with international domains
			$hosts = array(
				'.arcor-ip.net'	=> '.de',
				'.t-dialin.net'	=> '.de',
				'.sui-inter.net'	=> '.ch',
				'.drei.com'		=> '.at',
				'.proxad.net'	=> '.fr',
				'.gaoland.net'	=> '.fr',
				'.mchsi.com'	=> '.us',
				'.comcast.net'	=> '.us',
				'.as13285.net'	=> '.uk',
				'.as29017.net'	=> '.uk',
			);
			$cc = substr(strrchr(strtr($host, $hosts), '.'), 1);
		}
		return (strlen($cc) == 2) ? $cc : $this->config['whatsapp_default_cc'];
	}

	/*
	 * Generate the array with country codes
	 */
	private function _country_code()
	{
		$country_code = array(
			'AD'		=> array($this->user->lang('CC_AD'), 376),
			'AE'		=> array($this->user->lang('CC_AE'), 971),
			'AF'		=> array($this->user->lang('CC_AF'), 93),
			'AG'		=> array($this->user->lang('CC_AG'), 1),
			'AI'		=> array($this->user->lang('CC_AI'), 1),
			'AL'		=> array($this->user->lang('CC_AL'), 355),
			'AM'		=> array($this->user->lang('CC_AM'), 374),
			'AO'		=> array($this->user->lang('CC_AO'), 244),
			'AR'		=> array($this->user->lang('CC_AR'), 54),
			'AS'		=> array($this->user->lang('CC_AS'), 1),
			'AT'		=> array($this->user->lang('CC_AT'), 43),
			'AU'		=> array($this->user->lang('CC_AU'), 61),
			'AW'		=> array($this->user->lang('CC_AW'), 297),
			'AZ'		=> array($this->user->lang('CC_AZ'), 994),
			'BA'		=> array($this->user->lang('CC_BA'), 387),
			'BB'		=> array($this->user->lang('CC_BB'), 1),
			'BD'		=> array($this->user->lang('CC_BD'), 880),
			'BE'		=> array($this->user->lang('CC_BE'), 32),
			'BF'		=> array($this->user->lang('CC_BF'), 226),
			'BG'		=> array($this->user->lang('CC_BG'), 359),
			'BH'		=> array($this->user->lang('CC_BH'), 973),
			'BI'		=> array($this->user->lang('CC_BI'), 257),
			'BJ'		=> array($this->user->lang('CC_BJ'), 229),
			'BL'		=> array($this->user->lang('CC_BL'), 590),
			'BM'		=> array($this->user->lang('CC_BM'), 1),
			'BN'		=> array($this->user->lang('CC_BN'), 673),
			'BO'		=> array($this->user->lang('CC_BO'), 591),
			'BQ'		=> array($this->user->lang('CC_BQ'), 599),
			'BR'		=> array($this->user->lang('CC_BR'), 55),
			'BS'		=> array($this->user->lang('CC_BS'), 1),
			'BT'		=> array($this->user->lang('CC_BT'), 975),
			'BW'		=> array($this->user->lang('CC_BW'), 267),
			'BY'		=> array($this->user->lang('CC_BY'), 375),
			'BZ'		=> array($this->user->lang('CC_BZ'), 501),
			'CA'		=> array($this->user->lang('CC_CA'), 1),
			'CD'		=> array($this->user->lang('CC_CD'), 243),
			'CF'		=> array($this->user->lang('CC_CF'), 236),
			'CG'		=> array($this->user->lang('CC_CG'), 242),
			'CH'		=> array($this->user->lang('CC_CH'), 41),
			'CI'		=> array($this->user->lang('CC_CI'), 225),
			'CK'		=> array($this->user->lang('CC_CK'), 682),
			'CL'		=> array($this->user->lang('CC_CL'), 56),
			'CM'		=> array($this->user->lang('CC_CM'), 237),
			'CN'		=> array($this->user->lang('CC_CN'), 86),
			'CO'		=> array($this->user->lang('CC_CO'), 57),
			'CR'		=> array($this->user->lang('CC_CR'), 506),
			'CU'		=> array($this->user->lang('CC_CU'), 53),
			'CV'		=> array($this->user->lang('CC_CV'), 238),
			'CW'		=> array($this->user->lang('CC_CW'), 599),
			'CY'		=> array($this->user->lang('CC_CY'), 357),
			'CZ'		=> array($this->user->lang('CC_CZ'), 420),
			'DE'		=> array($this->user->lang('CC_DE'), 49),
			'DJ'		=> array($this->user->lang('CC_DJ'), 253),
			'DK'		=> array($this->user->lang('CC_DK'), 45),
			'DM'		=> array($this->user->lang('CC_DM'), 1),
			'DO'		=> array($this->user->lang('CC_DO'), 1),
			'DZ'		=> array($this->user->lang('CC_DZ'), 213),
			'EC'		=> array($this->user->lang('CC_EC'), 593),
			'EE'		=> array($this->user->lang('CC_EE'), 372),
			'EG'		=> array($this->user->lang('CC_EG'), 20),
			'EH'		=> array($this->user->lang('CC_EH'), 212),
			'ER'		=> array($this->user->lang('CC_ER'), 291),
			'ES'		=> array($this->user->lang('CC_ES'), 34),
			'ET'		=> array($this->user->lang('CC_ET'), 251),
			'FK'		=> array($this->user->lang('CC_FK'), 500),
			'FI'		=> array($this->user->lang('CC_FI'), 358),
			'FJ'		=> array($this->user->lang('CC_FJ'), 679),
			'FO'		=> array($this->user->lang('CC_FO'), 298),
			'FM'		=> array($this->user->lang('CC_FM'), 691),
			'FR'		=> array($this->user->lang('CC_FR'), 33),
			'GA'		=> array($this->user->lang('CC_GA'), 241),
			'GB'		=> array($this->user->lang('CC_GB'), 44),
			'GD'		=> array($this->user->lang('CC_GD'), 1),
			'GE'		=> array($this->user->lang('CC_GE'), 995),
			'GF'		=> array($this->user->lang('CC_GF'), 594),
			'GG'		=> array($this->user->lang('CC_GG'), 44),
			'GH'		=> array($this->user->lang('CC_GH'), 233),
			'GI'		=> array($this->user->lang('CC_GI'), 350),
			'GL'		=> array($this->user->lang('CC_GL'), 299),
			'GM'		=> array($this->user->lang('CC_GM'), 220),
			'GN'		=> array($this->user->lang('CC_GN'), 224),
			'GQ'		=> array($this->user->lang('CC_GQ'), 240),
			'GP'		=> array($this->user->lang('CC_GP'), 590),
			'GR'		=> array($this->user->lang('CC_GR'), 30),
			'GT'		=> array($this->user->lang('CC_GT'), 502),
			'GU'		=> array($this->user->lang('CC_GU'), 1),
			'GW'		=> array($this->user->lang('CC_GW'), 245),
			'GY'		=> array($this->user->lang('CC_GY'), 592),
			'HK'		=> array($this->user->lang('CC_HK'), 852),
			'HN'		=> array($this->user->lang('CC_HN'), 504),
			'HR'		=> array($this->user->lang('CC_HR'), 385),
			'HT'		=> array($this->user->lang('CC_HT'), 509),
			'HU'		=> array($this->user->lang('CC_HU'), 36),
			'ID'		=> array($this->user->lang('CC_ID'), 62),
			'IE'		=> array($this->user->lang('CC_IE'), 353),
			'IL'		=> array($this->user->lang('CC_IL'), 972),
			'IM'		=> array($this->user->lang('CC_IM'), 44),
			'IN'		=> array($this->user->lang('CC_IN'), 91),
			'IO'		=> array($this->user->lang('CC_IO'), 246),
			'IQ'		=> array($this->user->lang('CC_IQ'), 964),
			'IR'		=> array($this->user->lang('CC_IR'), 98),
			'IS'		=> array($this->user->lang('CC_IS'), 354),
			'IT'		=> array($this->user->lang('CC_IT'), 39),
			'JE'		=> array($this->user->lang('CC_JE'), 44),
			'JM'		=> array($this->user->lang('CC_JM'), 1),
			'JO'		=> array($this->user->lang('CC_JO'), 962),
			'JO'		=> array($this->user->lang('CC_JO'), 81),
			'KE'		=> array($this->user->lang('CC_KE'), 254),
			'KG'		=> array($this->user->lang('CC_KG'), 996),
			'KH'		=> array($this->user->lang('CC_KH'), 855),
			'KI'		=> array($this->user->lang('CC_KI'), 686),
			'KM'		=> array($this->user->lang('CC_KM'), 269),
			'KN'		=> array($this->user->lang('CC_KN'), 1),
			'KP'		=> array($this->user->lang('CC_KP'), 850),
			'KR'		=> array($this->user->lang('CC_KR'), 82),
			'KW'		=> array($this->user->lang('CC_KW'), 965),
			'KY'		=> array($this->user->lang('CC_KY'), 1),
			'KZ'		=> array($this->user->lang('CC_KZ'), 7),
			'LA'		=> array($this->user->lang('CC_LA'), 856),
			'LB'		=> array($this->user->lang('CC_LB'), 961),
			'LC'		=> array($this->user->lang('CC_LC'), 1),
			'LI'		=> array($this->user->lang('CC_LI'), 423),
			'LK'		=> array($this->user->lang('CC_LK'), 94),
			'LR'		=> array($this->user->lang('CC_LR'), 231),
			'LS'		=> array($this->user->lang('CC_LS'), 266),
			'LT'		=> array($this->user->lang('CC_LT'), 370),
			'LU'		=> array($this->user->lang('CC_LU'), 352),
			'LV'		=> array($this->user->lang('CC_LV'), 371),
			'LY'		=> array($this->user->lang('CC_LY'), 218),
			'MA'		=> array($this->user->lang('CC_MA'), 212),
			'MC'		=> array($this->user->lang('CC_MC'), 377),
			'MD'		=> array($this->user->lang('CC_MD'), 373),
			'ME'		=> array($this->user->lang('CC_ME'), 382),
			'MF'		=> array($this->user->lang('CC_MF'), 590),
			'MG'		=> array($this->user->lang('CC_MG'), 261),
			'MH'		=> array($this->user->lang('CC_MH'), 692),
			'MK'		=> array($this->user->lang('CC_MK'), 389),
			'ML'		=> array($this->user->lang('CC_ML'), 223),
			'MM'		=> array($this->user->lang('CC_MM'), 95),
			'MN'		=> array($this->user->lang('CC_MN'), 976),
			'MO'		=> array($this->user->lang('CC_MO'), 853),
			'MP'		=> array($this->user->lang('CC_MP'), 1),
			'MQ'		=> array($this->user->lang('CC_MQ'), 596),
			'MR'		=> array($this->user->lang('CC_MR'), 222),
			'MS'		=> array($this->user->lang('CC_MS'), 1),
			'MT'		=> array($this->user->lang('CC_MT'), 356),
			'MU'		=> array($this->user->lang('CC_MU'), 230),
			'MV'		=> array($this->user->lang('CC_MV'), 960),
			'MW'		=> array($this->user->lang('CC_MW'), 265),
			'MX'		=> array($this->user->lang('CC_MX'), 52),
			'MY'		=> array($this->user->lang('CC_MY'), 60),
			'MZ'		=> array($this->user->lang('CC_MZ'), 258),
			'NA'		=> array($this->user->lang('CC_NA'), 264),
			'NC'		=> array($this->user->lang('CC_NC'), 687),
			'NE'		=> array($this->user->lang('CC_NE'), 227),
			'NF'		=> array($this->user->lang('CC_NF'), 672),
			'NG'		=> array($this->user->lang('CC_NG'), 234),
			'NI'		=> array($this->user->lang('CC_NI'), 505),
			'NL'		=> array($this->user->lang('CC_NL'), 31),
			'NO'		=> array($this->user->lang('CC_NO'), 47),
			'NP'		=> array($this->user->lang('CC_NP'), 977),
			'NR'		=> array($this->user->lang('CC_NR'), 674),
			'NU'		=> array($this->user->lang('CC_NU'), 683),
			'NZ'		=> array($this->user->lang('CC_NZ'), 64),
			'OM'		=> array($this->user->lang('CC_OM'), 968),
			'PA'		=> array($this->user->lang('CC_PA'), 507),
			'PE'		=> array($this->user->lang('CC_PE'), 51),
			'PF'		=> array($this->user->lang('CC_PF'), 689),
			'PG'		=> array($this->user->lang('CC_PG'), 675),
			'PH'		=> array($this->user->lang('CC_PH'), 63),
			'PK'		=> array($this->user->lang('CC_PK'), 92),
			'PL'		=> array($this->user->lang('CC_PL'), 48),
			'PM'		=> array($this->user->lang('CC_PM'), 508),
			'PR'		=> array($this->user->lang('CC_PR'), 1),
			'PS'		=> array($this->user->lang('CC_PS'), 970),
			'PT'		=> array($this->user->lang('CC_PT'), 351),
			'PW'		=> array($this->user->lang('CC_PW'), 680),
			'PY'		=> array($this->user->lang('CC_PY'), 595),
			'QA'		=> array($this->user->lang('CC_QA'), 974),
			'RE'		=> array($this->user->lang('CC_RE'), 262),
			'RO'		=> array($this->user->lang('CC_RO'), 40),
			'RS'		=> array($this->user->lang('CC_RS'), 381),
			'RU'		=> array($this->user->lang('CC_RU'), 7),
			'RW'		=> array($this->user->lang('CC_RW'), 250),
			'SA'		=> array($this->user->lang('CC_SA'), 966),
			'SB'		=> array($this->user->lang('CC_SB'), 677),
			'SC'		=> array($this->user->lang('CC_SC'), 248),
			'SD'		=> array($this->user->lang('CC_SD'), 249),
			'SE'		=> array($this->user->lang('CC_SE'), 46),
			'SG'		=> array($this->user->lang('CC_SG'), 65),
			'SH'		=> array($this->user->lang('CC_SH'), 290),
			'SI'		=> array($this->user->lang('CC_SI'), 386),
			'SK'		=> array($this->user->lang('CC_SK'), 421),
			'SL'		=> array($this->user->lang('CC_SL'), 232),
			'SM'		=> array($this->user->lang('CC_SM'), 378),
			'SN'		=> array($this->user->lang('CC_SN'), 221),
			'SO'		=> array($this->user->lang('CC_SO'), 252),
			'SR'		=> array($this->user->lang('CC_SR'), 597),
			'SS'		=> array($this->user->lang('CC_SS'), 211),
			'ST'		=> array($this->user->lang('CC_ST'), 239),
			'SV'		=> array($this->user->lang('CC_SV'), 503),
			'SX'		=> array($this->user->lang('CC_SX'), 599),
			'SY'		=> array($this->user->lang('CC_SY'), 963),
			'SZ'		=> array($this->user->lang('CC_SZ'), 268),
			'TC'		=> array($this->user->lang('CC_TC'), 1),
			'TD'		=> array($this->user->lang('CC_TD'), 235),
			'TG'		=> array($this->user->lang('CC_TG'), 228),
			'TH'		=> array($this->user->lang('CC_TH'), 66),
			'TJ'		=> array($this->user->lang('CC_TJ'), 992),
			'TK'		=> array($this->user->lang('CC_TK'), 690),
			'TL'		=> array($this->user->lang('CC_TL'), 670),
			'TM'		=> array($this->user->lang('CC_TM'), 993),
			'TN'		=> array($this->user->lang('CC_TN'), 216),
			'TO'		=> array($this->user->lang('CC_TO'), 676),
			'TR'		=> array($this->user->lang('CC_TR'), 90),
			'TT'		=> array($this->user->lang('CC_TT'), 1),
			'TV'		=> array($this->user->lang('CC_TV'), 688),
			'TW'		=> array($this->user->lang('CC_TW'), 886),
			'TZ'		=> array($this->user->lang('CC_TZ'), 255),
			'UA'		=> array($this->user->lang('CC_UA'), 380),
			'UG'		=> array($this->user->lang('CC_UG'), 256),
			'US'		=> array($this->user->lang('CC_US'), 1),
			'UY'		=> array($this->user->lang('CC_UY'), 598),
			'UZ'		=> array($this->user->lang('CC_UZ'), 998),
			'VA'		=> array($this->user->lang('CC_VA'), 39),
			'VC'		=> array($this->user->lang('CC_VC'), 1),
			'VE'		=> array($this->user->lang('CC_VE'), 58),
			'VG'		=> array($this->user->lang('CC_VG'), 1),
			'VI'		=> array($this->user->lang('CC_VI'), 1),
			'VN'		=> array($this->user->lang('CC_VN'), 84),
			'VU'		=> array($this->user->lang('CC_VU'), 678),
			'WF'		=> array($this->user->lang('CC_WF'), 681),
			'WS'		=> array($this->user->lang('CC_WS'), 685),
			'XK'		=> array($this->user->lang('CC_XK'), 381),
			'YE'		=> array($this->user->lang('CC_YE'), 967),
			'YT'		=> array($this->user->lang('CC_YT'), 262),
			'ZA'		=> array($this->user->lang('CC_ZA'), 27),
			'ZM'		=> array($this->user->lang('CC_ZM'), 260),
			'ZW'		=> array($this->user->lang('CC_ZW'), 263),
		);

		array_multisort($country_code, SORT_ASC,  0);
		return $country_code;
	}
}
