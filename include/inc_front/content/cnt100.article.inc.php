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



// Content Type List
// initiated by J�r�me

$CNT_TMP .= headline($crow["acontent_title"], $crow["acontent_subtitle"], $template_default["article"]);

if(substr_count ($crow["acontent_text"], '~')) {

	//please proof that section again	
	//the first line will always start with an delimeter
	//and every linebreak \n will be converted to <br />

	// split into all parent <li>
	$crow["acontent_text"] = substr($crow["acontent_text"], 1);
	$crow["acontent_text"] = str_replace("\r\n~", '###', $crow["acontent_text"]);
	$crow["acontent_text"] = str_replace("\n~", '###', $crow["acontent_text"]);
	
	$clist_listtype = @unserialize($crow["acontent_form"]);
	
	switch($clist_listtype['list_type']) {
		case 1:		$clist_listmain  = 'ol'; 
					$clist_listentry = 'li';
					break;
					
		case 2:		$clist_listmain  = 'dl';
					$clist_listentry = 'dt';
					break;
					
		default:	$clist_listmain  = 'ul';
					$clist_listentry = 'li';
	}
	
	
	$clist_list = explode('###', $crow["acontent_text"]);
	$clist_line = count($clist_list);
	
	if($clist_line) {

		// start list
		$crow["acontent_text"]  = '<'.$clist_listmain.'>' . LF;
		
		// now check level depth
		$clist_level = array();
		foreach($clist_list as $key => $value) {
			
			$clist_diff = 0;
			
			$clist_level[$key] = 0;
			
			while(substr($value,0,1) == '~') {
				
				$value = substr($value, 1);
				$clist_level[$key]++;				
				
			}
			$clist_list[$key] = $value;
			
		}
		
		//--------------------------------------------------------
	
		foreach($clist_list as $key => $value) {
		
		
			//check previous difference
			if(isset($clist_level[$key-1])) {
				$clist_diff = $clist_level[$key] - $clist_level[$key-1];
			} else {
				$clist_diff = $clist_level[$key];
			}
			
			//now create list stuff before value

			if($clist_diff > 0) {
				for($i=0; $i < $clist_diff; $i++) {
					$crow["acontent_text"] .= '<'.$clist_listmain.'>' . LF;
				}
			}
			
			//proof if it is a <dl> and split into definition and description
			if($clist_listtype['list_type'] == 2) {
				$value = explode('|', $value);
				$value[1] = empty($value[1]) ? '' : trim($value[1]);
			} else {
				$value = array(0 => $value, 1 => '');
			}
			
			$value[0] = trim($value[0]);
			
			//insert value
			$crow["acontent_text"] .= '<'.$clist_listentry.'>'.plaintext_htmlencode($value[0]);
			if($clist_listtype['list_type'] == 2 && $value[1]) {
				$crow["acontent_text"] .= LF . '<dd>'.plaintext_htmlencode($value[1]).'</dd>' . LF;
			}
			
			//--------------------------------------------------------

			//check next difference
			
			if(isset($clist_level[$key+1])) {
				$clist_diff_next = $clist_level[$key] - $clist_level[$key+1];
			} else {
				$clist_diff_next = $clist_level[$key];
			}
			
			if($clist_diff_next == 0) {
					//entry close tag
					$crow["acontent_text"] .= '</'.$clist_listentry.'>' . LF;
			
			} else {
			
				if($clist_diff_next > 0) {
					//entry close tag and list close tag
					$crow["acontent_text"] .= '</'.$clist_listentry.'>' . LF . '</'.$clist_listmain.'>' . LF;
					if($clist_diff_next >= 1) {
						for($i=0; $i < (abs($clist_diff_next)-1); $i++) {
							//entry close tag
							if(!$i) $crow["acontent_text"] .= '</'.$clist_listentry.'>' . LF;
							//list close tag
							$crow["acontent_text"] .= '</'.$clist_listmain.'>' . LF;
						}
						//entry close tag
						$crow["acontent_text"] .= '</'.$clist_listentry.'>' . LF;
					}
				}
			}
		}
		//list close tag
		$crow["acontent_text"] .= '</'.$clist_listmain.'>' . LF;
	}

	$CNT_TMP .= $crow["acontent_text"];

} else {

	// show text only and do nothing else
	$CNT_TMP .= div_class(plaintext_htmlencode($crow["acontent_text"]), $template_default["article"]["text_class"]);

}

									
?>