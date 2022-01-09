<?php
/**
 * phpwcms content management system
 *
 * @author Oliver Georgi <og@phpwcms.org>
 * @copyright Copyright (c) 2002-2022, Oliver Georgi
 * @license http://opensource.org/licenses/GPL-2.0 GNU GPL-2
 * @link http://www.phpwcms.org
 *
 **/


// ----------------------------------------------------------------
// obligate check for phpwcms constants
if (!defined('PHPWCMS_ROOT')) {
	die("You Cannot Access This Script Directly, Have a Nice Day.");
}
// ----------------------------------------------------------------

// Content Type WYSIWYG HTML
$content["html"] = $row["acontent_html"];
$content["template"] = $row["acontent_template"];
$content["custom_fields"] = @unserialize($row["acontent_form"]);
$content["custom_fields"] = empty($content["custom_fields"]['cnt_fields']) ? array() : $content["custom_fields"]['cnt_fields'];