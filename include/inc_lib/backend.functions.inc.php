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

// general functions used in backend only

function update_cache() {
	// used to update cache setting all current cache entries 
	// will be forced to update cache but will not be deleted
	//$sql = "UPDATE ".DB_PREPEND."phpwcms_cache SET cache_timeout='0'";
	//mysql_query($sql, $GLOBALS['db']) or die("error while updating cache");
}

function set_chat_focus($do, $p) { //set_chat_focus("chat", 1)
	if($do == "chat" && $p == 1) {
		echo "<script type=\"text/javascript\">\n";
		echo "document.sendchatmessage.chatmsg.focus();\ndocument.sendchatmessage.chatmsg.value=get_cookie('chatstring');\n";
		echo "timer = chat_reload(20000);\nfunction chat_reload(zeit) {\n";
		echo "timer=setTimeout(\"write_cookie(1);self.location.href='phpwcms.php?do=chat&p=1&l=".$chatlist."'\", zeit);\n";
		echo "return timer;\n}\nfunction restart_reload(timer) {\n";
		echo "if(timer != null) { clearTimeout(timer); timer=null; timer = chat_reload(20000); }\nreturn timer;\n}\n</script>\n";
	}
}

function forward_to($to, $link, $time=2500) { //Javascript forwarding
	if($to) echo "<script type=\"text/javascript\">\n setTimeout(\"document.location.href='".$link."'\", ".(intval($time))."); \n</script>\n";
}

function subnavtext($text, $link, $is, $should, $getback=1, $js='') {
	//generate subnavigation based on text
	$id = "subnavid".randpassword(5);
	$sn = '';
	if($is == $should) {
		$sn .= '<tr><td><img src="img/subnav/subnav_B.gif" width="15" height="13" border="0" alt="" /></td>';	
		$sn .= '<td class="subnavactive"><a href="'.$link.'">'.$text.'</a></td></tr>';
	} else {
		$sn .= "<tr><td><img name=\"".$id."\" src=\"img/subnav/subnav_A.gif\" width=\"15\" height=\"13\" border=\"0\" alt=\"\" /></td>";
		$sn .= "<td class=\"subnavinactive\"><a href=\"".$link."\" ".$js;
		$sn .= "onMouseOver=\"".$id.".src='img/subnav/subnav_B.gif'\" onMouseOut=\"".$id;
		$sn .= ".src='img/subnav/subnav_A.gif'\">".$text."</a></td></tr>";
	}
	$sn .= "\n";
	if(!$getback) { 
		return $sn; 
	} else {
		echo $sn;
	}
}

function subnavtextext($text, $link, $target='_blank', $getback=1) {
	//generate subnavigation based on text and links to new page
	$id  = 'subnavid'.randpassword(5);
	$sn  = '<tr><td><img src="img/subnav/subnav_A.gif" width="15" height="13" border="0" name="'.$id.'" alt="" /></td>';	
	$sn .= '<td class="subnavinactive"><a href="'.$link.'" target="'.$target.'" ';
	$sn .= "onMouseOver=\"".$id.".src='img/subnav/subnav_B.gif'\" onMouseOut=\"".$id.".src='img/subnav/subnav_A.gif'\"";
	$sn .= '>'.$text.'</a></td></tr>';
	$sn .= "\n";
	if(!$getback) { return $sn; } else { echo $sn; }
}

function subnavback($text, $link, $h_before=0, $h_after=0) {
	$id = "subbackid".randpassword(5);
	$sn  = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
	$sn .= (intval($h_before)) ? "<tr><td colspan=\"2\"><img src=\"img/leer.gif\" width=\"1\" height=\"".intval($h_before)."\" alt=\"\" /></td></tr>\n" : "";
	$sn .= "<tr>";
	$sn .= "<td><img name=\"".$id."\" src=\"img/subnav/subnav_back_0.gif\" width=\"9\" height=\"9\" border=\"0\" alt=\"\" /></td>";
	$sn .= "<td class=\"subnavinactive\">&nbsp;<a href=\"".$link."\" onMouseOver=\"".$id.".src='img/subnav/subnav_back_1.gif'\" ";
	$sn .= "onMouseOut=\"".$id.".src='img/subnav/subnav_back_0.gif'\"><strong>".$text."</strong></a></td>";
	$sn .= "</tr>\n";
	$sn .= (intval($h_after)) ? "<tr><td colspan=\"2\"><img src=\"img/leer.gif\" width=\"1\" height=\"".intval($h_after)."\" alt=\"\" /></td></tr>\n" : "";
	$sn .= "</table>\n";
	echo $sn;
}

function clearfilename($formvar) {
	//Filename anpassen und säubern
	$formvar = trim($formvar);
	if( get_magic_quotes_gpc() ) $formvar = stripslashes ($formvar);
	$formvar = str_replace(array("'", ':', '/', '\\', '"'), '_', $formvar);
	return $formvar;
}

function check_image_extension($file, $filename='') {
	// only checks against correct image extension
	$image_info	= getimagesize($file);	
	$result		= false;
	if(false != $image_info) {
		switch($image_info[2]) {
			case  1: $result = 'gif';	break;
			case  2: $result = 'jpg';	break;
			case  3: $result = 'png';	break;
			case  4: $result = 'swf';	break;
			case  5: $result = 'psd';	break;
			case  6: $result = 'bmp';	break;
			case  7: $result = 'tif';	break; //(intel byte order), 
			case  8: $result = 'tif';	break; //(motorola byte order), 
			case  9: $result = 'jpc';	break;
			case 10: $result = 'jp2';	break;
			case 11: $result = 'jpx';	break;
			case 12: $result = 'jb2';	break;
			
			case 13: // there is a problem in some cases swf -> swc ? why ever!
					 // do an additional extension check and compare against swf
					 $result = (strtolower(which_ext($file)) == 'swf') ? 'swf' : 'swc';
					 break;
			
			case 14: $result = 'iff';	break;

			case 15: // there seems to be a problem with getimagesize and Quicktime VR
					 // mov -> wmbf ? why ever!
					 // do an additional extension check and compare against mov
					 $result = (strtolower(which_ext( empty($filename) ? $file : $filename )) == 'mov') ? 'mov' : 'wbmp';
					 break;

			case 16: $result = 'xbm';	break;
		}
	}	
	return $result;
}

