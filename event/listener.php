<?php
/**
*
* @package phpBB Extension - tas2580 Whatsapp Notifier
* @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tas2580\whatsapp\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/* @var \phpbb\request\request */
	protected $request;
	
	/* @var \phpbb\user */
	protected $user;
	
	/** @var \phpbb\template\template */
	protected $template;
	
	/** @var string phpbb_root_path */
	protected $phpbb_root_path;
	
	/**
	* Constructor
	*
	* @param \phpbb\request\request			$request			Request object
	* @param \phpbb\user					$user			User Object
	* @param \phpbb\template\template		$template			Template Object
	* @param string						$phpbb_root_path	phpbb_root_path
	* @access public
	*/
	public function __construct(\phpbb\request\request $request, \phpbb\user $user, \phpbb\template\template $template, $phpbb_root_path)
	{
		$this->request = $request;
		$this->user = $user;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.ucp_profile_modify_profile_info'					=> 'profile_modify_profile_info',
			'core.ucp_profile_info_modify_sql_ary'				=> 'profile_info_modify_sql_ary',
			'core.acp_users_modify_profile'						=> 'profile_modify_profile_info',
			'core.acp_users_profile_modify_sql_ary'				=> 'profile_info_modify_sql_ary',
		);
	}
	public function profile_modify_profile_info($event)
	{
		// Request the user option vars and add them to the data array
		$event['data'] = array_merge($event['data'], array(
			'user_whatsapp'	=> $this->request->variable('whatsapp', $this->user->data['user_whatsapp']),
		));
		$this->user->add_lang_ext('tas2580/whatsapp', 'common');
		
		$this->template->assign_vars(array(
			'WHATSAPP'		=> $this->user->data['user_whatsapp'],
		));
	}
	
	/**
	* Validate users changes to their whatsapp number
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function ucp_profile_validate_profile_info($event)
	{
			$array = $event['error'];
			if (!function_exists('validate_data'))
			{
				include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
			}
			$validate_array = array(
				'user_whatsapp'	=> array('num', true, 0, 12),
			);
			$error = validate_data($event['data'], $validate_array);
			$event['error'] = array_merge($array, $error);
	}
	
	/**
	* User changed their whatsapp number, update the database
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function profile_info_modify_sql_ary($event)
	{
		$event['sql_ary'] = array_merge($event['sql_ary'], array(
				'user_whatsapp' => $event['data']['user_whatsapp'],
		));
	}
}
