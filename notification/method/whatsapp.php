<?php
/**
*
* @package phpBB Extension - tas2580 Whatsapp Notifier
* @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace tas2580\whatsapp\notification\method;
/**
* Whatsapp notification method class
* This class handles sending Whatsapp messages for notifications
*/
class whatsapp extends \phpbb\notification\method\messenger_base
{
	/**
	* Get notification method name
	*
	* @return string
	*/
	public function get_type()
	{
		$this->user->add_lang_ext('tas2580/whatsapp', 'common');
		return 'notification.method.whatsapp';
	}
	/**
	* Is this method available for the user?
	* This is checked on the notifications options
	*/
	public function is_available()
	{
		return ($this->global_available() && (strlen($this->user->data['user_whatsapp']) > 2));
	}
	/**
	* Is this method available at all?
	* This is checked before notifications are sent
	*/
	public function global_available()
	{
		return !(empty($this->config['whatsapp_sender']) || empty($this->config['whatsapp_password']));
	}

	public function notify()
	{
		$template_dir_prefix = '';

		if (!$this->global_available())
		{
			return;
		}

		if (empty($this->queue))
		{
			 return;
		}

		// Load all users we want to notify (we need their email address)
		$user_ids = $users = array();
		foreach ($this->queue as $notification)
		{
			$user_ids[] = $notification->user_id;
		}

		// We do not send whatsapp to banned users
		if (!function_exists('phpbb_get_banned_user_ids'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}
		$banned_users = phpbb_get_banned_user_ids($user_ids);

		// Load all the users we need
		$this->user_loader->load_users($user_ids);

		global $config, $phpbb_container;
		$wa = $phpbb_container->get('tas2580.whatsapp.helper');

		// Time to go through the queue and send emails
		foreach ($this->queue as $notification)
		{
			if ($notification->get_email_template() === false)
			{
				continue;
			}

			$user = $this->user_loader->get_user($notification->user_id);

			if ($user['user_type'] == USER_IGNORE || in_array($notification->user_id, $banned_users))
			{
				continue;
			}

			$this->template($template_dir_prefix . $notification->get_email_template(), $user['user_lang']);
			$this->assign_vars(array_merge(array(
				'USERNAME'				=> $user['username'],
				'SITENAME'				=> htmlspecialchars_decode($config['sitename']),
				'U_NOTIFICATION_SETTINGS'	=> generate_board_url() . '/ucp.' . $this->php_ext . '?i=ucp_notifications',
			), $notification->get_email_template_variables()));

		 	$this->msg = trim($this->template->assign_display('body'));

			// Lets send the Whatsapp
			$wa->send($user['user_whatsapp'], $this->msg);
		}
		$this->empty_queue();
	}

	function template($template_file, $template_lang = '', $template_path = '')
	{
		global $config, $phpbb_root_path, $phpEx, $user, $phpbb_extension_manager;

		$this->setup_template();

		if (!trim($template_file))
		{
			trigger_error('No template file for emailing set.', E_USER_ERROR);
		}

		if (!trim($template_lang))
 		{
			// fall back to board default language if the user's language is
			// missing $template_file.  If this does not exist either,
			// $this->template->set_filenames will do a trigger_error
			$template_lang = basename($config['default_lang']);
		}

		if ($template_path)
		{
			$template_paths = array(
				$template_path,
			);
		}
		else
		{
			$template_path = (!empty($user->lang_path)) ? $user->lang_path : $phpbb_root_path . 'language/';
			$template_path .= $template_lang . '/email';

			$template_paths = array(
				$template_path,
			);

			// we can only specify default language fallback when the path is not a custom one for which we
			// do not know the default language alternative
			if ($template_lang !== basename($config['default_lang']))
			{
				$fallback_template_path = (!empty($user->lang_path)) ? $user->lang_path : $phpbb_root_path . 'language/';
				$fallback_template_path .= basename($config['default_lang']) . '/email';

				$template_paths[] = $fallback_template_path;
			}
		}

		$this->set_template_paths(array(
			array(
				'name'         => $template_lang . '_email',
				'ext_path'     => 'language/' . $template_lang . '/email'
			),
		), $template_paths);

		$this->template->set_filenames(array(
			'body'        => $template_file . '.txt',
		));

		return true;
	}

	/**
	* assign variables to email template
	*/
	function assign_vars($vars)
	{
		$this->setup_template();

		$this->template->assign_vars($vars);
	}

	/**
	* Setup template engine
	*/
	protected function setup_template()
	{
		global $config, $phpbb_path_helper, $user, $phpbb_extension_manager;

		if ($this->template instanceof \phpbb\template\template)
		{
			return;
		}

		$this->template = new \phpbb\template\twig\twig($phpbb_path_helper, $config, $user, new \phpbb\template\context(), $phpbb_extension_manager);
	}

	/**
	* Set template paths to load
	*/
	protected function set_template_paths($path_name, $paths)
	{
		$this->setup_template();

		$this->template->set_custom_style($path_name, $paths);
	}
}
