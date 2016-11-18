<?php
/**
*
* @author Gremlinn (Nathan DuPra) mods@dupra.net | Anvar Stybaev (DEV Extension phpBB3.1.x)
* @package Medals System Extension
* @copyright Anvar 2015 (c) Extensions bb3.mobi
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace bb3mobi\medals\core;

class medals_viewtopic
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $helper;

	public function __construct(\phpbb\config\config $config, $helper)
	{
		$this->config = $config;
		$this->helper = $helper;
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
					$image = $this->helper->route('bb3mobi_medals_controller', array('m' => 'mi', 'med' => generate_board_url() . '/images/medals/' . $image, 'd' => $device));
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
		return $img;
	}
}
