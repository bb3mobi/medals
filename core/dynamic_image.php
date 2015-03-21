<?php
/***************************************************************************
*
* @package Medals Mod for phpBB3
* @version $Id: dynamic_image.php,v 1.0.0 2009/10/29 Gremlinn$
* @copyright (c) 2009 Nathan DuPra (mods@dupra.net)
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
				return imagecreatefromgif($image);
			break;
			case 'jpg':
				return imagecreatefromjpeg($image);
			break;
			case 'png':
				return imagecreatefrompng($image);
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
