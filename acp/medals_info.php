<?php
/**
*
* @author Gremlinn (Nathan DuPra) mods@dupra.net | Anvar Stybaev (DEV Extension phpBB3.1.x)
* @package phpBB3.1 Medals System Extension
* @copyright Anvar 2015 (c) Extensions bb3.mobi
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bb3mobi\medals\acp;

class medals_info
{
	var $u_action;

	function module()
	{
		return array(
			'filename'		=> '\bb3mobi\medals\acp\medals_module',
			'title'			=> 'ACP_MEDALS_INDEX',
			'version'		=> '1.0.0',
			'modes'			=> array(
				'config'		=> array(
					'title' 		=> 'ACP_MEDALS_SETTINGS',
					'auth' 			=> 'ext_bb3mobi/medals && acl_a_manage_medals',
					'cat' 			=> array('ACP_MEDALS_INDEX'),
				),
				'management'	=> array(
					'title'			=> 'ACP_MEDALS_TITLE',
					'auth'			=> 'ext_bb3mobi/medals && acl_a_manage_medals',
					'cat' 			=> array('ACP_MEDALS_INDEX'),
				),
			),
		);
	}
}
