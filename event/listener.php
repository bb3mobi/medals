<?php
/**
* @package Medals System Extension for phpBB3
* @author Anvar [http://bb3.mobi]
* @version v1.0.0, 2015/02/11
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

namespace bb3mobi\medals\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	public function __construct(\phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\template\template $template, \phpbb\controller\helper $helper, \phpbb\db\driver\driver_interface $db, $tb_medals, $tb_medals_awarded, $tb_medals_cats, $memberlist, $viewtopic)
	{
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->template = $template;
		$this->helper = $helper;
		$this->db = $db;
		$this->tb_medal = $tb_medals;
		$this->tb_medals_awarded = $tb_medals_awarded;
		$this->tb_medals_cats = $tb_medals_cats;
		$this->memberlist = $memberlist;
		$this->viewtopic = $viewtopic;

		$user->add_lang_ext('bb3mobi/medals', 'info_medals_mod');
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header_after'			=> 'medals_enable_link',
			'core.memberlist_view_profile'		=> 'memberlist_view_profile',
			'core.viewtopic_modify_post_data'	=> 'viewtopic_modify_post_data',
			'core.viewtopic_modify_post_row'	=> 'viewtopic_post_row_after',
			'core.permissions'					=> 'add_permission',
		);
	}

	public function medals_enable_link($event)
	{
		$this->template->assign_vars(array(
			'U_MEDALS'	=> $this->helper->route('bb3mobi_medals_controller'),
			'S_MEDALS'	=> ($this->config['medals_active']) ? true : false,
			)
		);
	}

	public function memberlist_view_profile($event)
	{
		$member = $event['member'];
		$user_id = (int) $member['user_id'];
		$this->memberlist->medal_row($user_id);
	}

	public function viewtopic_modify_post_data($event)
	{
		$user_ids = array();
		$rowset = $event['rowset'];
		$post_list = $event['post_list'];
		// Получаем список пользователей
		for ($i = 0, $end = sizeof($post_list); $i < $end; ++$i)
		{
			if (!isset($rowset[$post_list[$i]]))
			{
				continue;
			}
			$row = $rowset[$post_list[$i]];
			$poster_id = $row['user_id'];
			// Исключаем гостей, скрытые/игнорируемые сообщения и повтор.
			if ($poster_id != ANONYMOUS && !$row['foe'] && !$row['hide_post'] && !in_array($poster_id, $user_ids))
			{
				$user_ids[] = $poster_id;

				$this->medals_count[$poster_id] = 0;
				$this->nominated_medals[$poster_id] = false;
			}
			unset($rowset[$post_list[$i]]);
		}

		// Medals count and nominated
		if (sizeof($user_ids))
		{
			$sql = "SELECT user_id, nominated
				FROM " . $this->tb_medals_awarded . "
				WHERE " . $this->db->sql_in_set('user_id', $user_ids);
			$m_result = $this->db->sql_query($sql);
			$has_perms = $this->user->data['user_type'] == USER_FOUNDER || $this->auth->acl_get('u_award_medals');
			while ($m_row = $this->db->sql_fetchrow($m_result))
			{
				if ($has_perms && $m_row['nominated'])
				{
					$this->nominated_medals[$m_row['user_id']] = true;
				}
				else if (!$m_row['nominated'])
				{
					$this->medals_count[$m_row['user_id']]++;
				}
			}
			$this->db->sql_freeresult($m_result);
		}
	}

	public function viewtopic_post_row_after($event)
	{
		$row = $event['row'];
		$poster_id = $row['user_id'];
		if (isset($this->medals_count[$poster_id]))
		{
			$nominated_medals = (isset($this->nominated_medals[$poster_id])) ? $this->nominated_medals[$poster_id] : '';
			if ($nominated_medals)
			{
				$u_is_nominated = $this->helper->route('bb3mobi_medals_controller', array('m' => 'validate', 'u' => $poster_id));
				$nominated_medals = sprintf($this->user->lang['USER_IS_NOMINATED'], $u_is_nominated);
			}

			$medals_count = $this->medals_count[$poster_id];
			$event['post_row'] = array_merge($event['post_row'], array(
					'MEDALS_COUNT'		=> $medals_count,
					'MEDALS_NOMINATED'	=> $nominated_medals,
					'S_HAS_MEDALS'		=> ($medals_count) ? true : false,
					'S_HAS_NOMINATIONS'	=> ($nominated_medals) ? true : false,
				)
			);

			if ($this->config['medal_display_topic'] && $medals_count)
			{
				$sql = "SELECT m.id, m.name, m.image, m.device, m.dynamic, m.parent, ma.time, c.id as cat_id, c.name as cat_name
					FROM " . $this->tb_medal . " m, " . $this->tb_medals_awarded . " ma, " . $this->tb_medals_cats . " c
						WHERE ma.user_id = '" . $poster_id . "'
							AND m.parent = c.id
							AND m.id = ma.medal_id
							AND ma.nominated <> 1
					ORDER BY c.order_id, m.order_id, ma.time";
				if ($result = $this->db->sql_query($sql))
				{
					$rowset2 = array();
					while ($row = $this->db->sql_fetchrow($result))
					{
						$rowset2[$row['image']]['name'] = $row['name'];
						if ($rowset2[$row['image']]['name'] == $row['name'])
						{
							if (isset($rowset2[$row['image']]['count']))
							{
								$rowset2[$row['image']]['count'] += '1';
							}
							else
							{
								$rowset2[$row['image']]['count'] = '1';
							}
						}
						$rowset2[$row['image']]['dynamic'] = $row['dynamic'];
						$rowset2[$row['image']]['device'] = $row['device'];
					}

					if ($medals = $this->viewtopic->medal_row($rowset2))
					{
						$cp_row = $event['cp_row'];
						if (isset($cp_row['blockrow']) && is_array($cp_row['blockrow']))
						{
							$cp_row['blockrow'] = array_merge((array) $cp_row['blockrow'], array($medals));
							$event['cp_row'] = $cp_row;
						}
					}
				}
			}
		}
	}

	/**
	 * Add permissions
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function add_permission($event)
	{
		$permissions = $event['permissions'];
		$permissions['u_award_medals'] = array('lang' => 'ACL_U_AWARD_MEDALS', 'cat' => 'misc');
		$permissions['u_nominate_medals'] = array('lang' => 'ACL_U_NOMINATE_MEDALS', 'cat' => 'misc');
		$permissions['a_manage_medals'] = array('lang' => 'ACL_A_MANAGE_MEDALS', 'cat' => 'misc');
		$event['permissions'] = $permissions;
	}
}
