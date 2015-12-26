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

class medals_viewtopic
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	protected $phpbb_root_path;

	public function __construct(\phpbb\config\config $config, \phpbb\template\template $template, $phpbb_root_path)
	{
		$this->config = $config;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	public function medal_row($rowset2)
	{
		$medal_width	= ($this->config['medal_small_img_width']) ? ' width="' . $this->config['medal_small_img_width'] . '"' : '';
		$medal_height	= ($this->config['medal_small_img_ht']) ? ' height="' . $this->config['medal_small_img_ht'] . '"' : '';
		$medal_rows		= ($this->config['medal_topic_col']) ? $this->config['medal_topic_col'] : 1;
		$medal_cols		= ($this->config['medal_topic_row']) ? $this->config['medal_topic_row'] : 1;

		$split_row = $medal_cols - 1;

		$s_colspan = 0;
		$row = 0;
		$col = 0;
		$img = '';
		while (list($image, $medal) = @each($rowset2))
		{
			if (!$col)
			{
				$img .= '<br />';
			}

			if ($medal['count'] > 1)
			{
				if ($medal['dynamic'])
				{
					$device = generate_board_url() . '/images/medals/devices/' . $medal['device'] . '-' . ($medal['count'] - 1) . '.gif' ;
					$image = generate_board_url() . '/medals.php?m=mi&med=' . generate_board_url() . '/images/medals/' . $image . '&' . 'd=' . $device ;
					// $image = generate_board_url() . '/images/medals/' . $image ;
				}
				else
				{
					$cluster = '-' . $medal['count'] ;
					$device_image = substr_replace($image,$cluster, -4) . substr($image, -4) ;
					if ( file_exists($device_image) )
					{
						$image = $device_image ;
					}
					$image = generate_board_url() . '/images/medals/' . $image ;
				}
			}
			else
			{
				$image = generate_board_url() . '/images/medals/' . $image ;
			}

			$img .= '<img src="' . $image . '" alt="' . $medal['name'] . '" title="' . $medal['name'] . ' (' . $medal['count']. ')"' . $medal_width . $medal_height . ' /> ';

			$s_colspan = max($s_colspan, $col + 1);
			if ($col == $split_row)
			{
				if ($row == $medal_rows - 1)
				{
					break;
				}
				$col = 0;
				$row++;
			}
			else
			{
				$col++;
			}
		}
		return array(
			'PROFILE_FIELD_IDENT' => 'medals',
			'PROFILE_FIELD_NAME' => '',
			'PROFILE_FIELD_VALUE' => $img,
			'S_PROFILE_CONTACT' => false,
		);
	}
}
