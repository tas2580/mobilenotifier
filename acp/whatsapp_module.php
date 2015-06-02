<?php
/**
*
* @package phpBB Extension - tas2580 Whatsapp Notifier
* @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tas2580\whatsapp\acp;

class whatsapp_module
{
    public $u_action;

    public function main($id, $mode)
    {
        global $config, $user, $template, $request;

	$user->add_lang_ext('tas2580/whatsapp', 'common');		
	$this->tpl_name = 'acp_whatsapp_body';
	$this->page_title = $user->lang('ACP_WHATSAPP_TITLE');

	add_form_key('acp_whatsapp');

	// Form is submitted
	if($request->is_set_post('submit'))
	{
		if (!check_form_key('acp_whatsapp'))
		{
			trigger_error($user->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
		}


		$config->set('whatsapp_nickname', $request->variable('nickname', ''));
		$config->set('whatsapp_sender', $request->variable('sender', ''));
		$config->set('whatsapp_password', $request->variable('password', ''));

		trigger_error($user->lang('ACP_SAVED') . adm_back_link($this->u_action));
	}


	$template->assign_vars(array(
		'U_ACTION'			=> $this->u_action,
		'NICKNAME'			=> isset($config['whatsapp_nickname']) ? $config['whatsapp_nickname'] : '',
		'SENDER'				=> isset($config['whatsapp_sender']) ? $config['whatsapp_sender'] : '',
		'PASSWORD'			=> isset($config['whatsapp_password']) ? $config['whatsapp_password'] : '',
	));
    }

}