function getParentStructArray($structID) {
	$structID	= intval($structID);
	$sql = "SELECT * FROM ".DB_PREPEND."phpwcms_articlecat WHERE acat_id=".$structID." LIMIT 1";
	if($result = mysql_query($sql, $GLOBALS['db'])) {
		if($row = mysql_fetch_assoc($result)) {
			return $row;
		}
		mysql_free_result($result);
	}
	return $GLOBALS['indexpage'];
}

/*
 * Get sort value for an article based on category
 */
function getArticleSortValue($cat_id=0) {
	
	// default sort value
	$sort		= 0;
	$cat_id		= intval($cat_id);
	$count		= 0;
	$max_sort	= 0;
	// Count all articles within the given structure ID
	$sql  = "SELECT article_id, article_sort FROM ".DB_PREPEND."phpwcms_article ";
	$sql .= "WHERE article_cid=".$cat_id." ";
	$sql .= "ORDER BY article_sort DESC";
	if($result = mysql_query($sql, $GLOBALS['db'])) {
		// this is the max articles in structure
		$count = mysql_num_rows($result);
		// but neccessary trying to get the highest article sort value
		if($row = mysql_fetch_assoc($result)) {
			$max_sort = $row['article_sort'];
		}
		mysql_free_result($result);
	}
	$count = ($count + 1) * 10;
	$count = ($max_sort < $count) ? $count : $max_sort + 10;
	return $count;
}

/*
 * Make a re-sort for given structure ID and 
 * return new sorted articles as array
 */
function getArticleReSorted(& $cat_id, & $ordered_by) {
	
	// get all articles including deleted and update sorting
	// in correct sort order by adding sort + 10
	$sort				= 10;
	$sort_multiply_by	= 1;
	$count_article		= 0;
	$ao 				= get_order_sort($ordered_by);
	
	$sql  = "SELECT article_id, article_cid, article_title, article_public, article_aktiv, article_uid, ";
	$sql .= "date_format(article_tstamp, '%Y-%m-%d %H:%i:%s') AS article_date, article_sort, article_deleted, article_tstamp ";
	$sql .= "FROM ".DB_PREPEND."phpwcms_article ";
	$sql .= "WHERE article_cid='".$cat_id."' ORDER BY ".$ao[2];
	
	if($result = mysql_query($sql, $GLOBALS['db'])) {
	
		// now check if it's sorted manually and DESC
		// then sort has to be lowerd by -10
		if($ao[0] == 0 && $ao[1] == 1) {
			$max_article_count	= mysql_num_rows($result);
			$sort 				= ($max_article_count + 1) * 10;
			$sort_multiply_by	= -1;
		}
	
		// take all entries and build new array with it
		while($row = mysql_fetch_assoc($result)) {

			// SQL update query with new sort value
			$update_sql  = "UPDATE ".DB_PREPEND."phpwcms_article SET ";
			$update_sql .= "article_sort=".$sort.", ";
			$update_sql .= "article_tstamp='".$row['article_tstamp']."' ";
			$update_sql .= "WHERE article_id=".$row['article_id']." LIMIT 1";
			@mysql_query($update_sql, $GLOBALS['db']);

			// add entry to the returning array only for article_deleted=0
			// drops all deleted articles or articles having another status
			if($row['article_deleted'] == 0) {
			
				$article[$count_article]					= $row;
				$article[$count_article]['article_sort']	= $sort;
						
				// count up for article array index
				$count_article++;
				
			}

			// count sort up by 10
			$sort = $sort + (10 * $sort_multiply_by);

		}
		mysql_free_result($result);
	}

	return $article;	

}

function phpwcmsversionCheck() {

	// Check for new version
	
	global $phpwcms;
	global $BL;
	
	if(empty($phpwcms['version_check'])) return '';

	$errno 						= 0;
	$errstr 					= '';
	$version_info				= '';

	if ($fsock = @fsockopen('www.phpwcms.de', 80, $errno, $errstr, 10))	{

		$identify = '?version='.rawurlencode($phpwcms["release"].'-'.str_replace('/', '', $phpwcms["release_date"])).'&hash='.md5($_SERVER['REQUEST_URI']);
	
		@fputs($fsock, "GET /versioncheck/phpwcms_releaseinfo.txt".$identify." HTTP/1.1\r\n");
		@fputs($fsock, "HOST: www.phpwcms.de\r\n");
		@fputs($fsock, "Connection: close\r\n\r\n");

		$get_info = false;
		while (!@feof($fsock)) {
			if ($get_info) {
				$version_info .= @fread($fsock, 1024);
			} else {
				if (@fgets($fsock, 1024) == "\r\n") {
					$get_info = true;
				}
			}
		}
		@fclose($fsock);

		$version_info		= explode("\n", $version_info);
		
		$latest_version		= trim($version_info[0]);
		$latest_revdate		= trim($version_info[1]);
		$latest_revision	= intval(trim($version_info[2]));

		// do version check
		$check = $latest_revision > $phpwcms['revision'] ? false : true;
		
		
		if ($check)	{
			$version_info  = '<p class="valid">' . $BL['Version_up_to_date'] . '</p>';
			$version_info .= '<p class="valid">'.sprintf($BL['Latest_version_info'], $latest_version.' ('.$latest_revdate.', r'.$latest_revision.')'). ' ';
			$version_info .= sprintf($BL['Current_version_info'], $phpwcms["release"].' ('.$phpwcms["release_date"].', r'.$phpwcms["revision"].')') . '</p>';
		
		} else {
			$version_info  = '<p class="error">' . $BL['Version_not_up_to_date'] . '</p>';
			$version_info .= '<p class="error">'.sprintf($BL['Latest_version_info'], ' ' . $latest_version . ' ('.$latest_revdate.', r'.$latest_revision.')'). ' ';
			$version_info .= sprintf($BL['Current_version_info'], $phpwcms["release"].' ('.$phpwcms["release_date"].', r'.$phpwcms["revision"].')') . '</p>';

		}
		

	} else {
	
		if ($errstr) {
			$version_info = '<p class="error">' . sprintf($BL['Connect_socket_error'], $errstr) . '</p>';
		} else {
			$version_info = '<p>' . $BL['Socket_functions_disabled'] . '</p>';
		}
	}
	
	//$version_info .= '<p>' . $BL['Mailing_list_subscribe_reminder'] . '</p>';

	return '<div class="versioncheck"><h1>'.$BL['Version_information'].'</h1> '.$version_info.'</div>';

}


