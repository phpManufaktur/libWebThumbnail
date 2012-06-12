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

// first we need the LEPTON configuration file to init the CMS framwork
if (!defined('WB_PATH')) require_once '../../config.php';

// wb2lepton compatibility
if (!defined('LEPTON_PATH')) require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/wb2lepton.php';

require_once LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/library.php';

$thumbs = new libWebThumbnail();
if (!$thumbs->execUpdate()) {
  exit($thumbs->getError());
}
exit('OK');