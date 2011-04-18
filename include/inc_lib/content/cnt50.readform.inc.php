<?php
/*************************************************************************************
   Copyright notice
   
   (c) 2002-2011 Oliver Georgi (oliver@phpwcms.de) // All rights reserved.
 
   This script is part of PHPWCMS. The PHPWCMS web content management system is
   free software; you can redistribute it and/or modify it under the terms of
   the GNU General Public License as published by the Free Software Foundation;
   either version 2 of the License, or (at your option) any later version.
  
   The GNU General Public License can be found at http://www.gnu.org/copyleft/gpl.html
   A copy is found in the textfile GPL.txt and important notices to the license 
   from the author is found in LICENSE.txt distributed with these scripts.
  
   This script is distributed in the hope that it will be useful, but WITHOUT ANY 
   WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
   PARTICULAR PURPOSE.  See the GNU General Public License for more details.

   This copyright notice MUST APPEAR in all copies of the script!
*************************************************************************************/


// ----------------------------------------------------------------
// obligate check for phpwcms constants
if (!defined('PHPWCMS_ROOT')) {
   die("You Cannot Access This Script Directly, Have a Nice Day.");
}
// ----------------------------------------------------------------



// Content Type Reference
$content['reference']["list"] 		= isset($_POST["cimage_list"]) ? $_POST["cimage_list"] : array();
$content['reference']["width"]		= (intval($_POST["creference_width"]))  ? intval($_POST["creference_width"])  : '';
$content['reference']["height"]		= (intval($_POST["creference_height"])) ? intval($_POST["creference_height"]) : '';
$content['reference']["blockwidth"]	= (intval($_POST["creference_blockwidth"]))  ? intval($_POST["creference_blockwidth"])  : '';
$content['reference']["blockheight"]= (intval($_POST["creference_blockheight"])) ? intval($_POST["creference_blockheight"]) : '';
$temp_width							= $content['reference']["width"];
$temp_height						= $content['reference']["height"];
$content['reference']["space"]		= intval($_POST["creference_space"]);
$content['reference']["pos"]		= intval($_POST["creference_pos"]);
$content['reference']["border"]		= intval($_POST["creference_border"]);
$content['reference']["listborder"] = intval($_POST["creference_listborder"]);
$content['reference']["basis"]		= intval($_POST["creference_basis"]);
$content['reference']["caption"]	= clean_slweg($_POST["creference_caption"]);
$content['reference']["zoom"]		= isset($_POST["creference_zoom"]) ? intval($_POST["creference_zoom"]) : 0;
$content['reference']["text"] 		= html_specialchars(slweg($_POST["creference_text"]));
$content['reference']["tmpl"] 		= clean_slweg($_POST["creference_tmpl"]);

$content['reference']['showlist']	= 0;


if (is_array($content['reference']["list"]) && count($content['reference']["list"])) {
	
	$img_sql = "SELECT * FROM ".DB_PREPEND."phpwcms_file WHERE (";
	$imgx = 0;
	
	foreach($content['reference']["list"] as $key => $value) {
		unset ($content['reference']["list"][$key]);
		$content['reference']["list"][$key]['img_id'] = intval($value);
		if ($imgx) $img_sql .= " OR ";
		$img_sql .= "f_id=" . $content['reference']["list"][$key]['img_id'];
		$imgx++;
	}
	$img_sql .= ");";

	// check for image information and get alle infos from file
	if ($img_result = mysql_query($img_sql, $db) or die("error while getting content image only info")) {
		// count images
		$content['reference']["col"] = mysql_num_rows($img_result);

		if ($content['reference']["width"] > $phpwcms["content_width"] || $content['reference']["width"] == '') {
			$content['reference']["width"] = $phpwcms["content_width"];
		}
			
		$temp_width = $content['reference']["width"] - (2 * $content['reference']["border"]);
		$temp_height = $content['reference']["height"] - (2 * $content['reference']["border"]);
			
		while ($img_row = mysql_fetch_assoc($img_result)) {
			foreach($content['reference']["list"] as $key => $value) {
				if ($content['reference']["list"][$key]['img_id'] == $img_row['f_id']) {
	
					$content['reference']["list"][$key][0]	= $img_row['f_id'];
					$content['reference']["list"][$key][1]	= $img_row['f_name'];
					$content['reference']["list"][$key][2]	= $img_row['f_hash'];
					$content['reference']["list"][$key][3]	= $img_row['f_ext'];
					$content['reference']["list"][$key][4]	= $temp_width;
					$content['reference']["list"][$key][5]	= $temp_height;

				}
			}
		}
	}
		
	// check if more than 1 reference image available
	$temp_list_count = count($content['reference']["list"]);
	if($temp_list_count > 1) {

		if(!$content['reference']["blockwidth"]) {
			$content['reference']["blockwidth"]  = $phpwcms["content_width"];
		}
		if(!$content['reference']["blockheight"]) {
			$content['reference']["blockheight"] = $phpwcms["img_prev_height"];
		}
				
		$content['reference']['showlist'] = 1;

		// alignment: 0 = horizontal
		//            1 = vertical

		if(!$content['reference']["basis"]) {

			$temp_list_width = $content['reference']["blockwidth"] - (($temp_list_count - 1) * $content['reference']["space"]);
			$temp_list_width = $temp_list_width / $temp_list_count;
			$temp_list_width = $temp_list_width - (2 * $content['reference']["listborder"]);
			$temp_list_width = intval($temp_list_width);
			if($temp_list_width <= 0) $temp_list_width = 10;
			$content['reference']["temp_list_width"]  = $temp_list_width;
			$content['reference']["temp_list_height"] = '';
				
		} else {
			
			$temp_list_height = $content['reference']["blockheight"] - (($temp_list_count - 1) * $content['reference']["space"]);
			$temp_list_height = $temp_list_height / $temp_list_count;
			$temp_list_height = $temp_list_height - (2 * $content['reference']["listborder"]);
			$temp_list_height = intval($temp_list_height);
			if($temp_list_height <= 0) $temp_list_height = 10;
			$content['reference']["temp_list_height"] = $temp_list_height;
			$content['reference']["temp_list_width"]  = '';
							
		}
	}
		

}


?>