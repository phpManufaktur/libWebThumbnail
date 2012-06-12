<?php

/**
 * libWebThumbnail
 *
 * @desc api.webthumbnail.org
 * @author Lukasz Cepowski <lukasz[at]cepowski.pl>
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2012 Ralf Hertsch, phpManufaktur
 * @copyright 2012 Ognisco Software
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
  if (defined('LEPTON_VERSION'))
    include(WB_PATH.'/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root.'/framework/class.secure.php')) {
    include($root.'/framework/class.secure.php');
  }
  else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

// wb2lepton compatibility
if (!defined('LEPTON_PATH')) require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/wb2lepton.php';

require_once LEPTON_PATH.'/modules/droplets/functions.inc.php';

global $database;
global $admin;

$SQL = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."mod_webthumbnail` ( ".
    "`id` INT(11) NOT NULL AUTO_INCREMENT, ".
    "`url` TEXT, ".
    "`browser` ENUM('chrome','firefox','opera') NOT NULL DEFAULT 'firefox', ".
    "`img_name` VARCHAR(255) NOT NULl DEFAULT '', ".
    "`img_format` ENUM('png','jpg','gif') NOT NULL DEFAULT 'png', ".
    "`width` INT(11) NOT NULL DEFAULT '70', ".
    "`height` INT(11) NOT NULL DEFAULT '70', ".
    "`page_id` INT(11) NOT NULL DEFAULT '-1', ".
    "`timestamp` TIMESTAMP, ".
    "PRIMARY KEY (`id`), UNIQUE (`img_name`)".
    " ) ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET utf8 COLLATE utf8_general_ci";
$database->query($SQL);
if ($database->is_error()) {
  $admin->print_error($database->get_error());
}

if (!file_exists(_PATH.'/temp/unzip/')) @mkdir(LEPTON_PATH-'/temp/unzip/');
$result = wb_unpack_and_import(LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/droplets/droplet_webthumbnail.zip', LEPTON_PATH.'/temp/unzip/');