function createOptionTransferSelectList($id='', $leftData, $rightData, $option = array()) {
	// used to create
	
	global $BL;

	$id_left				= $id.'_left';
	$id_right				= $id.'_right';
	$id_left_box			= $id_left.'list';
	$id_right_box			= $id_right.'list';
	$option_object			= generic_string(4, 4);
	
	$table					= '';
	
	$option['rows']			= empty($option['rows']) || !intval($option['rows']) ? 5 : $option['rows'];
	$option['delimeter']	= empty($option['delimeter']) ? ',' : $option['delimeter'];
	$option['encode']		= (isset($option['encode']) && $option['encode'] === false) ? false : true;
	$option['style']		= empty($option['style']) ? '' : ' style="'.$option['style'].'"';
	$option['class']		= empty($option['class']) ? ' class="#SIDE#"' : ' class="#SIDE# '.$option['class'].'"';
	$option['formname']		= empty($option['formname']) ? 'document.forms[0]' : 'document.getElementById(\''.$option['formname'].'\')';
	
	
	$GLOBALS['BE']['HEADER']['optionselect.js'] = getJavaScriptSourceLink('include/inc_js/optionselect.js');
	
	$table .= '<table border="0" cellspacing="0" cellpadding="0">'.LF.'<tr>'.LF;
	
	// left select list
	$table .= '<td valign><select name="'.$id_left_box.'" id="'.$id_left_box.'" size="'.$option['rows'].'" multiple="multiple"';
	$table .= $option['style'].str_replace('#SIDE#', 'leftSide', $option['class']).' ondblclick="'.$option_object.'.transferRight()">'.LF;
	if(!empty($leftData) && is_array($leftData)) {
		foreach($leftData as $key => $value) {
			$table .= '		<option value="'.$key.'">'.$value.'</option>'.LF;
		}
	}	$table .= '</select></td>'.LF;
	
	// left <-> right buttons
	$table .= '<td'.$option['style'].$option['class'].'>';
	$table .= '<img src="img/leer.gif" alt="" width="1" height="1" />'.LF;
	$table .= '</td>'.LF;
	
	// right select list
	$table .= '<td><select name="'.$id_right_box.'" id="'.$id_right_box.'" size="'.$option['rows'].'" multiple="multiple"';
	$table .= $option['style'].str_replace('#SIDE#', 'rightSide', $option['class']).' ondblclick="'.$option_object.'.transferLeft()">'.LF;
	if(!empty($rightData) && is_array($rightData)) {
		foreach($rightData as $key => $value) {
			$table .= '		<option value="'.$key.'">'.$value.'</option>'.LF;
		}
	}
	$table .= '</select></td>'.LF;
	$table .= '</tr>'.LF;
	
	$table .= '<tr>'.LF.'<td>';
	$table .= '<img src="img/button/list_pos_up.gif" alt="" border="0" onclick="moveOptionUp('.$option['formname'].'.'.$id_left_box.');'.$option_object.'.update();">';
	$table .= '<img src="img/leer.gif" width="2" height="2" alt="" />';
	$table .= '<img src="img/button/list_pos_down.gif" alt="" border="0" onclick="moveOptionDown('.$option['formname'].'.'.$id_left_box.');'.$option_object.'.update();">';
	$table .= '<img src="img/leer.gif" width="4" height="4" alt="" />';
	$table .= '<img src="img/button/put_right_a.gif" alt="Move selected to right" border="0" onclick="'.$option_object.'.transferRight();" />';
	$table .= '<img src="img/leer.gif" width="2" height="2" alt="" />';
	$table .= '<img src="img/button/put_right.gif" alt="Move all to right" border="0" onclick="'.$option_object.'.transferAllRight();"/>';
	$table .= '</td>'.LF;
	
	$table .= '<td><img src="img/leer.gif" alt="" width="1" height="1" /></td>'.LF;
	
	$table .= '<td>';
	$table .= '<img src="img/button/put_left_a.gif" alt="Move selected to left" border="0" onclick="'.$option_object.'.transferLeft();" />';
	$table .= '<img src="img/leer.gif" width="2" height="2" alt="" />';
	$table .= '<img src="img/button/put_left.gif" alt="Move all to left" border="0" onclick="'.$option_object.'.transferAllLeft();" />';
	$table .= '</td>'.LF;
	
	$table .= '</tr>'.LF.'</table>'.LF;
	
	$table .= '<input type="hidden" name="'.$id_left.'" id="'.$id_left.'" value="" />';
	$table .= '<input type="hidden" name="'.$id_right.'" id="'.$id_right.'" value="" />';
	
	$table .= '<script language="javascript">'.LF;
	$table .= SCRIPT_CDATA_START.LF;
	$table .= '	var '.$option_object.' = new OptionTransfer("'.$id_left_box.'","'.$id_right_box.'");'.LF;
	$table .= '	'.$option_object.'.setAutoSort(false);'.LF;
	$table .= '	'.$option_object.'.setDelimiter("'.$option['delimeter'].'");'.LF;
	$table .= '	'.$option_object.'.saveNewLeftOptions("'.$id_left.'");'.LF;
	$table .= '	'.$option_object.'.saveNewRightOptions("'.$id_right.'");'.LF;
	$table .= '	'.$option_object.'.init('.$option['formname'].');'.LF;
	
	$table .= LF.SCRIPT_CDATA_END.LF;
	$table .= '</script>'.LF;

	return $table;
}

