<?php
/**
*
* @author Gremlinn (Nathan DuPra) mods@dupra.net
* @package umil
* @version $Id install.php 1.0.0 2009-11-24 18:15:00Z Gremlinn $
* @copyright (c) 2009 Gremlinn
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
/**
* @package phpBB3.1 Medals System Extension
* @copyright Anvar (c) 2015 bb3.mobi
*/

namespace bb3mobi\medals\migrations;

class v_1_0_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['medals_version']) && version_compare($this->config['medals_version'], '1.0.0', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}
	/**
	* Add the medals table schema to the database:
	*
	* @return array Array of table schema
	* @access public
	*/
	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'medals'	=> array(
					'COLUMNS'		=> array(
						'id'			=> array('UINT:11', null, 'auto_increment'),
						'name'			=> array('VCHAR:30', ''),
						'image'			=> array('VCHAR:100', ''),
						'dynamic'		=> array('BOOL', 0),
						'device'		=> array('VCHAR:32', ''),
						'number'		=> array('UINT:2', 1),
						'parent'		=> array('UINT:5', 0),
						'nominated'		=> array('BOOL', 0),
						'order_id'		=> array('UINT:5', 0),
						'description'	=> array('VCHAR:256', ''),
						'points'		=> array('INT:4', 0),
					),
					'PRIMARY_KEY'	=> 'id',
					'KEYS'		=> array(
						'order_id'			=> array('INDEX', array('order_id')),
					),
				),
				$this->table_prefix . 'medals_awarded'	=> array(
					'COLUMNS'		=> array(
						'id'				=> array('UINT:10', null, 'auto_increment'),
						'medal_id'			=> array('UINT', 0),
						'user_id'			=> array('UINT', 0),
						'awarder_id'		=> array('UINT', 0),
						'awarder_un'		=> array('VCHAR:255', ''),
						'awarder_color'		=> array('VCHAR:6', ''),
						'time'				=> array('TIMESTAMP', 0),
						'nominated'			=> array('BOOL', 0),
						'nominated_reason'	=> array('TEXT', ''),
						'points'			=> array('INT:4', 0),
						'bbuid'				=> array('VCHAR:255', ''),
						'bitfield'			=> array('VCHAR:255', ''),
					),
					'PRIMARY_KEY'	=> 'id',
					'KEYS'			=> array(
						'time'			=> array('INDEX', 'time'),
					),
				),
				$this->table_prefix . 'medals_cats'	=> array(
					'COLUMNS'		=> array(
						'id'			=> array('UINT:5', null, 'auto_increment'),
						'name'			=> array('VCHAR:30', ''),
						'order_id'		=> array('UINT:5', 0),
					),
					'PRIMARY_KEY'	=> 'id',
					'KEYS'			=> array(
						'order_id'			=> array('INDEX', 'order_id'),
					),
				),
			),
			'add_columns' => array(
				$this->table_prefix . 'users' => array(
					'medal_user_points'	=> array('UINT:11', 0),
				),
			),
		);
	}
	/**
	* Drop the medals table schema from the database
	*
	* @return array Array of table schema
	* @access public
	*/
	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'medals',
				$this->table_prefix . 'medals_awarded',
				$this->table_prefix . 'medals_cats',
			),
		);
	}

	public function update_data()
	{
		return array(
			// Add configs
			array('config.add', array('medals_active', '1')),
			array('config.add', array('medal_small_img_width', '0')),
			array('config.add', array('medal_small_img_ht', '0')),
			array('config.add', array('medal_profile_across', '5')),
			array('config.add', array('medal_display_topic', '0')),
			array('config.add', array('medal_topic_row', '1')),
			array('config.add', array('medal_topic_col', '1')),
			// Current version
			array('config.add', array('medals_version', '1.0.0')),

			/* phpBB3.0 Migrate - Remove old config version */
			array('if', array(
				(isset($this->config['medals_mod_version'])),
				array('config.remove', array('medals_mod_version')),
			)),

			array('if', array(
				array('module.exists', array('acp', false, 'Medals Control Panel')),
				array('module.remove', array('acp', false, 'Medals Control Panel')),
			)),

			array('if', array(
				array('module.exists', array('acp', false, 'ACP_MEDALS_INDEX')),
				array('module.remove', array('acp', false, 'ACP_MEDALS_INDEX')),
			)),

			// Add permission
			array('permission.add', array('u_award_medals', true)),
			array('permission.add', array('u_nominate_medals', true)),
			array('permission.add', array('a_manage_medals', true)),

			// Set permissions
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_award_medals')),
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_nominate_medals')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_manage_medals')),

			// Add ACP modules
			array('module.add', array('acp', 'ACP_CAT_DOT_MODS', 'ACP_MEDALS_INDEX')),

			array('module.add', array('acp', 'ACP_MEDALS_INDEX', array(
				'module_basename'	=> '\bb3mobi\medals\acp\medals_module',
				'module_langname'	=> 'ACP_MEDALS_SETTINGS',
				'module_mode'		=> 'config',
				'module_auth'		=> 'ext_bb3mobi/medals && acl_a_manage_medals',
			))),

			array('module.add', array('acp', 'ACP_MEDALS_INDEX', array(
				'module_basename'	=> '\bb3mobi\medals\acp\medals_module',
				'module_langname'	=> 'ACP_MEDALS_TITLE',
				'module_mode'		=> 'management',
				'module_auth'		=> 'ext_bb3mobi/medals && acl_a_manage_medals',
			))),

			array('custom', array(array(&$this, 'medals_cats_insert'))),
		);
	}

	/** New custom category */
	public function medals_cats_insert()
	{
		$in_ary = array(
			'name' => 'Sample',
		);
		$this->db->sql_query('INSERT INTO ' . $this->table_prefix . 'medals_cats ' . $this->db->sql_build_array('INSERT', $in_ary));
	}
}
