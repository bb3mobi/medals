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

class dynamic_image
{
	// Dynamic Medal Image creation
	public function create_dynamic_image($baseimg, $extraimg = '')
	{
		$image = $this->create_from_extention($baseimg);

		imagecolortransparent($image, imagecolorat($image, 0, 0));

		if (file_exists($extraimg) and $extraimg <> '')
		{
			$insert = $this->create_from_extention($extraimg);

			$image = $this->image_overlap($image, $insert);
			ImageDestroy($insert);
		}

		Header ('Content-type: image/png');
		ImagePNG ($image);
		//Clean Up
		ImageDestroy ($image);
	}

	private function create_from_extention($image)
	{
		$imageEx  = substr(strrchr($image, '.'), 1);

		switch ($imageEx)
		{
			case 'gif':
				return @imagecreatefromgif($image);
			break;
			case 'jpg':
				return @imagecreatefromjpeg($image);
			break;
			case 'png':
				return @imagecreatefrompng($image);
			break;
			default:
			exit;
		}
	}

	private function image_overlap($background, $foreground)
	{
		$insertWidth = imagesx($foreground);
		$insertHeight = imagesy($foreground);

		$imageWidth = imagesx($background);
		$imageHeight = imagesy($background);

		$overlapX = $imageWidth/2 - $insertWidth/2;
		$overlapY = $imageHeight/2 - $insertHeight/2;
		imagecolortransparent($foreground, imagecolorat($foreground, 0, 0));
		imagecopymerge($background, $foreground, $overlapX, $overlapY, 0, 0, $insertWidth, $insertHeight, 100);
		return $background;
	}
}