function countNewsletterRecipients($target) {
	// try to count all recipients for special newsletter
	$recipients	= _dbQuery('SELECT * FROM '.DB_PREPEND.'phpwcms_address WHERE address_verified=1');
	$counter	= 0;
	$check		= (empty($target) || !is_array($target) || !count($target)) ? false : true;
	foreach($recipients as $value) {
		if(empty($value['address_subscription'])) {
			$counter++;
			continue;
		} elseif($check) {
			$value['address_subscription'] = unserialize($value['address_subscription']);
			if(is_array($value['address_subscription'])) {
				foreach($value['address_subscription'] as $subscr) {
					if(in_array(intval($subscr), $target)) {
						$counter++;
						break;
					}
				}
			}
		}
	}
	return $counter;
}

function getContentPartOptionTag($value='', $text='', $selected='', $module='') {

	$result = '';
	

	// neccessary plugin check
	if($value == 30) {
	
		if(!isset($GLOBALS['temp_count'])) {
			$GLOBALS['temp_count'] = 0;
		}
	
		foreach($GLOBALS['phpwcms']['modules'] as $module_value) {
		
			if($module_value['cntp'] && file_exists($module_value['path'].'inc/cnt.list.php')) {
				$result .= '<option value="'.$value.':'.$module_value['name'].'"';
				if($value == $selected && $module_value['name'] == $module) {
					$result .= ' selected="selected"';
					$GLOBALS['contentpart_temp_selected'] = $GLOBALS['temp_count'];
				}
				$result .= '>'.$text;
				$result .= ': '.$GLOBALS['BL']['modules'][ $module_value['name'] ]['listing_title'];
				$result .= '</option>'.LF;
				$GLOBALS['temp_count']++;
			}
		
		}
	
	} else {
	
		$result .= '<option value="'.$value.'"';
		if($value == $selected) {
			$result .= ' selected="selected"';
			$GLOBALS['contentpart_temp_selected'] = $GLOBALS['temp_count'];
		}
		$result .= '>'.$text.'</option>'.LF;
	
	}
	
	return $result;

}

function isContentPartSet($value='') {
	
	$value = explode(':', $value);
	$value[0] = intval($value[0]);
	
	// check for set module
	if(empty($value[1])) {

		$value[1] = 1;
		
	} elseif(		isset($GLOBALS['phpwcms']['modules'][$value[1]]) 
				&&	$GLOBALS['phpwcms']['modules'][$value[1]]['cntp']
				&&	file_exists($GLOBALS['phpwcms']['modules'][$value[1]]['path'].'inc/cnt.list.php')) {
		
		$value[1] = 1;
	
	} else {
	
		$value[1] = 0;
	
	}
	
	// check if content part ID exists
	if(isset($GLOBALS['wcs_content_type'][ $value[0] ]) && $value[1]) {
	
		return true;
	
	} else {
	
		return false;
	
	}

}

/*
 *	Show System Status Message
 */
function show_status_message($return_status=false) {
	if(empty($_SESSION['system_status']['msg'])) {
		$status = '';
	} else {
		$status  = '<div class="status_message_' . $_SESSION['system_status']['type'] .'">';
		$status .= nl2br( trim( html_specialchars($_SESSION['system_status']['msg']) ) ) . '</div>';
		$_SESSION['system_status']['msg'] = '';
	}
	if($return_status) {
		return $status;
	} else {
		echo $status;
		return NULL;
	}
}
/*
 *	Set System Status Message
 */
function set_status_message($msg='', $type='info', $replace=array()) {
	if(is_array($replace) && count($replace)) {
		foreach($replace as $key => $item) {
			$msg = str_replace('{'.strtoupper($key).'}', $item, $msg);
		}
	}
	$_SESSION['system_status']['msg']  = $msg;
	switch($type) {
		case 'success':
		case 'info':
		case 'help':
		case 'error':
		case 'warning':	break;
		default: $type = 'info';
	}
	$_SESSION['system_status']['type'] = $type;
	return NULL;
}

function set_language_cookie() {
	setcookie('phpwcmsBELang', $_SESSION["wcs_user_lang"], time()+(3600*24*365), '/', getCookieDomain() );
}

