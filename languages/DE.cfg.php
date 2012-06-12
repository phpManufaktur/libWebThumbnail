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

if ('á' != "\xc3\xa1") {
	// important: language files must be saved as UTF-8 (without BOM)
	trigger_error('The language file <b>'.basename(__FILE__).'</b> is damaged, it must be saved <b>UTF-8</b> encoded!', E_USER_ERROR);
}

if (!defined('CFG_CURRENCY'))
    define('CFG_CURRENCY', '%s €');
if (!defined('CFG_DATE_SEPARATOR'))
    define('CFG_DATE_SEPARATOR', '.');
if (!defined('CFG_DATE_STR'))
    define('CFG_DATE_STR', 'd.m.Y');
if (!defined('CFG_DATETIME_STR'))
    define('CFG_DATETIME_STR', 'd.m.Y H:i');
if (!defined('CFG_DAY_NAMES'))
    define('CFG_DAY_NAMES', "Sonntag, Montag, Dienstag, Mittwoch, Donnerstag, Freitag, Samstag");
if (!defined('CFG_DECIMAL_SEPARATOR'))
    define('CFG_DECIMAL_SEPARATOR', ',');
if (!defined('CFG_MONTH_NAMES'))
    define('CFG_MONTH_NAMES', "Januar,Februar,März,April,Mai,Juni,Juli,August,September,Oktober,November,Dezember");
if (!defined('CFG_THOUSAND_SEPARATOR'))
    define('CFG_THOUSAND_SEPARATOR', '.');
if (!defined('CFG_TIME_LONG_STR'))
    define('CFG_TIME_LONG_STR', 'H:i:s');
if (!defined('CFG_TIME_STR'))
    define('CFG_TIME_STR', 'H:i');
if (!defined('CFG_TIME_ZONE'))
    define('CFG_TIME_ZONE', 'Europe/Berlin');
if (!defined('CFG_TITLE'))
    define('CFG_TITLE', 'Herr,Frau');
