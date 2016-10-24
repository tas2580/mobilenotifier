<?php
/**
*
* @package phpBB Extension - tas2580 Usermap
* @copyright (c) 2016 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tas2580\mobilenotifier\controller;

use Symfony\Component\DependencyInjection\Container;

class send
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb_extension_manager */
	protected $phpbb_extension_manager;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string php_ext */
	protected $php_ext;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth				$auth						Auth object
	* @param \phpbb\config\config			$config						Config object
	* @param \phpbb\db\driver\driver_interface	$db
	* @param \phpbb\controller\helper			$helper
	* @param \phpbb\pagination				$pagination
	* @param \phpbb\path_helper				$path_helper
	* @param \phpbb\request\request			$request
	* @param \phpbb_extension_manager		$phpbb_extension_manager
	* @param \phpbb\user					$user						User Object
	* @param \phpbb\template\template		$template
	* @param string						$phpbb_root_path				phpbb_root_path
	* @param string						$php_ext						php_ext
	*/
	public function __construct(Container $phpbb_container, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->phpbb_container = $phpbb_container;
		$this->db = $db;
		$this->helper = $helper;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;

	}


	/**
	 * Delete a thing
	 *
	 * @param int $id	The Thing ID
	 * @return type
	 */
	public function wa($user_id)
	{
		$this->user->add_lang_ext('tas2580/mobilenotifier', 'common');


		$sql = 'SELECT username, user_allow_whatsapp, user_whatsapp
			FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);


		$submit = $this->request->is_set_post('submit');
		if ($submit)
		{
			$wa = $this->phpbb_container->get('tas2580.mobilenotifier.src.helper');

			$wa->send($row['user_whatsapp'], 'test');
		}

		$this->template->assign_vars(array(
			'U_ACTION'			=> $this->helper->route('tas2580_mobilenotifier_send', array('user_id' => $user_id)),
			'SEND_WHATSAPP'		=> $this->user->lang('SEND_WHATSAPP', $row['username']),

		));

		return $this->helper->render('whatsapp_send.html', $this->user->lang('SEND_WHATSAPP', $row['username']));
	}

}