// checks for alias and sets unique value
function proof_alias($current_id, $alias='', $mode='CATEGORY') {

	$current_id	= intval($current_id);
	$alias = clean_slweg(strtolower($alias), 150);
	$alias = uri_sanitize($alias);
	/*
	$alias = pre_remove_accents($alias);
	$alias = get_alnum_dashes($alias, true);
	$alias = trim($alias);
	if($alias != '') {
		$alias = trim( preg_replace('/\-\-+/', '-', $alias), '-' );
		$alias = trim( preg_replace('/__+/', '_', $alias), '_' );
	}
	*/
	
	$reserved	= array(
		'print',
		'newsdetail',
		'newspage',
		'id',
		'aid',
		'subgallery',
		'listpage',
		'page',
		'subscribe',
		'unsubscribe',
		'email',
		'u',
		's',
		'q'
		);

	if($mode == 'CATEGORY' && $alias == '' && isset($_POST["acat_name"])) {
		$alias = $_POST["acat_name"];
		$set = true;
	} elseif($mode == 'ARTICLE' && $alias == '' && isset($_POST["article_title"])) {
		$alias = $_POST["article_title"];
		$set = true;
	} elseif($mode == 'CONTENT' && $alias == '' && ( isset($_POST["cnt_title"]) || isset($_POST["cnt_name"]) )) {
		$alias = trim($_POST["cnt_title"]) == '' ? $_POST["cnt_name"] : $_POST["cnt_title"];
		$set = true;
	} else {
		$set = false;
	}
	
	if($set) {
		$alias = clean_slweg(strtolower($alias), 150);
		$alias = pre_remove_accents($alias);
		$alias = get_alnum_dashes($alias, true);
		if($alias != '') {
			$alias		= trim( preg_replace('/\-\-+/', '-', $alias), '-' );
			$alias		= trim( preg_replace('/__+/', '_', $alias), '_' );
		}
	}
	
	// new reserved alias can be defined in $phpwcms['reserved_alias']
	if( isset($phpwcms['reserved_alias']) && is_array($phpwcms['reserved_alias']) && count($phpwcms['reserved_alias']) ) {
		$reserved = array_merge($reserved, $phpwcms['reserved_alias']);
	}
	
	if( $alias == '' || in_array($alias, $reserved) || ($alias == 'index' && $current_id != 'index') ) {
	
		if($mode == 'CONTENT') {
			$alias .= date('-Y-n-j');
		} else {
			$alias .= '-view';
		}
	}
	
	$alias = trim( $alias, '-' );
	
	/*
	$where_acat		= 'acat_id != '.$current_id.' AND ';
	$where_article	= 'article_id != '.$current_id.' AND ';
	$where_content	= 'cnt_id != '.$current_id.' AND ';

	switch($mode) {
		case 'CATEGORY':	$where_article	= '';	break;
		case 'ARTICLE':		$where_acat		= '';	break;
		case 'CONTENT':		$where_content	= '';	break;
	}
	*/
	$where_acat		= '';
	$where_article	= '';
	$where_content	= '';

	switch($mode) {
		case 'CATEGORY':	$where_acat		= 'acat_id != '.$current_id.' AND ';	break;
		case 'ARTICLE':		$where_article	= 'article_id != '.$current_id.' AND ';	break;
		case 'CONTENT':		$where_content	= 'cnt_id != '.$current_id.' AND ';		break;
	}
	
	// check alias against all structure alias
	$sql  = "SELECT COUNT(acat_id) FROM ".DB_PREPEND."phpwcms_articlecat WHERE ";
	$sql .= $where_acat;
	$sql .= "acat_alias='".aporeplace($alias)."'";
	$acat_count = _dbQuery($sql, 'COUNT');
	
	// check alias against all articles
	$sql  = "SELECT COUNT(article_id) FROM ".DB_PREPEND."phpwcms_article WHERE ";
	$sql .= $where_article;
	$sql .= "article_alias='".aporeplace($alias)."'";
	$article_count = _dbQuery($sql, 'COUNT');
	
	// check alias against all "sub" contents like news
	$sql  = "SELECT COUNT(cnt_id) FROM ".DB_PREPEND."phpwcms_content WHERE ";
	$sql .= $where_content;
	$sql .= "cnt_alias='".aporeplace($alias)."'";
	$content_count = _dbQuery($sql, 'COUNT');

	if( $acat_count > 0 || $article_count > 0 || $content_count > 0 ) {
	
		$sql  = "SELECT acat_alias FROM ".DB_PREPEND."phpwcms_articlecat WHERE ";
		$sql .= $where_acat;
		$sql .= "acat_alias LIKE '".aporeplace($alias)."%'";
		$all_acat_alias = _dbQuery($sql);
		
		$sql  = "SELECT article_alias FROM ".DB_PREPEND."phpwcms_article WHERE ";
		$sql .= $where_article;
		$sql .= "article_alias LIKE '".aporeplace($alias)."%'";
		$all_article_alias = _dbQuery($sql);
		
		$sql  = "SELECT cnt_alias FROM ".DB_PREPEND."phpwcms_content WHERE ";
		$sql .= $where_content;
		$sql .= "cnt_alias LIKE '".aporeplace($alias)."%'";
		$all_content_alias = _dbQuery($sql);

		$all_alias = array();
		foreach($all_acat_alias as $item) {
			$item = $item['acat_alias'];
			$all_alias[$item] = $item;
		}
		foreach($all_article_alias as $item) {
			$item = $item['article_alias'];
			$all_alias[$item] = $item;
		}
		foreach($all_content_alias as $item) {
			$item = $item['cnt_alias'];
			$all_alias[$item] = $item;
		}
		$all_alias_count = count($all_alias);
		while( isset( $all_alias[ $alias.'-'.$all_alias_count ] ) ) {
			$all_alias_count++;
		}
				
		if(preg_match('/\-(\d+)$/', $alias)) {
			$alias .= $all_alias_count;
		} else {
			$alias .= '-'.$all_alias_count;
		}
	}
	
	return $alias;
}

function _getTime($time='', $delimeter=':', $default_time='H:i:s') {

	$timeformat		= explode($delimeter, trim($default_time));
	$time			= explode($delimeter, trim($time));
	
	$hour			= 0;
	$minute			= 0;
	$second			= 0;
	
	for($x=0; $x<=2; $x++) {
	
		if(isset($timeformat[$x])) {
		
			$value = trim($timeformat[$x]);
			switch( $value{0} ) {
			
				case 'H': 	if(isset($time[$x])) {
								$hour = intval($time[$x]);
								if($hour < 0 || $hour > 23) $hour = 0;
							}
							break;
							
				case 'i': 	if(isset($time[$x])) {
								$minute = intval($time[$x]);
								if($minute < 0 || $minute > 59) $minute = 0;
							}
							break;
							
				case 's': 	if(isset($time[$x])) {
								$second = intval($time[$x]);
								if($second < 0 || $second > 59) $second = 0;
							}
							break;
			}
		
		}
	
	}
	
	$time = str_replace($delimeter, ':', $default_time);
	$time = str_replace('H', $hour, $time);
	$time = str_replace('i', $minute, $time);
	$time = str_replace('s', $second, $time);

	return $time;
}

