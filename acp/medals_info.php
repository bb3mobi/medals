<?php
/***************************************************************************
*
* @package Medals Mod for phpBB3
* @version $Id: medals.php,v 0.9.1 2008/02/19 Gremlinn$
* @copyright (c) 2008 Nathan DuPra (mods@dupra.net)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
***************************************************************************/

/***************************************************************************
*
* @package Medals System for phpBB3.1 Extension
* @version: v 1.0.0 2015/02/07 Anvar (apwa.ru)
* @copyright Anvar 2015 (c) bb3.mobi
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
***************************************************************************/

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
