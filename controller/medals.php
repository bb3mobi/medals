<?php
/**
*
* @author Gremlinn (Nathan DuPra) mods@dupra.net | Anvar Stybaev (DEV Extension phpBB3.1.x)
* @package Medals System Extension
* @copyright Anvar 2015 (c) Extensions bb3.mobi
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bb3mobi\medals\controller;

/**
* Main controller
*/
class medals
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	protected $phpbb_root_path;
	protected $php_ext;

	public function __construct(\phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\controller\helper $helper, \phpbb\db\driver\driver_interface $db, $tb_medals, $tb_medals_awarded, $tb_medals_cats, $dynamic, $phpbb_root_path, $php_ext)
	{
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->request = $request;
		$this->template = $template;
		$this->helper = $helper;
		$this->db = $db;
		$this->tb_medal = $tb_medals;
		$this->tb_medals_awarded = $tb_medals_awarded;
		$this->tb_medals_cats = $tb_medals_cats;
		$this->dynamic = $dynamic;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$user->add_lang_ext('bb3mobi/medals', 'info_medals_mod');
		$user->add_lang('posting');

		// will be modified by generate_text_for_storage
		$this->uid = '';
		$this->bitfield = '';
		$this->allow_bbcode = true;
		$this->allow_smilies = true;
		$this->allow_urls = false;
		$this->m_flags = '3'; // 1 is bbcode, 2 is smiles, 4 is urls (add together to turn on more than one)

		$this->points_enable = (isset($this->config['points_enable'])) ? $this->config['points_enable'] : 0;
	}

	public function medals_system()
	{
		if (!$this->config['medals_active'])
		{
			$url = append_sid($this->phpbb_root_path . 'index.' . $this->php_ext);
			$message = "This extension is not active. <br /><br />Click <a href=\"$url\">here</a> to return to the index.<br />";
			trigger_error($message);
		}

		// Gather post and get variables
		$mode		= $this->request->variable('m', '');
		$from		= $this->request->variable('f', '');
		$user_id	= $this->request->variable('u', 0);
		$usernames	= $this->request->variable('add', '', true);
		$medal_id	= $this->request->variable('mid', 0);
		$med_id		= $this->request->variable('med', 0);
		$submit		= $this->request->is_set_post('submit');
		$catchoice	= $this->request->variable('cat', $this->getfirstcat());

		// Dynamic Medal Image creation
		if ($mode == "mi")
		{
			$medal	= $this->request->variable('med', '');
			$device	= $this->request->variable('d', '');
			$this->dynamic->create_dynamic_image($medal, $device);
			exit;
		}

		$phpbb_root_path = $this->phpbb_root_path;
		$phpEx = $this->php_ext;
		$medals_path = generate_board_url() . '/images/medals';

		include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

		$medals = array();

		$sql = "SELECT *
			FROM " . $this->tb_medal . "
			ORDER BY order_id ASC";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$medals[$row['id']] = array(
				'name' 			=> $row['name'],
				'image'	 		=> $medals_path . '/' . $row['image'],
				'device' 		=> $medals_path . '/devices/' . $row['device'],
				'dynamic'		=> $row['dynamic'],
				'parent' 		=> $row['parent'],
				'id'			=> $row['id'],
				'number'		=> $row['number'],
				'nominated'		=> $row['nominated'],
				'order_id'		=> $row['order_id'],
				'description'	=> $row['description'],
				'points'		=> $row['points'],
			);
		}
		$this->db->sql_freeresult($result);

		$sql = "SELECT *
			FROM " . $this->tb_medals_cats . "
			ORDER BY order_id ASC";
		$result = $this->db->sql_query($sql);
		$cats = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$cats[$row['id']] = array(
				'name' 		=> $row['name'],
				'id'		=> $row['id'],
				'order_id'	=> $row['order_id'],
			);
			$this->template->assign_block_vars('catlinkrow', array(
				'U_CATPAGE'		=> $this->helper->route('bb3mobi_medals_controller', array('cat' => $row['id'])),
				'MEDAL_CAT'		=> $row['name'],
			));
		}
		$this->db->sql_freeresult($result);

		generate_smilies('inline', 0);
		$this->template->assign_vars(array(
			'S_CAN_AWARD_MEDALS' => ($this->user->data['user_type'] == USER_FOUNDER || $this->auth->acl_get('u_award_medals')) ? true : false,
			'S_CAN_NOMINATE_MEDALS'	=> ($this->auth->acl_get('u_nominate_medals') && $user_id != $this->user->data['user_id']) ? true : false,
			'U_NOMINATE_PANEL'		=> $this->helper->route('bb3mobi_medals_controller', array('m' => 'nominate', 'u' => $user_id)),
			'U_AWARD_PANEL'			=> $this->helper->route('bb3mobi_medals_controller', array('m' => 'award', 'u' => $user_id)),
			'U_VALIDATE_PANEL'		=> $this->helper->route('bb3mobi_medals_controller', array('m' => 'validate', 'u' => $user_id)),
			'U_AWARDED_PANEL' 		=> $this->helper->route('bb3mobi_medals_controller', array('m' => 'awarded', 'u' => $user_id)),
		));

		switch ($mode)
		{
			case 'nominate':
				if ( $this->user->data['user_id'] == ANONYMOUS || !$this->auth->acl_get('u_nominate_medals') )
				{
					trigger_error($this->user->lang['NO_GOOD_PERMS']);
				}
				if ( $user_id == 0 || $user_id == ANONYMOUS )
				{
					trigger_error('NO_USER_ID');
				}
				if ( $user_id == $this->user->data['user_id'] )
				{
					trigger_error('NOT_SELF');
				}
				$sql = "SELECT *
						FROM " . $this->tb_medals_awarded . "
						WHERE user_id = {$user_id}
						ORDER BY medal_id AND nominated";
				$result = $this->db->sql_query($sql);
				$my_medals = array();
				while ($row = $this->db->sql_fetchrow($result))
				{
					$awarded_by_me = (isset($my_medals[$row['medal_id']]['awarded_by_me']) && $row['nominated'] == 1) ? $my_medals[$row['medal_id']]['awarded_by_me'] : 0;
					$row['awarded_by_me'] = ($this->user->data['user_id'] == $row['awarder_id'] && $awarded_by_me == 0 && $row['nominated'] == 1) ? 1 : $awarded_by_me;
					$my_medals[$row['medal_id']] = $row;
				}
				$this->db->sql_freeresult($result);

				$sql = "SELECT user_id, username, user_colour
					FROM " . USERS_TABLE . "
					WHERE user_id = {$user_id}";
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$username = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], $row['username']);

				$medals_options = '<option value=""></option>';
				$temp_string = '';
				$i = 0;
				foreach ($cats as $key => $value)
				{
					$at_least_one = false;
					foreach ($medals as $key2 => $value2)
					{
						if ($value2['parent'] == $value['id'])
						{
							$can_award = false;

							$my_medals[$value2['id']]['awarded_by_me'] = isset($my_medals[$value2['id']]['awarded_by_me']) ? $my_medals[$value2['id']]['awarded_by_me'] : 0;
							if ( $value2['nominated'] == 1 && $my_medals[$value2['id']]['awarded_by_me'] == 0 )
							{
								$temp_string .= '<option value="' . $value2['id'] . '">&bull;&nbsp;' . $value2['name'] . '</option>';
								$at_least_one = true;
							}
						}
					}
					if ($at_least_one)
					{
						$medals_options .= '<option value="">' . $value['name'] . '</option>';
						$medals_options .= $temp_string;
						$at_least_one = false;
						$temp_string = '';
						$i++;
					}
				}
				if ($i == 0)
				{
					trigger_error(sprintf($this->user->lang['NO_MEDALS_TO_NOMINATE'], append_sid('memberlist.php?mode=viewprofile&u=' . $user_id)));
				}

				$medals_arr = 'var medals = new Array();';
				$medals_desc_arr = 'var medals_desc = new Array();' ;
				foreach ($medals as $key => $value)
				{
					$medals_arr .= 'medals[' . $value['id'] . '] = "' . $value['image'] . '";';
					$medals_desc_arr .= 'medals_desc[' . $value['id'] . '] = "' . $value['description'] . '";';
				}
				$medals_arr .= "\n" . $medals_desc_arr . "\n" ;

				$bbcode_status	= ($this->config['allow_bbcode']) ? true : false;
				$smilies_status	= ($bbcode_status && $this->config['allow_smilies']) ? true : false;
				$img_status		= ($bbcode_status) ? true : false;
				$url_status		= ($bbcode_status && $this->config['allow_post_links']) ? true : false;
				$flash_status	= ($bbcode_status) ? true : false;
				$quote_status	= ($bbcode_status) ? true : false;
				display_custom_bbcodes();

				$this->template->assign_vars(array(
					'USERNAME'			=> $username,
					'MEDALS'			=> $medals_options,
					'JS'				=> $medals_arr,
					'U_MEDALS_ACTION'	=> $this->helper->route('bb3mobi_medals_controller', array('m' => 'submit_nomination', 'u' => $user_id)),
					'S_BBCODE_ALLOWED'	=> $bbcode_status,
					'S_BBCODE_IMG'		=> $img_status,
					'S_BBCODE_URL'		=> $url_status,
					'S_BBCODE_FLASH'	=> $flash_status,
					'S_BBCODE_QUOTE'	=> $quote_status,
				));

				page_header($this->user->lang['NOMINATE']);
				$this->template->set_filenames(array(
					'body' => '@bb3mobi_medals/medalcp_nominate.html')
				);
				page_footer();
			break;

			case 'submit_nomination':
				if ( $this->user->data['user_id'] == ANONYMOUS || !$this->auth->acl_get('u_nominate_medals') )
				{
					trigger_error($this->user->lang['NO_GOOD_PERMS']);
				}
				$medal_id = $this->request->variable('medal', 0);
				if (!$medal_id)
				{
					$redirect = $this->helper->route('bb3mobi_medals_controller', array('m' => 'nominate', 'u' => $user_id));
					meta_refresh(3, $redirect);
					trigger_error('NO_MEDAL_ID');
				}

				include_once($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);

				$this->user->add_lang('ucp');

				$message = utf8_normalize_nfc($this->request->variable('message', '', true));
				if (!strlen($message))
				{
					$return_to = $this->helper->route('bb3mobi_medals_controller', array('m' => 'nominate', 'u' => $user_id));
					trigger_error(sprintf($this->user->lang['NO_MEDAL_MSG'], $return_to));
				}
				$sql = "SELECT *
						FROM " . $this->tb_medals_awarded . "
						WHERE user_id = {$user_id} 
						AND medal_id = {$medal_id}";
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$medals[$medal_id]['number'] > 1 && !empty($row))
				{
					trigger_error(sprintf($this->user->lang['CANNOT_AWARD_MULTIPLE'], append_sid('memberlist.php?mode=viewprofile&u=' . $user_id)));
				}

				generate_text_for_storage($message, $this->uid, $this->bitfield, $this->m_flags, $this->allow_bbcode, $this->allow_urls, $this->allow_smilies);

				$sql_ary = array(
					'medal_id'			=> $medal_id,
					'user_id'			=> $user_id,
					'awarder_id'		=> $this->user->data['user_id'],
					'awarder_un'		=> $this->user->data['username'],
					'awarder_color'		=> $this->user->data['user_colour'],
					'nominated'			=> 1,
					'nominated_reason'	=> $message,
					'time'				=> time(),
					'bbuid'				=> $this->uid,
					'bitfield'			=> $this->bitfield,
				);
				$sql = 'INSERT INTO ' . $this->tb_medals_awarded . ' ' . $this->db->sql_build_array('INSERT', $sql_ary) ;
				$this->db->sql_query($sql);

				$redirect = append_sid('memberlist.php?mode=viewprofile&u=' . $user_id);
				meta_refresh(3, $redirect);
				trigger_error(sprintf($this->user->lang['MEDAL_NOMINATE_GOOD']));
			break;

			case 'award':
				if ($this->user->data['user_type'] != USER_FOUNDER && !$this->auth->acl_get('u_award_medals'))
				{
					trigger_error($this->user->lang['NO_GOOD_PERMS']);
				}
				if ($user_id == 0 || $user_id == ANONYMOUS)
				{
					trigger_error('NO_USER_ID');
				}
				$sql = "SELECT *
						FROM " . $this->tb_medals_awarded . "
						WHERE user_id = {$user_id}
						ORDER BY medal_id AND nominated";
				$result = $this->db->sql_query($sql);
				$my_medals = array();
				while ($row = $this->db->sql_fetchrow($result))
				{
					if ( isset($my_medals[$row['medal_id']]['count']) )
					{
						$row['count'] = $my_medals[$row['medal_id']]['count'] + '1';
					}
					else
					{
						$row['count'] = '1';
					}
					$my_medals[$row['medal_id']] = $row;
				}
				$this->db->sql_freeresult($result);

				$sql = "SELECT user_id, username, user_colour
					FROM " . USERS_TABLE . "
					WHERE user_id = {$user_id}";
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$username = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], $row['username']);

				$medals_options = '<option value=""></option>';
				$temp_string = '';
				$no_medals = true ;
				foreach ($cats as $key => $value)
				{
					$at_least_one = false;
					foreach ($medals as $key2 => $value2)
					{
						if ($value2['parent'] == $value['id'])
						{
							$can_award = false;
							$my_medals[$value2['id']]['count'] = isset($my_medals[$value2['id']]['count']) ? $my_medals[$value2['id']]['count'] : 0;
							if ($my_medals[$value2['id']]['count'] < $value2['number'] || $medal_id == $value2['id'])
							{
								$my_medals[$value2['id']]['nominated'] = isset($my_medals[$value2['id']]['nominated']) ? $my_medals[$value2['id']]['nominated'] : 0;
								if (isset($my_medals[$value2['id']]) && $my_medals[$value2['id']]['nominated'] == 1)
								{
									$value2['name'] .= ' ' . sprintf($this->user->lang['NOMINATED_BY'], $my_medals[$value2['id']]['awarder_un']);
								}
								else if ($value2['nominated'])
								{
									$value2['name'] .= ' ' . $this->user->lang['NOMINATABLE'];
								}
								if ($medal_id == $value2['id'])
								{
									$temp_string .= '<option value="' . $value2['id'] . '" selected="selected">&bull;&nbsp;' . $value2['name'] . '</option>';
									$sql = "SELECT *
										FROM " . $this->tb_medals_awarded . "
											WHERE id = {$med_id}";
									$result = $this->db->sql_query($sql);
									$row = $this->db->sql_fetchrow($result);
									$this->db->sql_freeresult($result);
									$message = generate_text_for_edit($row['nominated_reason'], $row['bbuid'], $this->m_flags);
									$medal_edit = "&med=$med_id" ;
								}
								else
								{
									$temp_string .= '<option value="' . $value2['id'] . '">&bull;&nbsp;' . $value2['name'] . '</option>';
								}
								$at_least_one = true;
							}
						}
					}
					if ($at_least_one)
					{
						$medals_options .= '<option value="">' . $value['name'] . '</option>';
						$medals_options .= $temp_string;
						$at_least_one = false;
						$temp_string = '';
						$no_medals = false ;
					}
				}

				$medals_arr = 'var medals = new Array();';
				$medals_desc_arr = 'var medals_desc = new Array();' ;
				foreach ($medals as $key => $value)
				{
					$medals_arr .= 'medals[' . $value['id'] . '] = "' . $value['image'] . '";';
					$medals_desc_arr .= 'medals_desc[' . $value['id'] . '] = "' . $value['description'] . '";';
				}
				$medals_arr .= "\n" . $medals_desc_arr . "\n" ;

				if ($no_medals)
				{
					$medals_options = '<option value="">' . $this->user->lang['NO_MEDALS'] . '</option>';
				}

				$bbcode_status	= ($this->config['allow_bbcode']) ? true : false;
				$smilies_status	= ($bbcode_status && $this->config['allow_smilies']) ? true : false;
				$img_status		= ($bbcode_status) ? true : false;
				$url_status		= ($bbcode_status && $this->config['allow_post_links']) ? true : false;
				$flash_status	= ($bbcode_status) ? true : false;
				$quote_status	= ($bbcode_status) ? true : false;
				display_custom_bbcodes();

				$message = isset($message['text']) ? $message['text'] : '';
				$medal_action = $this->helper->route('bb3mobi_medals_controller', array('m' => 'submit', 'u' => $user_id));

				$this->template->assign_vars(array(
					'USERNAME'				=> $username,
					'MEDALS'				=> $medals_options,
					'JS'					=> $medals_arr,

					'U_MEDALS_ACTION'		=> isset($medal_edit) ? $medal_action . $medal_edit : $medal_action,
					'MESSAGE'				=> $message,

					'S_BBCODE_ALLOWED'		=> $bbcode_status,
					'S_BBCODE_IMG'			=> $img_status,
					'S_BBCODE_URL'			=> $url_status,
					'S_BBCODE_FLASH'		=> $flash_status,
					'S_BBCODE_QUOTE'		=> $quote_status,
				));

				page_header($this->user->lang['AWARD_MEDAL']);
				$this->template->set_filenames(array(
					'body' => '@bb3mobi_medals/medalcp_award_user.html')
				);
				page_footer();
			break;

			case 'awarded':
				$sql = "SELECT user_id, username, user_colour
					FROM " . USERS_TABLE . "
					WHERE user_id = {$user_id}";
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$username = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], $row['username']);

				$sql3 = "SELECT *
						FROM " . $this->tb_medals_awarded . "
						WHERE user_id = {$user_id}
							AND nominated <> 1" ;
				$result3 = $this->db->sql_query($sql3);
				$s_medals = false;
				$users_medals = array();
				while ($row3 = $this->db->sql_fetchrow($result3))
				{
					$awarder_name = get_username_string('full', $row3['awarder_id'], $row3['awarder_un'], $row3['awarder_color'], $row3['awarder_un']) ;
					$nom_message = sprintf($this->user->lang['NOMINATE_MESSAGE'], $awarder_name, $medals[$row3['medal_id']]['name']);

					// Parse the message and subject
					$reason = generate_text_for_display($row3['nominated_reason'], $row3['bbuid'], $row3['bitfield'], $this->m_flags);
					$message = $this->user->lang['AWARDED_BY'] . ' ' . $awarder_name . ' ' . $this->user->format_date($row3['time']) . '<br \>' . $reason ;

					$this_cat = $cats[$medals[$row3['medal_id']]['parent']];
					$users_medals[$this_cat['order_id']]['name'] = $this_cat['name'];
					$users_medals[$this_cat['order_id']][$medals[$row3['medal_id']]['order_id']][] = array(
						'MEDAL_NAME'		=> $medals[$row3['medal_id']]['name'],
						'MEDAL_IMAGE'		=> '<img src="' . $medals[$row3['medal_id']]['image'] . '" title="' . $medals[$row3['medal_id']]['name'] . '" alt="' . $medals[$row3['medal_id']]['name'] . '" />',
						'MEDAL_REASON'		=> $message,
						'ID'				=> $row3['id'],
					);
					$s_medals = true;
				}
				$this->db->sql_freeresult($result3);

				$my_medals_arr = array();
				ksort($users_medals);
				foreach ($users_medals as $key => $value)
				{
					ksort($value);
					foreach ($value as $key2 => $value2)
					{
						if ($key2 != 'name')
						{
							foreach ($value2 as $key3 => $value3)
							{
								$my_medals_arr[] = array($value3, false);
							}
						}
						else
						{
							$my_medals_arr[] = array($value2, true);
						}
					}
				}

				foreach ($my_medals_arr as $key => $value)
				{
					if ($value[1])
					{
						$this->template->assign_block_vars('medals', array(
							'MEDAL_NAME'	=> $value[0],
							'IS_CAT'		=> true,
						));
					}
					else
					{
						$u_delete = $this->helper->route('bb3mobi_medals_controller', array(
							'm' => 'delete',
							'u' => $user_id,
							'med' => $value[0]['ID'])
						);
						$this->template->assign_block_vars('medals', array(
							'MEDAL_NAME'	=> $value[0]['MEDAL_NAME'],
							'MEDAL_IMAGE'	=> $value[0]['MEDAL_IMAGE'],
							'MEDAL_REASON'	=> $value[0]['MEDAL_REASON'],
							'U_DELETE'		=> $u_delete,

							'IS_CAT'		=> false,
						));
					}
				}

				$this->template->assign_vars(array(
					'USERNAME'			=> $username,
					'U_MEDALS_ACTION'	=> $this->helper->route('bb3mobi_medals_controller', array('m' => 'submit', 'u' => $user_id)),
				));

				page_header($this->user->lang['AWARDED_MEDAL_TO']);
				$this->template->set_filenames(array(
					'body' => '@bb3mobi_medals/medalcp_awarded_user.html')
				);
				page_footer();
			break;

			case 'submit':
				if ($this->user->data['user_type'] != USER_FOUNDER && !$this->auth->acl_get('u_award_medals'))
				{
					trigger_error($this->user->lang['NO_GOOD_PERMS']);
				}
				if (!$medal_id)
				{
					$redirect = $this->helper->route('bb3mobi_medals_controller', array('m' => 'award', 'u' => $user_id));
					meta_refresh(3, $redirect);
					trigger_error('NO_MEDAL_ID');
				}

				include_once($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);

				$message = utf8_normalize_nfc($this->request->variable('message', '', true));
				if (!strlen($message))
				{
					$return_to = $this->helper->route('bb3mobi_medals_controller', array('m' => 'award', 'u' => $user_id));
					trigger_error(sprintf($this->user->lang['NO_MEDAL_MSG'], $return_to));
				}

				$username = array();
				if ( sizeof($user_id) > 1 )
				{
					foreach ($this->uid as $user_id)
					{
						// Change usernames to ids
						$sql = "SELECT user_id
							FROM " . USERS_TABLE . "
							WHERE username = {$this->uid}" ;
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						$username[] = $row['user_id'] ;
					}
				}
				else
				{
					$username[] = $user_id ;
				}

				foreach ($username as $user_id)
				{
					$sql = "SELECT count(*) as count
						FROM " . $this->tb_medals_awarded . "
						WHERE medal_id = {$medal_id}
							AND user_id = {$user_id}
							AND nominated = 0" ;
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if ($row['count'] >= $medals[$medal_id]['number'])
					{
						trigger_error(sprintf($this->user->lang['CANNOT_AWARD_MULTIPLE'], append_sid('memberlist.php?mode=viewprofile&u=' . $user_id)));
					}

					// Call award_medal function
					if (isset($med_id))
					{
						$this->award_medal($medals, $medal_id, $user_id, $message, time(), $medals[$medal_id]['points'], $med_id);
					}
					else
					{
						$this->award_medal($medals, $medal_id, $user_id, $message, time(), $medals[$medal_id]['points']);
					}
				}
				$redirect = append_sid('memberlist.php?mode=viewprofile&u=' . $user_id);
				meta_refresh(3, $redirect);
				trigger_error(sprintf($this->user->lang['MEDAL_AWARD_GOOD']));
			break;

			case 'delete':
				if ($this->user->data['user_type'] != USER_FOUNDER && !$this->auth->acl_get('u_award_medals'))
				{
					trigger_error($this->user->lang['NO_GOOD_PERMS']);
				}
				if (!$med_id)
				{
					trigger_error('NO_MEDAL_ID');
				}
				if (confirm_box(true))
				{
					if ($this->points_enable == 1)
					{
						$sql = "SELECT points
							FROM " . $this->tb_medals_awarded . "
							WHERE id = {$med_id}
							LIMIT 1";
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						$sql = "UPDATE " . USERS_TABLE . " 
							SET medal_user_points = user_points - " . $row['points'] . "
							WHERE user_id = $user_id" ;
						$this->db->sql_query($sql);
					}

					$sql = "DELETE FROM " . $this->tb_medals_awarded . "
						WHERE id = {$med_id}
						LIMIT 1";
					$this->db->sql_query($sql);
					$redirect = $this->helper->route('bb3mobi_medals_controller', array('m' => 'awarded', 'u' => $user_id));
					meta_refresh(3, $redirect);
					trigger_error(sprintf($this->user->lang['MEDAL_REMOVE_GOOD']));
				}
				else
				{
					confirm_box(false, $this->user->lang['MEDAL_REMOVE_CONFIRM'], build_hidden_fields(array(
						'action'   => 'delete',
					)));
					$redirect = $this->helper->route('bb3mobi_medals_controller', array('m' => 'awarded', 'u' => $user_id));
					meta_refresh(1, $redirect);
					trigger_error(sprintf($this->user->lang['MEDAL_REMOVE_NO']));
				}
			break;

			case 'approve':
				if ($this->user->data['user_type'] != USER_FOUNDER && !$this->auth->acl_get('u_award_medals'))
				{
					trigger_error($this->user->lang['NO_GOOD_PERMS']);
				}
				if (!$med_id )
				{
					trigger_error('NO_MEDAL_ID');
				}

				$sql = "SELECT count(*) as count
						FROM " . $this->tb_medals_awarded . "
						WHERE medal_id = {$medal_id}
						  AND user_id = {$user_id}
						  AND nominated = 0" ;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row['count'] >= $medals[$medal_id]['number'])
				{
					$redirect = append_sid('memberlist.php?mode=viewprofile&u=' . $user_id);
					meta_refresh(3, $redirect);
					trigger_error(sprintf($this->user->lang['CANNOT_AWARD_MULTIPLE']));
				}

				$sql = "SELECT *
						FROM " . $this->tb_medals_awarded . "
						WHERE id = {$med_id}" ;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$message = generate_text_for_edit($row['nominated_reason'], $row['bbuid'], $this->m_flags);

				$this->award_medal($medals, $row['medal_id'], $row['user_id'], $message['text'], $row['time'], $medals[$medal_id]['points'], $row['id']) ;

				$redirect = $this->helper->route('bb3mobi_medals_controller', array('m' => 'validate', 'u' => $user_id));
				meta_refresh(3, $redirect);
				trigger_error(sprintf($this->user->lang['MEDAL_AWARD_GOOD']));
			break;

			case 'validate':
				if ($this->user->data['user_type'] != USER_FOUNDER && !$this->auth->acl_get('u_award_medals'))
				{
					trigger_error($this->user->lang['NO_GOOD_PERMS']);
				}
				$sql = 'SELECT user_id, username, user_colour
						FROM ' . USERS_TABLE . "
						WHERE user_id = {$user_id}";
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);
				$username = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], $row['username']);

				$sql = "SELECT ma.*, m.name
						FROM " . $this->tb_medals_awarded . " as ma, " . $this->tb_medal . " as m
						WHERE ma.user_id = {$user_id}
						  AND ma.medal_id = m.id
						  AND ma.nominated <> 0";
				$result = $this->db->sql_query($sql);
				$i = 0;
				while ($row = $this->db->sql_fetchrow($result))
				{
					$awarder_name = get_username_string('full', $row['awarder_id'], $row['awarder_un'], $row['awarder_color'], $row['awarder_un']) ;
					$nom_message = sprintf($this->user->lang['NOMINATE_MESSAGE'], $awarder_name, $row['name']);

					// Parse the message and subject
					$message = generate_text_for_display($row['nominated_reason'], $row['bbuid'], $row['bitfield'], $this->m_flags);
					$message = $nom_message . $message ;
					$message = censor_text($message);
					$message = str_replace("\n", '<br />', $message);

					$this->uid = $row['bbuid'];
					$this->bitfield = $row['bitfield'];

					$u_delete = $this->helper->route('bb3mobi_medals_controller', array('m' => 'delete', 'med' => $row['id'], 'u' => $user_id));
					$u_approve = $this->helper->route('bb3mobi_medals_controller', array(
						'm' => 'approve',
						'med' => $row['id'],
						'mid' => $row['medal_id'],
						'u' => $user_id)
					);
					$u_m_edit = $this->helper->route('bb3mobi_medals_controller', array(
						'm' => 'award',
						'med' => $row['id'],
						'mid' => $row['medal_id'],
						'u' => $user_id)
					);

					$this->template->assign_block_vars('nominations', array(
						'USERNAME'		=> $awarder_name,
						'REASON'		=> $message,
						'U_DELETE'		=> $u_delete,
						'U_APPROVE'		=> $u_approve,
						'U_MEDAL_EDIT'	=> $u_m_edit,
					));
					$i++;
				}
				$this->db->sql_freeresult($result);

				$this->template->assign_vars(array(
					'U_MEDALS_ACTION'		=> $this->helper->route('bb3mobi_medals_controller', array('m' => 'submit', 'u' => $user_id)),
					'NOMINATE_MEDAL'		=> sprintf($this->user->lang['NOMINATE_USER_LOG'] , $username),
					'S_ROW_COUNT'			=> $i,
				));

				page_header($this->user->lang['NOMINATE_MEDAL']);
				$this->template->set_filenames(array(
					'body' => '@bb3mobi_medals/medalcp_nominate_user.html')
				);
				page_footer();
			break;

			case 'mnd':
				if ($this->user->data['user_type'] != USER_FOUNDER && !$this->auth->acl_get('u_award_medals'))
				{
					trigger_error($this->user->lang['NO_GOOD_PERMS']);
				}
				if (!$med_id)
				{
					trigger_error('NO_MEDAL_ID');
				}
				$sql = "DELETE FROM " . $this->tb_medals_awarded . "
						WHERE medal_id = {$med_id}
							AND nominated = 1";
				$this->db->sql_query($sql);
				trigger_error(sprintf($this->user->lang['NOMINATIONS_REMOVE_GOOD'], $this->helper->route('bb3mobi_medals_controller')));
			// No break;

			case 'mn':
				if ($this->user->data['user_type'] != USER_FOUNDER && !$this->auth->acl_get('u_award_medals'))
				{
					trigger_error($this->user->lang['NO_GOOD_PERMS']);
				}

				$sql = "SELECT u.username, u.user_colour, ma.*
						FROM " . USERS_TABLE . " u, " . $this->tb_medals_awarded . " ma
						WHERE u.user_id = ma.user_id
							AND ma.nominated = 1
							AND ma.medal_id = {$med_id}
						ORDER BY u.username_clean";
				$result = $this->db->sql_query($sql);
				$users_medals = array();
				$i = 1;
				while ($row = $this->db->sql_fetchrow($result))
				{
					$awarder_name = get_username_string('full', $row['awarder_id'], $row['awarder_un'], $row['awarder_color'], $row['awarder_un']);
					$users_medals[$i] = array(
						'id'	 		=> $row['id'],
						'username'		=> $row['username'],
						'user_colour'	=> $row['user_colour'],
						'user_id'		=> $row['user_id'],
						'reason'		=> $this->user->lang['MEDAL_NOM_BY'] . ' : ' . $awarder_name . '<br />' . $row['nominated_reason'],
						'bbuid'			=> $row['bbuid'],
						'bitfield'		=> $row['bitfield'],
					);
					$i++;
				}
				$this->db->sql_freeresult($result);

				foreach ($users_medals as $key => $value)
				{
					$awarded = get_username_string('full', $value['user_id'], $value['username'], $value['user_colour']) ;

					$this->template->assign_block_vars('nominatedrow', array(
						'NOMINATED'		=> $awarded,
						'REASON'		=> generate_text_for_display($value['reason'], $value['bbuid'], $value['bitfield'], $this->m_flags),
						'U_MCP'			=> "?m=approve&med={$value['id']}&mid={$med_id}&u={$value['user_id']}",
						'U_USER_DELETE'	=> "?m=delete&med={$value['id']}&u={$value['user_id']}",
					));

					$nominated_users[$value['user_id']]['user'] = $awarded;
					$nominated_users[$value['user_id']]['count'] = isset($nominated_users[$value['user_id']]['count']) ? $nominated_users[$value['user_id']]['count'] + '1' : 1;
				}

				if (isset($nominated_users))
				{
					$i = 0;
					$nom_users = '';
					foreach ($nominated_users as $key => $value)
					{
						if ($i > 0)
						{
							$nom_users .= ", ";
						}
						$nom_users .= "{$value['user']} ({$value['count']})";
						$i++;
					}
				}

				$this->template->assign_vars(array(
						'S_MEDAL_NOM'		=> true,
						'MEDAL_NAME'		=> $medals[$med_id]['name'],
						'MEDAL_DESC'		=> $medals[$med_id]['description'],
						'MEDAL_IMG'			=> '<img src="' . $medals[$med_id]['image'] . '">',
						'MEDAL_AWARDED'		=> isset($awarded_users) ? $awarded_users : $this->user->lang['NO_MEDALS_ISSUED'],
						'NOMINATED_USERS'	=> isset($nom_users) ? $nom_users : $this->user->lang['NO_MEDALS_NOMINATED'],
						'S_DELETE_ALL'		=> isset($nom_users) ? true : false,
						'U_MEDALS_ACTION'	=> "?m={$mode}d&med=$med_id",
						'U_FIND_USERNAME'	=> append_sid($phpbb_root_path . 'memberlist.' . $phpEx, 'mode=searchuser&amp;form=post&amp;field=add'),
				));

				page_header($this->user->lang['MEDALS_VIEW']);
				$this->template->set_filenames(array(
					'body' => '@bb3mobi_medals/medals.html')
				);
				page_footer();

			break;

			case 'ma':
				if ($this->user->data['user_type'] != USER_FOUNDER && !$this->auth->acl_get('u_award_medals'))
				{
					trigger_error($this->user->lang['NO_GOOD_PERMS']);
				}
				if ($submit)
				{
					if (!$med_id)
					{
						trigger_error('NO_MEDAL_ID');
					}

					$message = utf8_normalize_nfc($this->request->variable('message', '', true));
					if (!strlen($message))
					{
						$return_to = $this->helper->route('bb3mobi_medals_controller', array('mode' => $mode, 'med' => $med_id));
						trigger_error(sprintf($this->user->lang['NO_MEDAL_MSG'], $return_to));
					}

					$usernames = explode("\n", $usernames) ;
					foreach ($usernames as $value)
					{
						$username[] = $this->db->sql_escape(utf8_clean_string($value));
					}

					$award_user = $not_award_user = $awarded_user = $no_such_user = array() ;

					// Change usernames to ids
					$sql = 'SELECT user_id, username, username_clean
							FROM ' . USERS_TABLE . '
							WHERE ' . $this->db->sql_in_set('username_clean', $username) ;
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$sql = "SELECT count(*) as number
								FROM " . $this->tb_medals_awarded . "
								WHERE medal_id = {$med_id}
									AND user_id = {$row['user_id']}" ;
						$result2 = $this->db->sql_query($sql);
						$row2 = $this->db->sql_fetchrow($result2);
						$this->db->sql_freeresult($result2);

						if ($row2['number'] < $medals[$med_id]['number'])
						{
							$award_user[] = $row['user_id'] ;
							$awarded_user[] = $row['username_clean'] ;
						}
					}
					$this->db->sql_freeresult($result);
					$not_award_user = array_diff($username, $awarded_user);
					// Call award_medal function
					$time = time() ;
					if (sizeof($award_user))
					{
						foreach ($award_user as $uid)
						{
							$this->award_medal($medals, $med_id, $uid, $message, $time, $medals[$med_id]['points']) ;
						}
					}
					if (sizeof($not_award_user))
					{
						$redirect = $this->helper->route('bb3mobi_medals_controller', array('mode' => $mode, 'med' => $med_id));
						meta_refresh(3, $redirect);
						trigger_error(sprintf($this->user->lang['NO_USER_SELECTED'], implode(", ", $not_award_user)));
					}
					else
					{
						$redirect = $this->helper->route('bb3mobi_medals_controller', array('mode' => $mode, 'med' => $med_id));
						meta_refresh(3, $redirect);
						trigger_error($this->user->lang['MEDAL_AWARD_GOOD']);
					}
				}

				$sql = "SELECT u.username, u.user_colour, ma.user_id
						FROM " . USERS_TABLE . " u, " . $this->tb_medals_awarded . " ma
						WHERE u.user_id = ma.user_id
							AND ma.nominated = 0
							AND ma.medal_id = {$med_id}
						GROUP BY ma.user_id, u.username, ma.medal_id
						ORDER BY u.username";
				$result = $this->db->sql_query($sql);
				$users_medals = array();
				$i = 1;
				while ($row = $this->db->sql_fetchrow($result))
				{
					$users_medals[$i] = array(
						'username' 		=> $row['username'],
						'user_colour' 	=> $row['user_colour'],
						'user_id'		=> $row['user_id'],
					);
					$i++;
				}
				$this->db->sql_freeresult($result);

				foreach ($users_medals as $key => $value)
				{
					$awarded = get_username_string('full', $value['user_id'], $value['username'], $value['user_colour']) ;
					$awarded_users = isset($awarded_users) ? $awarded_users . ', ' . $awarded : $awarded ;
				}
				$this->template->assign_vars(array(
						'S_MEDAL_AWARD'		=> true,
						'MEDAL_NAME'		=> $medals[$med_id]['name'],
						'MEDAL_DESC'		=> $medals[$med_id]['description'],
						'MEDAL_IMG'			=> '<img src="' . $medals[$med_id]['image'] . '">',
						'MEDAL_AWARDED'		=> isset($awarded_users) ? $awarded_users : $this->user->lang['NO_MEDALS_ISSUED'],
						'U_MEDALS_ACTION'	=> "?m=$mode&med=$med_id",
						'U_FIND_USERNAME'	=> append_sid($phpbb_root_path . 'memberlist.' . $phpEx, 'mode=searchuser&amp;form=post&amp;field=add'),
				));

				page_header($this->user->lang['MEDALS_VIEW']);
				$this->template->set_filenames(array(
					'body' => '@bb3mobi_medals/medals.html')
				);
				page_footer();

			break;

			default:
				$sql = "SELECT u.username, u.user_colour, ma.user_id, ma.medal_id, ma.nominated
						FROM " . USERS_TABLE . " u, " . $this->tb_medals_awarded . " ma
						WHERE u.user_id = ma.user_id
						GROUP BY ma.nominated, ma.user_id, u.username, ma.medal_id
						ORDER BY u.username_clean";
				$result = $this->db->sql_query($sql);
				$users_medals = array();
				$i = 1;
				while ($row = $this->db->sql_fetchrow($result))
				{
					$users_medals[$i] = array(
						'username' 		=> $row['username'],
						'user_colour' 	=> $row['user_colour'],
						'medal_id' 		=> $row['medal_id'],
						'user_id'		=> $row['user_id'],
						'nominated'		=> $row['nominated'],
					);
					$i++;
				}
				$this->db->sql_freeresult($result);

				$at_least_one_awarded = false;
				foreach ($cats as $key => $value)
				{
					$at_least_one = true;

					foreach ($medals as $key2 => $value2)
					{
						if ($value2['parent'] == $value['id'])
						{
							if ($at_least_one)
							{
								$at_least_one_awarded = true;
								$this->template->assign_block_vars('medalrow', array(
										'IS_CAT'	=> 1,
										'MEDAL_CAT'	=> $value['name'],
								));
								$at_least_one = false;
							}
							$awarded_users = '' ;
							$nominations = 0 ;
							foreach ($users_medals as $key3 => $value3)
							{
								if ($value3['medal_id'] == $value2['id'] && $value3['nominated'] == 0)
								{
									$awarded = get_username_string('full', $value3['user_id'], $value3['username'], $value3['user_colour']) ;
									$awarded_users = $awarded_users ? $awarded_users . ', ' . $awarded : $awarded ;
								}
								else if ($value3['medal_id'] == $value2['id'] && $value3['nominated'] == 1)
								{
									$nominations++ ;
								}
							}

							$u_medal_award = $this->helper->route('bb3mobi_medals_controller', array('m' => 'ma', 'med' => $value2['id']));
							$u_medal_ncp = $this->helper->route('bb3mobi_medals_controller', array('m' => 'mn', 'med' => $value2['id']));

							$this->template->assign_block_vars('medalrow', array(
									'MEDAL_NAME'			=> $value2['name'],
									'U_MEDAL_AWARD_PANEL'	=> $u_medal_award,
									'MEDAL_IMG'				=> '<img src="' . $value2['image'] . '">',
									'MEDAL_DESC'			=> $value2['description'],
									'MEDAL_AWARDED'			=> $awarded_users ? $awarded_users : $this->user->lang['NO_MEDALS_ISSUED'],
									'NOMINATIONS'			=> ($nominations > 0) ? true : false,
									'U_MEDAL_NCP'			=> $u_medal_ncp,
									'MEDAL_DESC'			=> $value2['description'],
							));
						}
					}
				}

				$this->template->assign_vars(array(
						'S_MEDAL_VIEW'	=> true,
						'NO_MEDAL'		=> $at_least_one_awarded ? 0 : 1,
				));

				page_header($this->user->lang['MEDALS_VIEW']);
				$this->template->set_filenames(array(
					'body' => '@bb3mobi_medals/medals.html')
				);
				page_footer();

			break;
		}
	}

	private function getfirstcat()
	{
		$sql = "SELECT * FROM " . $this->tb_medals_cats . "
			ORDER BY order_id ASC";
		$result = $this->db->sql_query_limit($sql, 1, 0);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$cat = $row['id'];
		}
		return $cat;
	}

	private function award_medal($medals, $medal_id, $user_id, $message, $time, $points = 0, $update = 0)
	{
		generate_text_for_storage($message, $this->uid, $this->bitfield, $this->m_flags, $this->allow_bbcode, $this->allow_urls, $this->allow_smilies);

		if ($update > 0)
		{
			$sql_ary = array(
				'medal_id'			=> $medal_id,
				'user_id'			=> $user_id,
				'nominated'			=> 0,
				'nominated_reason'	=> $message,
				'points'			=> $points,
				'time'				=> $time,
				'bitfield'			=> $this->bitfield,
				'bbuid'				=> $this->uid,
			);

			$sql = "UPDATE " . $this->tb_medals_awarded . " SET " . $this->db->sql_build_array('UPDATE', $sql_ary) . "
					WHERE id = {$update}
					LIMIT 1";
			$this->db->sql_query($sql);

			$sql = "SELECT awarder_id, awarder_un, awarder_color
					FROM " . $this->tb_medals_awarded . "
					WHERE id = {$update}
					LIMIT 1";

			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$color = $row['awarder_color'] <> "" ? '[color=#' . $row['awarder_color'] . ']' . $row['awarder_un'] . '[/color]': $row['awarder_un'] ;
		}
		else
		{
			$sql_ary = array(
				'medal_id'			=> $medal_id,
				'user_id'			=> $user_id,
				'awarder_id'		=> $this->user->data['user_id'],
				'awarder_un'		=> $this->user->data['username'],
				'awarder_color'		=> $this->user->data['user_colour'],
				'nominated'			=> 0,
				'nominated_reason'	=> $message,
				'points'			=> $points,
				'time'				=> $time,
				'bitfield'			=> $this->bitfield,
				'bbuid'				=> $this->uid,
			);

			$sql = "INSERT INTO " . $this->tb_medals_awarded . " " . $this->db->sql_build_array('INSERT', $sql_ary);

			$color = $this->user->data['user_colour'] ? '[color=#' . $this->user->data['user_colour'] . ']' . $this->user->data['username'] . '[/color]': $this->user->data['username'] ;
		}
		$result = $this->db->sql_query($sql);

		$message = generate_text_for_edit($message,$this->uid,$this->m_flags);
		$message = isset($message['text']) ? $message['text'] : '';

		if ($result && $this->points_enable == 1)
		{
			$sql = "UPDATE " . USERS_TABLE . " 
				SET medal_user_points = user_points + $points
				WHERE user_id = $user_id" ;
			$this->db->sql_query($sql);
		}

		$message2  = sprintf($this->user->lang['PM_MESSAGE'], '[img]' . $medals[$medal_id]['image'] . '[/img]', $medals[$medal_id]['name'], $color );
		$message2  .= $message;
		if ($this->points_enable == 1)
		{
			if ($points < 0)
			{
				$plural = $points < -1 ? 's' : '';
				$message2 .= sprintf($this->user->lang['PM_MESSAGE_POINTS_DEDUCT'], $points * -1, $plural);
			}
			else if ( $points > 0 )
			{
				$plural = $points > 1 ? 's' : '';
				$message2 .= sprintf($this->user->lang['PM_MESSAGE_POINTS_EARN'], $points, $plural);
			}
		}

		generate_text_for_storage($message2, $this->uid, $this->bitfield, $this->m_flags, $this->allow_bbcode, $this->allow_urls, $this->allow_smilies);
		$this->user->add_lang('ucp');
		include_once($this->phpbb_root_path . 'includes/functions_privmsgs.' . $this->php_ext);
		$pm_data = array(
			'address_list'		=> array('u' => array($user_id => 'to')),
			'from_user_id'		=> $this->user->data['user_id'],
			'from_user_ip'		=> $this->user->data['user_ip'],
			'from_username'		=> $this->user->data['username'],
			'enable_sig'		=> false,
			'enable_bbcode'		=> $this->allow_bbcode,
			'enable_smilies'	=> $this->allow_smilies,
			'enable_urls'		=> $this->allow_urls,
			'icon_id'			=> 0,
			'bbcode_bitfield'	=> $this->bitfield,
			'bbcode_uid'		=> $this->uid,
			'message'			=> $message2,
		);

		$subject = sprintf($this->user->lang['PM_MSG_SUBJECT'], $this->user->data['username']);
		submit_pm('post', $subject, $pm_data, false);

		return;
	}
}
