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

// use LEPTON 2.x I18n for access to language files
if (!class_exists('LEPTON_Helper_I18n')) require_once LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/framework/LEPTON/Helper/I18n.php';

// load language depending onfiguration
if (!file_exists(LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/'.LANGUAGE.'.cfg.php')) {
  require_once(LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/EN.cfg.php');
}
else {
  require_once(LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/'.LANGUAGE.'.cfg.php');
}

global $I18n;
if (!is_object($I18n)) {
  $I18n = new LEPTON_Helper_I18n();
}
else {
  $I18n->addFile('DE.php', LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/');
}

require_once LEPTON_PATH.'/modules/'.basename(dirname(__FILE__)).'/webthumbnail/webthumbnail.php';


class libWebThumbnail {

  const PARAM_URL = 'url';
  const PARAM_BROWSER = 'browser';
  const PARAM_FORMAT = 'format';
  const PARAM_WIDTH = 'width';
  const PARAM_HEIGHT = 'height';
  const PARAM_ALT = 'alt';
  const PARAM_TITLE = 'title';
  const PARAM_TARGET = 'target';
  const PARAM_DO_LINK = 'link';
  const PARAM_HTML_STRICT = 'strict';
  const PARAM_CLASS_LINK = 'class_link';
  const PARAM_CLASS_IMAGE = 'class_image';
  const PARAM_PAGE_ID = 'page_id';

  private $params = array(
      self::PARAM_URL => 'https://phpmanufaktur.de',
      self::PARAM_BROWSER => webthumbnail::BROWSER_CHROME,
      self::PARAM_FORMAT => webthumbnail::FORMAT_PNG,
      self::PARAM_HEIGHT => webthumbnail::MIN_HEIGHT,
      self::PARAM_WIDTH => webthumbnail::MIN_WIDTH,
      self::PARAM_ALT => '',
      self::PARAM_TITLE => '',
      self::PARAM_TARGET => '_blank',
      self::PARAM_DO_LINK => true,
      self::PARAM_HTML_STRICT => false,
      self::PARAM_CLASS_IMAGE => '',
      self::PARAM_CLASS_LINK => '',
      self::PARAM_PAGE_ID => -1
      );

  const IMAGE_DIRECTORY = '/webthumbnail/';

  // the update cyclus in hours, default: 24*7 = 168 => each week
  const UPDATE_CYCLE = 168;

  private $error;

  protected $lang;
  protected $url_image_directory;
  protected $path_image_directory;

  protected static $config_file = 'config.json';
  protected static $table_prefix = TABLE_PREFIX;

  /**
   * Constructor for libWebThumbnail
   * Set $error on problems while initializing the library
   */
  public function __construct() {
    global $I18n;
    $this->lang = $I18n;
    date_default_timezone_set(CFG_TIME_ZONE);
    $this->url_image_directory = LEPTON_URL.MEDIA_DIRECTORY.self::IMAGE_DIRECTORY;
    $this->path_image_directory = LEPTON_PATH.MEDIA_DIRECTORY.self::IMAGE_DIRECTORY;
    if (!file_exists($this->path_image_directory)) {
      if (!mkdir($this->path_image_directory, 0755)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
            $this->lang->translate('Error: Can\'t create the directory {{ directory }}.',
                array('directory' => MEDIA_DIRECTORY.self::IMAGE_DIRECTORY))));
      }
    }
    if (file_exists(LEPTON_PATH.'/modules/lib_webthumbnail/config.json')) {
      $config = json_decode(file_get_contents(LEPTON_PATH.'/modules/lib_webthumbnail/config.json', true));
      if (isset($config['table_prefix']))
        self::$table_prefix = $config['table_prefix'];
    }
  } // __construct()

  /**
   * Get the parameters - this function is important for the droplet usage.
   *
   * @return array $params
   */
  public function getParams() {
    return $this->params;
  } // getParams()

  /**
   * Set the parameters - this function will be called by droplets.
   *
   * @param $params array
   * @return boolean true on success
   */
  public function setParams($params = array()) {
    $this->params = $params;
    $this->params[self::PARAM_PAGE_ID] = PAGE_ID;
    return true;
  } // setParams()

  /**
   * Set $this->error to $error
   *
   * @param $error string
   */
  protected function setError($error) {
    $this->error = $error;
  } // setError()

  /**
   * Get Error from $this->error;
   *
   * @return string $this->error
   */
  public function getError() {
    return $this->error;
  } // getError()

  /**
   * Check if $this->error is empty
   *
   * @return boolean
   */
  public function isError() {
    return (bool) !empty($this->error);
  } // isError

  /**
   * Create a unique GUID
   *
   * @return string
   */
  public static function createGUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
  } // createGUID()

  /**
   * Check if the desired URL for the specified browser, image type, width and
   * height is registered in the database and if the Thumbnail exists.
   * Return TRUE, the data record and the filename or FALSE and the proposed
   * filename.
   *
   * @param array $params all parameters
   * @param string reference $img_name existing or proposed image name
   * @return boolean
   */
  protected function checkThumbnailExists($params, &$img_name) {
    global $database;
    $SQL = sprintf("SELECT * FROM `%smod_webthumbnail` WHERE `url`='%s' AND ".
        "`browser`='%s' AND `img_format`='%s' AND `width`='%d' AND `height`='%d'",
        self::$table_prefix, $params[self::PARAM_URL], $params[self::PARAM_BROWSER],
        $params[self::PARAM_FORMAT], $params[self::PARAM_WIDTH],
        $params[self::PARAM_HEIGHT]);
    if (false === ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    if ($query->numRows() > 0) {
      // hit in the database records
      $data = $query->fetchRow(MYSQL_ASSOC);
      if (file_exists($this->path_image_directory.$data['img_name'])) {
        // file already exists
        $img_name = $data['img_name'];
        return true;
      }
      else {
        // url is registered but missing the file
        $img_name = $data['img_name'];
        return false;
      }
    }
    else {
      // this url is not registered
      $img_name = sprintf('%s.%s', self::createGUID(), $params[self::PARAM_FORMAT]);
      return false;
    }
  } // checkThumbnailExists()

  /**
   * Create a linked image tag from the $params.
   *
   * @param array $params
   * @param string $image_name
   * @return string complete image tag
   */
  protected function createImageTag($params, $image_name) {
    $img_tag = '';
    if ($params[self::PARAM_DO_LINK]) {
      $img_tag .= sprintf('<a %shref="%s"',
          !empty($params[self::PARAM_CLASS_LINK]) ? sprintf('class="%s" ',
              $params[self::PARAM_CLASS_LINK]) : '',
          $params[self::PARAM_URL]);
      if (!$params[self::PARAM_HTML_STRICT])
        $img_tag .= sprintf(' target="%s"', $params[self::PARAM_TARGET]);
      if (!empty($params[self::PARAM_TITLE]))
        $img_tag .= sprintf(' title="%s"', $params[self::PARAM_TITLE]);
      $img_tag .= '>';
    }
    $img_tag .= sprintf('<img %ssrc="%s%s" width="%d" height="%d" alt="%s"',
        !empty($params[self::PARAM_CLASS_IMAGE]) ? sprintf('class="%s" ',
              $params[self::PARAM_CLASS_LINK]) : '',
        $this->url_image_directory, $image_name, $params[self::PARAM_WIDTH],
        $params[self::PARAM_HEIGHT], $params[self::PARAM_ALT]);
    if (!empty($params[self::PARAM_TITLE]))
      $img_tag .= sprintf(' title="%s"', $params[self::PARAM_TITLE]);
    $img_tag .= '>';
    if ($params[self::PARAM_DO_LINK]) $img_tag .= '</a>';
    return $img_tag;
  } // createImageTag()

  /**
   * Write the WebThumbthunbnail to the file $image_name and create a data record
   *
   * @param array $params
   * @param string $image_name
   * @return boolean
   */
  protected function createThumbnail($params, $image_name) {
    global $database;
    // first step: create the thumbnail
    try {
      $thumb = new Webthumbnail($params[self::PARAM_URL]);
      $thumb->setBrowser($params[self::PARAM_BROWSER]);
      $thumb->setFormat($params[self::PARAM_FORMAT]);
      $thumb->setHeight($params[self::PARAM_HEIGHT]);
      $thumb->setWidth($params[self::PARAM_WIDTH]);
      $thumb->captureToFile($this->path_image_directory.$image_name);
    } catch (Exception $e) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, $e->getLine(), $e->getMessage()));
			return false;
    }
    // second step: write the data into to the database
    $SQL = sprintf("INSERT INTO `%smod_webthumbnail` (`url`,`browser`,`img_name`,".
        "`img_format`,`width`,`height`,`page_id`) VALUES ('%s','%s','%s','%s','%d',".
        "'%d','%d') ON DUPLICATE KEY UPDATE `url`='%s',`browser`='%s',`img_format`='%s',".
        "`width`='%d',`height`='%d',`page_id`='%d',`timestamp`='%s'",
        self::$table_prefix,
        $params[self::PARAM_URL],
        $params[self::PARAM_BROWSER],
        $image_name,
        $params[self::PARAM_FORMAT],
        $params[self::PARAM_WIDTH],
        $params[self::PARAM_HEIGHT],
        $params[self::PARAM_PAGE_ID],
        $params[self::PARAM_URL],
        $params[self::PARAM_BROWSER],
        $params[self::PARAM_FORMAT],
        $params[self::PARAM_WIDTH],
        $params[self::PARAM_HEIGHT],
        $params[self::PARAM_PAGE_ID],
        date('Y-m-d H:i:s')
        );
    if (!$database->query($SQL)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    return true;
  } // createThumbnail()

  /**
   * Called by the droplet [[webthumbnail]] to create and return a local saved
   * WebThumbnail of a website as complete image tag.
   *
   * @return boolean false or string image tag
   */
  public function getImageTag() {
    if (empty($this->params[self::PARAM_URL])) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
          $this->lang->translate('Error: No URL specified!')));
      return false;
    }
    // check if already a thumbnail exists
    $image_name = '';
    if ($this->checkThumbnailExists($this->params, $image_name)) {
      return $this->createImageTag($this->params, $image_name);
    }
    if (!$this->createThumbnail($this->params, $image_name)) return false;
    return $this->createImageTag($this->params, $image_name);
  } // getImageTag()

  /**
   * This function is called by cronjob.php and update each run one thumbnail
   * which is older than in self::UPDATE_CYCLE specified
   *
   * @return boolean
   */
  public function execUpdate() {
    global $database;
    // get the date in the past
    $past_date = date('Y-m-d H:i:s', mktime(date('H')-self::UPDATE_CYCLE,date('i'),date('s'),date('m'),date('d'),date('Y')));
    $SQL = sprintf("SELECT * FROM `%smod_webthumbnail` WHERE `timestamp`<'%s' ORDER BY `timestamp` ASC LIMIT 1",
        self::$table_prefix, $past_date);
    if (false === ($query = $database->query($SQL))) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
      return false;
    }
    if ($query->numRows() > 0) {
      $data = $query->fetchRow(MYSQL_ASSOC);
      $params = array(
          self::PARAM_URL => $data['url'],
          self::PARAM_BROWSER => $data['browser'],
          self::PARAM_FORMAT => $data['img_format'],
          self::PARAM_WIDTH => $data['width'],
          self::PARAM_HEIGHT => $data['height'],
          self::PARAM_PAGE_ID => $data['page_id']
          );
      if (!$this->createThumbnail($params, $data['img_name']))
        return false;
    }
    return true;
  } // execUpdate()

} // class libWebThumbnail