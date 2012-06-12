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

if (!file_exists(WB_PATH.'/modules/lib_webthumbnail/library.php')) {
  return "libWebThumbnail is not installed!";
}
require_once WB_PATH.'/modules/lib_webthumbnail/library.php';

if (!isset($url))
  return "Please spefify the requested website with the parameter 'url'.";

$thumb = new libWebThumbnail();
$params = $thumb->getParams();

$params[libWebThumbnail::PARAM_URL] = $url;
$params[libWebThumbnail::PARAM_ALT] = (isset($alt)) ? $alt : '';
$params[libWebThumbnail::PARAM_BROWSER] = (isset($browser)) ? strtolower($browser) : 'chrome';
$params[libWebThumbnail::PARAM_CLASS_IMAGE] = (isset($class_image)) ? $class_image : '';
$params[libWebThumbnail::PARAM_CLASS_LINK] = (isset($class_link)) ? $class_link : '';
$params[libWebThumbnail::PARAM_DO_LINK] = (isset($do_link) && (strtolower($do_link) == 'false')) ? false : true;
$params[libWebThumbnail::PARAM_FORMAT] = (isset($format)) ? strtolower($format) : 'png';
$params[libWebThumbnail::PARAM_HEIGHT] = (isset($height)) ? (int) $height : 196;
$params[libWebThumbnail::PARAM_HTML_STRICT] = (isset($html_strict) && (strtolower($html_strict) == 'true')) ? true : false;
$params[libWebThumbnail::PARAM_TARGET] = (isset($target)) ? strtolower($target) : '_blank';
$params[libWebThumbnail::PARAM_TITLE] = (isset($title)) ? $title : '';
$params[libWebThumbnail::PARAM_WIDTH] = (isset($width)) ? (int) $width : 196;
if (!$thumb->setParams($params))
  return $thumb->getError();
if (false === ($result = $thumb->getImageTag()))
  return $thumb->getError();
return $result;