<?php
/*

	DoIt! CMS and VarVar framework
	Copyright (C) 2017 Fakhrutdinov Damir (aka Ainu)

	*      This program is free software; you can redistribute it and/or modify
	*      it under the terms of the GNU General Public License as published by
	*      the Free Software Foundation; either version 2 of the License, or
	*      (at your option) any later version.
	*
	*      This program is distributed in the hope that it will be useful,
	*      but WITHOUT ANY WARRANTY; without even the implied warranty of
	*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	*      GNU General Public License for more details.
	*
	*      You should have received a copy of the GNU General Public License
	*      along with this program; if not, write to the Free Software
	*      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
	*      MA 02110-1301, USA.
 
*/
class Watermark_creator_universal{

	function create( $main_img_obj, $watermark_img_obj, $alpha_level = 100, $position="center" ) {
		imagealphablending($main_img_obj, true);
		imagesavealpha($main_img_obj, true);
		if($alpha_level != 100){
			$transparency = 1 - $alpha_level/100;
			imagefilter($watermark_img_obj, IMG_FILTER_COLORIZE, 0,0,0,127*$transparency);
		}

		$p1='center';
		$p2='center';
		
		if     ($position == 'top-left' || $position == 'left-top') {
			$p1='left';
			$p2='top';
		}elseif($position == 'top-right' || $position == 'right-top'){
			$p1='right';
			$p2='top';
		}elseif($position == 'top-center' || $position == 'center-top'){
			$p1='center';
			$p2='top';
		}elseif($position == 'center-left' || $position == 'left-center'){
			$p1='left';
			$p2='center';
		}elseif($position == 'center-right' || $position == 'right-center'){
			$p1='right';
			$p2='center';
		}elseif($position == 'center' || $position == 'center-center'){
			$p1='center';
			$p2='center';
		}elseif($position == 'bottom-left' || $position == 'left-bottom'){
			$p1='left';
			$p2='bottom';
		}elseif($position == 'bottom-right' || $position == 'right-bottom'){
			$p1='right';
			$p2='bottom';
		}elseif($position == 'bottom-center' || $position == 'center-bottom'){
			$p1='center';
			$p2='bottom';
		}
		
		$water_width = imagesx($watermark_img_obj);
		$water_height = imagesy($watermark_img_obj);
		$image_width = imagesx($main_img_obj);
		$image_height = imagesy($main_img_obj);		
		if($position == 'repeat'){
			for ($x = 0; $x<=$image_width; $x += $water_width){
				for ($y = 0; $y<=$image_width; $y += $water_width){
					$water_position_x = $x;
					$water_position_y = $y;
					imagecopy($main_img_obj, $watermark_img_obj, $water_position_x, $water_position_y , $water_part_x, $water_part_y, $water_width, $water_height);
				}				
			}
		}else {
			//Начиная с какого куска водяного знака брать данные.
			$water_part_x = 0;
			$water_part_y = 0;

			//Расположение водяного знака на итоговой картинке
			if($p1 == 'left'){
				$water_position_x = 0;
			}elseif($p1=='right'){
				$water_position_x =  round($image_width  - $water_width  );
			}else{
				$water_position_x =  round($image_width / 2 - $water_width /2);
			}
			if($p2 == 'top'){
				$water_position_y = 0;
			}elseif($p2=='bottom'){
				$water_position_y =  round($image_height  - $water_height  );
			}else{
				$water_position_y =  round($image_height / 2 - $water_height /2);
			}
			imagecopy($main_img_obj, $watermark_img_obj, $water_position_x, $water_position_y , $water_part_x, $water_part_y, $water_width, $water_height);
		}

		return $main_img_obj;
	}

	 

}