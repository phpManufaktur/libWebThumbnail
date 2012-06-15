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

$module_directory = 'lib_webthumbnail';
$module_name = 'libWebThumbnail';
$module_function = (defined('LEPTON_VERSION')) ? 'library' : 'snippet';
$module_version = '0.11';
$module_status = 'BETA';
$module_platform = '2.8';
$module_author = 'Ralf Hertsch, Berlin (Germany)';
$module_license = 'MIT License (MIT)';
$module_description = 'Library for access to API of WebThumbnail.org, save files local';
$module_home = 'http://phpmanufaktur.de';
$module_guid = 'CE4DC5A9-CCA8-4A76-85B7-B7F52B99FCF3';

?>