function _getDate($date='', $delimeter='', $default_date='') {

	global $BL;
	
	$delimeter		= $delimeter == '' ? $BL['default_date_delimiter'] : $delimeter;
	$default_date	= $default_date == '' ? $BL['default_date'] : $default_date;
	
	$dateformat		= explode($delimeter, trim($default_date));
	$date			= explode($delimeter, trim($date));
	
	$day			= '';
	$month			= '';
	$year			= '';
	
	for($x=0; $x<=2; $x++) {
	
		if(isset($dateformat[$x])) {
		
			$value = trim($dateformat[$x]);
			$value = strtolower($value);
			switch( $value{0} ) {
			
				case 'y': 	if(isset($date[$x])) {
								$year = intval($date[$x]);
								if($year < 0) $year = '';
							}
							break;
							
				case 'd': 	if(isset($date[$x])) {
								$day = intval($date[$x]);
								if($day < 1 || $day > 31) $day = '';
							}
							break;
							
				case 'm': 	if(isset($date[$x])) {
								$month = intval($date[$x]);
								if($month < 1 || $month > 12) $month = '';
							}
							break;
			
			}
		
		}
	
	}
	
	if($year && $month && $day) {
	
		return $year.'-'.$month.'-'.$day;
	
	} else {
	
		return '0000-00-00';
	
	}
	
}

function _dbSaveCategories($categories=array(), $type='', $pid=0, $seperator=',') {

	$pid	= intval($pid);
	$type	= trim($type);
	
	if(is_string($categories)) {
		$categories = convertStringToArray($categories, $seperator);
	}
	
	// delete all related categories first
	if($type && $pid) {
		
		$sql = 'DELETE FROM '.DB_PREPEND.'phpwcms_categories WHERE cat_pid='.$pid." AND cat_type='".aporeplace( $type )."'";
		_dbQuery($sql, 'DELETE');
	
	}

	if(is_array($categories) && count($categories) && $type && $pid) {
	
		$data = array(	'cat_type'			=> $type,
						'cat_pid' 			=> $pid,
						'cat_status'		=> 1,
						'cat_createdate'	=> date('Y-m-d H:i:s'),
						'cat_changedate'	=> date('Y-m-d H:i:s'),
						'cat_name'			=> '',
						'cat_info'			=> ''
						);
	
		foreach($categories as $value) {
			$value = trim($value);
			if($value != '') {
				
				$data['cat_name'] = $value;
				_dbInsert('phpwcms_categories', $data);
				
			}
		}
	}
}

function setItemsPerPage($default=25) {
	if( isset($_GET['showipp']) ) {
		$ipp = intval( is_numeric($_GET['showipp']) ? $_GET['showipp'] : $default );
		setcookie('phpwcmsBEItemsPerPage', $ipp, time()+157680000, '/', getCookieDomain() );
	} elseif(isset($_SESSION['PAGE_FILTER'])) {
		$ipp = $_SESSION['PAGE_FILTER']['IPP'];
	} elseif( isset($_COOKIE['phpwcmsBEItemsPerPage']) ) {
		$ipp = intval( $_COOKIE['phpwcmsBEItemsPerPage'] );
	} else {
		$ipp = $default;
	}
	
	if(!isset($_SESSION['PAGE_FILTER'])) {
		$_SESSION['PAGE_FILTER'] = array();
	}
	
	$_SESSION['PAGE_FILTER']['IPP'] = $ipp;
	
	return $ipp;
}

function getItemsPerPageMenu($base_url='', $steps=array(10,25,50,100,250,0), $separator=' ') {

	$ipp = isset($_SESSION['PAGE_FILTER']['IPP']) ? $_SESSION['PAGE_FILTER']['IPP'] : setItemsPerPage();
	
	if(!in_array($ipp, $steps)) {
		array_unshift($steps, $ipp);
	}
	
	$menu = array();
	$x = 0;
	foreach($steps as $item) {
	
		$menu[$x]  = '<a href="'.$base_url.'&amp;showipp='.$item.'"';
		if($ipp == $item) {
			$menu[$x] .= ' class="active"';
		}
		$menu[$x] .= '>';
		$menu[$x] .= $item == 0 ? $GLOBALS['BL']['be_ftptakeover_all'] : $item;
		$menu[$x] .= '</a>';
	
		$x++;
	}
	
	return implode($separator, $menu);
}

