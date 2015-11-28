<?php
/**
 * phpwcms content management system
 *
 * @author Oliver Georgi <og@phpwcms.org>
 * @copyright Copyright (c) 2002-2015, Oliver Georgi
 * @license http://opensource.org/licenses/GPL-2.0 GNU GPL-2
 * @link http://www.phpwcms.de
 *
 **/

session_start();
$phpwcms = array();
require_once '../../include/config/conf.inc.php';
require_once '../inc_lib/default.inc.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/helper.session.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/dbcon.inc.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/general.inc.php';
checkLogin();
validate_csrf_get_token('csrftoken');
require_once PHPWCMS_ROOT.'/include/inc_lib/backend.functions.inc.php';

if($_SESSION["wcs_user_admin"] == 1) { //Wenn Benutzer Admin-Rechte hat

	if(isset($_GET['do']) && intval($_GET['do']) === 9) {

		$sql = "TRUNCATE TABLE ".DB_PREPEND."phpwcms_cache";
		mysql_query($sql, $db) or die("error while deleting all cache entries");

	} else {

		update_cache();

	}

}

$ref = empty($_SESSION['REFERER_URL']) ? PHPWCMS_URL.get_login_file() : $_SESSION['REFERER_URL'];
headerRedirect($ref);
