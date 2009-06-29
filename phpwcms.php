<?php
/*************************************************************************************
   Copyright notice
   
   (c) 2002-2008 Oliver Georgi (oliver@phpwcms.de) // All rights reserved.

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

// set page processiong start time
list($usec, $sec) = explode(' ', microtime());
$phpwcms_rendering_start = $usec + $sec;

session_start();

//define used var names
$body_onload				= '';
$forward_to_message_center	= false;
$wcsnav 					= array();
$indexpage 					= array();
$phpwcms 					= array();
$BL							= array();
$BE							= array('HTML' => '', 'BODY_OPEN' => array(), 'BODY_CLOSE' => array(), 'HEADER' => array(), 'LANG' => 'en');

// check against user's language
if(!empty($_SESSION["wcs_user_lang"]) && preg_match('/[a-z]{2}/i', $_SESSION["wcs_user_lang"])) {
	$BE['LANG'] = $_SESSION["wcs_user_lang"];
}

require_once ('config/phpwcms/conf.inc.php');
require_once ('include/inc_lib/default.inc.php');
require_once (PHPWCMS_ROOT.'/include/inc_lib/dbcon.inc.php');
require_once (PHPWCMS_ROOT.'/include/inc_lib/general.inc.php');
checkLogin();
require_once (PHPWCMS_ROOT.'/include/inc_lib/backend.functions.inc.php');
require_once (PHPWCMS_ROOT.'/include/inc_lib/default.backend.inc.php');

//load default language EN
require_once (PHPWCMS_ROOT.'/include/inc_lang/backend/en/lang.inc.php');
$BL['modules']				= array();

if(!empty($_SESSION["wcs_user_lang_custom"])) {
	//use custom lang if available -> was set in login.php
	$BL['merge_lang_array'][0]		= $BL['be_admin_optgroup_label'];
	$BL['merge_lang_array'][1]		= $BL['be_cnt_field'];	
	include(PHPWCMS_ROOT.'/include/inc_lang/backend/'. $BE['LANG'] .'/lang.inc.php');
	$BL['be_admin_optgroup_label']	= array_merge($BL['merge_lang_array'][0], $BL['be_admin_optgroup_label']);
	$BL['be_cnt_field']				= array_merge($BL['merge_lang_array'][1], $BL['be_cnt_field']);
	unset($BL['merge_lang_array']);
}

require_once (PHPWCMS_ROOT.'/include/inc_lib/navi_text.inc.php');
require_once (PHPWCMS_ROOT.'/include/inc_lib/checkmessage.inc.php');
require_once (PHPWCMS_ROOT.'/config/phpwcms/conf.template_default.inc.php');
require_once (PHPWCMS_ROOT.'/config/phpwcms/conf.indexpage.inc.php');
require_once (PHPWCMS_ROOT.'/include/inc_lib/imagick.convert.inc.php');

// check modules 
require_once (PHPWCMS_ROOT.'/include/inc_lib/modules.check.inc.php');	

$BL['be_admin_struct_index'] = html_specialchars($indexpage['acat_name']);


$subnav	= ''; //Sub Navigation
$p		= isset($_GET["p"])  ? intval($_GET["p"]) : 0; //which page should be opened
$do		= isset($_GET["do"]) ? $_GET["do"] : 'default'; //which backend section and which $do action
$module	= isset($_GET['module'])  ? clean_slweg($_GET['module']) : ''; //which module

switch ($do) {

	case "articles":	//articles
						include(PHPWCMS_ROOT.'/include/inc_lib/admin.functions.inc.php');
						$wcsnav["articles"] = "<strong class=\"navtexta\">".$wcsnav["articles"]."</strong>";
						include(PHPWCMS_ROOT.'/include/inc_lib/article.contenttype.inc.php'); //loading array with actual content types
						include(PHPWCMS_ROOT.'/include/inc_lib/article.functions.inc.php'); //loading article funtions
						$subnav .= subnavtext($BL['be_subnav_article_center'], "phpwcms.php?do=articles", $p, "", 0);
						$subnav .= subnavtext($BL['be_subnav_article_new'], "phpwcms.php?do=articles&amp;p=1&amp;struct=0", $p, "1", 0);
						$subnav .= '<tr><td colspan="2"><img src="img/leer.gif" height="5" width="1" alt="" /></td></tr>'."\n";
						$subnav .= subnavtext($BL['be_news'], "phpwcms.php?do=articles&amp;p=3", $p, "3", 0);
						break;

	case "files":		//files
						$wcsnav["files"] = "<strong class=\"navtexta\">".$wcsnav["files"]."</strong>";
						$subnav .= subnavtext($BL['be_subnav_file_center'], "phpwcms.php?do=files", $p, "", 0);
						$subnav .= subnavtext($BL['be_subnav_file_ftptakeover'], "phpwcms.php?do=files&amp;p=8", $p, "8", 0);
						$subnav .= subnavtext($BL['be_file_multiple_upload'], "phpwcms.php?do=files&amp;p=9", $p, "9", 0);
						break;

	case "modules":		//modules
						$wcsnav["modules"] = "<strong class=\"navtexta\">".$wcsnav["modules"]."</strong>";
						if($phpwcms["gt_mod"]) { //enabled/disable GT MOD
							include(PHPWCMS_ROOT.'/include/inc_module/mod_graphical_text/inc_lang/backend/en/lang.inc.php');
							if($BE['LANG'] != 'en' && is_file(PHPWCMS_ROOT.'/include/inc_module/mod_graphical_text/inc_lang/backend/'.$BE['LANG'].'/lang.inc.php')) {
								include(PHPWCMS_ROOT.'/include/inc_module/mod_graphical_text/inc_lang/backend/'.$BE['LANG'].'/lang.inc.php');
							}
							$subnav .= subnavtext($BL['be_subnav_graphicaltext_mod'], "phpwcms.php?do=modules&amp;p=2", $p, "2", 0);
						}
						
						foreach($phpwcms['modules'] as $value) {
						
							$subnav .= subnavtext($BL['modules'][ $value['name'] ]['backend_menu'], 'phpwcms.php?do=modules&amp;module='.$value['name'], $module, $value['name'], 0);
						
						}
						
						break;

	case "messages":	//messages
						$wcsnav["messages"] = "<strong class=\"navtexta\">".$wcsnav["messages"]."</strong>";
						if(isset($_SESSION["wcs_user_admin"]) && $_SESSION["wcs_user_admin"] == 1) {
							$subnav .= subnavtext($BL['be_subnav_msg_newslettersend'], "phpwcms.php?do=messages&amp;p=3", $p, "3", 0);
							$subnav .= subnavtext($BL['be_subnav_msg_subscribers'], "phpwcms.php?do=messages&amp;p=4", $p, "4", 0);
							$subnav .= subnavtext($BL['be_subnav_msg_newsletter'], "phpwcms.php?do=messages&amp;p=2", $p, "2", 0);
							
							if(!empty($phpwcms['enable_messages'])) {
								$subnav .= '<tr><td colspan="2"><img src="img/leer.gif" height="5" width="1" alt="" /></td></tr>'."\n";
							}
						}
						if(!empty($phpwcms['enable_messages'])) {
							$subnav .= subnavtext($BL['be_subnav_msg_center'], "phpwcms.php?do=messages", $p, "", 0);
							$subnav .= subnavtext($BL['be_subnav_msg_new'], "phpwcms.php?do=messages&amp;p=1", $p, "1", 0);
						}
						break;

	case "discuss":		//discuss
						$wcsnav["discuss"] = "<strong class=\"navtexta\">".$wcsnav["discuss"]."</strong>";
						break;

	case "chat":		//chat
						$wcsnav["chat"] = "<strong class=\"navtexta\">".$wcsnav["chat"]."</strong>";
						$subnav .= subnavtext($BL['be_subnav_chat_main'], "phpwcms.php?do=chat", $p, "", 0);
						$subnav .= subnavtext($BL['be_subnav_chat_internal'], "phpwcms.php?do=chat&amp;p=1", $p, "1", 0);
						break;

	case "profile":		//profile
						$wcsnav["profile"] = "<strong class=\"navtexta\">".$wcsnav["profile"]."</strong>";
						if(!empty($_POST["form_aktion"])) {
							switch($_POST["form_aktion"]) { //Aktualisieren der wcs account & profile Daten
								case "update_account":	include(PHPWCMS_ROOT.'/include/inc_lib/profile.updateaccount.inc.php');
														break;
								case "update_detail":	include(PHPWCMS_ROOT.'/include/inc_lib/profile.update.inc.php'); 
														break;
								case "create_detail":	include(PHPWCMS_ROOT.'/include/inc_lib/profile.create.inc.php'); 
														break;
							}
						}
						$subnav .= subnavtext($BL['be_subnav_profile_login'], "phpwcms.php?do=profile", $p, "", 0);
						$subnav .= subnavtext($BL['be_subnav_profile_personal'], "phpwcms.php?do=profile&amp;p=1", $p, "1", 0);
						break;

	case "logout":		//Logout
						$sql  = "UPDATE ".DB_PREPEND."phpwcms_userlog SET ";
						$sql .= "logged_change=".time().", logged_in=0 ";
						$sql .= "WHERE logged_user='".$_SESSION["wcs_user"]."' AND logged_in=1";
						@mysql_query($sql, $db);
						session_destroy();
						headerRedirect(PHPWCMS_URL.'login.php');
						break;

	case "admin":		//Admin
						if(isset($_SESSION["wcs_user_admin"]) && $_SESSION["wcs_user_admin"] == 1) {
							include(PHPWCMS_ROOT.'/include/inc_lib/admin.functions.inc.php');
							$subnav .= subnavtext($BL['be_subnav_admin_sitestructure'], "phpwcms.php?do=admin&amp;p=6", $p, "6", 0);
							$subnav .= '<tr><td colspan="2"><img src="img/leer.gif" height="5" width="1" alt="" /></td></tr>'."\n";
							$subnav .= subnavtext($BL['be_subnav_admin_pagelayout'], "phpwcms.php?do=admin&amp;p=8", $p, "8", 0);
							$subnav .= subnavtext($BL['be_subnav_admin_templates'], "phpwcms.php?do=admin&amp;p=11", $p, "11", 0);
							$subnav .= subnavtext($BL['be_subnav_admin_css'], "phpwcms.php?do=admin&amp;p=10", $p, "10", 0);
							$subnav .= '<tr><td colspan="2"><img src="img/leer.gif" height="5" width="1" alt="" /></td></tr>'."\n";
							$subnav .= subnavtext($BL['be_subnav_admin_users'], "phpwcms.php?do=admin", $p, "", 0);
							//$subnav .= subnavtext($BL['be_subnav_admin_groups'], "phpwcms.php?do=admin&amp;p=1", $p, "1", 0);
							$subnav .= '<tr><td colspan="2"><img src="img/leer.gif" height="15" width="1" alt="" /></td></tr>'."\n";
							//$subnav .= subnavtext($BL['be_admin_keywords'], "phpwcms.php?do=admin&amp;p=5", $p, "5", 0);
							$subnav .= subnavtext($BL['be_subnav_admin_filecat'], "phpwcms.php?do=admin&amp;p=7", $p, "7", 0);
							$subnav .= subnavtext($BL['be_subnav_admin_starttext'], "phpwcms.php?do=admin&amp;p=12", $p, "12", 0);
							$subnav .= subnavtext($BL['be_article_urlalias'].' ('.$BL['be_ftptakeover_active'].')', 'phpwcms.php?do=admin&amp;p=13', $p, "4", 0);
							$subnav .= '<tr><td colspan="2"><img src="img/leer.gif" height="15" width="1" alt="" /></td></tr>'."\n";
							//$subnav .= subnavtext($BL['be_cnt_cache_update'], 'include/inc_act/act_cache.php', 1, 0, 0);
							//$subnav .= subnavtext($BL['be_cnt_cache_delete'], 'include/inc_act/act_cache.php?do=9', 1, 0, 0, 'onclick="return confirm(\''.$BL['be_cnt_cache_delete_msg'].'\');" ');
							$subnav .= subnavtext($BL['be_cnt_move_deleted'], 'include/inc_act/act_file.php?movedeletedfiles='. $_SESSION["wcs_user_id"], 1, 0, 0, 'onclick="return confirm(\''.$BL['be_cnt_move_deleted_msg'].'\');" ');
							$subnav .= '<tr><td colspan="2"><img src="img/leer.gif" height="15" width="1" alt="" /></td></tr>'."\n";
							$subnav .= subnavtextext('phpinfo()', 'include/inc_act/act_phpinfo.php', '_blank', 0);
							if($phpwcms["phpmyadmin"]) {
								$subnav .= subnavtextext('phpMyAdmin', 'include/inc_ext/phpMyAdmin/', '_blank', 0);
							}
						}
						break;
						
		default:		include(PHPWCMS_ROOT.'/include/inc_lib/article.contenttype.inc.php'); //loading array with actual content types

} //Ende Auswahl Aktion


//Subnav Wrap Text Tabelle
if($subnav) {
	$subnav  = '<table border="0" cellpadding="0" cellspacing="0" summary="">'.LF.$subnav;
	$subnav .= "<tr><td colspan=\"2\"><img src=\"img/leer.gif\" width=\"1\" height=\"15\" alt=\"\" /></td></tr>\n</table>";
}

//Wenn der User kein Admin ist, anderenfalls
if(empty($_SESSION["wcs_user_admin"])) {
	unset($wcsnav["admin"]);
} elseif($do  == "admin") {
	$wcsnav["admin"] = '<strong class="navtexta">'.$wcsnav["admin"].'</strong>';
}

//script chaching to allow header redirect
ob_start(); //without Compression

// set correct content type for backend
header('Content-Type: text/html; charset='.PHPWCMS_CHARSET);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?php echo $BL['be_page_title'].' - '.PHPWCMS_HOST ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo PHPWCMS_CHARSET ?>">
	<link href="include/inc_css/phpwcms.css" rel="stylesheet" type="text/css">
	<link href="include/inc_css/additional.css" rel="stylesheet" type="text/css">
	<meta name="robots" content="noindex, nofollow">
<?php

$BE['HEADER']['phpwcms.js']			= getJavaScriptSourceLink('include/inc_js/phpwcms.js');
$BE['HEADER']['browserSniffer.js']	= getJavaScriptSourceLink('include/inc_js/browserSniffer.js');



if($do == "messages" && $p == 1) {

	include(PHPWCMS_ROOT.'/include/inc_lib/message.sendjs.inc.php');

} elseif($do == "articles") {

	if($p == 2 && isset($_GET["aktion"]) && intval($_GET["aktion"]) == 2) {
		initJsOptionSelect();
	}
	if(($p == 1) || ($p == 2 && isset($_GET["aktion"]) && intval($_GET["aktion"]) == 1)) {
		initJsCalendar();
	}

} elseif($do == 'admin' && ($p == 6 || $p == 11)) {

	// struct editor
	initJsOptionSelect();

}

if($BE['LANG'] == 'ar') {
	$BE['HEADER'][] = '<style type="text/css">' . LF . '<!--' . LF . '* {direction: rtl;}' . LF . '//-->' . LF . '</style>';
}

?>
<!-- phpwcms HEADER -->
</head>

<body<?php echo $body_onload ?>><!-- phpwcms BODY_OPEN -->
<table width="770" border="0" align="center" cellpadding="0" cellspacing="0" summary="main layout structure">
	<tr>
	  <td colspan="6"><img src="img/leer.gif" alt="" width="1" height="7"></td>
  </tr>
	<tr>
		<td colspan="6">
		<div style="position:relative">
	  		<a href="phpwcms.php" target="_top"><img src="img/backend/backend_r1_c3.jpg" alt="phpwcms v<?php echo  html_specialchars($phpwcms["release"]); ?>" width="95" height="24" border="0" hspace="15"></a>
			<a href="<?php echo PHPWCMS_URL ?>" class="v10" style="position:absolute;right:15px;bottom:4px;color:#FFFFFF" target="_blank"><?php echo PHPWCMS_HOST ?></a>
		</div>
		</td>
    </tr>
	<tr>
	  <td colspan="6"><img src="img/leer.gif" alt="" width="1" height="7"></td>
  </tr>
	<tr bgcolor="#FFFFFF">
	 <td valign="top" style="background-image:url(img/backend/backend_r3_c4.jpg); background-repeat:repeat-x; "><img src="img/backend/backend_r3_c1.jpg" alt="" width="15" height="40"></td>
	  <td colspan="4" valign="top" style="background-image:url(img/backend/backend_r3_c4.jpg); background-repeat:repeat-x; "><table width="740" border="0" cellpadding="0" cellspacing="0" summary="sub structure">
        <tr>
          <td colspan="2"><img src="img/leer.gif" alt="" width="1" height="9"></td>
        </tr>
        <tr>
          <td valign="top" class="navtext"><?php 
		  
		  // create backend main navigation
		  if($do == 'default') {
		  	echo '<strong class="navtexta"><a href="phpwcms.php">HOME</a></strong>&nbsp;&nbsp;&nbsp;';
		  } else {
		  	echo '<a href="phpwcms.php">HOME</a>&nbsp;&nbsp;&nbsp;';
		  }
		  echo implode('&nbsp;&nbsp;&nbsp;', $wcsnav);


				?></td>
          <td align="right" valign="top" class="navtext"><a href="phpwcms.php?do=logout" target="_top"><?php echo $BL['be_nav_logout'] ?></a></td>
        </tr>
      </table></td>
	  <td valign="top" style="background-image:url(img/backend/backend_r3_c4.jpg); background-repeat:repeat-x; "><img src="img/backend/backend_r3_c7.jpg" alt="" width="15" height="40"></td>
  </tr>
	<tr bgcolor="#FFFFFF">
	  <td width="15" bgcolor="#FFFFFF" style="background-image:url(img/backend/preinfo2_r7_c2.gif);background-repeat:repeat-y;"><img src="img/leer.gif" alt="" width="15" height="1"></td>
		<td width="175" valign="top" bgcolor="#FFFFFF"><?php 

		echo $subnav;
		echo '<img src="img/leer.gif" width="1" height="5" alt="" /><br /><span class="title">';
		echo $BL['usr_online'];
		echo '</span><br /><img src="img/leer.gif" width="1" height="3" alt="" /><br />';
		echo online_users($db, "<br />", "<span class=\"subnavinactive\">|</span>");

		?><img src="img/leer.gif" alt="" width="175" height="1"></td>
      <td width="10" bgcolor="#FFFFFF"><img src="img/leer.gif" alt="" width="10" height="1"></td>
      <td width="15" bgcolor="#FFFFFF" style="background-image:url(img/backend/dividerA.gif);background-repeat:repeat-y;"><img src="img/leer.gif" alt="" width="15" height="200"></td>
      <td width="540" valign="top" bgcolor="#FFFFFF" class="v11b width540">{STATUS_MESSAGE}<?php
	 
	 
      switch($do) {

      	case "profile":	//Profile
      	switch($p) {
      		case 1:		include(PHPWCMS_ROOT.'/include/inc_tmpl/profile.data.tmpl.php');
      					break;
      		default:	include(PHPWCMS_ROOT.'/include/inc_tmpl/profile.account.tmpl.php');
      	}
      	break;

      	case "files":	//Hochladen sowie Downloaden und Verwalten von Dateien
      	switch($p) {
      		case 8:		//FTP File upload
						include(PHPWCMS_ROOT.'/include/inc_lib/files.create.dirmenu.inc.php');
						include(PHPWCMS_ROOT.'/include/inc_tmpl/files.ftptakeover.tmpl.php');
						break;
					
						// Multiple, queued file upload
			case 9:		include(PHPWCMS_ROOT.'/include/inc_lib/files.create.dirmenu.inc.php');
						include(PHPWCMS_ROOT.'/include/inc_lib/files.multipleupload.inc.php');
						include(PHPWCMS_ROOT.'/include/inc_tmpl/files.multipleupload.tmpl.php');
						break;
						
      		default:	include(PHPWCMS_ROOT.'/include/inc_tmpl/files.reiter.tmpl.php'); //Files Navigation/Reiter
      		switch($files_folder) {
      			case 0:	//Listing der Privaten Dateien
      			if(isset($_GET["mkdir"]) || (isset($_POST["dir_aktion"]) && intval($_POST["dir_aktion"]) == 1) ) {
					include(PHPWCMS_ROOT.'/include/inc_tmpl/files.private.newdir.tmpl.php');
				}
      			if(isset($_GET["editdir"]) || (isset($_POST["dir_aktion"]) && intval($_POST["dir_aktion"]) == 2) ) {
					include(PHPWCMS_ROOT.'/include/inc_tmpl/files.private.editdir.tmpl.php');
				}
      			if(isset($_GET["upload"]) || (isset($_POST["file_aktion"]) && intval($_POST["file_aktion"]) == 1) ) {
      				include(PHPWCMS_ROOT.'/include/inc_lib/files.create.dirmenu.inc.php');
      				include(PHPWCMS_ROOT.'/include/inc_tmpl/files.private.upload.tmpl.php');
      			}
      			if(isset($_GET["editfile"]) || (isset($_POST["file_aktion"]) && intval($_POST["file_aktion"]) == 2) ) {
      				include(PHPWCMS_ROOT.'/include/inc_lib/files.create.dirmenu.inc.php');
      				include(PHPWCMS_ROOT.'/include/inc_tmpl/files.private.editfile.tmpl.php');
      			}
      			include(PHPWCMS_ROOT.'/include/inc_lib/files.private-functions.inc.php'); //Listing-Funktionen einf�gen
      			include(PHPWCMS_ROOT.'/include/inc_lib/files.private.additions.inc.php'); //Zus�tzliche Private Funktionen
      			break;
      			case 1: //Funktionen zum Listen von Public Files
      			include(PHPWCMS_ROOT.'/include/inc_lib/files.public-functions.inc.php'); //Public Listing-Funktionen einf�gen
      			include(PHPWCMS_ROOT.'/include/inc_tmpl/files.public.list.tmpl.php'); //Elemetares f�r Public Listing
      			break;
      			case 2:	//Dateien im Papierkorb
      			include(PHPWCMS_ROOT.'/include/inc_tmpl/files.private.trash.tmpl.php');
      			break;
      			case 3:	//Dateisuche
      			include(PHPWCMS_ROOT.'/include/inc_tmpl/files.search.tmpl.php');
      			break;
      		}
      		include(PHPWCMS_ROOT.'/include/inc_tmpl/files.abschluss.tmpl.php'); //Abschlie�ende Tabellenzeile = dicke Linie
      	}
      	break;

      	case "chat":	//Chat
      	switch($p) {
      		case 0: include(PHPWCMS_ROOT.'/include/inc_tmpl/chat.main.tmpl.php'); break; //Chat Startseite
      		case 1: include(PHPWCMS_ROOT.'/include/inc_tmpl/chat.list.tmpl.php'); break; //Chat/Listing
      	}
      	break;

		case "messages":	//Messages
      	switch($p) {
      		case 0: include(PHPWCMS_ROOT.'/include/inc_tmpl/message.center.tmpl.php'); break; //Messages Overview
      		case 1: include(PHPWCMS_ROOT.'/include/inc_tmpl/message.send.tmpl.php');   break;	//New Message
      		case 2: //Newsletter subscription
      		if($_SESSION["wcs_user_admin"] == 1) include(PHPWCMS_ROOT.'/include/inc_tmpl/message.subscription.tmpl.php');
      		break;
      		case 3: //Newsletter
      		if($_SESSION["wcs_user_admin"] == 1) include(PHPWCMS_ROOT.'/include/inc_tmpl/newsletter.list.tmpl.php');
      		break;
      		case 4: //Newsletter subscribers
      		if($_SESSION["wcs_user_admin"] == 1) {
				include(PHPWCMS_ROOT.'/include/inc_tmpl/message.subscribers.tmpl.php');
			}
      		break;	
      	}
      	break;

      	case "modules":	//Modules
		
			if($p == 2 && $phpwcms["gt_mod"]) { //enabled/disable GT MOD
				// include language vars for J�r�me's Graphical Text MOD
      			include(PHPWCMS_ROOT.'/include/inc_module/mod_graphical_text/main.inc.php');
      		}
			
			// if a module is selected
			if(isset($phpwcms['modules'][$module])) {
			
				include($phpwcms['modules'][$module]['path'].'backend.default.php');
			
			}
			
			break;

      	case "admin":	//Administration
      	if($_SESSION["wcs_user_admin"] == 1) {
      		switch($p) {
      			case 0: //User Administration
      			switch(!empty($_GET['s']) ? intval($_GET["s"]) : 0) {
      				case 1: include(PHPWCMS_ROOT.'/include/inc_tmpl/admin.newuser.tmpl.php');  break; //New User
      				case 2: include(PHPWCMS_ROOT.'/include/inc_tmpl/admin.edituser.tmpl.php'); break; //Edit User
      			}
      			include(PHPWCMS_ROOT.'/include/inc_tmpl/admin.listuser.tmpl.php');
      			break;
				
				case 1: //Users and Groups
				include(PHPWCMS_ROOT.'/include/inc_lib/admin.groups.inc.php');
				include(PHPWCMS_ROOT.'/include/inc_tmpl/admin.groups.'.$_entry['mode'].'.tmpl.php');				
				break;
				
				case 2: //Settings
				include(PHPWCMS_ROOT.'/include/inc_tmpl/admin.settings.tmpl.php');				
				break;
				
				case 5: //Keywords
				include(PHPWCMS_ROOT.'/include/inc_tmpl/admin.keyword.tmpl.php');	
				break;
				
      			case 6: //article structure
				
      			include(PHPWCMS_ROOT.'/include/inc_lib/admin.structure.inc.php');
      			if(isset($_GET["struct"])) {
					include(PHPWCMS_ROOT.'/include/inc_lib/article.contenttype.inc.php'); //loading array with actual content types
      				include(PHPWCMS_ROOT.'/include/inc_tmpl/admin.structform.tmpl.php');
      			} else {
      				include(PHPWCMS_ROOT.'/include/inc_tmpl/admin.structlist.tmpl.php');
      			}
      			break;
      			
				case 7:	//File Categories
      			include(PHPWCMS_ROOT.'/include/inc_tmpl/admin.filecat.tmpl.php');
      			break;
				
      			case 8:	//Page Layout
      			include(PHPWCMS_ROOT.'/include/inc_tmpl/admin.pagelayout.tmpl.php');
      			break;
      			
				case 10:	//Frontend CSS
      			include(PHPWCMS_ROOT.'/include/inc_tmpl/admin.frontendcss.tmpl.php');
      			break;
      			
				case 11:	//Templates
      			include(PHPWCMS_ROOT.'/include/inc_tmpl/admin.templates.tmpl.php');
      			break;
				
      			case 12:	//Default backend starup HTML
      			include(PHPWCMS_ROOT.'/include/inc_tmpl/admin.startup.tmpl.php');
      			break;
				
				//Default backend sitemap HTML
				case 13: 
				include(PHPWCMS_ROOT.'/include/inc_tmpl/admin.aliaslist.tmpl.php');
        		break;

      		}
      	}
      	break;

		// articles
      	case "articles":
			$_SESSION['image_browser_article'] = 0; //set how image file browser should work
			switch($p) {
				
				// List articles
				case 0: 
					include(PHPWCMS_ROOT.'/include/inc_tmpl/article.structlist.tmpl.php');
					break;
				
				// Edit/create article
				case 1:
				case 2: 
					include(PHPWCMS_ROOT.'/include/inc_lib/article.editcontent.inc.php');
					break;
				
				// News
				case 3:
					include(PHPWCMS_ROOT.'/include/inc_lib/news.inc.php');
					include(PHPWCMS_ROOT.'/include/inc_tmpl/news.tmpl.php');
					break;
			}
			break;
		
		// about phpwcms
		case "about":
			include(PHPWCMS_ROOT.'/include/inc_tmpl/about.tmpl.php');
			break;
		
		// start
		default:
			include(PHPWCMS_ROOT.'/include/inc_tmpl/be_start.tmpl.php');
			include(PHPWCMS_TEMPLATE.'inc_default/startup.php');

	}

?></td>
      <td width="15" bgcolor="#FFFFFF" style="background-image:url(img/backend/preinfo2_r7_c7.gif);background-repeat:repeat-y;background-position:right;"><img src="img/leer.gif" alt="" width="15" height="1"></td>
	</tr>
	<tr>
	  <td><img src="img/backend/backend_a_r1_c1.gif" alt="" width="15" height="15" border="0"></td>
	  <td colspan="4" valign="bottom" bgcolor="#FFFFFF" class="navtext"><img src="img/backend/backend_r6_c2.jpg" alt="" width="740" height="15" border="0"></td>
      <td valign="bottom" class="navtext"><img src="img/backend/backend_a_r1_c7.gif" alt="" width="15" height="15" border="0"></td>
  </tr>
	<tr>
	  <td width="15"><img src="img/leer.gif" alt="" width="14" height="17"></td>
	  <td colspan="5" valign="bottom" class="navtext" style="padding: 8px 0 15px 0;">
	  	<a href="phpwcms.php?do=about" title="<?php echo $BL['be_aboutlink_title'] ?>">phpwcms <?php echo $phpwcms["release"] ?> 
			&copy; 2003&#8212;<?php echo date('Y'); ?> Oliver Georgi. Licensed under GPL. Extensions are copyright
			of their respective owners.</a></td>
  </tr>
</table>
<?php

//Set Focus for chat insert filed
set_chat_focus($do, $p);

//If new message was sent -> automatic forwarding to message center
forward_to($forward_to_message_center, PHPWCMS_URL."phpwcms.php?do=messages", 2500);

$BE['BODY_CLOSE']['wz_tooltip.js'] = getJavaScriptSourceLink('include/inc_js/wz_js/wz_tooltip.js', '');

?>
<!-- phpwcms BODY_CLOSE -->
</body>
</html>
<?php

// retrieve complete processing time
list($usec, $sec) = explode(' ', microtime());
header('X-phpwcms-Page-Processed-In: ' . number_format(1000*($usec + $sec - $phpwcms_rendering_start), 3) .' ms');

$BE['HTML'] = ob_get_contents();
ob_end_clean();

//	replace special backend sections -> good for additional code like custom JavaScript, CSS and so on
//	<!-- phpwcms BODY_CLOSE -->
//	<!-- phpwcms BODY_OPEN -->
//	<!-- phpwcms HEADER -->

// special body onload JavaScript
if($body_onload) {
	$BE['HTML'] = str_replace('<body>', '<body '.$body_onload.'>', $BE['HTML']);
}

$BE['HEADER'][] = '	<!--[if lt IE 7]><style type="text/css"> body { behavior: url("include/inc_css/csshover2.htc"); } </style><![endif]-->';

// html head section
$BE['HTML'] = str_replace('<!-- phpwcms HEADER -->', implode(LF, $BE['HEADER']), $BE['HTML']);

// body open area
$BE['HTML'] = str_replace('<!-- phpwcms BODY_OPEN -->', implode(LF, $BE['BODY_OPEN']), $BE['HTML']);

// body close area
$BE['HTML'] = str_replace('<!-- phpwcms BODY_CLOSE -->', implode(LF, $BE['BODY_CLOSE']), $BE['HTML']);

// Show global system status message
$BE['HTML'] = str_replace('{STATUS_MESSAGE}', show_status_message(true), $BE['HTML']);

// return all
echo $BE['HTML'];

?>