function initJsCalendar() {
	$GLOBALS['BE']['HEADER']['date.js']			= getJavaScriptSourceLink('include/inc_js/date.js');
	$GLOBALS['BE']['HEADER']['dynCalendar.js']	= getJavaScriptSourceLink('include/inc_js/dynCalendar.js');
}
function initMootools($mode='1.1', $more=array()) {
	switch($mode) {
		// MooTools 1.1
		case '1.1':
			$GLOBALS['BE']['HEADER']['mootools.js'] = getJavaScriptSourceLink(TEMPLATE_PATH.'lib/mootools/mootools-1.1-yc.js');
			break;
			
		// MooTools 1.2 + More
		default:
			unset($GLOBALS['BE']['HEADER']['mootools.js']);
			$GLOBALS['BE']['HEADER']['mootools-1.2-core.js'] = getJavaScriptSourceLink(TEMPLATE_PATH.'lib/mootools/mootools-1.2-core-yc.js');
			
			if(is_array($more) && count($more)) {
				array_unshift($more, 'Core/More');
				foreach($more as $item) {
					$name = 'mootools-more-'.$item;
					if(empty($GLOBALS['BE']['HEADER'][$name]) && is_file(PHPWCMS_TEMPLATE.'lib/mootools/more/'.$item.'.js')) {
						$GLOBALS['BE']['HEADER'][$name] = getJavaScriptSourceLink(TEMPLATE_PATH.'lib/mootools/more/'.$item.'.js');
					}
				}
			}
	}
	$GLOBALS['phpwcms']['mootools_mode'] = $mode;
}
function initMootoolsAutocompleter($mode='1.1') {
	initMootools($mode);
	$GLOBALS['BE']['HEADER']['Autocompleter.js']		= getJavaScriptSourceLink('include/inc_js/mootools/cnet/Autocompleter.js');
	$GLOBALS['BE']['HEADER']['Autocompleter.Remote.js']	= getJavaScriptSourceLink('include/inc_js/mootools/cnet/Autocompleter.Remote.js');
	$GLOBALS['BE']['HEADER']['Observer.js']				= getJavaScriptSourceLink('include/inc_js/mootools/cnet/Observer.js');
}
function initJsOptionSelect() {
	$GLOBALS['BE']['HEADER']['optionselect.js']	= getJavaScriptSourceLink('include/inc_js/optionselect.js');
}
function initMultipleUpload($mode='1.2') {
	switch($mode) {
		case '1.1':
			initMootools('1.1');
			$GLOBALS['BE']['HEADER']['Swiff.Base.js']		= getJavaScriptSourceLink('include/inc_js/mootools/FancyUpload/v1/Swiff.Base.js');
			$GLOBALS['BE']['HEADER']['Swiff.Uploader.js']	= getJavaScriptSourceLink('include/inc_js/mootools/FancyUpload/v1/Swiff.Uploader.js');
			$GLOBALS['BE']['HEADER']['FancyUpload.js']		= getJavaScriptSourceLink('include/inc_js/mootools/FancyUpload/v1/FancyUpload.js');
			break;

		default:
			initMootools('1.2');
			$GLOBALS['BE']['HEADER']['Swiff.Uploader.js']	= getJavaScriptSourceLink('include/inc_js/mootools/FancyUpload/Swiff.Uploader.js');
			$GLOBALS['BE']['HEADER']['FX.ProgressBar.js']	= getJavaScriptSourceLink('include/inc_js/mootools/FancyUpload/Fx.ProgressBar.js');
			$GLOBALS['BE']['HEADER']['FancyUpload2.js']		= getJavaScriptSourceLink('include/inc_js/mootools/FancyUpload/FancyUpload2.js');
	}
}

// make phpwcms compatibility and upgrade check
function phpwcms_revision_check($revision) {

	$revision_file = PHPWCMS_ROOT.'/include/inc_lib/revision/r';

	// loop while trying to find the latest revision file (for r407 and up)
	// then there is no need to implement new revision updater for each revision
	while(!is_file( $revision_file . $revision.'.php')) {
		$revision--;
		if($revision < 406) {
			return false;
		}
	}

	$revision_temp = phpwcms_revision_check_temp($revision);
	if($revision_temp === NULL) {
		return false;
	} elseif($revision_temp) {
		return true;
	}
	
	include_once($revision_file . $revision.'.php');

	$revision_function = 'phpwcms_revision_r'.$revision;
	if(function_exists($revision_function) && empty($GLOBALS['phpwcms']['check_r'.$revision])) {
		if( call_user_func($revision_function) !== false ) {
			$GLOBALS['phpwcms']['check_r'.$revision] = true;
			@write_textfile(PHPWCMS_TEMP.'r'.$revision.'.checked.tmp', date('Y-d-m H:i:s'));
			return true;
		} else {
			return false;
		}
	}

	return true;
}

// check upgrade temp file for current revision
function phpwcms_revision_check_temp($revision) {
	if(empty($revision) || !preg_match('/^\d+$/', $revision)) {
		return NULL;
	}
	return is_file(PHPWCMS_TEMP.'r'.$revision.'.checked.tmp');
}

/*
 * Parse backend for language related BBCode [EN][/EN] or {EN}{/EN} unsing JavaScript
 * 
 * To enable backend language parser set config in conf.inc.php
 * $phpwcms['be_lang_parse'] = 'BBCode'; // to enable parsing for [EN][/EN]
 * $phpwcms['be_lang_parse'] = 'BraceCode'; // to enable parsing for {EN}{/EN}
 * $phpwcms['be_lang_parse'] = 'i18n'; // ToDo: to enable parsing for @@Default@@
 * $phpwcms['be_lang_parse'] = false; // to disable backend language parsing
 */ 
