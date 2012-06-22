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
  if (defined('LEPTON_VERSION')) include (WB_PATH . '/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root . '/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root . '/framework/class.secure.php')) {
    include ($root . '/framework/class.secure.php');
  }
  else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

// wb2lepton compatibility
if (!defined('LEPTON_PATH'))
  require_once WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/wb2lepton.php';

if (file_exists(LEPTON_PATH . '/modules/droplets/functions.inc.php'))
  require_once LEPTON_PATH . '/modules/droplets/functions.inc.php';

if (!function_exists('wb_unpack_and_import')) {
  function wb_unpack_and_import($temp_file, $temp_unzip) {
    global $admin, $database;
    // Include the PclZip class file
    require_once (WB_PATH . '/include/pclzip/pclzip.lib.php');
    $errors = array();
    $count = 0;
    $archive = new PclZip($temp_file);
    $list = $archive->extract(PCLZIP_OPT_PATH, $temp_unzip);
    // now, open all *.php files and search for the header;
    // an exported droplet starts with "//:"
    if (false !== ($dh = opendir($temp_unzip))) {
      while (false !== ($file = readdir($dh))) {
        if ($file != "." && $file != "..") {
          if (preg_match('/^(.*)\.php$/i', $file, $name_match)) {
            // Name of the Droplet = Filename
            $name = $name_match[1];
            // Slurp file contents
            $lines = file($temp_unzip . '/' . $file);
            // First line: Description
            if (preg_match('#^//\:(.*)$#', $lines[0], $match)) {
              $description = $match[1];
            }
            // Second line: Usage instructions
            if (preg_match('#^//\:(.*)$#', $lines[1], $match)) {
              $usage = addslashes($match[1]);
            }
            // Remaining: Droplet code
            $code = implode('', array_slice($lines, 2));
            // replace 'evil' chars in code
            $tags = array('<?php', '?>', '<?');
            $code = addslashes(str_replace($tags, '', $code));
            // Already in the DB?
            $stmt = 'INSERT';
            $id = NULL;
            $found = $database->get_one("SELECT * FROM " . TABLE_PREFIX .
                "mod_droplets WHERE name='$name'");
            if ($found && $found > 0) {
              $stmt = 'REPLACE';
              $id = $found;
            }
            // execute
            $result = $database->query("$stmt INTO " . TABLE_PREFIX .
                "mod_droplets VALUES('$id','$name','$code','$description','" .
                time() . "','" . $admin->get_user_id() . "',1,0,0,0,'$usage')");
            if (!$database->is_error()) {
              $count++;
              $imports[$name] = 1;
            }
            else {
              $errors[$name] = $database->get_error();
            }
          }
        }
      }
      closedir($dh);
    }
    return array('count' => $count, 'errors' => $errors, 'imported' => $imports);
  } // end function wb_unpack_and_import()
}

global $database;
global $admin;

$table_prefix = TABLE_PREFIX;
if (file_exists(LEPTON_PATH.'/modules/lib_webthumbnail/config.json')) {
  $config = json_decode(file_get_contents(LEPTON_PATH.'/modules/lib_webthumbnail/config.json', true));
  if (isset($config['table_prefix']))
    $table_prefix = $config['table_prefix'];
}

$SQL = "CREATE TABLE IF NOT EXISTS `" . $table_prefix . "mod_webthumbnail` ( " .
  "`id` INT(11) NOT NULL AUTO_INCREMENT, " .
  "`url` TEXT, " .
  "`browser` ENUM('chrome','firefox','opera') NOT NULL DEFAULT 'firefox', " .
  "`img_name` VARCHAR(255) NOT NULl DEFAULT '', " .
  "`img_format` ENUM('png','jpg','gif') NOT NULL DEFAULT 'png', " .
  "`width` INT(11) NOT NULL DEFAULT '70', " .
  "`height` INT(11) NOT NULL DEFAULT '70', " .
  "`page_id` INT(11) NOT NULL DEFAULT '-1', " .
  "`timestamp` TIMESTAMP, " .
  "PRIMARY KEY (`id`), UNIQUE (`img_name`)" .
  " ) ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET utf8 COLLATE utf8_general_ci";
$database->query($SQL);
if ($database->is_error()) {
  $admin->print_error($database->get_error());
}

if (!file_exists(LEPTON_PATH.'/temp/unzip/')) @mkdir(LEPTON_PATH.'/temp/unzip/');
wb_unpack_and_import(LEPTON_PATH . '/modules/' . basename(dirname(__FILE__)) .
    '/droplets/droplet_webthumbnail.zip', LEPTON_PATH . '/temp/unzip/');

