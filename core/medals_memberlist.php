<?php
/***************************************************************************
*
* @package Medals Mod for phpBB3
* @version $Id: medals.php,v 0.7.0 2008/01/23 Gremlinn$
* @copyright (c) 2008 Nathan DuPra (mods@dupra.net)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
***************************************************************************/
/**
* @package Medals System Extension for phpBB3
* @author Anvar [http://bb3.mobi]
* @version v1.0.0, 2015/02/11
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

namespace bb3mobi\medals\core;

class medals_memberlist
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	protected $phpbb_root_path;

	/** @var \phpbb\controller\helper */
	protected $helper;

	public function __construct(\phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\template\template $template, \phpbb\db\driver\driver_interface $db, $tb_medals, $tb_medals_awarded, $tb_medals_cats, $phpbb_root_path, $helper)
	{
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->template = $template;
		$this->db = $db;
		$this->tb_medal = $tb_medals;
		$this->tb_medals_awarded = $tb_medals_awarded;
		$this->tb_medals_cats = $tb_medals_cats;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->helper = $helper;

		$this->user->add_lang_ext('bb3mobi/medals', 'info_medals_mod');
	}

	public function medal_row($user_id)
	{
		$s_nominate = false;

		if ($this->auth->acl_get('u_nominate_medals') && $user_id != $this->user->data['user_id'])
		{
			$s_nominate = true;
		}

		$is_mod = ($this->user->data['user_type'] == USER_FOUNDER || $this->auth->acl_get('u_award_medals')) ? true : false;

		$uid			= $bitfield			= '';	// will be modified by generate_text_for_storage
		$allow_bbcode	= $allow_smilies	= true;
		$allow_urls		= false;
		$m_flags = '3';  // 1 is bbcode, 2 is smiles, 4 is urls (add together to turn on more than one)
		//
		// Category
		//

		$sql = "SELECT id, name
			FROM " . $this->tb_medals_cats . "
			ORDER BY order_id";
		if( !($result = $this->db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not query medal categories list', '', __LINE__, __FILE__, $sql);
		}

		$category_rows = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$category_rows[] = $row;
		}
		$this->db->sql_freeresult($result);

		$sql = "SELECT m.medal_id, m.user_id
			FROM " . $this->tb_medals_awarded . " m
			WHERE m.user_id = {$user_id}
				AND m.nominated = 0";

		if ($result = $this->db->sql_query($sql))
		{
			$medal_list = $this->db->sql_fetchrowset($result);
			$medal_count = count($medal_list);

			if ($medal_count)
			{
				$this->template->assign_block_vars('switch_display_medal', array());

				$this->template->assign_block_vars('switch_display_medal.medal', array(
					'MEDAL_BUTTON' => '<input type="button" class="button2" onclick="hdr_toggle(\'toggle_medal\',\'medal_open_close\')" value="' . $this->user->lang['MEDALS_VIEW_BUTTON'] . '"/>'
				));
			}
		}

		$u_nominate = '';
		if ($s_nominate)
		{
			$u_nominate = $this->helper->route('bb3mobi_medals_controller', array('m' => 'nominate', 'u' => $user_id));
		}

		$u_can_award = '';
		if ($this->auth->acl_get('a_user') || $is_mod)
		{
			$u_can_award = $this->helper->route('bb3mobi_medals_controller', array('m' => 'award', 'u' => $user_id));
		}

		$this->template->assign_vars(array(
			'USER_ID'				=> $user_id,
			'U_NOMINATE'			=> $u_nominate,
			'U_CAN_AWARD_MEDALS'	=> $u_can_award,
			'L_USER_MEDAL'			=> $this->user->lang['MEDALS'],
			'USER_MEDAL_COUNT'		=> $medal_count,
			'L_MEDAL_INFORMATION'	=> $this->user->lang['MEDAL_INFORMATION'],
			'L_MEDAL_NAME'			=> $this->user->lang['MEDAL'],
			'L_MEDAL_DETAIL'		=> $this->user->lang['MEDAL_DETAIL'],
		));

		for ($i = 0; $i < count($category_rows); $i++)
		{
			$cat_id = $category_rows[$i]['id'];

			$sql = "SELECT m.id, m.name, m.description, m.image, m.device, m.dynamic, m.parent,
						ma.nominated_reason, ma.time, ma.awarder_id, ma.awarder_un, ma.awarder_color, ma.bbuid, ma.bitfield,
						c.id as cat_id, c.name as cat_name
					FROM " . $this->tb_medal . " m, " . $this->tb_medals_awarded . " ma, " . $this->tb_medals_cats . " c
					WHERE ma.user_id = {$user_id}
						AND m.parent = c.id
						AND m.id = ma.medal_id
						AND ma.nominated = 0
					ORDER BY c.order_id, m.order_id, ma.time";

			if ($result = $this->db->sql_query($sql))
			{
				$row = array();
				$rowset = array();
				$medal_time = $this->user->lang['AWARD_TIME'] . ':&nbsp;';
				$medal_reason = $this->user->lang['MEDAL_AWARD_REASON'] . ':&nbsp;';
				while ($row = $this->db->sql_fetchrow($result))
				{
					if (empty($rowset[$row['name']]))
					{
						$rowset[$row['name']]['cat_id'] = $row['cat_id'];
						$rowset[$row['name']]['cat_name'] = $row['cat_name'];
						if (isset($rowset[$row['name']]['description']))
						{
							$rowset[$row['name']]['description'] .= $row['description'];
						}
						else
						{
							$rowset[$row['name']]['description'] = $row['description'];
						}
						$rowset[$row['name']]['image'] = generate_board_url() . '/images/medals/' . $row['image'];
						$rowset[$row['name']]['device'] = generate_board_url() . '/images/medals/devices/' . $row['device'];
						$rowset[$row['name']]['dynamic'] = $row['dynamic'];
					}
					$row['nominated_reason'] = ($row['nominated_reason']) ? $row['nominated_reason'] : 'Medal_no_reason';
					$awarder_name = "";
					if ($row['awarder_id'])
					{
						$awarder_name = "<br />" . $this->user->lang['AWARDED_BY'] . ": " . get_username_string('full', $row['awarder_id'], $row['awarder_un'], $row['awarder_color'], $row['awarder_un']) ;
					}
					//generate_text_for_storage($row['nominated_reason'], $uid, $bitfield, $m_flags, $allow_bbcode, $allow_urls, $allow_smilies);
					$reason = generate_text_for_display($row['nominated_reason'], $row['bbuid'], $row['bitfield'], $m_flags);
					if (isset($rowset[$row['name']]['medal_issue']))
					{
						$rowset[$row['name']]['medal_issue'] .= $medal_time . $this->user->format_date($row['time']) . $awarder_name . '</td></tr><tr><td>' . $medal_reason . '<div class="content">' . $reason . '</div><hr />';
					}
					else
					{
						$rowset[$row['name']]['medal_issue'] = $medal_time . $this->user->format_date($row['time']) . $awarder_name . '</td></tr><tr><td>' . $medal_reason . '<div class="content">' . $reason . '</div><hr />';
					}
					if (isset($rowset[$row['name']]['medal_count']))
					{
						$rowset[$row['name']]['medal_count'] += '1';
					}
					else
					{
						$rowset[$row['name']]['medal_count'] = '1';
					}
				}

				$medal_width = ($this->config['medal_small_img_width']) ? ' width="'.$this->config['medal_small_img_width'].'"' : '';
				$medal_height = ($this->config['medal_small_img_ht']) ? ' height="'.$this->config['medal_small_img_ht'].'"' : '';

				$medal_name = array();
				$data = array();

				//
				// Should we display this category/medal set?
				//
				$display_medal = 0;
				$numberofmedals = 0;
				$after_first_cat = 0;
				$newcat = 1;

				while (list($medal_name, $data) = @each($rowset))
				{
					if ($cat_id == $data['cat_id'])
					{
						$display_medal = 1;
					}

					$display_across = $this->config['medal_profile_across'] ? $this->config['medal_profile_across'] : 5 ;
					if ($numberofmedals == $display_across)
					{
						$break = '<br />' ;
						$numberofmedals = 0 ;
					}
					else
					{
						$break = '' ;
					}

					if (!empty($newcat) && !empty($after_first_cat))
					{
						$break = '<hr />&nbsp;' ;
						$numberofmedals = 0 ;
					}

					$numberofmedals++ ;
					if (!empty($display_medal))
					{
						if ($data['medal_count'] > 1)
						{
							if ($data['dynamic'])
							{
								$img_medals = $this->helper->route('bb3mobi_medals_controller', array(
										'm' => 'mi',
										'med' => $data['image'],
										'd' => $data['device'] . '-' . ($data['medal_count'] - 1) . '.gif'
									)
								);

								$image = '<img src="' . $img_medals . '" alt="' . $medal_name . '" title="' . $medal_name . '" />' ;
								$small_image = $break . '<img src="' . $img_medals . '" alt="' . $medal_name . '" title="' . $medal_name . '"' . $medal_width . $medal_height . ' />' ;
							}
							else
							{
								$cluster = '-' . $data['medal_count'] ;
								$device_image = substr_replace($data['image'],$cluster, -4) . substr($data['image'], -4);
								if (file_exists($device_image))
								{
									$data['image'] = $device_image;
								}
								$image = '<img src="' . $data['image'] . '" alt="' . $medal_name . '" title="' . $medal_name . '" />';
								$small_image = $break . '<img src="' . $data['image'] . '" alt="' . $medal_name . '" title="' . $medal_name . '"' . $medal_width . $medal_height . ' />';
							}
						}
						else
						{
							$image = '<img src="' . $data['image'] . '" alt="' . $medal_name . '" title="' . $medal_name . '" />';
							$small_image = $break . '<img src="' . $data['image'] . '" alt="' . $medal_name . '" title="' . $medal_name . '"' . $medal_width . $medal_height . ' />';
						}

						$this->template->assign_block_vars('switch_display_medal.details', array(
							'ISMEDAL_CAT' 		=> $newcat,
							'MEDAL_CAT' 		=> $data['cat_name'],
							'MEDAL_NAME' 		=> $medal_name,
							'MEDAL_DESCRIPTION' => $data['description'],
							'MEDAL_IMAGE' 		=> $image,
							'MEDAL_IMAGE_SMALL' => $small_image,
							'MEDAL_ISSUE' 		=> $data['medal_issue'],
							'MEDAL_COUNT' 		=> $this->user->lang['MEDAL_AMOUNT'] . ': ' . $data['medal_count'],
						));
						$display_medal = 0;
						$newcat = 0 ;
					}
					else
					{
						// New category lets put an hr between
						$newcat = 1 ;
						$after_first_cat = 1;
					}
				}
			}
		}
	}
}