function backend_language_parser() {
	
	global $phpwcms, $BE, $BL;
		
	
	if(!$phpwcms['be_parse_lang_process'] || empty($phpwcms['be_lang_parse'])) {
		
		return backend_language_replace('');
		
	} elseif(empty($phpwcms['allowed_lang']) || !is_array($phpwcms['allowed_lang']) || count($phpwcms['allowed_lang']) < 2) {
		
		return backend_language_replace('');
	
	} else {
		
		$parse_mode = strtoupper($phpwcms['be_lang_parse']);
		
		if(!in_array($parse_mode, array('BBCODE', 'BRACECODE'))) { // i18n later
			
			return backend_language_replace('');
	
		}

	}
	
	// cut main backend content innerHTML
	$html_pos1	= strpos($BE['HTML'], '<!--BE_MAIN_CONTENT_START//-->');
	$html_pos2	= strpos($BE['HTML'], '<!--BE_MAIN_CONTENT_END//-->');
	
	if($html_pos1 !== false && $html_pos2 !== false) {
	
		$html_pos1 += strlen('<!--BE_MAIN_CONTENT_START//-->');
		$html_pos2 -= 1;
		
	}
	
	$html		= trim( preg_replace('/\s+/', ' ', substr($BE['HTML'], $html_pos1, $html_pos2-$html_pos1) ) );
	$BE['HTML']	= substr($BE['HTML'], 0, $html_pos1) . substr($BE['HTML'], $html_pos2);
	
	// load MooTools too
	if(empty($phpwcms['mootools_mode'])) {
		initMootools();
	}
	
	// init language replacements
	$regexp 	= array( 'search' => array(), 'replace' => array() );
	$bracket	= array('BBCODE_OPEN' => '[', 'BRACECODE_OPEN' => '{', 'BBCODE_CLOSE' => ']', 'BRACECODE_CLOSE' => '}');
	$cookie		= (empty($_COOKIE['phpwcms_be_parse_lang']) || !in_array($_COOKIE['phpwcms_be_parse_lang'], $phpwcms['allowed_lang'])) ? false : $_COOKIE['phpwcms_be_parse_lang'];
	
	// init menu
	$menu		= array(
		'<ul id="be_lang">',
		'<li class="be-lang-label chatlist">'.$BL['be_profile_label_lang'].':</li>',
		'<li><a href="#" class="be-disabled'.
			($cookie === false ? ' be-active' : '').
			'" rel="disabled" title="'.
			$BL['be_profile_label_lang'].': '.$BL['be_off'].'">'.$BL['be_off'].'</a></li>'
	);
	
	// Header CSS section
	$BE['HEADER']['be_parse_lang']  = '	<style type="text/css">' . LF;
	
	// JavaScript section
	$BE['BODY_CLOSE']['hidden_main_content']  = '	<script type="text/javascript">' . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= "		var be_lang_html = [];" . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= "		var cur_be_lang = 'disabled';" . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= "		be_lang_html['disabled'] = '" . trim(str_replace(array("\\", "'"), array("\\\\", "\\'"), $html)) . "';" . LF . LF;
	
	// build regular expression at first
	foreach($phpwcms['allowed_lang'] as $lang) {
		$regexp['search'][$lang]	= '/\\'.$bracket[$parse_mode.'_OPEN'].$lang.'\\'.$bracket[$parse_mode.'_CLOSE'].'(.*?)\\'.$bracket[$parse_mode.'_OPEN'].'\/'.$lang.'\\'.$bracket[$parse_mode.'_CLOSE'].'/is';
		$regexp['replace'][$lang]	= '';
	}
	// parse each language at second
	foreach($phpwcms['allowed_lang'] as $lang) {
		$replace		= $regexp['replace'];
		$replace[$lang]	= '$1';
		$lang_html		= preg_replace($regexp['search'], $replace, $html);
		
		$BE['HEADER']['be_parse_lang'] .= '	#be_lang a.be-lang-'.$lang.' {background-image:url(img/famfamfam/lang/'.$lang.'.png);}'.LF;
		
		
		$menu_item		= '<li><a href="#" class="be-lang be-lang-'.$lang;
		
		// check which is current default
		if($lang == $cookie) {
			$new_html = $lang_html;	// phpwcms should use the curent lang html
			$BE['BODY_CLOSE']['hidden_main_content'] .= "		cur_be_lang = '" . $lang . "';" . LF;
			$menu_item .= ' be-active';
		}
		
		$menu[] = $menu_item . '" rel="'.$lang.'" title="'.$BL['be_profile_label_lang'].': '.strtoupper($lang).'">'.$lang.'</a></li>';

		$BE['BODY_CLOSE']['hidden_main_content'] .= "		be_lang_html['".$lang."'] = '" . trim(str_replace(array("\\", "'"), array("\\\\", "\\'"), $lang_html)) . "';" . LF . LF;
	}
	
	$BE['HEADER']['be_parse_lang']			 .= '	</style>';
	
	$BE['BODY_CLOSE']['hidden_main_content'] .= '	window.addEvent("domready", function() {' . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= '		var be_lang = $("be_lang");' . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= '		var be_lang_cnt = $("be_lang_cnt");' . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= '		if(be_lang && be_lang_cnt) {' . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= '			var be_lang_items = be_lang.getElements("a");' . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= '			be_lang_items.each(function(l) {' . LF;

	$BE['BODY_CLOSE']['hidden_main_content'] .= '				l.addEvent("click", function(){' . LF;
	
	$BE['BODY_CLOSE']['hidden_main_content'] .= "					if(cur_be_lang == l.rel) {return;}" . LF;
	
	$BE['BODY_CLOSE']['hidden_main_content'] .= "					cur_be_lang = l.rel;" . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= '					be_lang_items.each(function(el){el.removeClass("be-active");});' . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= '					l.addClass("be-active");' . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= '					be_lang_cnt.setHTML(be_lang_html[l.rel]);' . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= '					Cookie.set("phpwcms_be_parse_lang", cur_be_lang);' . LF;
	
	$BE['BODY_CLOSE']['hidden_main_content'] .= '				});' . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= '			});' . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= '		}' . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= '	});' . LF;
	$BE['BODY_CLOSE']['hidden_main_content'] .= '	</script>';
	
	$menu[] = '</ul>';
	
	// wrap current lang/html with <div>
	$BE['HTML']	= replace_tmpl_section('BE_MAIN_CONTENT', $BE['HTML'], '<div id="be_lang_cnt">' . (empty($new_html) ? $html : $new_html) . '</div>');
	
	backend_language_replace( implode(LF, $menu) );
	
}

function backend_language_replace($result) {
	
	$GLOBALS['BE']['HTML'] = str_replace('{BE_PARSE_LANG}', $result, $GLOBALS['BE']['HTML']);
	return NULL;
	
}

?>