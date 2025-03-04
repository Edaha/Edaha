<?php
/*
 * This file is part of kusaba.
 *
 * kusaba is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * kusaba is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * kusaba; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
/*
 * Static functions that don't fit anywhere else
 * Last Updated: $Date$

 * @author     $Author$

 * @package    kusaba

 * @version    $Revision$
 *
 */

class kxFunc
{
  /**
   * Cleans input
   *
   * @access  public
   * @param  array   Input data
   * @param  int           Iteration
   * @return  array   Cleaned data
   */
  public static function cleaninput(&$data, $i = 0)
  {

    // Don't parse arrays deeper than 10, as it's most likely someone trying to crash PHP
    if ($i > 10) {
      return;
    }

    foreach ($data as $k => $v) {
      if (is_array($v)) {
        self::cleaninput($data[$k], ++$i);
      } else {
        // Decimal places. Script kiddies might think they can try to access files outside the board
        $v = str_replace("&#46;&#46;/", "../", $v);

        // This litte bugger changes the formatting to be right-to-left, like on a hebrew locale.
        $v = str_replace('&#8238;', '', $v);

        // Null byte characters can mess with formatting as well, so we remove them
        $v = str_replace("\x00", '', $v);
        $v = str_replace(chr('0'), '', $v);
        $v = str_replace("\0", '', $v);

        $data[$k] = $v;
      }
    }
  }

  /**
   * Recursively cleans keys and values and
   * inserts them into the input array
   *
   * @access  public
   * @param  mixed    Input data
   * @param  array    Parsed data
   * @param  integer    Iteration
   * @return  array     Cleaned data
   */
  public static function parseinput(&$data, $input = array(), $i = 0)
  {
    if ($i > 10) {
      return $input;
    }

    foreach ($data as $k => $v) {
      if (is_array($v)) {
        $input[$k] = self::parseinput($data[$k], array(), $i++);
      } else {
        $k = self::cleanInputKey($k);
        $v = self::cleanInputVal($v, false);

        $input[$k] = $v;
      }
    }
    return $input;
  }
  /**
   * Clean up input key
   *
   * @access  public
   * @param  string    Key name
   * @return  string    Cleaned key name
   */
  public static function cleanInputKey($key)
  {
    if ($key == "") {
      return "";
    }

    $key = htmlspecialchars(urldecode($key));
    $key = str_replace("..", "", $key);
    $key = preg_replace("/\_\_(.+?)\_\_/", "", $key);
    $key = preg_replace("/^([\w\.\-\_]+)$/", "$1", $key);

    return $key;
  }

  /**
   * Clean up input data
   *
   * @access  public
   * @param  string    Input
   * @return  string    Cleaned Input
   */
  public static function cleanInputVal($txt)
  {
    if (empty($txt)) {
      return "";
    }

    $search = array("&#032;",
      "\r\n", "\n\r", "\r",
      "&",
      "<!--",
      "-->",
      "<",
      ">",
      "\n",
      '"',
      "<script",
      "$",
      "!",
      "'");
    $replace = array(" ",
      "\n", "\n", "\n",
      "&amp;",
      "&#60;&#33;--",
      "--&#62;",
      "&lt;",
      "&gt;",
      "<br />\n",
      "&quot;",
      "&#60;script",
      "&#036;",
      "&#33;",
      "&#39;");
    $txt = str_replace($search, $replace, $txt);

    $txt = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $txt);
    $txt = preg_replace("/&#(\d+?)([^\d;])/i", "&#\\1;\\2", $txt);

    return $txt;
  }

  /**
   * Returns only alphanumeric characters
   *
   * @access  public
   * @param  string    Input String
   * @param  string    Additional characters
   * @return  string    Parsed string
   */
  public static function alphanum($txt, $extra = "")
  {
    if ($extra) {
      $extra = preg_quote($extra, "/");
    }

    return preg_replace("/[^a-zA-Z0-9\-\_" . $extra . "]/", "", $txt);
  }

  /**
   * Generates a path for an application, with module if applicable
   *
   * @access  public
   * @param  string    application
   * @param  string    module (optional)
   * @return  mixed    Directory to app or module (or false if error)
   */
  public static function getAppDir($app, $module = '')
  {
    if (empty($app) || !is_string($app)) {
      return false;
    }

    $appFolder = KX_ROOT . '/application/' . $app;
    $modulesFolder = (defined("IN_MANAGE") && IN_MANAGE) ? 'manage' : 'public';

    if ($module) {
      return $appFolder . "/" . $modulesFolder . "/" . $module;
    } else {
      return $appFolder;
    }
  }

  /* Depending on the configuration, use either a meta refresh or a direct header */
  public static function doRedirect($url, $ispost = false, $file = '')
  {
    $headermethod = true;

    if ($headermethod) {
      if ($ispost) {
        header('Location: ' . $url);
      } else {
        die('<meta http-equiv="refresh" content="1;url=' . $url . '">');
      }
    } else {
      if ($ispost && $file != '') {
        echo sprintf(_('%s uploaded.'), $file) . ' ' . _('Updating pages.');
      } elseif ($ispost) {
        echo _('Post added.') . ' ' . _('Updating pages.'); # TEE COME BACK
      } else {
        echo '---> ---> --->';
      }
      die('<meta http-equiv="refresh" content="1;url=' . $url . '">');
    }
  }
  public static function showError($errormsg, $extended = '')
  {

    $twigData['styles'] = explode(':', kxEnv::Get('kx:css:sitestyles'));
    $twigData['errormsg'] = $errormsg;

    if ($extended != '') {
      $twigData['errormsgext'] = '<br /><div style="text-align: center;font-size: 1.25em;">' . $extended . '</div>';
    }

    kxTemplate::output('error', $twigData);

    die();
  }

  /**
   * Check if the supplied md5 file hash is currently recorded inside of the database, attached to a non-deleted post
   */
  public static function checkMD5($md5, $boardid)
  {

    $matches = kxDB::getinstance()->select("posts");
    $matches->innerJoin("post_files", "", "file_post = post_id AND file_board = post_board");
    $matches = $matches->fields("posts", array("post_id", "post_parent"))
      ->condition("post_board", $boardid)
      ->condition("post_deleted", 0)
      ->condition("file_md5", $md5)
      ->range(0, 1)
      ->execute()
      ->fetchAll();
    if (count($matches) > 0) {
      $real_parentid = ($matches[0]->post_parent == 0) ? $matches[0]->post_id : $matches[0]->post_parent;
      return array($real_parentid, $matches[0]->post_id);
    }

    return false;
  }

  private static function get_rnd_iv($iv_len)
  {
    $iv = '';
    while ($iv_len-- > 0) {
      $iv .= chr(mt_rand() & 0xff);
    }
    return $iv;
  }
  public static function encryptMD5($plain_text, $password, $iv_len = 16)
  {
    $plain_text .= "\x13";
    $n = strlen($plain_text);
    if ($n % 16) {
      $plain_text .= str_repeat("\0", 16 - ($n % 16));
    }

    $i = 0;
    $enc_text = self::get_rnd_iv($iv_len);
    $iv = substr($password ^ $enc_text, 0, 512);
    while ($i < $n) {
      $block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
      $enc_text .= $block;
      $iv = substr($block . $iv, 0, 512) ^ $password;
      $i += 16;
    }
    return base64_encode($enc_text);
  }
  public static function decryptMD5($enc_text, $password, $iv_len = 16)
  {
    $enc_text = base64_decode($enc_text);
    $n = strlen($enc_text);
    $i = $iv_len;
    $plain_text = '';
    $iv = substr($password ^ substr($enc_text, 0, $iv_len), 0, 512);
    while ($i < $n) {
      $block = substr($enc_text, $i, 16);
      $plain_text .= $block ^ pack('H*', md5($iv));
      $iv = substr($block . $iv, 0, 512) ^ $password;
      $i += 16;
    }
    return preg_replace('/\\x13\\x00*$/', '', $plain_text);
  }
  /**
   * Calculate the number of pages which will be needed for the supplied number of posts
   *
   * @param integer $boardtype Board type
   * @param integer $numposts Number of posts
   * @return integer Number of pages required
   */
  public static function pageCount($boardtype, $numposts)
  {
    if ($boardtype == 1) {
      return (floor($numposts / kxEnv::Get('kx:display:txtthreads')));
    } elseif ($boardtype == 3) {
      return (floor($numposts / 30));
    }

    return (floor($numposts / kxEnv::Get('kx:display:imgthreads')));
  }
  /**
   * Gets information about the filetype provided, which is specified in the manage panel
   *
   * @param string $filetype Filetype
   * @return array Filetype image, width, and height
   */
  public static function getFileTypeInfo($filetype)
  {

    $results = kxDB::getinstance()->select("filetypes")
      ->fields("filetypes", array("type_image", "type_image_width", "type_image_height"))
      ->condition("type_ext", $filetype)
      ->range(0, 1)
      ->execute()
      ->fetchAll();
    if (count($results) > 0) {
      foreach ($results as $line) {
        return array($line->type_image, $line->type_image_width, $line->type_image_height);
      }
    } else {
      /* No info was found, return the generic icon */
      return array('generic.png', 48, 48);
    }
  }

  public static function formatDate($timestamp, $type = 'post', $locale = 'en', $email = '')
  {
    $output = '';
    if ($email != '') {
      $output .= '<a href="mailto:' . $email . '">';
    }

    if ($type == 'post') {
      if ($locale == 'ja') {
        /* Format the timestamp japanese style */
        $fulldate = strftime("%Yy%mm%dd(DAYOFWEEK) %HH%MM%SS", $timestamp);
        $dayofweek = strftime('%a', $timestamp);

        /* I don't like this method, but I can't rely on PHP's locale settings to do it for me... */
        switch ($dayofweek) {
          case 'Sun':
            $dayofweek = '&#26085;';
            break;

          case 'Mon':
            $dayofweek = '&#26376;';
            break;

          case 'Tue':
            $dayofweek = '&#28779;';
            break;

          case 'Wed':
            $dayofweek = '&#27700;';
            break;

          case 'Thu':
            $dayofweek = '&#26408;';
            break;

          case 'Fri':
            $dayofweek = '&#37329;';
            break;

          case 'Sat':
            $dayofweek = '&#22303;';
            break;

          default:
            // The date must be in the correct language already, so let's convert it to unicode if it isn't already.
            $dayofweek = mb_convert_encoding($dayofweek, "UTF-8", "JIS, eucjp-win, sjis-win");
            break;

        }
        $fulldate = self::formatJapaneseNumbers($fulldate);
        //Convert the symbols for year, month, etc to unicode equivalents. We couldn't do this above beause the numbers would be formatted to japanese.
        $fulldate = str_replace(array("y", "m", "d", "H", "M", "S"), array("&#24180;", "&#26376;", "&#26085;", "&#26178;", "&#20998;", "&#31186;"), $fulldate);
        $fulldate = str_replace('DAYOFWEEK', $dayofweek, $fulldate);
        return $output . $fulldate . (($email != '') ? ('</a>') : (""));
      } else {
        /* Format the timestamp english style */
        return $output . date('y/m/d(D)H:i', $timestamp) . (($email != '') ? ('</a>') : (""));
      }
    }

    return $output . date('y/m/d(D)H:i', $timestamp) . (($email != '') ? ('</a>') : (""));
  }
  public static function formatJapaneseNumbers($input)
  {
    $patterns = array('/1/', '/2/', '/3/', '/4/', '/5/', '/6/', '/7/', '/8/', '/9/', '/0/');
    $replace = array('１', '２', '３', '４', '５', '６', '７', '８', '９', '０');

    return preg_replace($patterns, $replace, $input);
  }

  /* <3 coda for this wonderful snippet
  print $contents to $filename by using a temporary file and renaming it */
  public static function outputToFile($filename, $contents, $board)
  {
    $tempfile = tempnam(KX_BOARD . '/' . $board . '/res', 'tmp'); /* Create the temporary file */
    $fp = fopen($tempfile, 'w');
    fwrite($fp, $contents);
    fclose($fp);
    /* If we aren't able to use the rename function, try the alternate method */
    if (!@rename($tempfile, $filename)) {
      copy($tempfile, $filename);
      unlink($tempfile);
    }
    chmod($filename, 0664); /* it was created 0600 */
  }

  public static function getManageSession()
  {

    $_session = (isset(kxEnv::$request['sid'])) ? kxEnv::$request['sid'] : '';

    // Do we have a session at all?
    if (!$_session) {
      return false;
    } else {
      // So far so good, let's check it
      $session_data = kxDB::getInstance()->select("manage_sessions")
        ->fields("manage_sessions")
        ->condition("session_id", $_session)
        ->execute()
        ->fetchAll();
      if (empty($session_data[0]->session_id)) {
        // No session found
        return false;
      } else if (empty($session_data[0]->session_staff_id)) {
        // No staffer assigned to that sid
        return false;
      } else {
        // Alright! Looks good so far. let's do some triple and quadruple checking though.

        // Check if the user ID is valid
        $userid = kxDB::getInstance()->select("staff")
          ->fields("staff", array("user_id"))
          ->condition("user_id", $session_data[0]->session_staff_id)
          ->execute()
          ->fetchField();

        if (!$userid) {
          // Welp...
          return false;
        }

        // Now, we'll check the IP address to see if it matches the stored one.
        $first_ip = preg_replace("/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/", "\\1.\\2.\\3", $session_data[0]->session_ip);
        $second_ip = preg_replace("/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/", "\\1.\\2.\\3", $_SERVER['REMOTE_ADDR']);

        if ($first_ip != $second_ip) {
          // Man you just can't win today can you?
          return false;
        }
        // Okay, last one I promise. Is our session expired?
        if ($session_data[0]->session_last_action < (time() - 60 * 60)) {
          // Argh!!
          return false;
        }

        // Congratulations!
        return true;
      }
    }
  }

  /**
   * Get the current manage user's ID and username
   *
   */
  public static function getManageUser(): ?array
  {
    if (kxFunc::getManageSession()) {
      $_session = kxEnv::$request['sid'];

      $session_data = kxDB::getInstance()->select("manage_sessions");
      $session_data->innerJoin("staff", "", "session_staff_id = user_id");
      $session_data = $session_data->fields("staff", ["user_id", "user_name"])
        ->condition("session_id", $_session)
        ->execute()
        ->fetchAssoc();

      return $session_data;
    }
  }

  public static function ConvertBytes($bytes)
  {
    // Thanks to an anonymous user for this
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0); // cleanup to make sure the value is an integer
    $exponent = floor(($bytes ? log($bytes) : 0) / log(1024)); // determine the offset (in powers of 1024) that is required to fit the given byte value
    $exponent = min($exponent, count($units) - 1); // clamp it so it doesn't exceed the maximum identifier in our unit array

    $bytes /= pow(1024, $exponent); // divide our number of bytes by the power granted by our exponent (since our number was >= our power, this gives a value with fractions such as 145.49572, of that unit)

    return round($bytes, 2) . $units[$exponent]; // return the rounded fraction (with 2 decimals) and what unit it relates to
  }

  public static function fullBoardList()
  {
    $sections = kxDB::getInstance()->select("sections")
      ->fields("sections")
      ->orderBy("section_order")
      ->execute()
      ->fetchAll();

    $boards = kxDB::getInstance()->select("boards")
      ->fields("boards", array('board_id', 'board_desc'))
      ->where("board_section = ?")
      ->orderBy("board_order")
      ->build();

    // Add boards to an array within their section
    foreach ($sections as &$section) {
      $boards->execute(array($section->id));
      $section->boards = $boards->fetchAll();
    }

    // Prepend boards with no section
    $boards->execute(array(0));
    return (array_merge($boards->fetchAll(), $sections));
  }

  public static function visibleBoardList()
  {
    $sections = kxDB::getInstance()->select("sections")
      ->fields("sections")
      ->orderBy("section_order")
      ->execute()
      ->fetchAll();

    $boards = kxDB::getInstance()->select("boards")
      ->fields("boards", array('board_id', 'board_desc', 'board_name'))
      ->where("board_section = ?")
      ->orderBy("board_order")
      ->build();

    // Add boards to an array within their section
    foreach ($sections as &$section) {
      $boards->execute(array($section->id));
      $section->boards = $boards->fetchAll();
    }

    return ($sections);
  }

  public static function deletePost($board_id, $post_id) {
    // Soft delete the post
    $fields['post_reviewed'] = 1;
    $fields['post_deleted'] = 1;
    $fields['post_delete_time'] = time();

    $update_post = kxDb::getInstance()->update("posts")
      ->fields($fields)
      ->condition("post_id", $post_id)
      ->condition("post_board", $board_id)
      ->execute();
    
    // Hard delete its images
    $post_files = kxDb::getInstance()->select("post_files")
      ->fields("post_files", ["file_board", "file_name"])
      ->condition("file_board", $board_id)
      ->condition("file_post", $post_id)
      ->execute()
      ->fetchAll();

    foreach ($post_files as $file) {  
      kxFunc::deleteFile($file->file_board, $file->file_name);   
    }
  }

  public static function deleteFile($board_id, $file_name) {
    $file_type = kxDb::getInstance()->select("post_files")
      ->fields("post_files", ["file_type"])
      ->condition("file_board", $board_id)
      ->condition("file_name", $file_name)
      ->execute()
      ->fetchField();
    
    if (isset($file_type)) {
      // TODO This should come from the cache if available
      $board_name = kxDb::getInstance()->select("boards")
        ->fields("boards", ["board_name"])
        ->condition("board_id", $board_id)
        ->execute()
        ->fetchField();
      
      $file_paths['main']    = KX_BOARD . '/' . $board_name . '/src/' . $file_name . '.' . $file_type;
      $file_paths['thumb']   = KX_BOARD . '/' . $board_name . '/thumb/' . $file_name . 's.' . $file_type;
      $file_paths['catalog'] = KX_BOARD . '/' . $board_name . '/src/' . $file_name . 'c.' . $file_type;

      foreach ($file_paths as $path) {
        if (file_exists($path)) {
          try {
            unlink($path);
          } catch (Exception $e) {
            kxFunc::showError('Error when deleting file: ' . $e->getMessage());
          }
        }
      }
    }

    $deleted = kxDb::getInstance()->delete("post_files")
      ->condition("file_board", $board_id)
      ->condition("file_name", $file_name)
      ->execute();
    
    return $deleted;
  }
}

class kxMb
{
  public static $kxCharset;
  private static $_caseFold;
  private static $_codeRanges;
  private static $_codeRange;
  private static $_table;

  /**
   * Convert a string between charsets
   *
   * @param   string    Input String
   * @param   string    Source char set
   * @param   string    Destination char set
   * @return  string    Converted string
   */
  public static function convertCharset($string, $sourceCharset, $destCharset = "UTF-8")
  {
    $sourceCharset = strtolower($sourceCharset);
    $destCharset = strtolower($destCharset);

    if ($sourceCharset == $destCharset) {
      return $string;
    }

    if (!is_object(self::$kxCharset)) {
      self::$kxCharset = new kxCharset();

      if (function_exists('mb_convert_encoding')) {
        self::$kxCharset->method = 'mbstrings';
      } elseif (function_exists('iconv')) {
        self::$kxCharset->method = 'iconv';
      } elseif (function_exists('recode_string')) {
        self::$kxCharset->method = 'recode';
      } else {
        self::$kxCharset->method = 'tables';
      }
    }

    $stringConverted = self::$kxCharset->convert($string, $sourceCharset, $destCharset);

    return $stringConverted ? $stringConverted : $string;
  }
  /**
   * mb_strtoupper wrapper
   *
   * @param   string  Input String
   * @return  string  Parsed string
   */
  public static function strtoupper($text)
  {
    if (0 && function_exists('mb_strtoupper')) {
      $encodings = array_map('strtolower', mb_list_encodings());

      if (count($encodings) && in_array(strtolower(kxEnv::get("kx:charset")), $encodings)) {
        return mb_strtoupper($text, strtoupper(kxEnv::get("kx:charset")));
      }
    }
    $convertBack = !(self::seemsUtf8($text));
    if ($convertBack) {
      if (strtoupper(kxEnv::get("kx:charset")) == "UTF-8") {
        return strtoupper($text);
      } else {
        $text = self::convertCharset($text, kxEnv::get("kx:charset"), "UTF-8");
      }
    }
    $utf8Map = self::utf8($text);

    $length = count($utf8Map);
    $matched = false;
    $replaced = array();
    $upperCase = array();

    for ($i = 0; $i < $length; $i++) {
      $char = $utf8Map[$i];

      if ($char < 128) {
        $str = strtoupper(chr($char));
        $strlen = strlen($str);
        for ($ii = 0; $ii < $strlen; $ii++) {
          $upper = ord(substr($str, $ii, 1));
        }
        $upperCase[] = $upper;
        $matched = true;

      } else {
        $matched = false;
        $keys = self::_findCase($char);
        $keyCount = count($keys);

        if (!empty($keys)) {
          foreach ($keys as $key => $value) {
            $matched = false;
            $replace = 0;
            if ($length > 1 && count($keys[$key]['lower']) > 1) {
              $j = 0;

              for ($ii = 0, $count = count($keys[$key]['lower']); $ii < $count; $ii++) {
                $nextChar = $utf8Map[$i + $ii];

                if (isset($nextChar) && ($nextChar == $keys[$key]['lower'][$j + $ii])) {
                  $replace++;
                }
              }
              if ($replace == $count) {
                $upperCase[] = $keys[$key]['upper'];
                $replaced = array_merge($replaced, array_values($keys[$key]['lower']));
                $matched = true;
                break 1;
              }
            } elseif ($length > 1 && $keyCount > 1) {
              $j = 0;
              for ($ii = 1; $ii < $keyCount; $ii++) {
                $nextChar = $utf8Map[$i + $ii - 1];

                if (in_array($nextChar, $keys[$ii]['lower'])) {

                  for ($jj = 0, $count = count($keys[$ii]['lower']); $jj < $count; $jj++) {
                    $nextChar = $utf8Map[$i + $jj];

                    if (isset($nextChar) && ($nextChar == $keys[$ii]['lower'][$j + $jj])) {
                      $replace++;
                    }
                  }
                  if ($replace == $count) {
                    $upperCase[] = $keys[$ii]['upper'];
                    $replaced = array_merge($replaced, array_values($keys[$ii]['lower']));
                    $matched = true;
                    break 2;
                  }
                }
              }
            }
            if ($keys[$key]['lower'][0] == $char) {
              $upperCase[] = $keys[$key]['upper'];
              $matched = true;
              break 1;
            }
          }
        }
      }
      if ($matched === false && !in_array($char, $replaced, true)) {
        $upperCase[] = $char;
      }
    }

    if ($convertBack) {
      return self::convertCharset(self::ascii($upperCase), "UTF-8", kxEnv::get("kx:charset"));
    } else {
      return self::ascii($upperCase);
    }
  }
  /**
   * mb_strtolower wrapper
   *
   * @param   string  Input String
   * @return  string  Parsed string
   */
  public static function strtolower($text)
  {
    if (function_exists('mb_strtolower')) {
      $encodings = array_map('strtolower', mb_list_encodings());

      if (count($encodings) && in_array(strtolower(kxEnv::get("kx:charset")), $encodings)) {
        return mb_strtolower($text, strtoupper(kxEnv::get("kx:charset")));
      }
    }
    $convertBack = self::seemsUtf8($text);
    if (!$convertBack) {
      if (strtoupper(kxEnv::get("kx:charset")) == "UTF-8") {
        return strtolower($text);
      } else {
        $text = convertCharset($text, kxEnv::get("kx:charset"), "UTF-8");
      }
    }
    $utf8Map = self::utf8($text);
    $length = count($utf8Map);
    $lowerCase = array();
    $matched = false;

    for ($i = 0; $i < $length; $i++) {
      $char = $utf8Map[$i];

      if ($char < 128) {
        $str = strtolower(chr($char));
        $strlen = strlen($str);
        for ($ii = 0; $ii < $strlen; $ii++) {
          $lower = ord(substr($str, $ii, 1));
        }
        $lowerCase[] = $lower;
        $matched = true;
      } else {
        $matched = false;
        $keys = self::_findCase($char, 'upper');

        if (!empty($keys)) {
          foreach ($keys as $key => $value) {
            if ($keys[$key]['upper'] == $char && count($keys[$key]['lower'][0]) === 1) {
              $lowerCase[] = $keys[$key]['lower'][0];
              $matched = true;
              break 1;
            }
          }
        }
      }
      if ($matched === false) {
        $lowerCase[] = $char;
      }
    }
    if ($convertBack) {
      return self::convertCharset(self::ascii($value), "UTF-8", kxEnv::get("kx:charset"));
    } else {
      return self::ascii($value);
    }
  }
  /**
   * mb_substr wrapper
   *
   * @param  string  Input String
   * @param  integer  Desired min. length
   * @return  string  Parsed string
   */
  public static function substr($text, $start, $length = null)
  {
    if ($start === 0 && $length === null) {
      return $text;
    }

    if (function_exists('mb_substr')) {

      $encodings = array_map('strtolower', mb_list_encodings());

      if (count($encodings) && in_array(strtolower(kxEnv::get("kx:charset")), $encodings)) {
        return $length ? mb_substr($text, $start, $length) : mb_substr($text, $start);
      }
    }
    $convertBack = false;
    if (!self::seemsUtf8($text)) {
      if (strtoupper(kxEnv::get("kx:charset")) == "UTF-8") {
        return $length ? substr($text, $start, $length) : substr($text, $start);
      } else {
        $convertBack = true;
        $text = convertCharset($text, kxEnv::get("kx:charset"), "UTF-8");
      }
    }

    $text = self::utf8($text);
    $stringCount = count($text);

    if ($start < 0) {
      $start = self::strlen($text) + $start;
    }

    for ($i = 1; $i <= $start; $i++) {
      unset($text[$i - 1]);
    }

    if ($length === null || count($text) < $length) {
      if ($convertBack) {
        return self::convertCharset(self::ascii($text), "UTF-8", kxEnv::get("kx:charset"));
      } else {
        return self::ascii($text);
      }
    }

    $text = array_values($text);

    $value = array();
    if ($length < 0) {
      $text = array_reverse($text);
      $legnth = abs($length);
      for ($i = 0; $i <= $length; $i++) {
        unset($text[$i - 1]);
      }
      $text = array_reverse($text);
      $value = $text;
    } else {
      for ($i = 0; $i < $length; $i++) {
        $value[] = $text[$i];
      }
    }

    if ($convertBack) {
      return self::convertCharset(self::ascii($value), "UTF-8", kxEnv::get("kx:charset"));
    } else {
      return self::ascii($value);
    }

  }
  /**
   * mb_strlen wrapper
   *
   * @param   string    Input String
   * @return  integer   String length
   */
  public static function strlen($text)
  {
    if (function_exists('mb_strlen')) {
      $encodings = array_map('strtolower', mb_list_encodings());

      if (count($encodings) && in_array(strtolower(kxEnv::get("kx:charset")), $encodings)) {
        return mb_strlen($text, strtoupper(kxEnv::get("kx:charset")));
      }
    }
    if (!self::seemsUtf8($text)) {
      if (strtoupper(kxEnv::get("kx:charset")) == "UTF-8") {
        return strlen($text);
      } else {
        $text = convertCharset($text, kxEnv::get("kx:charset"), "UTF-8");
      }
    }
    if (self::checkMultibyte($text)) {
      $text = self::utf8($text);
      return count($text);
    }
    return strlen($text);
  }
  /**
   * mb_stripos wrapper
   *
   * @param   string  Input haystack
   * @param   string  Input needle
   * @param   integer  D
   * @return  string  Parsed string
   * @since  2.0
   */
  public static function stripos($haystack, $needle, $offset = 0)
  {
    if (function_exists('mb_stripos')) {
      $encodings = mb_list_encodings();

      if (count($encodings) && in_array(strtolower(kxEnv::get("kx:charset")), $encodings)) {
        return mb_stripos($haystack, $needle, $offset, strtoupper(kxEnv::get("kx:charset")));
      }
    }
    if (!self::seemsUtf8($haystack)) {
      if (strtoupper(kxEnv::get("kx:charset")) == "UTF-8") {
        return stripos($haystack, $needle, $offset);
      } else {
        $text = convertCharset($haystack, kxEnv::get("kx:charset"), "UTF-8");
      }
    }
    if (!self::seemsUtf8($needle)) {
      if (strtoupper(kxEnv::get("kx:charset")) == "UTF-8") {
        return stripos($haystack, $needle, $offset);
      } else {
        $text = convertCharset($needle, kxEnv::get("kx:charset"), "UTF-8");
      }
    }
    if (self::checkMultibyte($haystack)) {
      $haystack = self::strtoupper($haystack);
      $needle = self::strtoupper($needle);
      return self::strpos($haystack, $needle, $offset);
    }
    return stripos($haystack, $needle, $offset);
  }

  /**
   * Converts a multibyte character string
   * to the decimal value of the character
   *
   * @param multibyte string $string
   * @return array
   * @access public
   * @static
   */
  public static function utf8($string)
  {
    $map = array();

    $values = array();
    $find = 1;
    $length = strlen($string);

    for ($i = 0; $i < $length; $i++) {
      $value = ord($string[$i]);

      if ($value < 128) {
        $map[] = $value;
      } else {
        if (empty($values)) {
          if ($value >= 252) {
            $find = 6;
          } elseif ($value >= 248) {
            $find = 5;
          } elseif ($value >= 240) {
            $find = 4;
          } elseif ($value >= 224) {
            $find = 3;
          } elseif ($value >= 192) {
            $find = 2;
          }

        }
        $values[] = $value;

        if (count($values) === $find) {
          switch ($find) {
            case 2:
              $map[] = ($values[0] - 192) * 64 + ($values[1] - 128);
              break;
            case 3:
              $map[] = ($values[0] - 224) * 4096 + ($values[1] - 128) * 64 + ($values[2] - 128);
              break;
            case 4:
              $map[] = ($values[0] - 240) * 262144 + ($values[1] - 128) * 4096 + ($values[2] - 128) * 64 + ($values[3] - 128);
              break;
            case 5:
              $map[] = ($values[0] - 248) * 16777216 + ($values[1] - 128) * 262144 + ($values[2] - 128) * 4096 + ($values[3] - 128) * 64 + ($values[4] - 128);
              break;
            case 6:
              $map[] = ($values[0] - 252) * 1073741824 + ($values[1] - 128) * 16777216 + ($values[2] - 128) * 262144 + ($values[3] - 128) * 4096 + ($values[4] - 128) * 64 + ($values[5] - 128);
              break;
          }
          $values = array();
          $find = 1;
        }
      }
    }
    return $map;
  }
  /**
   * Converts the decimal value of a multibyte character string
   * to a string
   *
   * @param array $array
   * @return string
   * @access public
   * @static
   */
  public static function ascii($array)
  {
    $ascii = '';

    foreach ($array as $utf8) {
      if ($utf8 < 128) {
        $ascii .= chr($utf8);
      } else if ($utf8 < 2048) {
        $ascii .= chr(192 + (($utf8 - ($utf8 % 64)) / 64));
        $ascii .= chr(128 + ($utf8 % 64));
      } else if ($utf8 < 65536) {
        $ascii .= chr(224 + (($utf8 - ($utf8 % 4096)) / 4096));
        $ascii .= chr(128 + ((($utf8 % 4096) - ($utf8 % 64)) / 64));
        $ascii .= chr(128 + ($utf8 % 64));
      } else if ($utf8 < 2097152) {
        $ascii .= chr(224 + (($utf8 - ($utf8 % 262144)) / 262144));
        $ascii .= chr(128 + ((($utf8 % 262144) - ($utf8 % 4096)) / 4096));
        $ascii .= chr(128 + ((($utf8 % 4096) - ($utf8 % 64)) / 64));
        $ascii .= chr(128 + ($utf8 % 64));
      }
    }
    return $ascii;
  }

  /**
   * Check the $string for multibyte characters
   * @param string $string value to test
   * @return boolean
   * @access public
   * @static
   */
  public static function checkMultibyte($string)
  {
    $length = strlen($string);

    for ($i = 0; $i < $length; $i++) {
      $value = ord(($string[$i]));
      if ($value > 128) {
        return true;
      }
    }
    return false;
  }
  public static function seemsUtf8($string)
  {
    for ($i = 0; $i < strlen($string); $i++) {
      if (ord($string[$i]) < 0x80) {
        continue;
      }
      # 0bbbbbbb
      elseif ((ord($string[$i]) & 0xE0) == 0xC0) {
        $n = 1;
      }
      # 110bbbbb
      elseif ((ord($string[$i]) & 0xF0) == 0xE0) {
        $n = 2;
      }
      # 1110bbbb
      elseif ((ord($string[$i]) & 0xF8) == 0xF0) {
        $n = 3;
      }
      # 11110bbb
      elseif ((ord($string[$i]) & 0xFC) == 0xF8) {
        $n = 4;
      }
      # 111110bb
      elseif ((ord($string[$i]) & 0xFE) == 0xFC) {
        $n = 5;
      }
      # 1111110b
      else {
        return false;
      }
      # Does not match any model
      for ($j = 0; $j < $n; $j++) { # n bytes matching 10bbbbbb follow ?
        if ((++$i == strlen($string)) || ((ord($string[$i]) & 0xC0) != 0x80)) {
          return false;
        }

      }
    }
    return true;
  }
  /**
   * Find the related code folding values for $char
   *
   * @param integer $char decimal value of character
   * @param string $type
   * @return array
   * @access private
   */
  private static function _findCase($char, $type = 'lower')
  {
    $value = false;
    $found = array();
    if (!isset(self::$_codeRange[$char])) {
      $range = self::_codepoint($char);
      if ($range === false) {
        return null;
      }
      self::$_caseFold[$range] = self::_getRange($range);
    }

    if (!self::$_codeRange[$char]) {
      return null;
    }
    self::$_table = self::$_codeRange[$char];
    $count = count(self::$_caseFold[self::$_table]);

    for ($i = 0; $i < $count; $i++) {
      if ($type === 'lower' && self::$_caseFold[self::$_table][$i][$type][0] === $char) {
        $found[] = self::$_caseFold[self::$_table][$i];
      } elseif ($type === 'upper' && self::$_caseFold[self::$_table][$i][$type] === $char) {
        $found[] = self::$_caseFold[self::$_table][$i];
      }
    }
    return $found;
  }

  private static function _codepoint($decimal)
  {
    if ($decimal > 128 && $decimal < 256) {
      $return = '0080_00ff'; // Latin-1 Supplement
    } elseif ($decimal < 384) {
      $return = '0100_017f'; // Latin Extended-A
    } elseif ($decimal < 592) {
      $return = '0180_024f'; // Latin Extended-B
    } elseif ($decimal < 688) {
      $return = '0250_02af'; // IPA Extensions
    } elseif ($decimal >= 880 && $decimal < 1024) {
      $return = '0370_03ff'; // Greek and Coptic
    } elseif ($decimal < 1280) {
      $return = '0400_04ff'; // Cyrillic
    } elseif ($decimal < 1328) {
      $return = '0500_052f'; // Cyrillic Supplement
    } elseif ($decimal < 1424) {
      $return = '0530_058f'; // Armenian
    } elseif ($decimal >= 7680 && $decimal < 7936) {
      $return = '1e00_1eff'; // Latin Extended Additional
    } elseif ($decimal < 8192) {
      $return = '1f00_1fff'; // Greek Extended
    } elseif ($decimal >= 8448 && $decimal < 8528) {
      $return = '2100_214f'; // Letterlike Symbols
    } elseif ($decimal < 8592) {
      $return = '2150_218f'; // Number Forms
    } elseif ($decimal >= 9312 && $decimal < 9472) {
      $return = '2460_24ff'; // Enclosed Alphanumerics
    } elseif ($decimal >= 11264 && $decimal < 11360) {
      $return = '2c00_2c5f'; // Glagolitic
    } elseif ($decimal < 11392) {
      $return = '2c60_2c7f'; // Latin Extended-C
    } elseif ($decimal < 11520) {
      $return = '2c80_2cff'; // Coptic
    } elseif ($decimal >= 65280 && $decimal < 65520) {
      $return = 'ff00_ffef'; // Halfwidth and Fullwidth Forms
    } else {
      $return = false;
    }
    self::$_codeRange[$decimal] = $return;
    return $return;
  }

  private static function _getRange($range)
  {
    if (empty(self::$_codeRanges[$range])) {
      /**
       * The upper field is the decimal value of the upper case character
       *
       * The lower filed is an array of the decimal values that form the lower case version of a character.
       *
       *  The status field is:
       * C: common case folding, common mappings shared by both simple and full mappings.
       * F: full case folding, mappings that cause strings to grow in length. Multiple characters are separated by spaces.
       * S: simple case folding, mappings to single characters where different from F.
       * T: special case for uppercase I and dotted uppercase I
       *   - For non-Turkic languages, this mapping is normally not used.
       *   - For Turkic languages (tr, az), this mapping can be used instead of the normal mapping for these characters.
       *     Note that the Turkic mappings do not maintain canonical equivalence without additional processing.
       *     See the discussions of case mapping in the Unicode Standard for more information.
       */
      switch ($range) {
        case '0080_00ff':
          self::$_codeRanges['0080_00ff'] = array(
            array('upper' => 181, 'status' => 'C', 'lower' => array(956)),
            array('upper' => 924, 'status' => 'C', 'lower' => array(181)),
            array('upper' => 192, 'status' => 'C', 'lower' => array(224)), /* LATIN CAPITAL LETTER A WITH GRAVE */
            array('upper' => 193, 'status' => 'C', 'lower' => array(225)), /* LATIN CAPITAL LETTER A WITH ACUTE */
            array('upper' => 194, 'status' => 'C', 'lower' => array(226)), /* LATIN CAPITAL LETTER A WITH CIRCUMFLEX */
            array('upper' => 195, 'status' => 'C', 'lower' => array(227)), /* LATIN CAPITAL LETTER A WITH TILDE */
            array('upper' => 196, 'status' => 'C', 'lower' => array(228)), /* LATIN CAPITAL LETTER A WITH DIAERESIS */
            array('upper' => 197, 'status' => 'C', 'lower' => array(229)), /* LATIN CAPITAL LETTER A WITH RING ABOVE */
            array('upper' => 198, 'status' => 'C', 'lower' => array(230)), /* LATIN CAPITAL LETTER AE */
            array('upper' => 199, 'status' => 'C', 'lower' => array(231)), /* LATIN CAPITAL LETTER C WITH CEDILLA */
            array('upper' => 200, 'status' => 'C', 'lower' => array(232)), /* LATIN CAPITAL LETTER E WITH GRAVE */
            array('upper' => 201, 'status' => 'C', 'lower' => array(233)), /* LATIN CAPITAL LETTER E WITH ACUTE */
            array('upper' => 202, 'status' => 'C', 'lower' => array(234)), /* LATIN CAPITAL LETTER E WITH CIRCUMFLEX */
            array('upper' => 203, 'status' => 'C', 'lower' => array(235)), /* LATIN CAPITAL LETTER E WITH DIAERESIS */
            array('upper' => 204, 'status' => 'C', 'lower' => array(236)), /* LATIN CAPITAL LETTER I WITH GRAVE */
            array('upper' => 205, 'status' => 'C', 'lower' => array(237)), /* LATIN CAPITAL LETTER I WITH ACUTE */
            array('upper' => 206, 'status' => 'C', 'lower' => array(238)), /* LATIN CAPITAL LETTER I WITH CIRCUMFLEX */
            array('upper' => 207, 'status' => 'C', 'lower' => array(239)), /* LATIN CAPITAL LETTER I WITH DIAERESIS */
            array('upper' => 208, 'status' => 'C', 'lower' => array(240)), /* LATIN CAPITAL LETTER ETH */
            array('upper' => 209, 'status' => 'C', 'lower' => array(241)), /* LATIN CAPITAL LETTER N WITH TILDE */
            array('upper' => 210, 'status' => 'C', 'lower' => array(242)), /* LATIN CAPITAL LETTER O WITH GRAVE */
            array('upper' => 211, 'status' => 'C', 'lower' => array(243)), /* LATIN CAPITAL LETTER O WITH ACUTE */
            array('upper' => 212, 'status' => 'C', 'lower' => array(244)), /* LATIN CAPITAL LETTER O WITH CIRCUMFLEX */
            array('upper' => 213, 'status' => 'C', 'lower' => array(245)), /* LATIN CAPITAL LETTER O WITH TILDE */
            array('upper' => 214, 'status' => 'C', 'lower' => array(246)), /* LATIN CAPITAL LETTER O WITH DIAERESIS */
            array('upper' => 216, 'status' => 'C', 'lower' => array(248)), /* LATIN CAPITAL LETTER O WITH STROKE */
            array('upper' => 217, 'status' => 'C', 'lower' => array(249)), /* LATIN CAPITAL LETTER U WITH GRAVE */
            array('upper' => 218, 'status' => 'C', 'lower' => array(250)), /* LATIN CAPITAL LETTER U WITH ACUTE */
            array('upper' => 219, 'status' => 'C', 'lower' => array(251)), /* LATIN CAPITAL LETTER U WITH CIRCUMFLEX */
            array('upper' => 220, 'status' => 'C', 'lower' => array(252)), /* LATIN CAPITAL LETTER U WITH DIAERESIS */
            array('upper' => 221, 'status' => 'C', 'lower' => array(253)), /* LATIN CAPITAL LETTER Y WITH ACUTE */
            array('upper' => 222, 'status' => 'C', 'lower' => array(254)), /* LATIN CAPITAL LETTER THORN */
            array('upper' => 223, 'status' => 'F', 'lower' => array(115, 115)), /* LATIN SMALL LETTER SHARP S */
          );
          break;
        case '0100_017f':
          self::$_codeRanges['0100_017f'] = array(
            array('upper' => 256, 'status' => 'C', 'lower' => array(257)), /* LATIN CAPITAL LETTER A WITH MACRON */
            array('upper' => 258, 'status' => 'C', 'lower' => array(259)), /* LATIN CAPITAL LETTER A WITH BREVE */
            array('upper' => 260, 'status' => 'C', 'lower' => array(261)), /* LATIN CAPITAL LETTER A WITH OGONEK */
            array('upper' => 262, 'status' => 'C', 'lower' => array(263)), /* LATIN CAPITAL LETTER C WITH ACUTE */
            array('upper' => 264, 'status' => 'C', 'lower' => array(265)), /* LATIN CAPITAL LETTER C WITH CIRCUMFLEX */
            array('upper' => 266, 'status' => 'C', 'lower' => array(267)), /* LATIN CAPITAL LETTER C WITH DOT ABOVE */
            array('upper' => 268, 'status' => 'C', 'lower' => array(269)), /* LATIN CAPITAL LETTER C WITH CARON */
            array('upper' => 270, 'status' => 'C', 'lower' => array(271)), /* LATIN CAPITAL LETTER D WITH CARON */
            array('upper' => 272, 'status' => 'C', 'lower' => array(273)), /* LATIN CAPITAL LETTER D WITH STROKE */
            array('upper' => 274, 'status' => 'C', 'lower' => array(275)), /* LATIN CAPITAL LETTER E WITH MACRON */
            array('upper' => 276, 'status' => 'C', 'lower' => array(277)), /* LATIN CAPITAL LETTER E WITH BREVE */
            array('upper' => 278, 'status' => 'C', 'lower' => array(279)), /* LATIN CAPITAL LETTER E WITH DOT ABOVE */
            array('upper' => 280, 'status' => 'C', 'lower' => array(281)), /* LATIN CAPITAL LETTER E WITH OGONEK */
            array('upper' => 282, 'status' => 'C', 'lower' => array(283)), /* LATIN CAPITAL LETTER E WITH CARON */
            array('upper' => 284, 'status' => 'C', 'lower' => array(285)), /* LATIN CAPITAL LETTER G WITH CIRCUMFLEX */
            array('upper' => 286, 'status' => 'C', 'lower' => array(287)), /* LATIN CAPITAL LETTER G WITH BREVE */
            array('upper' => 288, 'status' => 'C', 'lower' => array(289)), /* LATIN CAPITAL LETTER G WITH DOT ABOVE */
            array('upper' => 290, 'status' => 'C', 'lower' => array(291)), /* LATIN CAPITAL LETTER G WITH CEDILLA */
            array('upper' => 292, 'status' => 'C', 'lower' => array(293)), /* LATIN CAPITAL LETTER H WITH CIRCUMFLEX */
            array('upper' => 294, 'status' => 'C', 'lower' => array(295)), /* LATIN CAPITAL LETTER H WITH STROKE */
            array('upper' => 296, 'status' => 'C', 'lower' => array(297)), /* LATIN CAPITAL LETTER I WITH TILDE */
            array('upper' => 298, 'status' => 'C', 'lower' => array(299)), /* LATIN CAPITAL LETTER I WITH MACRON */
            array('upper' => 300, 'status' => 'C', 'lower' => array(301)), /* LATIN CAPITAL LETTER I WITH BREVE */
            array('upper' => 302, 'status' => 'C', 'lower' => array(303)), /* LATIN CAPITAL LETTER I WITH OGONEK */
            array('upper' => 304, 'status' => 'F', 'lower' => array(105, 775)), /* LATIN CAPITAL LETTER I WITH DOT ABOVE */
            array('upper' => 304, 'status' => 'T', 'lower' => array(105)), /* LATIN CAPITAL LETTER I WITH DOT ABOVE */
            array('upper' => 306, 'status' => 'C', 'lower' => array(307)), /* LATIN CAPITAL LIGATURE IJ */
            array('upper' => 308, 'status' => 'C', 'lower' => array(309)), /* LATIN CAPITAL LETTER J WITH CIRCUMFLEX */
            array('upper' => 310, 'status' => 'C', 'lower' => array(311)), /* LATIN CAPITAL LETTER K WITH CEDILLA */
            array('upper' => 313, 'status' => 'C', 'lower' => array(314)), /* LATIN CAPITAL LETTER L WITH ACUTE */
            array('upper' => 315, 'status' => 'C', 'lower' => array(316)), /* LATIN CAPITAL LETTER L WITH CEDILLA */
            array('upper' => 317, 'status' => 'C', 'lower' => array(318)), /* LATIN CAPITAL LETTER L WITH CARON */
            array('upper' => 319, 'status' => 'C', 'lower' => array(320)), /* LATIN CAPITAL LETTER L WITH MIDDLE DOT */
            array('upper' => 321, 'status' => 'C', 'lower' => array(322)), /* LATIN CAPITAL LETTER L WITH STROKE */
            array('upper' => 323, 'status' => 'C', 'lower' => array(324)), /* LATIN CAPITAL LETTER N WITH ACUTE */
            array('upper' => 325, 'status' => 'C', 'lower' => array(326)), /* LATIN CAPITAL LETTER N WITH CEDILLA */
            array('upper' => 327, 'status' => 'C', 'lower' => array(328)), /* LATIN CAPITAL LETTER N WITH CARON */
            array('upper' => 329, 'status' => 'F', 'lower' => array(700, 110)), /* LATIN SMALL LETTER N PRECEDED BY APOSTROPHE */
            array('upper' => 330, 'status' => 'C', 'lower' => array(331)), /* LATIN CAPITAL LETTER ENG */
            array('upper' => 332, 'status' => 'C', 'lower' => array(333)), /* LATIN CAPITAL LETTER O WITH MACRON */
            array('upper' => 334, 'status' => 'C', 'lower' => array(335)), /* LATIN CAPITAL LETTER O WITH BREVE */
            array('upper' => 336, 'status' => 'C', 'lower' => array(337)), /* LATIN CAPITAL LETTER O WITH DOUBLE ACUTE */
            array('upper' => 338, 'status' => 'C', 'lower' => array(339)), /* LATIN CAPITAL LIGATURE OE */
            array('upper' => 340, 'status' => 'C', 'lower' => array(341)), /* LATIN CAPITAL LETTER R WITH ACUTE */
            array('upper' => 342, 'status' => 'C', 'lower' => array(343)), /* LATIN CAPITAL LETTER R WITH CEDILLA */
            array('upper' => 344, 'status' => 'C', 'lower' => array(345)), /* LATIN CAPITAL LETTER R WITH CARON */
            array('upper' => 346, 'status' => 'C', 'lower' => array(347)), /* LATIN CAPITAL LETTER S WITH ACUTE */
            array('upper' => 348, 'status' => 'C', 'lower' => array(349)), /* LATIN CAPITAL LETTER S WITH CIRCUMFLEX */
            array('upper' => 350, 'status' => 'C', 'lower' => array(351)), /* LATIN CAPITAL LETTER S WITH CEDILLA */
            array('upper' => 352, 'status' => 'C', 'lower' => array(353)), /* LATIN CAPITAL LETTER S WITH CARON */
            array('upper' => 354, 'status' => 'C', 'lower' => array(355)), /* LATIN CAPITAL LETTER T WITH CEDILLA */
            array('upper' => 356, 'status' => 'C', 'lower' => array(357)), /* LATIN CAPITAL LETTER T WITH CARON */
            array('upper' => 358, 'status' => 'C', 'lower' => array(359)), /* LATIN CAPITAL LETTER T WITH STROKE */
            array('upper' => 360, 'status' => 'C', 'lower' => array(361)), /* LATIN CAPITAL LETTER U WITH TILDE */
            array('upper' => 362, 'status' => 'C', 'lower' => array(363)), /* LATIN CAPITAL LETTER U WITH MACRON */
            array('upper' => 364, 'status' => 'C', 'lower' => array(365)), /* LATIN CAPITAL LETTER U WITH BREVE */
            array('upper' => 366, 'status' => 'C', 'lower' => array(367)), /* LATIN CAPITAL LETTER U WITH RING ABOVE */
            array('upper' => 368, 'status' => 'C', 'lower' => array(369)), /* LATIN CAPITAL LETTER U WITH DOUBLE ACUTE */
            array('upper' => 370, 'status' => 'C', 'lower' => array(371)), /* LATIN CAPITAL LETTER U WITH OGONEK */
            array('upper' => 372, 'status' => 'C', 'lower' => array(373)), /* LATIN CAPITAL LETTER W WITH CIRCUMFLEX */
            array('upper' => 374, 'status' => 'C', 'lower' => array(375)), /* LATIN CAPITAL LETTER Y WITH CIRCUMFLEX */
            array('upper' => 376, 'status' => 'C', 'lower' => array(255)), /* LATIN CAPITAL LETTER Y WITH DIAERESIS */
            array('upper' => 377, 'status' => 'C', 'lower' => array(378)), /* LATIN CAPITAL LETTER Z WITH ACUTE */
            array('upper' => 379, 'status' => 'C', 'lower' => array(380)), /* LATIN CAPITAL LETTER Z WITH DOT ABOVE */
            array('upper' => 381, 'status' => 'C', 'lower' => array(382)), /* LATIN CAPITAL LETTER Z WITH CARON */
            array('upper' => 383, 'status' => 'C', 'lower' => array(115)), /* LATIN SMALL LETTER LONG S */
          );
          break;
        case '0180_024f':
          self::$_codeRanges['0180_024f'] = array(
            array('upper' => 385, 'status' => 'C', 'lower' => array(595)), /* LATIN CAPITAL LETTER B WITH HOOK */
            array('upper' => 386, 'status' => 'C', 'lower' => array(387)), /* LATIN CAPITAL LETTER B WITH TOPBAR */
            array('upper' => 388, 'status' => 'C', 'lower' => array(389)), /* LATIN CAPITAL LETTER TONE SIX */
            array('upper' => 390, 'status' => 'C', 'lower' => array(596)), /* LATIN CAPITAL LETTER OPEN O */
            array('upper' => 391, 'status' => 'C', 'lower' => array(392)), /* LATIN CAPITAL LETTER C WITH HOOK */
            array('upper' => 393, 'status' => 'C', 'lower' => array(598)), /* LATIN CAPITAL LETTER AFRICAN D */
            array('upper' => 394, 'status' => 'C', 'lower' => array(599)), /* LATIN CAPITAL LETTER D WITH HOOK */
            array('upper' => 395, 'status' => 'C', 'lower' => array(396)), /* LATIN CAPITAL LETTER D WITH TOPBAR */
            array('upper' => 398, 'status' => 'C', 'lower' => array(477)), /* LATIN CAPITAL LETTER REVERSED E */
            array('upper' => 399, 'status' => 'C', 'lower' => array(601)), /* LATIN CAPITAL LETTER SCHWA */
            array('upper' => 400, 'status' => 'C', 'lower' => array(603)), /* LATIN CAPITAL LETTER OPEN E */
            array('upper' => 401, 'status' => 'C', 'lower' => array(402)), /* LATIN CAPITAL LETTER F WITH HOOK */
            array('upper' => 403, 'status' => 'C', 'lower' => array(608)), /* LATIN CAPITAL LETTER G WITH HOOK */
            array('upper' => 404, 'status' => 'C', 'lower' => array(611)), /* LATIN CAPITAL LETTER GAMMA */
            array('upper' => 406, 'status' => 'C', 'lower' => array(617)), /* LATIN CAPITAL LETTER IOTA */
            array('upper' => 407, 'status' => 'C', 'lower' => array(616)), /* LATIN CAPITAL LETTER I WITH STROKE */
            array('upper' => 408, 'status' => 'C', 'lower' => array(409)), /* LATIN CAPITAL LETTER K WITH HOOK */
            array('upper' => 412, 'status' => 'C', 'lower' => array(623)), /* LATIN CAPITAL LETTER TURNED M */
            array('upper' => 413, 'status' => 'C', 'lower' => array(626)), /* LATIN CAPITAL LETTER N WITH LEFT HOOK */
            array('upper' => 415, 'status' => 'C', 'lower' => array(629)), /* LATIN CAPITAL LETTER O WITH MIDDLE TILDE */
            array('upper' => 416, 'status' => 'C', 'lower' => array(417)), /* LATIN CAPITAL LETTER O WITH HORN */
            array('upper' => 418, 'status' => 'C', 'lower' => array(419)), /* LATIN CAPITAL LETTER OI */
            array('upper' => 420, 'status' => 'C', 'lower' => array(421)), /* LATIN CAPITAL LETTER P WITH HOOK */
            array('upper' => 422, 'status' => 'C', 'lower' => array(640)), /* LATIN LETTER YR */
            array('upper' => 423, 'status' => 'C', 'lower' => array(424)), /* LATIN CAPITAL LETTER TONE TWO */
            array('upper' => 425, 'status' => 'C', 'lower' => array(643)), /* LATIN CAPITAL LETTER ESH */
            array('upper' => 428, 'status' => 'C', 'lower' => array(429)), /* LATIN CAPITAL LETTER T WITH HOOK */
            array('upper' => 430, 'status' => 'C', 'lower' => array(648)), /* LATIN CAPITAL LETTER T WITH RETROFLEX HOOK */
            array('upper' => 431, 'status' => 'C', 'lower' => array(432)), /* LATIN CAPITAL LETTER U WITH HORN */
            array('upper' => 433, 'status' => 'C', 'lower' => array(650)), /* LATIN CAPITAL LETTER UPSILON */
            array('upper' => 434, 'status' => 'C', 'lower' => array(651)), /* LATIN CAPITAL LETTER V WITH HOOK */
            array('upper' => 435, 'status' => 'C', 'lower' => array(436)), /* LATIN CAPITAL LETTER Y WITH HOOK */
            array('upper' => 437, 'status' => 'C', 'lower' => array(438)), /* LATIN CAPITAL LETTER Z WITH STROKE */
            array('upper' => 439, 'status' => 'C', 'lower' => array(658)), /* LATIN CAPITAL LETTER EZH */
            array('upper' => 440, 'status' => 'C', 'lower' => array(441)), /* LATIN CAPITAL LETTER EZH REVERSED */
            array('upper' => 444, 'status' => 'C', 'lower' => array(445)), /* LATIN CAPITAL LETTER TONE FIVE */
            array('upper' => 452, 'status' => 'C', 'lower' => array(454)), /* LATIN CAPITAL LETTER DZ WITH CARON */
            array('upper' => 453, 'status' => 'C', 'lower' => array(454)), /* LATIN CAPITAL LETTER D WITH SMALL LETTER Z WITH CARON */
            array('upper' => 455, 'status' => 'C', 'lower' => array(457)), /* LATIN CAPITAL LETTER LJ */
            array('upper' => 456, 'status' => 'C', 'lower' => array(457)), /* LATIN CAPITAL LETTER L WITH SMALL LETTER J */
            array('upper' => 458, 'status' => 'C', 'lower' => array(460)), /* LATIN CAPITAL LETTER NJ */
            array('upper' => 459, 'status' => 'C', 'lower' => array(460)), /* LATIN CAPITAL LETTER N WITH SMALL LETTER J */
            array('upper' => 461, 'status' => 'C', 'lower' => array(462)), /* LATIN CAPITAL LETTER A WITH CARON */
            array('upper' => 463, 'status' => 'C', 'lower' => array(464)), /* LATIN CAPITAL LETTER I WITH CARON */
            array('upper' => 465, 'status' => 'C', 'lower' => array(466)), /* LATIN CAPITAL LETTER O WITH CARON */
            array('upper' => 467, 'status' => 'C', 'lower' => array(468)), /* LATIN CAPITAL LETTER U WITH CARON */
            array('upper' => 469, 'status' => 'C', 'lower' => array(470)), /* LATIN CAPITAL LETTER U WITH DIAERESIS AND MACRON */
            array('upper' => 471, 'status' => 'C', 'lower' => array(472)), /* LATIN CAPITAL LETTER U WITH DIAERESIS AND ACUTE */
            array('upper' => 473, 'status' => 'C', 'lower' => array(474)), /* LATIN CAPITAL LETTER U WITH DIAERESIS AND CARON */
            array('upper' => 475, 'status' => 'C', 'lower' => array(476)), /* LATIN CAPITAL LETTER U WITH DIAERESIS AND GRAVE */
            array('upper' => 478, 'status' => 'C', 'lower' => array(479)), /* LATIN CAPITAL LETTER A WITH DIAERESIS AND MACRON */
            array('upper' => 480, 'status' => 'C', 'lower' => array(481)), /* LATIN CAPITAL LETTER A WITH DOT ABOVE AND MACRON */
            array('upper' => 482, 'status' => 'C', 'lower' => array(483)), /* LATIN CAPITAL LETTER AE WITH MACRON */
            array('upper' => 484, 'status' => 'C', 'lower' => array(485)), /* LATIN CAPITAL LETTER G WITH STROKE */
            array('upper' => 486, 'status' => 'C', 'lower' => array(487)), /* LATIN CAPITAL LETTER G WITH CARON */
            array('upper' => 488, 'status' => 'C', 'lower' => array(489)), /* LATIN CAPITAL LETTER K WITH CARON */
            array('upper' => 490, 'status' => 'C', 'lower' => array(491)), /* LATIN CAPITAL LETTER O WITH OGONEK */
            array('upper' => 492, 'status' => 'C', 'lower' => array(493)), /* LATIN CAPITAL LETTER O WITH OGONEK AND MACRON */
            array('upper' => 494, 'status' => 'C', 'lower' => array(495)), /* LATIN CAPITAL LETTER EZH WITH CARON */
            array('upper' => 496, 'status' => 'F', 'lower' => array(106, 780)), /* LATIN SMALL LETTER J WITH CARON */
            array('upper' => 497, 'status' => 'C', 'lower' => array(499)), /* LATIN CAPITAL LETTER DZ */
            array('upper' => 498, 'status' => 'C', 'lower' => array(499)), /* LATIN CAPITAL LETTER D WITH SMALL LETTER Z */
            array('upper' => 500, 'status' => 'C', 'lower' => array(501)), /* LATIN CAPITAL LETTER G WITH ACUTE */
            array('upper' => 502, 'status' => 'C', 'lower' => array(405)), /* LATIN CAPITAL LETTER HWAIR */
            array('upper' => 503, 'status' => 'C', 'lower' => array(447)), /* LATIN CAPITAL LETTER WYNN */
            array('upper' => 504, 'status' => 'C', 'lower' => array(505)), /* LATIN CAPITAL LETTER N WITH GRAVE */
            array('upper' => 506, 'status' => 'C', 'lower' => array(507)), /* LATIN CAPITAL LETTER A WITH RING ABOVE AND ACUTE */
            array('upper' => 508, 'status' => 'C', 'lower' => array(509)), /* LATIN CAPITAL LETTER AE WITH ACUTE */
            array('upper' => 510, 'status' => 'C', 'lower' => array(511)), /* LATIN CAPITAL LETTER O WITH STROKE AND ACUTE */
            array('upper' => 512, 'status' => 'C', 'lower' => array(513)), /* LATIN CAPITAL LETTER A WITH DOUBLE GRAVE */
            array('upper' => 514, 'status' => 'C', 'lower' => array(515)), /* LATIN CAPITAL LETTER A WITH INVERTED BREVE */
            array('upper' => 516, 'status' => 'C', 'lower' => array(517)), /* LATIN CAPITAL LETTER E WITH DOUBLE GRAVE */
            array('upper' => 518, 'status' => 'C', 'lower' => array(519)), /* LATIN CAPITAL LETTER E WITH INVERTED BREVE */
            array('upper' => 520, 'status' => 'C', 'lower' => array(521)), /* LATIN CAPITAL LETTER I WITH DOUBLE GRAVE */
            array('upper' => 522, 'status' => 'C', 'lower' => array(523)), /* LATIN CAPITAL LETTER I WITH INVERTED BREVE */
            array('upper' => 524, 'status' => 'C', 'lower' => array(525)), /* LATIN CAPITAL LETTER O WITH DOUBLE GRAVE */
            array('upper' => 526, 'status' => 'C', 'lower' => array(527)), /* LATIN CAPITAL LETTER O WITH INVERTED BREVE */
            array('upper' => 528, 'status' => 'C', 'lower' => array(529)), /* LATIN CAPITAL LETTER R WITH DOUBLE GRAVE */
            array('upper' => 530, 'status' => 'C', 'lower' => array(531)), /* LATIN CAPITAL LETTER R WITH INVERTED BREVE */
            array('upper' => 532, 'status' => 'C', 'lower' => array(533)), /* LATIN CAPITAL LETTER U WITH DOUBLE GRAVE */
            array('upper' => 534, 'status' => 'C', 'lower' => array(535)), /* LATIN CAPITAL LETTER U WITH INVERTED BREVE */
            array('upper' => 536, 'status' => 'C', 'lower' => array(537)), /* LATIN CAPITAL LETTER S WITH COMMA BELOW */
            array('upper' => 538, 'status' => 'C', 'lower' => array(539)), /* LATIN CAPITAL LETTER T WITH COMMA BELOW */
            array('upper' => 540, 'status' => 'C', 'lower' => array(541)), /* LATIN CAPITAL LETTER YOGH */
            array('upper' => 542, 'status' => 'C', 'lower' => array(543)), /* LATIN CAPITAL LETTER H WITH CARON */
            array('upper' => 544, 'status' => 'C', 'lower' => array(414)), /* LATIN CAPITAL LETTER N WITH LONG RIGHT LEG */
            array('upper' => 546, 'status' => 'C', 'lower' => array(547)), /* LATIN CAPITAL LETTER OU */
            array('upper' => 548, 'status' => 'C', 'lower' => array(549)), /* LATIN CAPITAL LETTER Z WITH HOOK */
            array('upper' => 550, 'status' => 'C', 'lower' => array(551)), /* LATIN CAPITAL LETTER A WITH DOT ABOVE */
            array('upper' => 552, 'status' => 'C', 'lower' => array(553)), /* LATIN CAPITAL LETTER E WITH CEDILLA */
            array('upper' => 554, 'status' => 'C', 'lower' => array(555)), /* LATIN CAPITAL LETTER O WITH DIAERESIS AND MACRON */
            array('upper' => 556, 'status' => 'C', 'lower' => array(557)), /* LATIN CAPITAL LETTER O WITH TILDE AND MACRON */
            array('upper' => 558, 'status' => 'C', 'lower' => array(559)), /* LATIN CAPITAL LETTER O WITH DOT ABOVE */
            array('upper' => 560, 'status' => 'C', 'lower' => array(561)), /* LATIN CAPITAL LETTER O WITH DOT ABOVE AND MACRON */
            array('upper' => 562, 'status' => 'C', 'lower' => array(563)), /* LATIN CAPITAL LETTER Y WITH MACRON */
            array('upper' => 570, 'status' => 'C', 'lower' => array(11365)), /* LATIN CAPITAL LETTER A WITH STROKE */
            array('upper' => 571, 'status' => 'C', 'lower' => array(572)), /* LATIN CAPITAL LETTER C WITH STROKE */
            array('upper' => 573, 'status' => 'C', 'lower' => array(410)), /* LATIN CAPITAL LETTER L WITH BAR */
            array('upper' => 574, 'status' => 'C', 'lower' => array(11366)), /* LATIN CAPITAL LETTER T WITH DIAGONAL STROKE */
            array('upper' => 577, 'status' => 'C', 'lower' => array(578)), /* LATIN CAPITAL LETTER GLOTTAL STOP */
            array('upper' => 579, 'status' => 'C', 'lower' => array(384)), /* LATIN CAPITAL LETTER B WITH STROKE */
            array('upper' => 580, 'status' => 'C', 'lower' => array(649)), /* LATIN CAPITAL LETTER U BAR */
            array('upper' => 581, 'status' => 'C', 'lower' => array(652)), /* LATIN CAPITAL LETTER TURNED V */
            array('upper' => 582, 'status' => 'C', 'lower' => array(583)), /* LATIN CAPITAL LETTER E WITH STROKE */
            array('upper' => 584, 'status' => 'C', 'lower' => array(585)), /* LATIN CAPITAL LETTER J WITH STROKE */
            array('upper' => 586, 'status' => 'C', 'lower' => array(587)), /* LATIN CAPITAL LETTER SMALL Q WITH HOOK TAIL */
            array('upper' => 588, 'status' => 'C', 'lower' => array(589)), /* LATIN CAPITAL LETTER R WITH STROKE */
            array('upper' => 590, 'status' => 'C', 'lower' => array(591)), /* LATIN CAPITAL LETTER Y WITH STROKE */
          );
          break;
        case '0250_02af':
          self::$_codeRanges['0250_02af'] = array(
            array('upper' => 422, 'status' => 'C', 'lower' => array(640)),
          );
          break;
        case '0370_03ff':
          self::$_codeRanges['0370_03ff'] = array(
            array('upper' => 902, 'status' => 'C', 'lower' => array(940)), /* GREEK CAPITAL LETTER ALPHA WITH TONOS */
            array('upper' => 904, 'status' => 'C', 'lower' => array(941)), /* GREEK CAPITAL LETTER EPSILON WITH TONOS */
            array('upper' => 905, 'status' => 'C', 'lower' => array(942)), /* GREEK CAPITAL LETTER ETA WITH TONOS */
            array('upper' => 906, 'status' => 'C', 'lower' => array(943)), /* GREEK CAPITAL LETTER IOTA WITH TONOS */
            array('upper' => 908, 'status' => 'C', 'lower' => array(972)), /* GREEK CAPITAL LETTER OMICRON WITH TONOS */
            array('upper' => 910, 'status' => 'C', 'lower' => array(973)), /* GREEK CAPITAL LETTER UPSILON WITH TONOS */
            array('upper' => 911, 'status' => 'C', 'lower' => array(974)), /* GREEK CAPITAL LETTER OMEGA WITH TONOS */
            array('upper' => 913, 'status' => 'C', 'lower' => array(945)), /* GREEK CAPITAL LETTER ALPHA */
            array('upper' => 914, 'status' => 'C', 'lower' => array(946)), /* GREEK CAPITAL LETTER BETA */
            array('upper' => 915, 'status' => 'C', 'lower' => array(947)), /* GREEK CAPITAL LETTER GAMMA */
            array('upper' => 916, 'status' => 'C', 'lower' => array(948)), /* GREEK CAPITAL LETTER DELTA */
            array('upper' => 917, 'status' => 'C', 'lower' => array(949)), /* GREEK CAPITAL LETTER EPSILON */
            array('upper' => 918, 'status' => 'C', 'lower' => array(950)), /* GREEK CAPITAL LETTER ZETA */
            array('upper' => 919, 'status' => 'C', 'lower' => array(951)), /* GREEK CAPITAL LETTER ETA */
            array('upper' => 920, 'status' => 'C', 'lower' => array(952)), /* GREEK CAPITAL LETTER THETA */
            array('upper' => 921, 'status' => 'C', 'lower' => array(953)), /* GREEK CAPITAL LETTER IOTA */
            array('upper' => 922, 'status' => 'C', 'lower' => array(954)), /* GREEK CAPITAL LETTER KAPPA */
            array('upper' => 923, 'status' => 'C', 'lower' => array(955)), /* GREEK CAPITAL LETTER LAMDA */
            array('upper' => 924, 'status' => 'C', 'lower' => array(956)), /* GREEK CAPITAL LETTER MU */
            array('upper' => 925, 'status' => 'C', 'lower' => array(957)), /* GREEK CAPITAL LETTER NU */
            array('upper' => 926, 'status' => 'C', 'lower' => array(958)), /* GREEK CAPITAL LETTER XI */
            array('upper' => 927, 'status' => 'C', 'lower' => array(959)), /* GREEK CAPITAL LETTER OMICRON */
            array('upper' => 928, 'status' => 'C', 'lower' => array(960)), /* GREEK CAPITAL LETTER PI */
            array('upper' => 929, 'status' => 'C', 'lower' => array(961)), /* GREEK CAPITAL LETTER RHO */
            array('upper' => 931, 'status' => 'C', 'lower' => array(963)), /* GREEK CAPITAL LETTER SIGMA */
            array('upper' => 932, 'status' => 'C', 'lower' => array(964)), /* GREEK CAPITAL LETTER TAU */
            array('upper' => 933, 'status' => 'C', 'lower' => array(965)), /* GREEK CAPITAL LETTER UPSILON */
            array('upper' => 934, 'status' => 'C', 'lower' => array(966)), /* GREEK CAPITAL LETTER PHI */
            array('upper' => 935, 'status' => 'C', 'lower' => array(967)), /* GREEK CAPITAL LETTER CHI */
            array('upper' => 936, 'status' => 'C', 'lower' => array(968)), /* GREEK CAPITAL LETTER PSI */
            array('upper' => 937, 'status' => 'C', 'lower' => array(969)), /* GREEK CAPITAL LETTER OMEGA */
            array('upper' => 938, 'status' => 'C', 'lower' => array(970)), /* GREEK CAPITAL LETTER IOTA WITH DIALYTIKA */
            array('upper' => 939, 'status' => 'C', 'lower' => array(971)), /* GREEK CAPITAL LETTER UPSILON WITH DIALYTIKA */
            array('upper' => 944, 'status' => 'F', 'lower' => array(965, 776, 769)), /* GREEK SMALL LETTER UPSILON WITH DIALYTIKA AND TONOS */
            array('upper' => 962, 'status' => 'C', 'lower' => array(963)), /* GREEK SMALL LETTER FINAL SIGMA */
            array('upper' => 976, 'status' => 'C', 'lower' => array(946)), /* GREEK BETA SYMBOL */
            array('upper' => 977, 'status' => 'C', 'lower' => array(952)), /* GREEK THETA SYMBOL */
            array('upper' => 981, 'status' => 'C', 'lower' => array(966)), /* GREEK PHI SYMBOL */
            array('upper' => 982, 'status' => 'C', 'lower' => array(960)), /* GREEK PI SYMBOL */
            array('upper' => 984, 'status' => 'C', 'lower' => array(985)), /* GREEK LETTER ARCHAIC KOPPA */
            array('upper' => 986, 'status' => 'C', 'lower' => array(987)), /* GREEK LETTER STIGMA */
            array('upper' => 988, 'status' => 'C', 'lower' => array(989)), /* GREEK LETTER DIGAMMA */
            array('upper' => 990, 'status' => 'C', 'lower' => array(991)), /* GREEK LETTER KOPPA */
            array('upper' => 992, 'status' => 'C', 'lower' => array(993)), /* GREEK LETTER SAMPI */
            array('upper' => 994, 'status' => 'C', 'lower' => array(995)), /* COPTIC CAPITAL LETTER SHEI */
            array('upper' => 996, 'status' => 'C', 'lower' => array(997)), /* COPTIC CAPITAL LETTER FEI */
            array('upper' => 998, 'status' => 'C', 'lower' => array(999)), /* COPTIC CAPITAL LETTER KHEI */
            array('upper' => 1000, 'status' => 'C', 'lower' => array(1001)), /* COPTIC CAPITAL LETTER HORI */
            array('upper' => 1002, 'status' => 'C', 'lower' => array(1003)), /* COPTIC CAPITAL LETTER GANGIA */
            array('upper' => 1004, 'status' => 'C', 'lower' => array(1005)), /* COPTIC CAPITAL LETTER SHIMA */
            array('upper' => 1006, 'status' => 'C', 'lower' => array(1007)), /* COPTIC CAPITAL LETTER DEI */
            array('upper' => 1008, 'status' => 'C', 'lower' => array(954)), /* GREEK KAPPA SYMBOL */
            array('upper' => 1009, 'status' => 'C', 'lower' => array(961)), /* GREEK RHO SYMBOL */
            array('upper' => 1012, 'status' => 'C', 'lower' => array(952)), /* GREEK CAPITAL THETA SYMBOL */
            array('upper' => 1013, 'status' => 'C', 'lower' => array(949)), /* GREEK LUNATE EPSILON SYMBOL */
            array('upper' => 1015, 'status' => 'C', 'lower' => array(1016)), /* GREEK CAPITAL LETTER SHO */
            array('upper' => 1017, 'status' => 'C', 'lower' => array(1010)), /* GREEK CAPITAL LUNATE SIGMA SYMBOL */
            array('upper' => 1018, 'status' => 'C', 'lower' => array(1019)), /* GREEK CAPITAL LETTER SAN */
            array('upper' => 1021, 'status' => 'C', 'lower' => array(891)), /* GREEK CAPITAL REVERSED LUNATE SIGMA SYMBOL */
            array('upper' => 1022, 'status' => 'C', 'lower' => array(892)), /* GREEK CAPITAL DOTTED LUNATE SIGMA SYMBOL */
            array('upper' => 1023, 'status' => 'C', 'lower' => array(893)), /* GREEK CAPITAL REVERSED DOTTED LUNATE SIGMA SYMBOL */
          );

          break;
        case '0400_04ff':
          self::$_codeRanges['0400_04ff'] = array(
            array('upper' => 1024, 'status' => 'C', 'lower' => array(1104)), /* CYRILLIC CAPITAL LETTER IE WITH GRAVE */
            array('upper' => 1025, 'status' => 'C', 'lower' => array(1105)), /* CYRILLIC CAPITAL LETTER IO */
            array('upper' => 1026, 'status' => 'C', 'lower' => array(1106)), /* CYRILLIC CAPITAL LETTER DJE */
            array('upper' => 1027, 'status' => 'C', 'lower' => array(1107)), /* CYRILLIC CAPITAL LETTER GJE */
            array('upper' => 1028, 'status' => 'C', 'lower' => array(1108)), /* CYRILLIC CAPITAL LETTER UKRAINIAN IE */
            array('upper' => 1029, 'status' => 'C', 'lower' => array(1109)), /* CYRILLIC CAPITAL LETTER DZE */
            array('upper' => 1030, 'status' => 'C', 'lower' => array(1110)), /* CYRILLIC CAPITAL LETTER BYELORUSSIAN-UKRAINIAN I */
            array('upper' => 1031, 'status' => 'C', 'lower' => array(1111)), /* CYRILLIC CAPITAL LETTER YI */
            array('upper' => 1032, 'status' => 'C', 'lower' => array(1112)), /* CYRILLIC CAPITAL LETTER JE */
            array('upper' => 1033, 'status' => 'C', 'lower' => array(1113)), /* CYRILLIC CAPITAL LETTER LJE */
            array('upper' => 1034, 'status' => 'C', 'lower' => array(1114)), /* CYRILLIC CAPITAL LETTER NJE */
            array('upper' => 1035, 'status' => 'C', 'lower' => array(1115)), /* CYRILLIC CAPITAL LETTER TSHE */
            array('upper' => 1036, 'status' => 'C', 'lower' => array(1116)), /* CYRILLIC CAPITAL LETTER KJE */
            array('upper' => 1037, 'status' => 'C', 'lower' => array(1117)), /* CYRILLIC CAPITAL LETTER I WITH GRAVE */
            array('upper' => 1038, 'status' => 'C', 'lower' => array(1118)), /* CYRILLIC CAPITAL LETTER SHORT U */
            array('upper' => 1039, 'status' => 'C', 'lower' => array(1119)), /* CYRILLIC CAPITAL LETTER DZHE */
            array('upper' => 1040, 'status' => 'C', 'lower' => array(1072)), /* CYRILLIC CAPITAL LETTER A */
            array('upper' => 1041, 'status' => 'C', 'lower' => array(1073)), /* CYRILLIC CAPITAL LETTER BE */
            array('upper' => 1042, 'status' => 'C', 'lower' => array(1074)), /* CYRILLIC CAPITAL LETTER VE */
            array('upper' => 1043, 'status' => 'C', 'lower' => array(1075)), /* CYRILLIC CAPITAL LETTER GHE */
            array('upper' => 1044, 'status' => 'C', 'lower' => array(1076)), /* CYRILLIC CAPITAL LETTER DE */
            array('upper' => 1045, 'status' => 'C', 'lower' => array(1077)), /* CYRILLIC CAPITAL LETTER IE */
            array('upper' => 1046, 'status' => 'C', 'lower' => array(1078)), /* CYRILLIC CAPITAL LETTER ZHE */
            array('upper' => 1047, 'status' => 'C', 'lower' => array(1079)), /* CYRILLIC CAPITAL LETTER ZE */
            array('upper' => 1048, 'status' => 'C', 'lower' => array(1080)), /* CYRILLIC CAPITAL LETTER I */
            array('upper' => 1049, 'status' => 'C', 'lower' => array(1081)), /* CYRILLIC CAPITAL LETTER SHORT I */
            array('upper' => 1050, 'status' => 'C', 'lower' => array(1082)), /* CYRILLIC CAPITAL LETTER KA */
            array('upper' => 1051, 'status' => 'C', 'lower' => array(1083)), /* CYRILLIC CAPITAL LETTER EL */
            array('upper' => 1052, 'status' => 'C', 'lower' => array(1084)), /* CYRILLIC CAPITAL LETTER EM */
            array('upper' => 1053, 'status' => 'C', 'lower' => array(1085)), /* CYRILLIC CAPITAL LETTER EN */
            array('upper' => 1054, 'status' => 'C', 'lower' => array(1086)), /* CYRILLIC CAPITAL LETTER O */
            array('upper' => 1055, 'status' => 'C', 'lower' => array(1087)), /* CYRILLIC CAPITAL LETTER PE */
            array('upper' => 1056, 'status' => 'C', 'lower' => array(1088)), /* CYRILLIC CAPITAL LETTER ER */
            array('upper' => 1057, 'status' => 'C', 'lower' => array(1089)), /* CYRILLIC CAPITAL LETTER ES */
            array('upper' => 1058, 'status' => 'C', 'lower' => array(1090)), /* CYRILLIC CAPITAL LETTER TE */
            array('upper' => 1059, 'status' => 'C', 'lower' => array(1091)), /* CYRILLIC CAPITAL LETTER U */
            array('upper' => 1060, 'status' => 'C', 'lower' => array(1092)), /* CYRILLIC CAPITAL LETTER EF */
            array('upper' => 1061, 'status' => 'C', 'lower' => array(1093)), /* CYRILLIC CAPITAL LETTER HA */
            array('upper' => 1062, 'status' => 'C', 'lower' => array(1094)), /* CYRILLIC CAPITAL LETTER TSE */
            array('upper' => 1063, 'status' => 'C', 'lower' => array(1095)), /* CYRILLIC CAPITAL LETTER CHE */
            array('upper' => 1064, 'status' => 'C', 'lower' => array(1096)), /* CYRILLIC CAPITAL LETTER SHA */
            array('upper' => 1065, 'status' => 'C', 'lower' => array(1097)), /* CYRILLIC CAPITAL LETTER SHCHA */
            array('upper' => 1066, 'status' => 'C', 'lower' => array(1098)), /* CYRILLIC CAPITAL LETTER HARD SIGN */
            array('upper' => 1067, 'status' => 'C', 'lower' => array(1099)), /* CYRILLIC CAPITAL LETTER YERU */
            array('upper' => 1068, 'status' => 'C', 'lower' => array(1100)), /* CYRILLIC CAPITAL LETTER SOFT SIGN */
            array('upper' => 1069, 'status' => 'C', 'lower' => array(1101)), /* CYRILLIC CAPITAL LETTER E */
            array('upper' => 1070, 'status' => 'C', 'lower' => array(1102)), /* CYRILLIC CAPITAL LETTER YU */
            array('upper' => 1071, 'status' => 'C', 'lower' => array(1103)), /* CYRILLIC CAPITAL LETTER YA */
            array('upper' => 1120, 'status' => 'C', 'lower' => array(1121)), /* CYRILLIC CAPITAL LETTER OMEGA */
            array('upper' => 1122, 'status' => 'C', 'lower' => array(1123)), /* CYRILLIC CAPITAL LETTER YAT */
            array('upper' => 1124, 'status' => 'C', 'lower' => array(1125)), /* CYRILLIC CAPITAL LETTER IOTIFIED E */
            array('upper' => 1126, 'status' => 'C', 'lower' => array(1127)), /* CYRILLIC CAPITAL LETTER LITTLE YUS */
            array('upper' => 1128, 'status' => 'C', 'lower' => array(1129)), /* CYRILLIC CAPITAL LETTER IOTIFIED LITTLE YUS */
            array('upper' => 1130, 'status' => 'C', 'lower' => array(1131)), /* CYRILLIC CAPITAL LETTER BIG YUS */
            array('upper' => 1132, 'status' => 'C', 'lower' => array(1133)), /* CYRILLIC CAPITAL LETTER IOTIFIED BIG YUS */
            array('upper' => 1134, 'status' => 'C', 'lower' => array(1135)), /* CYRILLIC CAPITAL LETTER KSI */
            array('upper' => 1136, 'status' => 'C', 'lower' => array(1137)), /* CYRILLIC CAPITAL LETTER PSI */
            array('upper' => 1138, 'status' => 'C', 'lower' => array(1139)), /* CYRILLIC CAPITAL LETTER FITA */
            array('upper' => 1140, 'status' => 'C', 'lower' => array(1141)), /* CYRILLIC CAPITAL LETTER IZHITSA */
            array('upper' => 1142, 'status' => 'C', 'lower' => array(1143)), /* CYRILLIC CAPITAL LETTER IZHITSA WITH DOUBLE GRAVE ACCENT */
            array('upper' => 1144, 'status' => 'C', 'lower' => array(1145)), /* CYRILLIC CAPITAL LETTER UK */
            array('upper' => 1146, 'status' => 'C', 'lower' => array(1147)), /* CYRILLIC CAPITAL LETTER ROUND OMEGA */
            array('upper' => 1148, 'status' => 'C', 'lower' => array(1149)), /* CYRILLIC CAPITAL LETTER OMEGA WITH TITLO */
            array('upper' => 1150, 'status' => 'C', 'lower' => array(1151)), /* CYRILLIC CAPITAL LETTER OT */
            array('upper' => 1152, 'status' => 'C', 'lower' => array(1153)), /* CYRILLIC CAPITAL LETTER KOPPA */
            array('upper' => 1162, 'status' => 'C', 'lower' => array(1163)), /* CYRILLIC CAPITAL LETTER SHORT I WITH TAIL */
            array('upper' => 1164, 'status' => 'C', 'lower' => array(1165)), /* CYRILLIC CAPITAL LETTER SEMISOFT SIGN */
            array('upper' => 1166, 'status' => 'C', 'lower' => array(1167)), /* CYRILLIC CAPITAL LETTER ER WITH TICK */
            array('upper' => 1168, 'status' => 'C', 'lower' => array(1169)), /* CYRILLIC CAPITAL LETTER GHE WITH UPTURN */
            array('upper' => 1170, 'status' => 'C', 'lower' => array(1171)), /* CYRILLIC CAPITAL LETTER GHE WITH STROKE */
            array('upper' => 1172, 'status' => 'C', 'lower' => array(1173)), /* CYRILLIC CAPITAL LETTER GHE WITH MIDDLE HOOK */
            array('upper' => 1174, 'status' => 'C', 'lower' => array(1175)), /* CYRILLIC CAPITAL LETTER ZHE WITH DESCENDER */
            array('upper' => 1176, 'status' => 'C', 'lower' => array(1177)), /* CYRILLIC CAPITAL LETTER ZE WITH DESCENDER */
            array('upper' => 1178, 'status' => 'C', 'lower' => array(1179)), /* CYRILLIC CAPITAL LETTER KA WITH DESCENDER */
            array('upper' => 1180, 'status' => 'C', 'lower' => array(1181)), /* CYRILLIC CAPITAL LETTER KA WITH VERTICAL STROKE */
            array('upper' => 1182, 'status' => 'C', 'lower' => array(1183)), /* CYRILLIC CAPITAL LETTER KA WITH STROKE */
            array('upper' => 1184, 'status' => 'C', 'lower' => array(1185)), /* CYRILLIC CAPITAL LETTER BASHKIR KA */
            array('upper' => 1186, 'status' => 'C', 'lower' => array(1187)), /* CYRILLIC CAPITAL LETTER EN WITH DESCENDER */
            array('upper' => 1188, 'status' => 'C', 'lower' => array(1189)), /* CYRILLIC CAPITAL LIGATURE EN GHE */
            array('upper' => 1190, 'status' => 'C', 'lower' => array(1191)), /* CYRILLIC CAPITAL LETTER PE WITH MIDDLE HOOK */
            array('upper' => 1192, 'status' => 'C', 'lower' => array(1193)), /* CYRILLIC CAPITAL LETTER ABKHASIAN HA */
            array('upper' => 1194, 'status' => 'C', 'lower' => array(1195)), /* CYRILLIC CAPITAL LETTER ES WITH DESCENDER */
            array('upper' => 1196, 'status' => 'C', 'lower' => array(1197)), /* CYRILLIC CAPITAL LETTER TE WITH DESCENDER */
            array('upper' => 1198, 'status' => 'C', 'lower' => array(1199)), /* CYRILLIC CAPITAL LETTER STRAIGHT U */
            array('upper' => 1200, 'status' => 'C', 'lower' => array(1201)), /* CYRILLIC CAPITAL LETTER STRAIGHT U WITH STROKE */
            array('upper' => 1202, 'status' => 'C', 'lower' => array(1203)), /* CYRILLIC CAPITAL LETTER HA WITH DESCENDER */
            array('upper' => 1204, 'status' => 'C', 'lower' => array(1205)), /* CYRILLIC CAPITAL LIGATURE TE TSE */
            array('upper' => 1206, 'status' => 'C', 'lower' => array(1207)), /* CYRILLIC CAPITAL LETTER CHE WITH DESCENDER */
            array('upper' => 1208, 'status' => 'C', 'lower' => array(1209)), /* CYRILLIC CAPITAL LETTER CHE WITH VERTICAL STROKE */
            array('upper' => 1210, 'status' => 'C', 'lower' => array(1211)), /* CYRILLIC CAPITAL LETTER SHHA */
            array('upper' => 1212, 'status' => 'C', 'lower' => array(1213)), /* CYRILLIC CAPITAL LETTER ABKHASIAN CHE */
            array('upper' => 1214, 'status' => 'C', 'lower' => array(1215)), /* CYRILLIC CAPITAL LETTER ABKHASIAN CHE WITH DESCENDER */
            array('upper' => 1216, 'status' => 'C', 'lower' => array(1231)), /* CYRILLIC LETTER PALOCHKA */
            array('upper' => 1217, 'status' => 'C', 'lower' => array(1218)), /* CYRILLIC CAPITAL LETTER ZHE WITH BREVE */
            array('upper' => 1219, 'status' => 'C', 'lower' => array(1220)), /* CYRILLIC CAPITAL LETTER KA WITH HOOK */
            array('upper' => 1221, 'status' => 'C', 'lower' => array(1222)), /* CYRILLIC CAPITAL LETTER EL WITH TAIL */
            array('upper' => 1223, 'status' => 'C', 'lower' => array(1224)), /* CYRILLIC CAPITAL LETTER EN WITH HOOK */
            array('upper' => 1225, 'status' => 'C', 'lower' => array(1226)), /* CYRILLIC CAPITAL LETTER EN WITH TAIL */
            array('upper' => 1227, 'status' => 'C', 'lower' => array(1228)), /* CYRILLIC CAPITAL LETTER KHAKASSIAN CHE */
            array('upper' => 1229, 'status' => 'C', 'lower' => array(1230)), /* CYRILLIC CAPITAL LETTER EM WITH TAIL */
            array('upper' => 1232, 'status' => 'C', 'lower' => array(1233)), /* CYRILLIC CAPITAL LETTER A WITH BREVE */
            array('upper' => 1234, 'status' => 'C', 'lower' => array(1235)), /* CYRILLIC CAPITAL LETTER A WITH DIAERESIS */
            array('upper' => 1236, 'status' => 'C', 'lower' => array(1237)), /* CYRILLIC CAPITAL LIGATURE A IE */
            array('upper' => 1238, 'status' => 'C', 'lower' => array(1239)), /* CYRILLIC CAPITAL LETTER IE WITH BREVE */
            array('upper' => 1240, 'status' => 'C', 'lower' => array(1241)), /* CYRILLIC CAPITAL LETTER SCHWA */
            array('upper' => 1242, 'status' => 'C', 'lower' => array(1243)), /* CYRILLIC CAPITAL LETTER SCHWA WITH DIAERESIS */
            array('upper' => 1244, 'status' => 'C', 'lower' => array(1245)), /* CYRILLIC CAPITAL LETTER ZHE WITH DIAERESIS */
            array('upper' => 1246, 'status' => 'C', 'lower' => array(1247)), /* CYRILLIC CAPITAL LETTER ZE WITH DIAERESIS */
            array('upper' => 1248, 'status' => 'C', 'lower' => array(1249)), /* CYRILLIC CAPITAL LETTER ABKHASIAN DZE */
            array('upper' => 1250, 'status' => 'C', 'lower' => array(1251)), /* CYRILLIC CAPITAL LETTER I WITH MACRON */
            array('upper' => 1252, 'status' => 'C', 'lower' => array(1253)), /* CYRILLIC CAPITAL LETTER I WITH DIAERESIS */
            array('upper' => 1254, 'status' => 'C', 'lower' => array(1255)), /* CYRILLIC CAPITAL LETTER O WITH DIAERESIS */
            array('upper' => 1256, 'status' => 'C', 'lower' => array(1257)), /* CYRILLIC CAPITAL LETTER BARRED O */
            array('upper' => 1258, 'status' => 'C', 'lower' => array(1259)), /* CYRILLIC CAPITAL LETTER BARRED O WITH DIAERESIS */
            array('upper' => 1260, 'status' => 'C', 'lower' => array(1261)), /* CYRILLIC CAPITAL LETTER E WITH DIAERESIS */
            array('upper' => 1262, 'status' => 'C', 'lower' => array(1263)), /* CYRILLIC CAPITAL LETTER U WITH MACRON */
            array('upper' => 1264, 'status' => 'C', 'lower' => array(1265)), /* CYRILLIC CAPITAL LETTER U WITH DIAERESIS */
            array('upper' => 1266, 'status' => 'C', 'lower' => array(1267)), /* CYRILLIC CAPITAL LETTER U WITH DOUBLE ACUTE */
            array('upper' => 1268, 'status' => 'C', 'lower' => array(1269)), /* CYRILLIC CAPITAL LETTER CHE WITH DIAERESIS */
            array('upper' => 1270, 'status' => 'C', 'lower' => array(1271)), /* CYRILLIC CAPITAL LETTER GHE WITH DESCENDER */
            array('upper' => 1272, 'status' => 'C', 'lower' => array(1273)), /* CYRILLIC CAPITAL LETTER YERU WITH DIAERESIS */
            array('upper' => 1274, 'status' => 'C', 'lower' => array(1275)), /* CYRILLIC CAPITAL LETTER GHE WITH STROKE AND HOOK */
            array('upper' => 1276, 'status' => 'C', 'lower' => array(1277)), /* CYRILLIC CAPITAL LETTER HA WITH HOOK */
            array('upper' => 1278, 'status' => 'C', 'lower' => array(1279)), /* CYRILLIC CAPITAL LETTER HA WITH STROKE */
          );
          break;
        case '0500_052f':
          self::$_codeRanges['0500_052f'] = array(
            array('upper' => 1280, 'status' => 'C', 'lower' => array(1281)), /* CYRILLIC CAPITAL LETTER KOMI DE */
            array('upper' => 1282, 'status' => 'C', 'lower' => array(1283)), /* CYRILLIC CAPITAL LETTER KOMI DJE */
            array('upper' => 1284, 'status' => 'C', 'lower' => array(1285)), /* CYRILLIC CAPITAL LETTER KOMI ZJE */
            array('upper' => 1286, 'status' => 'C', 'lower' => array(1287)), /* CYRILLIC CAPITAL LETTER KOMI DZJE */
            array('upper' => 1288, 'status' => 'C', 'lower' => array(1289)), /* CYRILLIC CAPITAL LETTER KOMI LJE */
            array('upper' => 1290, 'status' => 'C', 'lower' => array(1291)), /* CYRILLIC CAPITAL LETTER KOMI NJE */
            array('upper' => 1292, 'status' => 'C', 'lower' => array(1293)), /* CYRILLIC CAPITAL LETTER KOMI SJE */
            array('upper' => 1294, 'status' => 'C', 'lower' => array(1295)), /* CYRILLIC CAPITAL LETTER KOMI TJE */
            array('upper' => 1296, 'status' => 'C', 'lower' => array(1297)), /* CYRILLIC CAPITAL LETTER ZE */
            array('upper' => 1298, 'status' => 'C', 'lower' => array(1299)), /* CYRILLIC CAPITAL LETTER El with hook */
          );

          break;
        case '0530_058f':
          self::$_codeRanges['0530_058f'] = array(
            array('upper' => 1329, 'status' => 'C', 'lower' => array(1377)), /* ARMENIAN CAPITAL LETTER AYB */
            array('upper' => 1330, 'status' => 'C', 'lower' => array(1378)), /* ARMENIAN CAPITAL LETTER BEN */
            array('upper' => 1331, 'status' => 'C', 'lower' => array(1379)), /* ARMENIAN CAPITAL LETTER GIM */
            array('upper' => 1332, 'status' => 'C', 'lower' => array(1380)), /* ARMENIAN CAPITAL LETTER DA */
            array('upper' => 1333, 'status' => 'C', 'lower' => array(1381)), /* ARMENIAN CAPITAL LETTER ECH */
            array('upper' => 1334, 'status' => 'C', 'lower' => array(1382)), /* ARMENIAN CAPITAL LETTER ZA */
            array('upper' => 1335, 'status' => 'C', 'lower' => array(1383)), /* ARMENIAN CAPITAL LETTER EH */
            array('upper' => 1336, 'status' => 'C', 'lower' => array(1384)), /* ARMENIAN CAPITAL LETTER ET */
            array('upper' => 1337, 'status' => 'C', 'lower' => array(1385)), /* ARMENIAN CAPITAL LETTER TO */
            array('upper' => 1338, 'status' => 'C', 'lower' => array(1386)), /* ARMENIAN CAPITAL LETTER ZHE */
            array('upper' => 1339, 'status' => 'C', 'lower' => array(1387)), /* ARMENIAN CAPITAL LETTER INI */
            array('upper' => 1340, 'status' => 'C', 'lower' => array(1388)), /* ARMENIAN CAPITAL LETTER LIWN */
            array('upper' => 1341, 'status' => 'C', 'lower' => array(1389)), /* ARMENIAN CAPITAL LETTER XEH */
            array('upper' => 1342, 'status' => 'C', 'lower' => array(1390)), /* ARMENIAN CAPITAL LETTER CA */
            array('upper' => 1343, 'status' => 'C', 'lower' => array(1391)), /* ARMENIAN CAPITAL LETTER KEN */
            array('upper' => 1344, 'status' => 'C', 'lower' => array(1392)), /* ARMENIAN CAPITAL LETTER HO */
            array('upper' => 1345, 'status' => 'C', 'lower' => array(1393)), /* ARMENIAN CAPITAL LETTER JA */
            array('upper' => 1346, 'status' => 'C', 'lower' => array(1394)), /* ARMENIAN CAPITAL LETTER GHAD */
            array('upper' => 1347, 'status' => 'C', 'lower' => array(1395)), /* ARMENIAN CAPITAL LETTER CHEH */
            array('upper' => 1348, 'status' => 'C', 'lower' => array(1396)), /* ARMENIAN CAPITAL LETTER MEN */
            array('upper' => 1349, 'status' => 'C', 'lower' => array(1397)), /* ARMENIAN CAPITAL LETTER YI */
            array('upper' => 1350, 'status' => 'C', 'lower' => array(1398)), /* ARMENIAN CAPITAL LETTER NOW */
            array('upper' => 1351, 'status' => 'C', 'lower' => array(1399)), /* ARMENIAN CAPITAL LETTER SHA */
            array('upper' => 1352, 'status' => 'C', 'lower' => array(1400)), /* ARMENIAN CAPITAL LETTER VO */
            array('upper' => 1353, 'status' => 'C', 'lower' => array(1401)), /* ARMENIAN CAPITAL LETTER CHA */
            array('upper' => 1354, 'status' => 'C', 'lower' => array(1402)), /* ARMENIAN CAPITAL LETTER PEH */
            array('upper' => 1355, 'status' => 'C', 'lower' => array(1403)), /* ARMENIAN CAPITAL LETTER JHEH */
            array('upper' => 1356, 'status' => 'C', 'lower' => array(1404)), /* ARMENIAN CAPITAL LETTER RA */
            array('upper' => 1357, 'status' => 'C', 'lower' => array(1405)), /* ARMENIAN CAPITAL LETTER SEH */
            array('upper' => 1358, 'status' => 'C', 'lower' => array(1406)), /* ARMENIAN CAPITAL LETTER VEW */
            array('upper' => 1359, 'status' => 'C', 'lower' => array(1407)), /* ARMENIAN CAPITAL LETTER TIWN */
            array('upper' => 1360, 'status' => 'C', 'lower' => array(1408)), /* ARMENIAN CAPITAL LETTER REH */
            array('upper' => 1361, 'status' => 'C', 'lower' => array(1409)), /* ARMENIAN CAPITAL LETTER CO */
            array('upper' => 1362, 'status' => 'C', 'lower' => array(1410)), /* ARMENIAN CAPITAL LETTER YIWN */
            array('upper' => 1363, 'status' => 'C', 'lower' => array(1411)), /* ARMENIAN CAPITAL LETTER PIWR */
            array('upper' => 1364, 'status' => 'C', 'lower' => array(1412)), /* ARMENIAN CAPITAL LETTER KEH */
            array('upper' => 1365, 'status' => 'C', 'lower' => array(1413)), /* ARMENIAN CAPITAL LETTER OH */
            array('upper' => 1366, 'status' => 'C', 'lower' => array(1414)), /* ARMENIAN CAPITAL LETTER FEH */
          );
          break;
        case '1e00_1eff':
          self::$_codeRanges['1e00_1eff'] = array(
            array('upper' => 7680, 'status' => 'C', 'lower' => array(7681)), /* LATIN CAPITAL LETTER A WITH RING BELOW */
            array('upper' => 7682, 'status' => 'C', 'lower' => array(7683)), /* LATIN CAPITAL LETTER B WITH DOT ABOVE */
            array('upper' => 7684, 'status' => 'C', 'lower' => array(7685)), /* LATIN CAPITAL LETTER B WITH DOT BELOW */
            array('upper' => 7686, 'status' => 'C', 'lower' => array(7687)), /* LATIN CAPITAL LETTER B WITH LINE BELOW */
            array('upper' => 7688, 'status' => 'C', 'lower' => array(7689)), /* LATIN CAPITAL LETTER C WITH CEDILLA AND ACUTE */
            array('upper' => 7690, 'status' => 'C', 'lower' => array(7691)), /* LATIN CAPITAL LETTER D WITH DOT ABOVE */
            array('upper' => 7692, 'status' => 'C', 'lower' => array(7693)), /* LATIN CAPITAL LETTER D WITH DOT BELOW */
            array('upper' => 7694, 'status' => 'C', 'lower' => array(7695)), /* LATIN CAPITAL LETTER D WITH LINE BELOW */
            array('upper' => 7696, 'status' => 'C', 'lower' => array(7697)), /* LATIN CAPITAL LETTER D WITH CEDILLA */
            array('upper' => 7698, 'status' => 'C', 'lower' => array(7699)), /* LATIN CAPITAL LETTER D WITH CIRCUMFLEX BELOW */
            array('upper' => 7700, 'status' => 'C', 'lower' => array(7701)), /* LATIN CAPITAL LETTER E WITH MACRON AND GRAVE */
            array('upper' => 7702, 'status' => 'C', 'lower' => array(7703)), /* LATIN CAPITAL LETTER E WITH MACRON AND ACUTE */
            array('upper' => 7704, 'status' => 'C', 'lower' => array(7705)), /* LATIN CAPITAL LETTER E WITH CIRCUMFLEX BELOW */
            array('upper' => 7706, 'status' => 'C', 'lower' => array(7707)), /* LATIN CAPITAL LETTER E WITH TILDE BELOW */
            array('upper' => 7708, 'status' => 'C', 'lower' => array(7709)), /* LATIN CAPITAL LETTER E WITH CEDILLA AND BREVE */
            array('upper' => 7710, 'status' => 'C', 'lower' => array(7711)), /* LATIN CAPITAL LETTER F WITH DOT ABOVE */
            array('upper' => 7712, 'status' => 'C', 'lower' => array(7713)), /* LATIN CAPITAL LETTER G WITH MACRON */
            array('upper' => 7714, 'status' => 'C', 'lower' => array(7715)), /* LATIN CAPITAL LETTER H WITH DOT ABOVE */
            array('upper' => 7716, 'status' => 'C', 'lower' => array(7717)), /* LATIN CAPITAL LETTER H WITH DOT BELOW */
            array('upper' => 7718, 'status' => 'C', 'lower' => array(7719)), /* LATIN CAPITAL LETTER H WITH DIAERESIS */
            array('upper' => 7720, 'status' => 'C', 'lower' => array(7721)), /* LATIN CAPITAL LETTER H WITH CEDILLA */
            array('upper' => 7722, 'status' => 'C', 'lower' => array(7723)), /* LATIN CAPITAL LETTER H WITH BREVE BELOW */
            array('upper' => 7724, 'status' => 'C', 'lower' => array(7725)), /* LATIN CAPITAL LETTER I WITH TILDE BELOW */
            array('upper' => 7726, 'status' => 'C', 'lower' => array(7727)), /* LATIN CAPITAL LETTER I WITH DIAERESIS AND ACUTE */
            array('upper' => 7728, 'status' => 'C', 'lower' => array(7729)), /* LATIN CAPITAL LETTER K WITH ACUTE */
            array('upper' => 7730, 'status' => 'C', 'lower' => array(7731)), /* LATIN CAPITAL LETTER K WITH DOT BELOW */
            array('upper' => 7732, 'status' => 'C', 'lower' => array(7733)), /* LATIN CAPITAL LETTER K WITH LINE BELOW */
            array('upper' => 7734, 'status' => 'C', 'lower' => array(7735)), /* LATIN CAPITAL LETTER L WITH DOT BELOW */
            array('upper' => 7736, 'status' => 'C', 'lower' => array(7737)), /* LATIN CAPITAL LETTER L WITH DOT BELOW AND MACRON */
            array('upper' => 7738, 'status' => 'C', 'lower' => array(7739)), /* LATIN CAPITAL LETTER L WITH LINE BELOW */
            array('upper' => 7740, 'status' => 'C', 'lower' => array(7741)), /* LATIN CAPITAL LETTER L WITH CIRCUMFLEX BELOW */
            array('upper' => 7742, 'status' => 'C', 'lower' => array(7743)), /* LATIN CAPITAL LETTER M WITH ACUTE */
            array('upper' => 7744, 'status' => 'C', 'lower' => array(7745)), /* LATIN CAPITAL LETTER M WITH DOT ABOVE */
            array('upper' => 7746, 'status' => 'C', 'lower' => array(7747)), /* LATIN CAPITAL LETTER M WITH DOT BELOW */
            array('upper' => 7748, 'status' => 'C', 'lower' => array(7749)), /* LATIN CAPITAL LETTER N WITH DOT ABOVE */
            array('upper' => 7750, 'status' => 'C', 'lower' => array(7751)), /* LATIN CAPITAL LETTER N WITH DOT BELOW */
            array('upper' => 7752, 'status' => 'C', 'lower' => array(7753)), /* LATIN CAPITAL LETTER N WITH LINE BELOW */
            array('upper' => 7754, 'status' => 'C', 'lower' => array(7755)), /* LATIN CAPITAL LETTER N WITH CIRCUMFLEX BELOW */
            array('upper' => 7756, 'status' => 'C', 'lower' => array(7757)), /* LATIN CAPITAL LETTER O WITH TILDE AND ACUTE */
            array('upper' => 7758, 'status' => 'C', 'lower' => array(7759)), /* LATIN CAPITAL LETTER O WITH TILDE AND DIAERESIS */
            array('upper' => 7760, 'status' => 'C', 'lower' => array(7761)), /* LATIN CAPITAL LETTER O WITH MACRON AND GRAVE */
            array('upper' => 7762, 'status' => 'C', 'lower' => array(7763)), /* LATIN CAPITAL LETTER O WITH MACRON AND ACUTE */
            array('upper' => 7764, 'status' => 'C', 'lower' => array(7765)), /* LATIN CAPITAL LETTER P WITH ACUTE */
            array('upper' => 7766, 'status' => 'C', 'lower' => array(7767)), /* LATIN CAPITAL LETTER P WITH DOT ABOVE */
            array('upper' => 7768, 'status' => 'C', 'lower' => array(7769)), /* LATIN CAPITAL LETTER R WITH DOT ABOVE */
            array('upper' => 7770, 'status' => 'C', 'lower' => array(7771)), /* LATIN CAPITAL LETTER R WITH DOT BELOW */
            array('upper' => 7772, 'status' => 'C', 'lower' => array(7773)), /* LATIN CAPITAL LETTER R WITH DOT BELOW AND MACRON */
            array('upper' => 7774, 'status' => 'C', 'lower' => array(7775)), /* LATIN CAPITAL LETTER R WITH LINE BELOW */
            array('upper' => 7776, 'status' => 'C', 'lower' => array(7777)), /* LATIN CAPITAL LETTER S WITH DOT ABOVE */
            array('upper' => 7778, 'status' => 'C', 'lower' => array(7779)), /* LATIN CAPITAL LETTER S WITH DOT BELOW */
            array('upper' => 7780, 'status' => 'C', 'lower' => array(7781)), /* LATIN CAPITAL LETTER S WITH ACUTE AND DOT ABOVE */
            array('upper' => 7782, 'status' => 'C', 'lower' => array(7783)), /* LATIN CAPITAL LETTER S WITH CARON AND DOT ABOVE */
            array('upper' => 7784, 'status' => 'C', 'lower' => array(7785)), /* LATIN CAPITAL LETTER S WITH DOT BELOW AND DOT ABOVE */
            array('upper' => 7786, 'status' => 'C', 'lower' => array(7787)), /* LATIN CAPITAL LETTER T WITH DOT ABOVE */
            array('upper' => 7788, 'status' => 'C', 'lower' => array(7789)), /* LATIN CAPITAL LETTER T WITH DOT BELOW */
            array('upper' => 7790, 'status' => 'C', 'lower' => array(7791)), /* LATIN CAPITAL LETTER T WITH LINE BELOW */
            array('upper' => 7792, 'status' => 'C', 'lower' => array(7793)), /* LATIN CAPITAL LETTER T WITH CIRCUMFLEX BELOW */
            array('upper' => 7794, 'status' => 'C', 'lower' => array(7795)), /* LATIN CAPITAL LETTER U WITH DIAERESIS BELOW */
            array('upper' => 7796, 'status' => 'C', 'lower' => array(7797)), /* LATIN CAPITAL LETTER U WITH TILDE BELOW */
            array('upper' => 7798, 'status' => 'C', 'lower' => array(7799)), /* LATIN CAPITAL LETTER U WITH CIRCUMFLEX BELOW */
            array('upper' => 7800, 'status' => 'C', 'lower' => array(7801)), /* LATIN CAPITAL LETTER U WITH TILDE AND ACUTE */
            array('upper' => 7802, 'status' => 'C', 'lower' => array(7803)), /* LATIN CAPITAL LETTER U WITH MACRON AND DIAERESIS */
            array('upper' => 7804, 'status' => 'C', 'lower' => array(7805)), /* LATIN CAPITAL LETTER V WITH TILDE */
            array('upper' => 7806, 'status' => 'C', 'lower' => array(7807)), /* LATIN CAPITAL LETTER V WITH DOT BELOW */
            array('upper' => 7808, 'status' => 'C', 'lower' => array(7809)), /* LATIN CAPITAL LETTER W WITH GRAVE */
            array('upper' => 7810, 'status' => 'C', 'lower' => array(7811)), /* LATIN CAPITAL LETTER W WITH ACUTE */
            array('upper' => 7812, 'status' => 'C', 'lower' => array(7813)), /* LATIN CAPITAL LETTER W WITH DIAERESIS */
            array('upper' => 7814, 'status' => 'C', 'lower' => array(7815)), /* LATIN CAPITAL LETTER W WITH DOT ABOVE */
            array('upper' => 7816, 'status' => 'C', 'lower' => array(7817)), /* LATIN CAPITAL LETTER W WITH DOT BELOW */
            array('upper' => 7818, 'status' => 'C', 'lower' => array(7819)), /* LATIN CAPITAL LETTER X WITH DOT ABOVE */
            array('upper' => 7820, 'status' => 'C', 'lower' => array(7821)), /* LATIN CAPITAL LETTER X WITH DIAERESIS */
            array('upper' => 7822, 'status' => 'C', 'lower' => array(7823)), /* LATIN CAPITAL LETTER Y WITH DOT ABOVE */
            array('upper' => 7824, 'status' => 'C', 'lower' => array(7825)), /* LATIN CAPITAL LETTER Z WITH CIRCUMFLEX */
            array('upper' => 7826, 'status' => 'C', 'lower' => array(7827)), /* LATIN CAPITAL LETTER Z WITH DOT BELOW */
            array('upper' => 7828, 'status' => 'C', 'lower' => array(7829)), /* LATIN CAPITAL LETTER Z WITH LINE BELOW */
            array('upper' => 7840, 'status' => 'C', 'lower' => array(7841)), /* LATIN CAPITAL LETTER A WITH DOT BELOW */
            array('upper' => 7842, 'status' => 'C', 'lower' => array(7843)), /* LATIN CAPITAL LETTER A WITH HOOK ABOVE */
            array('upper' => 7844, 'status' => 'C', 'lower' => array(7845)), /* LATIN CAPITAL LETTER A WITH CIRCUMFLEX AND ACUTE */
            array('upper' => 7846, 'status' => 'C', 'lower' => array(7847)), /* LATIN CAPITAL LETTER A WITH CIRCUMFLEX AND GRAVE */
            array('upper' => 7848, 'status' => 'C', 'lower' => array(7849)), /* LATIN CAPITAL LETTER A WITH CIRCUMFLEX AND HOOK ABOVE */
            array('upper' => 7850, 'status' => 'C', 'lower' => array(7851)), /* LATIN CAPITAL LETTER A WITH CIRCUMFLEX AND TILDE */
            array('upper' => 7852, 'status' => 'C', 'lower' => array(7853)), /* LATIN CAPITAL LETTER A WITH CIRCUMFLEX AND DOT BELOW */
            array('upper' => 7854, 'status' => 'C', 'lower' => array(7855)), /* LATIN CAPITAL LETTER A WITH BREVE AND ACUTE */
            array('upper' => 7856, 'status' => 'C', 'lower' => array(7857)), /* LATIN CAPITAL LETTER A WITH BREVE AND GRAVE */
            array('upper' => 7858, 'status' => 'C', 'lower' => array(7859)), /* LATIN CAPITAL LETTER A WITH BREVE AND HOOK ABOVE */
            array('upper' => 7860, 'status' => 'C', 'lower' => array(7861)), /* LATIN CAPITAL LETTER A WITH BREVE AND TILDE */
            array('upper' => 7862, 'status' => 'C', 'lower' => array(7863)), /* LATIN CAPITAL LETTER A WITH BREVE AND DOT BELOW */
            array('upper' => 7864, 'status' => 'C', 'lower' => array(7865)), /* LATIN CAPITAL LETTER E WITH DOT BELOW */
            array('upper' => 7866, 'status' => 'C', 'lower' => array(7867)), /* LATIN CAPITAL LETTER E WITH HOOK ABOVE */
            array('upper' => 7868, 'status' => 'C', 'lower' => array(7869)), /* LATIN CAPITAL LETTER E WITH TILDE */
            array('upper' => 7870, 'status' => 'C', 'lower' => array(7871)), /* LATIN CAPITAL LETTER E WITH CIRCUMFLEX AND ACUTE */
            array('upper' => 7872, 'status' => 'C', 'lower' => array(7873)), /* LATIN CAPITAL LETTER E WITH CIRCUMFLEX AND GRAVE */
            array('upper' => 7874, 'status' => 'C', 'lower' => array(7875)), /* LATIN CAPITAL LETTER E WITH CIRCUMFLEX AND HOOK ABOVE */
            array('upper' => 7876, 'status' => 'C', 'lower' => array(7877)), /* LATIN CAPITAL LETTER E WITH CIRCUMFLEX AND TILDE */
            array('upper' => 7878, 'status' => 'C', 'lower' => array(7879)), /* LATIN CAPITAL LETTER E WITH CIRCUMFLEX AND DOT BELOW */
            array('upper' => 7880, 'status' => 'C', 'lower' => array(7881)), /* LATIN CAPITAL LETTER I WITH HOOK ABOVE */
            array('upper' => 7882, 'status' => 'C', 'lower' => array(7883)), /* LATIN CAPITAL LETTER I WITH DOT BELOW */
            array('upper' => 7884, 'status' => 'C', 'lower' => array(7885)), /* LATIN CAPITAL LETTER O WITH DOT BELOW */
            array('upper' => 7886, 'status' => 'C', 'lower' => array(7887)), /* LATIN CAPITAL LETTER O WITH HOOK ABOVE */
            array('upper' => 7888, 'status' => 'C', 'lower' => array(7889)), /* LATIN CAPITAL LETTER O WITH CIRCUMFLEX AND ACUTE */
            array('upper' => 7890, 'status' => 'C', 'lower' => array(7891)), /* LATIN CAPITAL LETTER O WITH CIRCUMFLEX AND GRAVE */
            array('upper' => 7892, 'status' => 'C', 'lower' => array(7893)), /* LATIN CAPITAL LETTER O WITH CIRCUMFLEX AND HOOK ABOVE */
            array('upper' => 7894, 'status' => 'C', 'lower' => array(7895)), /* LATIN CAPITAL LETTER O WITH CIRCUMFLEX AND TILDE */
            array('upper' => 7896, 'status' => 'C', 'lower' => array(7897)), /* LATIN CAPITAL LETTER O WITH CIRCUMFLEX AND DOT BELOW */
            array('upper' => 7898, 'status' => 'C', 'lower' => array(7899)), /* LATIN CAPITAL LETTER O WITH HORN AND ACUTE */
            array('upper' => 7900, 'status' => 'C', 'lower' => array(7901)), /* LATIN CAPITAL LETTER O WITH HORN AND GRAVE */
            array('upper' => 7902, 'status' => 'C', 'lower' => array(7903)), /* LATIN CAPITAL LETTER O WITH HORN AND HOOK ABOVE */
            array('upper' => 7904, 'status' => 'C', 'lower' => array(7905)), /* LATIN CAPITAL LETTER O WITH HORN AND TILDE */
            array('upper' => 7906, 'status' => 'C', 'lower' => array(7907)), /* LATIN CAPITAL LETTER O WITH HORN AND DOT BELOW */
            array('upper' => 7908, 'status' => 'C', 'lower' => array(7909)), /* LATIN CAPITAL LETTER U WITH DOT BELOW */
            array('upper' => 7910, 'status' => 'C', 'lower' => array(7911)), /* LATIN CAPITAL LETTER U WITH HOOK ABOVE */
            array('upper' => 7912, 'status' => 'C', 'lower' => array(7913)), /* LATIN CAPITAL LETTER U WITH HORN AND ACUTE */
            array('upper' => 7914, 'status' => 'C', 'lower' => array(7915)), /* LATIN CAPITAL LETTER U WITH HORN AND GRAVE */
            array('upper' => 7916, 'status' => 'C', 'lower' => array(7917)), /* LATIN CAPITAL LETTER U WITH HORN AND HOOK ABOVE */
            array('upper' => 7918, 'status' => 'C', 'lower' => array(7919)), /* LATIN CAPITAL LETTER U WITH HORN AND TILDE */
            array('upper' => 7920, 'status' => 'C', 'lower' => array(7921)), /* LATIN CAPITAL LETTER U WITH HORN AND DOT BELOW */
            array('upper' => 7922, 'status' => 'C', 'lower' => array(7923)), /* LATIN CAPITAL LETTER Y WITH GRAVE */
            array('upper' => 7924, 'status' => 'C', 'lower' => array(7925)), /* LATIN CAPITAL LETTER Y WITH DOT BELOW */
            array('upper' => 7926, 'status' => 'C', 'lower' => array(7927)), /* LATIN CAPITAL LETTER Y WITH HOOK ABOVE */
            array('upper' => 7928, 'status' => 'C', 'lower' => array(7929)), /* LATIN CAPITAL LETTER Y WITH TILDE */
          );
          break;
        case '1f00_1fff':
          self::$_codeRanges['1f00_1fff'] = array(
            array('upper' => 7944, 'status' => 'C', 'lower' => array(7936, 953)), /* GREEK CAPITAL LETTER ALPHA WITH PSILI */
            array('upper' => 7945, 'status' => 'C', 'lower' => array(7937)), /* GREEK CAPITAL LETTER ALPHA WITH DASIA */
            array('upper' => 7946, 'status' => 'C', 'lower' => array(7938)), /* GREEK CAPITAL LETTER ALPHA WITH PSILI AND VARIA */
            array('upper' => 7947, 'status' => 'C', 'lower' => array(7939)), /* GREEK CAPITAL LETTER ALPHA WITH DASIA AND VARIA */
            array('upper' => 7948, 'status' => 'C', 'lower' => array(7940)), /* GREEK CAPITAL LETTER ALPHA WITH PSILI AND OXIA */
            array('upper' => 7949, 'status' => 'C', 'lower' => array(7941)), /* GREEK CAPITAL LETTER ALPHA WITH DASIA AND OXIA */
            array('upper' => 7950, 'status' => 'C', 'lower' => array(7942)), /* GREEK CAPITAL LETTER ALPHA WITH PSILI AND PERISPOMENI */
            array('upper' => 7951, 'status' => 'C', 'lower' => array(7943)), /* GREEK CAPITAL LETTER ALPHA WITH DASIA AND PERISPOMENI */
            array('upper' => 7960, 'status' => 'C', 'lower' => array(7952)), /* GREEK CAPITAL LETTER EPSILON WITH PSILI */
            array('upper' => 7961, 'status' => 'C', 'lower' => array(7953)), /* GREEK CAPITAL LETTER EPSILON WITH DASIA */
            array('upper' => 7962, 'status' => 'C', 'lower' => array(7954)), /* GREEK CAPITAL LETTER EPSILON WITH PSILI AND VARIA */
            array('upper' => 7963, 'status' => 'C', 'lower' => array(7955)), /* GREEK CAPITAL LETTER EPSILON WITH DASIA AND VARIA */
            array('upper' => 7964, 'status' => 'C', 'lower' => array(7956)), /* GREEK CAPITAL LETTER EPSILON WITH PSILI AND OXIA */
            array('upper' => 7965, 'status' => 'C', 'lower' => array(7957)), /* GREEK CAPITAL LETTER EPSILON WITH DASIA AND OXIA */
            array('upper' => 7976, 'status' => 'C', 'lower' => array(7968)), /* GREEK CAPITAL LETTER ETA WITH PSILI */
            array('upper' => 7977, 'status' => 'C', 'lower' => array(7969)), /* GREEK CAPITAL LETTER ETA WITH DASIA */
            array('upper' => 7978, 'status' => 'C', 'lower' => array(7970)), /* GREEK CAPITAL LETTER ETA WITH PSILI AND VARIA */
            array('upper' => 7979, 'status' => 'C', 'lower' => array(7971)), /* GREEK CAPITAL LETTER ETA WITH DASIA AND VARIA */
            array('upper' => 7980, 'status' => 'C', 'lower' => array(7972)), /* GREEK CAPITAL LETTER ETA WITH PSILI AND OXIA */
            array('upper' => 7981, 'status' => 'C', 'lower' => array(7973)), /* GREEK CAPITAL LETTER ETA WITH DASIA AND OXIA */
            array('upper' => 7982, 'status' => 'C', 'lower' => array(7974)), /* GREEK CAPITAL LETTER ETA WITH PSILI AND PERISPOMENI */
            array('upper' => 7983, 'status' => 'C', 'lower' => array(7975)), /* GREEK CAPITAL LETTER ETA WITH DASIA AND PERISPOMENI */
            array('upper' => 7992, 'status' => 'C', 'lower' => array(7984)), /* GREEK CAPITAL LETTER IOTA WITH PSILI */
            array('upper' => 7993, 'status' => 'C', 'lower' => array(7985)), /* GREEK CAPITAL LETTER IOTA WITH DASIA */
            array('upper' => 7994, 'status' => 'C', 'lower' => array(7986)), /* GREEK CAPITAL LETTER IOTA WITH PSILI AND VARIA */
            array('upper' => 7995, 'status' => 'C', 'lower' => array(7987)), /* GREEK CAPITAL LETTER IOTA WITH DASIA AND VARIA */
            array('upper' => 7996, 'status' => 'C', 'lower' => array(7988)), /* GREEK CAPITAL LETTER IOTA WITH PSILI AND OXIA */
            array('upper' => 7997, 'status' => 'C', 'lower' => array(7989)), /* GREEK CAPITAL LETTER IOTA WITH DASIA AND OXIA */
            array('upper' => 7998, 'status' => 'C', 'lower' => array(7990)), /* GREEK CAPITAL LETTER IOTA WITH PSILI AND PERISPOMENI */
            array('upper' => 7999, 'status' => 'C', 'lower' => array(7991)), /* GREEK CAPITAL LETTER IOTA WITH DASIA AND PERISPOMENI */
            array('upper' => 8008, 'status' => 'C', 'lower' => array(8000)), /* GREEK CAPITAL LETTER OMICRON WITH PSILI */
            array('upper' => 8009, 'status' => 'C', 'lower' => array(8001)), /* GREEK CAPITAL LETTER OMICRON WITH DASIA */
            array('upper' => 8010, 'status' => 'C', 'lower' => array(8002)), /* GREEK CAPITAL LETTER OMICRON WITH PSILI AND VARIA */
            array('upper' => 8011, 'status' => 'C', 'lower' => array(8003)), /* GREEK CAPITAL LETTER OMICRON WITH DASIA AND VARIA */
            array('upper' => 8012, 'status' => 'C', 'lower' => array(8004)), /* GREEK CAPITAL LETTER OMICRON WITH PSILI AND OXIA */
            array('upper' => 8013, 'status' => 'C', 'lower' => array(8005)), /* GREEK CAPITAL LETTER OMICRON WITH DASIA AND OXIA */
            array('upper' => 8016, 'status' => 'F', 'lower' => array(965, 787)), /* GREEK SMALL LETTER UPSILON WITH PSILI */
            array('upper' => 8018, 'status' => 'F', 'lower' => array(965, 787, 768)), /* GREEK SMALL LETTER UPSILON WITH PSILI AND VARIA */
            array('upper' => 8020, 'status' => 'F', 'lower' => array(965, 787, 769)), /* GREEK SMALL LETTER UPSILON WITH PSILI AND OXIA */
            array('upper' => 8022, 'status' => 'F', 'lower' => array(965, 787, 834)), /* GREEK SMALL LETTER UPSILON WITH PSILI AND PERISPOMENI */
            array('upper' => 8025, 'status' => 'C', 'lower' => array(8017)), /* GREEK CAPITAL LETTER UPSILON WITH DASIA */
            array('upper' => 8027, 'status' => 'C', 'lower' => array(8019)), /* GREEK CAPITAL LETTER UPSILON WITH DASIA AND VARIA */
            array('upper' => 8029, 'status' => 'C', 'lower' => array(8021)), /* GREEK CAPITAL LETTER UPSILON WITH DASIA AND OXIA */
            array('upper' => 8031, 'status' => 'C', 'lower' => array(8023)), /* GREEK CAPITAL LETTER UPSILON WITH DASIA AND PERISPOMENI */
            array('upper' => 8040, 'status' => 'C', 'lower' => array(8032)), /* GREEK CAPITAL LETTER OMEGA WITH PSILI */
            array('upper' => 8041, 'status' => 'C', 'lower' => array(8033)), /* GREEK CAPITAL LETTER OMEGA WITH DASIA */
            array('upper' => 8042, 'status' => 'C', 'lower' => array(8034)), /* GREEK CAPITAL LETTER OMEGA WITH PSILI AND VARIA */
            array('upper' => 8043, 'status' => 'C', 'lower' => array(8035)), /* GREEK CAPITAL LETTER OMEGA WITH DASIA AND VARIA */
            array('upper' => 8044, 'status' => 'C', 'lower' => array(8036)), /* GREEK CAPITAL LETTER OMEGA WITH PSILI AND OXIA */
            array('upper' => 8045, 'status' => 'C', 'lower' => array(8037)), /* GREEK CAPITAL LETTER OMEGA WITH DASIA AND OXIA */
            array('upper' => 8046, 'status' => 'C', 'lower' => array(8038)), /* GREEK CAPITAL LETTER OMEGA WITH PSILI AND PERISPOMENI */
            array('upper' => 8047, 'status' => 'C', 'lower' => array(8039)), /* GREEK CAPITAL LETTER OMEGA WITH DASIA AND PERISPOMENI */
            array('upper' => 8064, 'status' => 'F', 'lower' => array(7936, 953)), /* GREEK SMALL LETTER ALPHA WITH PSILI AND YPOGEGRAMMENI */
            array('upper' => 8065, 'status' => 'F', 'lower' => array(7937, 953)), /* GREEK SMALL LETTER ALPHA WITH DASIA AND YPOGEGRAMMENI */
            array('upper' => 8066, 'status' => 'F', 'lower' => array(7938, 953)), /* GREEK SMALL LETTER ALPHA WITH PSILI AND VARIA AND YPOGEGRAMMENI */
            array('upper' => 8067, 'status' => 'F', 'lower' => array(7939, 953)), /* GREEK SMALL LETTER ALPHA WITH DASIA AND VARIA AND YPOGEGRAMMENI */
            array('upper' => 8068, 'status' => 'F', 'lower' => array(7940, 953)), /* GREEK SMALL LETTER ALPHA WITH PSILI AND OXIA AND YPOGEGRAMMENI */
            array('upper' => 8069, 'status' => 'F', 'lower' => array(7941, 953)), /* GREEK SMALL LETTER ALPHA WITH DASIA AND OXIA AND YPOGEGRAMMENI */
            array('upper' => 8070, 'status' => 'F', 'lower' => array(7942, 953)), /* GREEK SMALL LETTER ALPHA WITH PSILI AND PERISPOMENI AND YPOGEGRAMMENI */
            array('upper' => 8071, 'status' => 'F', 'lower' => array(7943, 953)), /* GREEK SMALL LETTER ALPHA WITH DASIA AND PERISPOMENI AND YPOGEGRAMMENI */
            array('upper' => 8072, 'status' => 'F', 'lower' => array(7936, 953)), /* GREEK CAPITAL LETTER ALPHA WITH PSILI AND PROSGEGRAMMENI */
            array('upper' => 8072, 'status' => 'S', 'lower' => array(8064)), /* GREEK CAPITAL LETTER ALPHA WITH PSILI AND PROSGEGRAMMENI */
            array('upper' => 8073, 'status' => 'F', 'lower' => array(7937, 953)), /* GREEK CAPITAL LETTER ALPHA WITH DASIA AND PROSGEGRAMMENI */
            array('upper' => 8073, 'status' => 'S', 'lower' => array(8065)), /* GREEK CAPITAL LETTER ALPHA WITH DASIA AND PROSGEGRAMMENI */
            array('upper' => 8074, 'status' => 'F', 'lower' => array(7938, 953)), /* GREEK CAPITAL LETTER ALPHA WITH PSILI AND VARIA AND PROSGEGRAMMENI */
            array('upper' => 8074, 'status' => 'S', 'lower' => array(8066)), /* GREEK CAPITAL LETTER ALPHA WITH PSILI AND VARIA AND PROSGEGRAMMENI */
            array('upper' => 8075, 'status' => 'F', 'lower' => array(7939, 953)), /* GREEK CAPITAL LETTER ALPHA WITH DASIA AND VARIA AND PROSGEGRAMMENI */
            array('upper' => 8075, 'status' => 'S', 'lower' => array(8067)), /* GREEK CAPITAL LETTER ALPHA WITH DASIA AND VARIA AND PROSGEGRAMMENI */
            array('upper' => 8076, 'status' => 'F', 'lower' => array(7940, 953)), /* GREEK CAPITAL LETTER ALPHA WITH PSILI AND OXIA AND PROSGEGRAMMENI */
            array('upper' => 8076, 'status' => 'S', 'lower' => array(8068)), /* GREEK CAPITAL LETTER ALPHA WITH PSILI AND OXIA AND PROSGEGRAMMENI */
            array('upper' => 8077, 'status' => 'F', 'lower' => array(7941, 953)), /* GREEK CAPITAL LETTER ALPHA WITH DASIA AND OXIA AND PROSGEGRAMMENI */
            array('upper' => 8077, 'status' => 'S', 'lower' => array(8069)), /* GREEK CAPITAL LETTER ALPHA WITH DASIA AND OXIA AND PROSGEGRAMMENI */
            array('upper' => 8078, 'status' => 'F', 'lower' => array(7942, 953)), /* GREEK CAPITAL LETTER ALPHA WITH PSILI AND PERISPOMENI AND PROSGEGRAMMENI */
            array('upper' => 8078, 'status' => 'S', 'lower' => array(8070)), /* GREEK CAPITAL LETTER ALPHA WITH PSILI AND PERISPOMENI AND PROSGEGRAMMENI */
            array('upper' => 8079, 'status' => 'F', 'lower' => array(7943, 953)), /* GREEK CAPITAL LETTER ALPHA WITH DASIA AND PERISPOMENI AND PROSGEGRAMMENI */
            array('upper' => 8079, 'status' => 'S', 'lower' => array(8071)), /* GREEK CAPITAL LETTER ALPHA WITH DASIA AND PERISPOMENI AND PROSGEGRAMMENI */
            array('upper' => 8080, 'status' => 'F', 'lower' => array(7968, 953)), /* GREEK SMALL LETTER ETA WITH PSILI AND YPOGEGRAMMENI */
            array('upper' => 8081, 'status' => 'F', 'lower' => array(7969, 953)), /* GREEK SMALL LETTER ETA WITH DASIA AND YPOGEGRAMMENI */
            array('upper' => 8082, 'status' => 'F', 'lower' => array(7970, 953)), /* GREEK SMALL LETTER ETA WITH PSILI AND VARIA AND YPOGEGRAMMENI */
            array('upper' => 8083, 'status' => 'F', 'lower' => array(7971, 953)), /* GREEK SMALL LETTER ETA WITH DASIA AND VARIA AND YPOGEGRAMMENI */
            array('upper' => 8084, 'status' => 'F', 'lower' => array(7972, 953)), /* GREEK SMALL LETTER ETA WITH PSILI AND OXIA AND YPOGEGRAMMENI */
            array('upper' => 8085, 'status' => 'F', 'lower' => array(7973, 953)), /* GREEK SMALL LETTER ETA WITH DASIA AND OXIA AND YPOGEGRAMMENI */
            array('upper' => 8086, 'status' => 'F', 'lower' => array(7974, 953)), /* GREEK SMALL LETTER ETA WITH PSILI AND PERISPOMENI AND YPOGEGRAMMENI */
            array('upper' => 8087, 'status' => 'F', 'lower' => array(7975, 953)), /* GREEK SMALL LETTER ETA WITH DASIA AND PERISPOMENI AND YPOGEGRAMMENI */
            array('upper' => 8088, 'status' => 'F', 'lower' => array(7968, 953)), /* GREEK CAPITAL LETTER ETA WITH PSILI AND PROSGEGRAMMENI */
            array('upper' => 8088, 'status' => 'S', 'lower' => array(8080)), /* GREEK CAPITAL LETTER ETA WITH PSILI AND PROSGEGRAMMENI */
            array('upper' => 8089, 'status' => 'F', 'lower' => array(7969, 953)), /* GREEK CAPITAL LETTER ETA WITH DASIA AND PROSGEGRAMMENI */
            array('upper' => 8089, 'status' => 'S', 'lower' => array(8081)), /* GREEK CAPITAL LETTER ETA WITH DASIA AND PROSGEGRAMMENI */
            array('upper' => 8090, 'status' => 'F', 'lower' => array(7970, 953)), /* GREEK CAPITAL LETTER ETA WITH PSILI AND VARIA AND PROSGEGRAMMENI */
            array('upper' => 8090, 'status' => 'S', 'lower' => array(8082)), /* GREEK CAPITAL LETTER ETA WITH PSILI AND VARIA AND PROSGEGRAMMENI */
            array('upper' => 8091, 'status' => 'F', 'lower' => array(7971, 953)), /* GREEK CAPITAL LETTER ETA WITH DASIA AND VARIA AND PROSGEGRAMMENI */
            array('upper' => 8091, 'status' => 'S', 'lower' => array(8083)), /* GREEK CAPITAL LETTER ETA WITH DASIA AND VARIA AND PROSGEGRAMMENI */
            array('upper' => 8092, 'status' => 'F', 'lower' => array(7972, 953)), /* GREEK CAPITAL LETTER ETA WITH PSILI AND OXIA AND PROSGEGRAMMENI */
            array('upper' => 8092, 'status' => 'S', 'lower' => array(8084)), /* GREEK CAPITAL LETTER ETA WITH PSILI AND OXIA AND PROSGEGRAMMENI */
            array('upper' => 8093, 'status' => 'F', 'lower' => array(7973, 953)), /* GREEK CAPITAL LETTER ETA WITH DASIA AND OXIA AND PROSGEGRAMMENI */
            array('upper' => 8093, 'status' => 'S', 'lower' => array(8085)), /* GREEK CAPITAL LETTER ETA WITH DASIA AND OXIA AND PROSGEGRAMMENI */
            array('upper' => 8094, 'status' => 'F', 'lower' => array(7974, 953)), /* GREEK CAPITAL LETTER ETA WITH PSILI AND PERISPOMENI AND PROSGEGRAMMENI */
            array('upper' => 8094, 'status' => 'S', 'lower' => array(8086)), /* GREEK CAPITAL LETTER ETA WITH PSILI AND PERISPOMENI AND PROSGEGRAMMENI */
            array('upper' => 8095, 'status' => 'F', 'lower' => array(7975, 953)), /* GREEK CAPITAL LETTER ETA WITH DASIA AND PERISPOMENI AND PROSGEGRAMMENI */
            array('upper' => 8095, 'status' => 'S', 'lower' => array(8087)), /* GREEK CAPITAL LETTER ETA WITH DASIA AND PERISPOMENI AND PROSGEGRAMMENI */
            array('upper' => 8096, 'status' => 'F', 'lower' => array(8032, 953)), /* GREEK SMALL LETTER OMEGA WITH PSILI AND YPOGEGRAMMENI */
            array('upper' => 8097, 'status' => 'F', 'lower' => array(8033, 953)), /* GREEK SMALL LETTER OMEGA WITH DASIA AND YPOGEGRAMMENI */
            array('upper' => 8098, 'status' => 'F', 'lower' => array(8034, 953)), /* GREEK SMALL LETTER OMEGA WITH PSILI AND VARIA AND YPOGEGRAMMENI */
            array('upper' => 8099, 'status' => 'F', 'lower' => array(8035, 953)), /* GREEK SMALL LETTER OMEGA WITH DASIA AND VARIA AND YPOGEGRAMMENI */
            array('upper' => 8100, 'status' => 'F', 'lower' => array(8036, 953)), /* GREEK SMALL LETTER OMEGA WITH PSILI AND OXIA AND YPOGEGRAMMENI */
            array('upper' => 8101, 'status' => 'F', 'lower' => array(8037, 953)), /* GREEK SMALL LETTER OMEGA WITH DASIA AND OXIA AND YPOGEGRAMMENI */
            array('upper' => 8102, 'status' => 'F', 'lower' => array(8038, 953)), /* GREEK SMALL LETTER OMEGA WITH PSILI AND PERISPOMENI AND YPOGEGRAMMENI */
            array('upper' => 8103, 'status' => 'F', 'lower' => array(8039, 953)), /* GREEK SMALL LETTER OMEGA WITH DASIA AND PERISPOMENI AND YPOGEGRAMMENI */
            array('upper' => 8104, 'status' => 'F', 'lower' => array(8032, 953)), /* GREEK CAPITAL LETTER OMEGA WITH PSILI AND PROSGEGRAMMENI */
            array('upper' => 8104, 'status' => 'S', 'lower' => array(8096)), /* GREEK CAPITAL LETTER OMEGA WITH PSILI AND PROSGEGRAMMENI */
            array('upper' => 8105, 'status' => 'F', 'lower' => array(8033, 953)), /* GREEK CAPITAL LETTER OMEGA WITH DASIA AND PROSGEGRAMMENI */
            array('upper' => 8105, 'status' => 'S', 'lower' => array(8097)), /* GREEK CAPITAL LETTER OMEGA WITH DASIA AND PROSGEGRAMMENI */
            array('upper' => 8106, 'status' => 'F', 'lower' => array(8034, 953)), /* GREEK CAPITAL LETTER OMEGA WITH PSILI AND VARIA AND PROSGEGRAMMENI */
            array('upper' => 8106, 'status' => 'S', 'lower' => array(8098)), /* GREEK CAPITAL LETTER OMEGA WITH PSILI AND VARIA AND PROSGEGRAMMENI */
            array('upper' => 8107, 'status' => 'F', 'lower' => array(8035, 953)), /* GREEK CAPITAL LETTER OMEGA WITH DASIA AND VARIA AND PROSGEGRAMMENI */
            array('upper' => 8107, 'status' => 'S', 'lower' => array(8099)), /* GREEK CAPITAL LETTER OMEGA WITH DASIA AND VARIA AND PROSGEGRAMMENI */
            array('upper' => 8108, 'status' => 'F', 'lower' => array(8036, 953)), /* GREEK CAPITAL LETTER OMEGA WITH PSILI AND OXIA AND PROSGEGRAMMENI */
            array('upper' => 8108, 'status' => 'S', 'lower' => array(8100)), /* GREEK CAPITAL LETTER OMEGA WITH PSILI AND OXIA AND PROSGEGRAMMENI */
            array('upper' => 8109, 'status' => 'F', 'lower' => array(8037, 953)), /* GREEK CAPITAL LETTER OMEGA WITH DASIA AND OXIA AND PROSGEGRAMMENI */
            array('upper' => 8109, 'status' => 'S', 'lower' => array(8101)), /* GREEK CAPITAL LETTER OMEGA WITH DASIA AND OXIA AND PROSGEGRAMMENI */
            array('upper' => 8110, 'status' => 'F', 'lower' => array(8038, 953)), /* GREEK CAPITAL LETTER OMEGA WITH PSILI AND PERISPOMENI AND PROSGEGRAMMENI */
            array('upper' => 8110, 'status' => 'S', 'lower' => array(8102)), /* GREEK CAPITAL LETTER OMEGA WITH PSILI AND PERISPOMENI AND PROSGEGRAMMENI */
            array('upper' => 8111, 'status' => 'F', 'lower' => array(8039, 953)), /* GREEK CAPITAL LETTER OMEGA WITH DASIA AND PERISPOMENI AND PROSGEGRAMMENI */
            array('upper' => 8111, 'status' => 'S', 'lower' => array(8103)), /* GREEK CAPITAL LETTER OMEGA WITH DASIA AND PERISPOMENI AND PROSGEGRAMMENI */
            array('upper' => 8114, 'status' => 'F', 'lower' => array(8048, 953)), /* GREEK SMALL LETTER ALPHA WITH VARIA AND YPOGEGRAMMENI */
            array('upper' => 8115, 'status' => 'F', 'lower' => array(945, 953)), /* GREEK SMALL LETTER ALPHA WITH YPOGEGRAMMENI */
            array('upper' => 8116, 'status' => 'F', 'lower' => array(940, 953)), /* GREEK SMALL LETTER ALPHA WITH OXIA AND YPOGEGRAMMENI */
            array('upper' => 8118, 'status' => 'F', 'lower' => array(945, 834)), /* GREEK SMALL LETTER ALPHA WITH PERISPOMENI */
            array('upper' => 8119, 'status' => 'F', 'lower' => array(945, 834, 953)), /* GREEK SMALL LETTER ALPHA WITH PERISPOMENI AND YPOGEGRAMMENI */
            array('upper' => 8120, 'status' => 'C', 'lower' => array(8112)), /* GREEK CAPITAL LETTER ALPHA WITH VRACHY */
            array('upper' => 8121, 'status' => 'C', 'lower' => array(8113)), /* GREEK CAPITAL LETTER ALPHA WITH MACRON */
            array('upper' => 8122, 'status' => 'C', 'lower' => array(8048)), /* GREEK CAPITAL LETTER ALPHA WITH VARIA */
            array('upper' => 8123, 'status' => 'C', 'lower' => array(8049)), /* GREEK CAPITAL LETTER ALPHA WITH OXIA */
            array('upper' => 8124, 'status' => 'F', 'lower' => array(945, 953)), /* GREEK CAPITAL LETTER ALPHA WITH PROSGEGRAMMENI */
            array('upper' => 8124, 'status' => 'S', 'lower' => array(8115)), /* GREEK CAPITAL LETTER ALPHA WITH PROSGEGRAMMENI */
            array('upper' => 8126, 'status' => 'C', 'lower' => array(953)), /* GREEK PROSGEGRAMMENI */
            array('upper' => 8130, 'status' => 'F', 'lower' => array(8052, 953)), /* GREEK SMALL LETTER ETA WITH VARIA AND YPOGEGRAMMENI */
            array('upper' => 8131, 'status' => 'F', 'lower' => array(951, 953)), /* GREEK SMALL LETTER ETA WITH YPOGEGRAMMENI */
            array('upper' => 8132, 'status' => 'F', 'lower' => array(942, 953)), /* GREEK SMALL LETTER ETA WITH OXIA AND YPOGEGRAMMENI */
            array('upper' => 8134, 'status' => 'F', 'lower' => array(951, 834)), /* GREEK SMALL LETTER ETA WITH PERISPOMENI */
            array('upper' => 8135, 'status' => 'F', 'lower' => array(951, 834, 953)), /* GREEK SMALL LETTER ETA WITH PERISPOMENI AND YPOGEGRAMMENI */
            array('upper' => 8136, 'status' => 'C', 'lower' => array(8050)), /* GREEK CAPITAL LETTER EPSILON WITH VARIA */
            array('upper' => 8137, 'status' => 'C', 'lower' => array(8051)), /* GREEK CAPITAL LETTER EPSILON WITH OXIA */
            array('upper' => 8138, 'status' => 'C', 'lower' => array(8052)), /* GREEK CAPITAL LETTER ETA WITH VARIA */
            array('upper' => 8139, 'status' => 'C', 'lower' => array(8053)), /* GREEK CAPITAL LETTER ETA WITH OXIA */
            array('upper' => 8140, 'status' => 'F', 'lower' => array(951, 953)), /* GREEK CAPITAL LETTER ETA WITH PROSGEGRAMMENI */
            array('upper' => 8140, 'status' => 'S', 'lower' => array(8131)), /* GREEK CAPITAL LETTER ETA WITH PROSGEGRAMMENI */
            array('upper' => 8146, 'status' => 'F', 'lower' => array(953, 776, 768)), /* GREEK SMALL LETTER IOTA WITH DIALYTIKA AND VARIA */
            array('upper' => 8147, 'status' => 'F', 'lower' => array(953, 776, 769)), /* GREEK SMALL LETTER IOTA WITH DIALYTIKA AND OXIA */
            array('upper' => 8150, 'status' => 'F', 'lower' => array(953, 834)), /* GREEK SMALL LETTER IOTA WITH PERISPOMENI */
            array('upper' => 8151, 'status' => 'F', 'lower' => array(953, 776, 834)), /* GREEK SMALL LETTER IOTA WITH DIALYTIKA AND PERISPOMENI */
            array('upper' => 8152, 'status' => 'C', 'lower' => array(8144)), /* GREEK CAPITAL LETTER IOTA WITH VRACHY */
            array('upper' => 8153, 'status' => 'C', 'lower' => array(8145)), /* GREEK CAPITAL LETTER IOTA WITH MACRON */
            array('upper' => 8154, 'status' => 'C', 'lower' => array(8054)), /* GREEK CAPITAL LETTER IOTA WITH VARIA */
            array('upper' => 8155, 'status' => 'C', 'lower' => array(8055)), /* GREEK CAPITAL LETTER IOTA WITH OXIA */
            array('upper' => 8162, 'status' => 'F', 'lower' => array(965, 776, 768)), /* GREEK SMALL LETTER UPSILON WITH DIALYTIKA AND VARIA */
            array('upper' => 8163, 'status' => 'F', 'lower' => array(965, 776, 769)), /* GREEK SMALL LETTER UPSILON WITH DIALYTIKA AND OXIA */
            array('upper' => 8164, 'status' => 'F', 'lower' => array(961, 787)), /* GREEK SMALL LETTER RHO WITH PSILI */
            array('upper' => 8166, 'status' => 'F', 'lower' => array(965, 834)), /* GREEK SMALL LETTER UPSILON WITH PERISPOMENI */
            array('upper' => 8167, 'status' => 'F', 'lower' => array(965, 776, 834)), /* GREEK SMALL LETTER UPSILON WITH DIALYTIKA AND PERISPOMENI */
            array('upper' => 8168, 'status' => 'C', 'lower' => array(8160)), /* GREEK CAPITAL LETTER UPSILON WITH VRACHY */
            array('upper' => 8169, 'status' => 'C', 'lower' => array(8161)), /* GREEK CAPITAL LETTER UPSILON WITH MACRON */
            array('upper' => 8170, 'status' => 'C', 'lower' => array(8058)), /* GREEK CAPITAL LETTER UPSILON WITH VARIA */
            array('upper' => 8171, 'status' => 'C', 'lower' => array(8059)), /* GREEK CAPITAL LETTER UPSILON WITH OXIA */
            array('upper' => 8172, 'status' => 'C', 'lower' => array(8165)), /* GREEK CAPITAL LETTER RHO WITH DASIA */
            array('upper' => 8178, 'status' => 'F', 'lower' => array(8060, 953)), /* GREEK SMALL LETTER OMEGA WITH VARIA AND YPOGEGRAMMENI */
            array('upper' => 8179, 'status' => 'F', 'lower' => array(969, 953)), /* GREEK SMALL LETTER OMEGA WITH YPOGEGRAMMENI */
            array('upper' => 8180, 'status' => 'F', 'lower' => array(974, 953)), /* GREEK SMALL LETTER OMEGA WITH OXIA AND YPOGEGRAMMENI */
            array('upper' => 8182, 'status' => 'F', 'lower' => array(969, 834)), /* GREEK SMALL LETTER OMEGA WITH PERISPOMENI */
            array('upper' => 8183, 'status' => 'F', 'lower' => array(969, 834, 953)), /* GREEK SMALL LETTER OMEGA WITH PERISPOMENI AND YPOGEGRAMMENI */
            array('upper' => 8184, 'status' => 'C', 'lower' => array(8056)), /* GREEK CAPITAL LETTER OMICRON WITH VARIA */
            array('upper' => 8185, 'status' => 'C', 'lower' => array(8057)), /* GREEK CAPITAL LETTER OMICRON WITH OXIA */
            array('upper' => 8186, 'status' => 'C', 'lower' => array(8060)), /* GREEK CAPITAL LETTER OMEGA WITH VARIA */
            array('upper' => 8187, 'status' => 'C', 'lower' => array(8061)), /* GREEK CAPITAL LETTER OMEGA WITH OXIA */
            array('upper' => 8188, 'status' => 'F', 'lower' => array(969, 953)), /* GREEK CAPITAL LETTER OMEGA WITH PROSGEGRAMMENI */
            array('upper' => 8188, 'status' => 'S', 'lower' => array(8179)), /* GREEK CAPITAL LETTER OMEGA WITH PROSGEGRAMMENI */
          );
          break;
        case '2100_214f':
          self::$_codeRanges['2100_214f'] = array(
            array('upper' => 8486, 'status' => 'C', 'lower' => array(969)), /* OHM SIGN */
            array('upper' => 8490, 'status' => 'C', 'lower' => array(107)), /* KELVIN SIGN */
            array('upper' => 8491, 'status' => 'C', 'lower' => array(229)), /* ANGSTROM SIGN */
            array('upper' => 8498, 'status' => 'C', 'lower' => array(8526)), /* TURNED CAPITAL F */
          );
          break;
        case '2150_218f':
          self::$_codeRanges['2150_218f'] = array(
            array('upper' => 8544, 'status' => 'C', 'lower' => array(8560)), /* ROMAN NUMERAL ONE */
            array('upper' => 8545, 'status' => 'C', 'lower' => array(8561)), /* ROMAN NUMERAL TWO */
            array('upper' => 8546, 'status' => 'C', 'lower' => array(8562)), /* ROMAN NUMERAL THREE */
            array('upper' => 8547, 'status' => 'C', 'lower' => array(8563)), /* ROMAN NUMERAL FOUR */
            array('upper' => 8548, 'status' => 'C', 'lower' => array(8564)), /* ROMAN NUMERAL FIVE */
            array('upper' => 8549, 'status' => 'C', 'lower' => array(8565)), /* ROMAN NUMERAL SIX */
            array('upper' => 8550, 'status' => 'C', 'lower' => array(8566)), /* ROMAN NUMERAL SEVEN */
            array('upper' => 8551, 'status' => 'C', 'lower' => array(8567)), /* ROMAN NUMERAL EIGHT */
            array('upper' => 8552, 'status' => 'C', 'lower' => array(8568)), /* ROMAN NUMERAL NINE */
            array('upper' => 8553, 'status' => 'C', 'lower' => array(8569)), /* ROMAN NUMERAL TEN */
            array('upper' => 8554, 'status' => 'C', 'lower' => array(8570)), /* ROMAN NUMERAL ELEVEN */
            array('upper' => 8555, 'status' => 'C', 'lower' => array(8571)), /* ROMAN NUMERAL TWELVE */
            array('upper' => 8556, 'status' => 'C', 'lower' => array(8572)), /* ROMAN NUMERAL FIFTY */
            array('upper' => 8557, 'status' => 'C', 'lower' => array(8573)), /* ROMAN NUMERAL ONE HUNDRED */
            array('upper' => 8558, 'status' => 'C', 'lower' => array(8574)), /* ROMAN NUMERAL FIVE HUNDRED */
            array('upper' => 8559, 'status' => 'C', 'lower' => array(8575)), /* ROMAN NUMERAL ONE THOUSAND */
            array('upper' => 8579, 'status' => 'C', 'lower' => array(8580)), /* ROMAN NUMERAL REVERSED ONE HUNDRED */
          );
          break;
        case '2460_24ff':
          self::$_codeRanges['2460_24ff'] = array(
            array('upper' => 9398, 'status' => 'C', 'lower' => array(9424)), /* CIRCLED LATIN CAPITAL LETTER A */
            array('upper' => 9399, 'status' => 'C', 'lower' => array(9425)), /* CIRCLED LATIN CAPITAL LETTER B */
            array('upper' => 9400, 'status' => 'C', 'lower' => array(9426)), /* CIRCLED LATIN CAPITAL LETTER C */
            array('upper' => 9401, 'status' => 'C', 'lower' => array(9427)), /* CIRCLED LATIN CAPITAL LETTER D */
            array('upper' => 9402, 'status' => 'C', 'lower' => array(9428)), /* CIRCLED LATIN CAPITAL LETTER E */
            array('upper' => 9403, 'status' => 'C', 'lower' => array(9429)), /* CIRCLED LATIN CAPITAL LETTER F */
            array('upper' => 9404, 'status' => 'C', 'lower' => array(9430)), /* CIRCLED LATIN CAPITAL LETTER G */
            array('upper' => 9405, 'status' => 'C', 'lower' => array(9431)), /* CIRCLED LATIN CAPITAL LETTER H */
            array('upper' => 9406, 'status' => 'C', 'lower' => array(9432)), /* CIRCLED LATIN CAPITAL LETTER I */
            array('upper' => 9407, 'status' => 'C', 'lower' => array(9433)), /* CIRCLED LATIN CAPITAL LETTER J */
            array('upper' => 9408, 'status' => 'C', 'lower' => array(9434)), /* CIRCLED LATIN CAPITAL LETTER K */
            array('upper' => 9409, 'status' => 'C', 'lower' => array(9435)), /* CIRCLED LATIN CAPITAL LETTER L */
            array('upper' => 9410, 'status' => 'C', 'lower' => array(9436)), /* CIRCLED LATIN CAPITAL LETTER M */
            array('upper' => 9411, 'status' => 'C', 'lower' => array(9437)), /* CIRCLED LATIN CAPITAL LETTER N */
            array('upper' => 9412, 'status' => 'C', 'lower' => array(9438)), /* CIRCLED LATIN CAPITAL LETTER O */
            array('upper' => 9413, 'status' => 'C', 'lower' => array(9439)), /* CIRCLED LATIN CAPITAL LETTER P */
            array('upper' => 9414, 'status' => 'C', 'lower' => array(9440)), /* CIRCLED LATIN CAPITAL LETTER Q */
            array('upper' => 9415, 'status' => 'C', 'lower' => array(9441)), /* CIRCLED LATIN CAPITAL LETTER R */
            array('upper' => 9416, 'status' => 'C', 'lower' => array(9442)), /* CIRCLED LATIN CAPITAL LETTER S */
            array('upper' => 9417, 'status' => 'C', 'lower' => array(9443)), /* CIRCLED LATIN CAPITAL LETTER T */
            array('upper' => 9418, 'status' => 'C', 'lower' => array(9444)), /* CIRCLED LATIN CAPITAL LETTER U */
            array('upper' => 9419, 'status' => 'C', 'lower' => array(9445)), /* CIRCLED LATIN CAPITAL LETTER V */
            array('upper' => 9420, 'status' => 'C', 'lower' => array(9446)), /* CIRCLED LATIN CAPITAL LETTER W */
            array('upper' => 9421, 'status' => 'C', 'lower' => array(9447)), /* CIRCLED LATIN CAPITAL LETTER X */
            array('upper' => 9422, 'status' => 'C', 'lower' => array(9448)), /* CIRCLED LATIN CAPITAL LETTER Y */
            array('upper' => 9423, 'status' => 'C', 'lower' => array(9449)), /* CIRCLED LATIN CAPITAL LETTER Z */
          );
          break;
        case '2c00_2c5f':
          self::$_codeRanges['2c00_2c5f'] = array(
            array('upper' => 11264, 'status' => 'C', 'lower' => array(11312)), /* GLAGOLITIC CAPITAL LETTER AZU */
            array('upper' => 11265, 'status' => 'C', 'lower' => array(11313)), /* GLAGOLITIC CAPITAL LETTER BUKY */
            array('upper' => 11266, 'status' => 'C', 'lower' => array(11314)), /* GLAGOLITIC CAPITAL LETTER VEDE */
            array('upper' => 11267, 'status' => 'C', 'lower' => array(11315)), /* GLAGOLITIC CAPITAL LETTER GLAGOLI */
            array('upper' => 11268, 'status' => 'C', 'lower' => array(11316)), /* GLAGOLITIC CAPITAL LETTER DOBRO */
            array('upper' => 11269, 'status' => 'C', 'lower' => array(11317)), /* GLAGOLITIC CAPITAL LETTER YESTU */
            array('upper' => 11270, 'status' => 'C', 'lower' => array(11318)), /* GLAGOLITIC CAPITAL LETTER ZHIVETE */
            array('upper' => 11271, 'status' => 'C', 'lower' => array(11319)), /* GLAGOLITIC CAPITAL LETTER DZELO */
            array('upper' => 11272, 'status' => 'C', 'lower' => array(11320)), /* GLAGOLITIC CAPITAL LETTER ZEMLJA */
            array('upper' => 11273, 'status' => 'C', 'lower' => array(11321)), /* GLAGOLITIC CAPITAL LETTER IZHE */
            array('upper' => 11274, 'status' => 'C', 'lower' => array(11322)), /* GLAGOLITIC CAPITAL LETTER INITIAL IZHE */
            array('upper' => 11275, 'status' => 'C', 'lower' => array(11323)), /* GLAGOLITIC CAPITAL LETTER I */
            array('upper' => 11276, 'status' => 'C', 'lower' => array(11324)), /* GLAGOLITIC CAPITAL LETTER DJERVI */
            array('upper' => 11277, 'status' => 'C', 'lower' => array(11325)), /* GLAGOLITIC CAPITAL LETTER KAKO */
            array('upper' => 11278, 'status' => 'C', 'lower' => array(11326)), /* GLAGOLITIC CAPITAL LETTER LJUDIJE */
            array('upper' => 11279, 'status' => 'C', 'lower' => array(11327)), /* GLAGOLITIC CAPITAL LETTER MYSLITE */
            array('upper' => 11280, 'status' => 'C', 'lower' => array(11328)), /* GLAGOLITIC CAPITAL LETTER NASHI */
            array('upper' => 11281, 'status' => 'C', 'lower' => array(11329)), /* GLAGOLITIC CAPITAL LETTER ONU */
            array('upper' => 11282, 'status' => 'C', 'lower' => array(11330)), /* GLAGOLITIC CAPITAL LETTER POKOJI */
            array('upper' => 11283, 'status' => 'C', 'lower' => array(11331)), /* GLAGOLITIC CAPITAL LETTER RITSI */
            array('upper' => 11284, 'status' => 'C', 'lower' => array(11332)), /* GLAGOLITIC CAPITAL LETTER SLOVO */
            array('upper' => 11285, 'status' => 'C', 'lower' => array(11333)), /* GLAGOLITIC CAPITAL LETTER TVRIDO */
            array('upper' => 11286, 'status' => 'C', 'lower' => array(11334)), /* GLAGOLITIC CAPITAL LETTER UKU */
            array('upper' => 11287, 'status' => 'C', 'lower' => array(11335)), /* GLAGOLITIC CAPITAL LETTER FRITU */
            array('upper' => 11288, 'status' => 'C', 'lower' => array(11336)), /* GLAGOLITIC CAPITAL LETTER HERU */
            array('upper' => 11289, 'status' => 'C', 'lower' => array(11337)), /* GLAGOLITIC CAPITAL LETTER OTU */
            array('upper' => 11290, 'status' => 'C', 'lower' => array(11338)), /* GLAGOLITIC CAPITAL LETTER PE */
            array('upper' => 11291, 'status' => 'C', 'lower' => array(11339)), /* GLAGOLITIC CAPITAL LETTER SHTA */
            array('upper' => 11292, 'status' => 'C', 'lower' => array(11340)), /* GLAGOLITIC CAPITAL LETTER TSI */
            array('upper' => 11293, 'status' => 'C', 'lower' => array(11341)), /* GLAGOLITIC CAPITAL LETTER CHRIVI */
            array('upper' => 11294, 'status' => 'C', 'lower' => array(11342)), /* GLAGOLITIC CAPITAL LETTER SHA */
            array('upper' => 11295, 'status' => 'C', 'lower' => array(11343)), /* GLAGOLITIC CAPITAL LETTER YERU */
            array('upper' => 11296, 'status' => 'C', 'lower' => array(11344)), /* GLAGOLITIC CAPITAL LETTER YERI */
            array('upper' => 11297, 'status' => 'C', 'lower' => array(11345)), /* GLAGOLITIC CAPITAL LETTER YATI */
            array('upper' => 11298, 'status' => 'C', 'lower' => array(11346)), /* GLAGOLITIC CAPITAL LETTER SPIDERY HA */
            array('upper' => 11299, 'status' => 'C', 'lower' => array(11347)), /* GLAGOLITIC CAPITAL LETTER YU */
            array('upper' => 11300, 'status' => 'C', 'lower' => array(11348)), /* GLAGOLITIC CAPITAL LETTER SMALL YUS */
            array('upper' => 11301, 'status' => 'C', 'lower' => array(11349)), /* GLAGOLITIC CAPITAL LETTER SMALL YUS WITH TAIL */
            array('upper' => 11302, 'status' => 'C', 'lower' => array(11350)), /* GLAGOLITIC CAPITAL LETTER YO */
            array('upper' => 11303, 'status' => 'C', 'lower' => array(11351)), /* GLAGOLITIC CAPITAL LETTER IOTATED SMALL YUS */
            array('upper' => 11304, 'status' => 'C', 'lower' => array(11352)), /* GLAGOLITIC CAPITAL LETTER BIG YUS */
            array('upper' => 11305, 'status' => 'C', 'lower' => array(11353)), /* GLAGOLITIC CAPITAL LETTER IOTATED BIG YUS */
            array('upper' => 11306, 'status' => 'C', 'lower' => array(11354)), /* GLAGOLITIC CAPITAL LETTER FITA */
            array('upper' => 11307, 'status' => 'C', 'lower' => array(11355)), /* GLAGOLITIC CAPITAL LETTER IZHITSA */
            array('upper' => 11308, 'status' => 'C', 'lower' => array(11356)), /* GLAGOLITIC CAPITAL LETTER SHTAPIC */
            array('upper' => 11309, 'status' => 'C', 'lower' => array(11357)), /* GLAGOLITIC CAPITAL LETTER TROKUTASTI A */
            array('upper' => 11310, 'status' => 'C', 'lower' => array(11358)), /* GLAGOLITIC CAPITAL LETTER LATINATE MYSLITE */
          );
          break;
        case '2c60_2c7f':
          self::$_codeRanges['2c60_2c7f'] = array(
            array('upper' => 11360, 'status' => 'C', 'lower' => array(11361)), /* LATIN CAPITAL LETTER L WITH DOUBLE BAR */
            array('upper' => 11362, 'status' => 'C', 'lower' => array(619)), /* LATIN CAPITAL LETTER L WITH MIDDLE TILDE */
            array('upper' => 11363, 'status' => 'C', 'lower' => array(7549)), /* LATIN CAPITAL LETTER P WITH STROKE */
            array('upper' => 11364, 'status' => 'C', 'lower' => array(637)), /* LATIN CAPITAL LETTER R WITH TAIL */
            array('upper' => 11367, 'status' => 'C', 'lower' => array(11368)), /* LATIN CAPITAL LETTER H WITH DESCENDER */
            array('upper' => 11369, 'status' => 'C', 'lower' => array(11370)), /* LATIN CAPITAL LETTER K WITH DESCENDER */
            array('upper' => 11371, 'status' => 'C', 'lower' => array(11372)), /* LATIN CAPITAL LETTER Z WITH DESCENDER */
            array('upper' => 11381, 'status' => 'C', 'lower' => array(11382)), /* LATIN CAPITAL LETTER HALF H */
          );
          break;
        case '2c80_2cff':
          self::$_codeRanges['2c80_2cff'] = array(
            array('upper' => 11392, 'status' => 'C', 'lower' => array(11393)), /* COPTIC CAPITAL LETTER ALFA */
            array('upper' => 11394, 'status' => 'C', 'lower' => array(11395)), /* COPTIC CAPITAL LETTER VIDA */
            array('upper' => 11396, 'status' => 'C', 'lower' => array(11397)), /* COPTIC CAPITAL LETTER GAMMA */
            array('upper' => 11398, 'status' => 'C', 'lower' => array(11399)), /* COPTIC CAPITAL LETTER DALDA */
            array('upper' => 11400, 'status' => 'C', 'lower' => array(11401)), /* COPTIC CAPITAL LETTER EIE */
            array('upper' => 11402, 'status' => 'C', 'lower' => array(11403)), /* COPTIC CAPITAL LETTER SOU */
            array('upper' => 11404, 'status' => 'C', 'lower' => array(11405)), /* COPTIC CAPITAL LETTER ZATA */
            array('upper' => 11406, 'status' => 'C', 'lower' => array(11407)), /* COPTIC CAPITAL LETTER HATE */
            array('upper' => 11408, 'status' => 'C', 'lower' => array(11409)), /* COPTIC CAPITAL LETTER THETHE */
            array('upper' => 11410, 'status' => 'C', 'lower' => array(11411)), /* COPTIC CAPITAL LETTER IAUDA */
            array('upper' => 11412, 'status' => 'C', 'lower' => array(11413)), /* COPTIC CAPITAL LETTER KAPA */
            array('upper' => 11414, 'status' => 'C', 'lower' => array(11415)), /* COPTIC CAPITAL LETTER LAULA */
            array('upper' => 11416, 'status' => 'C', 'lower' => array(11417)), /* COPTIC CAPITAL LETTER MI */
            array('upper' => 11418, 'status' => 'C', 'lower' => array(11419)), /* COPTIC CAPITAL LETTER NI */
            array('upper' => 11420, 'status' => 'C', 'lower' => array(11421)), /* COPTIC CAPITAL LETTER KSI */
            array('upper' => 11422, 'status' => 'C', 'lower' => array(11423)), /* COPTIC CAPITAL LETTER O */
            array('upper' => 11424, 'status' => 'C', 'lower' => array(11425)), /* COPTIC CAPITAL LETTER PI */
            array('upper' => 11426, 'status' => 'C', 'lower' => array(11427)), /* COPTIC CAPITAL LETTER RO */
            array('upper' => 11428, 'status' => 'C', 'lower' => array(11429)), /* COPTIC CAPITAL LETTER SIMA */
            array('upper' => 11430, 'status' => 'C', 'lower' => array(11431)), /* COPTIC CAPITAL LETTER TAU */
            array('upper' => 11432, 'status' => 'C', 'lower' => array(11433)), /* COPTIC CAPITAL LETTER UA */
            array('upper' => 11434, 'status' => 'C', 'lower' => array(11435)), /* COPTIC CAPITAL LETTER FI */
            array('upper' => 11436, 'status' => 'C', 'lower' => array(11437)), /* COPTIC CAPITAL LETTER KHI */
            array('upper' => 11438, 'status' => 'C', 'lower' => array(11439)), /* COPTIC CAPITAL LETTER PSI */
            array('upper' => 11440, 'status' => 'C', 'lower' => array(11441)), /* COPTIC CAPITAL LETTER OOU */
            array('upper' => 11442, 'status' => 'C', 'lower' => array(11443)), /* COPTIC CAPITAL LETTER DIALECT-P ALEF */
            array('upper' => 11444, 'status' => 'C', 'lower' => array(11445)), /* COPTIC CAPITAL LETTER OLD COPTIC AIN */
            array('upper' => 11446, 'status' => 'C', 'lower' => array(11447)), /* COPTIC CAPITAL LETTER CRYPTOGRAMMIC EIE */
            array('upper' => 11448, 'status' => 'C', 'lower' => array(11449)), /* COPTIC CAPITAL LETTER DIALECT-P KAPA */
            array('upper' => 11450, 'status' => 'C', 'lower' => array(11451)), /* COPTIC CAPITAL LETTER DIALECT-P NI */
            array('upper' => 11452, 'status' => 'C', 'lower' => array(11453)), /* COPTIC CAPITAL LETTER CRYPTOGRAMMIC NI */
            array('upper' => 11454, 'status' => 'C', 'lower' => array(11455)), /* COPTIC CAPITAL LETTER OLD COPTIC OOU */
            array('upper' => 11456, 'status' => 'C', 'lower' => array(11457)), /* COPTIC CAPITAL LETTER SAMPI */
            array('upper' => 11458, 'status' => 'C', 'lower' => array(11459)), /* COPTIC CAPITAL LETTER CROSSED SHEI */
            array('upper' => 11460, 'status' => 'C', 'lower' => array(11461)), /* COPTIC CAPITAL LETTER OLD COPTIC SHEI */
            array('upper' => 11462, 'status' => 'C', 'lower' => array(11463)), /* COPTIC CAPITAL LETTER OLD COPTIC ESH */
            array('upper' => 11464, 'status' => 'C', 'lower' => array(11465)), /* COPTIC CAPITAL LETTER AKHMIMIC KHEI */
            array('upper' => 11466, 'status' => 'C', 'lower' => array(11467)), /* COPTIC CAPITAL LETTER DIALECT-P HORI */
            array('upper' => 11468, 'status' => 'C', 'lower' => array(11469)), /* COPTIC CAPITAL LETTER OLD COPTIC HORI */
            array('upper' => 11470, 'status' => 'C', 'lower' => array(11471)), /* COPTIC CAPITAL LETTER OLD COPTIC HA */
            array('upper' => 11472, 'status' => 'C', 'lower' => array(11473)), /* COPTIC CAPITAL LETTER L-SHAPED HA */
            array('upper' => 11474, 'status' => 'C', 'lower' => array(11475)), /* COPTIC CAPITAL LETTER OLD COPTIC HEI */
            array('upper' => 11476, 'status' => 'C', 'lower' => array(11477)), /* COPTIC CAPITAL LETTER OLD COPTIC HAT */
            array('upper' => 11478, 'status' => 'C', 'lower' => array(11479)), /* COPTIC CAPITAL LETTER OLD COPTIC GANGIA */
            array('upper' => 11480, 'status' => 'C', 'lower' => array(11481)), /* COPTIC CAPITAL LETTER OLD COPTIC DJA */
            array('upper' => 11482, 'status' => 'C', 'lower' => array(11483)), /* COPTIC CAPITAL LETTER OLD COPTIC SHIMA */
            array('upper' => 11484, 'status' => 'C', 'lower' => array(11485)), /* COPTIC CAPITAL LETTER OLD NUBIAN SHIMA */
            array('upper' => 11486, 'status' => 'C', 'lower' => array(11487)), /* COPTIC CAPITAL LETTER OLD NUBIAN NGI */
            array('upper' => 11488, 'status' => 'C', 'lower' => array(11489)), /* COPTIC CAPITAL LETTER OLD NUBIAN NYI */
            array('upper' => 11490, 'status' => 'C', 'lower' => array(11491)), /* COPTIC CAPITAL LETTER OLD NUBIAN WAU */
          );
          break;
        case 'ff00_ffef':
          self::$_codeRanges['ff00_ffef'] = array(
            array('upper' => 65313, 'status' => 'C', 'lower' => array(65345)), /* FULLWIDTH LATIN CAPITAL LETTER A */
            array('upper' => 65314, 'status' => 'C', 'lower' => array(65346)), /* FULLWIDTH LATIN CAPITAL LETTER B */
            array('upper' => 65315, 'status' => 'C', 'lower' => array(65347)), /* FULLWIDTH LATIN CAPITAL LETTER C */
            array('upper' => 65316, 'status' => 'C', 'lower' => array(65348)), /* FULLWIDTH LATIN CAPITAL LETTER D */
            array('upper' => 65317, 'status' => 'C', 'lower' => array(65349)), /* FULLWIDTH LATIN CAPITAL LETTER E */
            array('upper' => 65318, 'status' => 'C', 'lower' => array(65350)), /* FULLWIDTH LATIN CAPITAL LETTER F */
            array('upper' => 65319, 'status' => 'C', 'lower' => array(65351)), /* FULLWIDTH LATIN CAPITAL LETTER G */
            array('upper' => 65320, 'status' => 'C', 'lower' => array(65352)), /* FULLWIDTH LATIN CAPITAL LETTER H */
            array('upper' => 65321, 'status' => 'C', 'lower' => array(65353)), /* FULLWIDTH LATIN CAPITAL LETTER I */
            array('upper' => 65322, 'status' => 'C', 'lower' => array(65354)), /* FULLWIDTH LATIN CAPITAL LETTER J */
            array('upper' => 65323, 'status' => 'C', 'lower' => array(65355)), /* FULLWIDTH LATIN CAPITAL LETTER K */
            array('upper' => 65324, 'status' => 'C', 'lower' => array(65356)), /* FULLWIDTH LATIN CAPITAL LETTER L */
            array('upper' => 65325, 'status' => 'C', 'lower' => array(65357)), /* FULLWIDTH LATIN CAPITAL LETTER M */
            array('upper' => 65326, 'status' => 'C', 'lower' => array(65358)), /* FULLWIDTH LATIN CAPITAL LETTER N */
            array('upper' => 65327, 'status' => 'C', 'lower' => array(65359)), /* FULLWIDTH LATIN CAPITAL LETTER O */
            array('upper' => 65328, 'status' => 'C', 'lower' => array(65360)), /* FULLWIDTH LATIN CAPITAL LETTER P */
            array('upper' => 65329, 'status' => 'C', 'lower' => array(65361)), /* FULLWIDTH LATIN CAPITAL LETTER Q */
            array('upper' => 65330, 'status' => 'C', 'lower' => array(65362)), /* FULLWIDTH LATIN CAPITAL LETTER R */
            array('upper' => 65331, 'status' => 'C', 'lower' => array(65363)), /* FULLWIDTH LATIN CAPITAL LETTER S */
            array('upper' => 65332, 'status' => 'C', 'lower' => array(65364)), /* FULLWIDTH LATIN CAPITAL LETTER T */
            array('upper' => 65333, 'status' => 'C', 'lower' => array(65365)), /* FULLWIDTH LATIN CAPITAL LETTER U */
            array('upper' => 65334, 'status' => 'C', 'lower' => array(65366)), /* FULLWIDTH LATIN CAPITAL LETTER V */
            array('upper' => 65335, 'status' => 'C', 'lower' => array(65367)), /* FULLWIDTH LATIN CAPITAL LETTER W */
            array('upper' => 65336, 'status' => 'C', 'lower' => array(65368)), /* FULLWIDTH LATIN CAPITAL LETTER X */
            array('upper' => 65337, 'status' => 'C', 'lower' => array(65369)), /* FULLWIDTH LATIN CAPITAL LETTER Y */
            array('upper' => 65338, 'status' => 'C', 'lower' => array(65370)), /* FULLWIDTH LATIN CAPITAL LETTER Z */
          );
          break;
      }
    }
    return self::$_codeRanges[$range];
  }
}

class kxBans
{
  /* Perform a check for a ban record for a specified IP address */
  public static function BanCheck($ip, $board = '', $force_display = false)
  {
    return false;
    if (!isset($_COOKIE['tc_previousip'])) {
      $_COOKIE['tc_previousip'] = '';
    }

    $bans = array();
    $results = kxDB::getinstance()->query("SELECT * FROM `" . kxEnv::Get('kx:db:prefix') . "banlist` WHERE ((`type` = '0' AND ( `ipmd5` = '" . md5($ip) . "' OR `ipmd5` = '" . md5($_COOKIE['tc_previousip']) . "' )) OR `type` = '1') AND (`expired` = 0)");
    if (count($results) > 0) {
      foreach ($results as $line) {
        if (($line['type'] == 1 && strpos($ip, md5_decrypt($line['ip'], kxEnv::Get('kx:misc:randomseed'))) === 0) || $line['type'] == 0) {
          if ($line['until'] != 0 && $line['until'] < time()) {
            kxDB::getinstance()->exec("UPDATE `" . kxEnv::Get('kx:db:prefix') . "banlist` SET `expired` = 1 WHERE `id` = " . $line['id']);
            $line['expired'] = 1;
            $this->UpdateHtaccess();
          }
          if ($line['globalban'] != 1) {
            if ((in_array($board, explode('|', $line['boards'])) || $board == '')) {
              $line['appealin'] = substr(timeDiff($line['appealat'], true, 2), 0, -1);
              $bans[] = $line;
            }
          } else {
            $line['appealin'] = substr(timeDiff($line['appealat'], true, 2), 0, -1);
            $bans[] = $line;
          }
        }
      }
    }
    if (count($bans) > 0) {
      kxDB::getinstance()->exec("END TRANSACTION");
      echo $this->DisplayBannedMessage($bans);
      die();
    }

    if ($force_display) {
      /* Instructed to display a page whether banned or not, so we will inform them today is their rucky day */
      echo '<title>' . _('YOU ARE NOT BANNED!') . '</title><div align="center"><img src="' . kxEnv::Get('kx:paths:main:folder') . 'youarenotbanned.jpg"><br /><br />' . _('Unable to find record of your IP being banned.') . '</div>';
    } else {
      return true;
    }
  }

  /* Add a ip/ip range ban */
  public static function BanUser($ip, $board_ids, $duration, $reason, $allow_read, $allow_appeal, $notes, $staff_id, $delete_all = false)
  {
    $boards = kxDb::getInstance()->select("boards")
      ->fields("boards", ["board_id", "board_name"])
      ->where("board_id in ( " . implode(", ", $board_ids) . " )")
      ->execute()
      ->fetchAllAssoc("board_id",PDO::FETCH_ASSOC);
    $fields= [
      'ip' => $ip,
      'ipmd5' => md5($ip),
      'boards' => json_encode($boards),
      'created' => time(),
      'expires' => time() + $duration,
      'reason' => $reason,
      'allow_read' => (int) $allow_read,
      'staff_note' => $notes,
      'created_by_staff_id' => (int) $staff_id,
    ];

    $ban_query = kxDb::getInstance()->insert("banlist")
      ->fields($fields)
      ->execute();

    if (!$proxyban && $type == 1) {
      $this->UpdateHtaccess();
    }
    return true;
  }

  /* Return the page which will inform the user a quite unfortunate message */
  private static function DisplayBannedMessage($bans, $board = '')
  {
    /* Set a cookie with the users current IP address in case they use a proxy to attempt to make another post */
    setcookie('tc_previousip', $_SERVER['REMOTE_ADDR'], (time() + 604800), kxEnv::Get('kx:paths:boards:folder'));

    require_once KX_ROOT . '/lib/dwoo.php';

    kxTemplate::assign('bans', $bans);

    return $dwoo->get(KX_ROOT . kxEnv::Get('kx:templates:dir') . '/banned.html.twig', $twigData);
  }

  public static function UpdateHtaccess()
  {

    $htaccess_contents = file_get_contents(KX_BOARD . '.htaccess');
    $htaccess_contents_preserve = substr($htaccess_contents, 0, strpos($htaccess_contents, '## !KU_BANS:') + 12) . "\n";

    $htaccess_contents_bans_iplist = '';
    $results = $kx_db->GetAll("SELECT `ip` FROM `" . kxEnv::Get('kx:db:prefix') . "banlist` WHERE `allowread` = 0 AND `type` = 0 AND (`expired` =  1) ORDER BY `ip` ASC");
    if (count($results) > 0) {
      $htaccess_contents_bans_iplist .= 'RewriteCond %{REMOTE_ADDR} (';
      foreach ($results as $line) {
        $htaccess_contents_bans_iplist .= str_replace('.', '\\.', md5_decrypt($line['ip'], kxEnv::Get('kx:misc:randomseed'))) . '|';
      }
      $htaccess_contents_bans_iplist = substr($htaccess_contents_bans_iplist, 0, -1);
      $htaccess_contents_bans_iplist .= ')$' . "\n";
    }
    if ($htaccess_contents_bans_iplist != '') {
      $htaccess_contents_bans_start = "<IfModule mod_rewrite.c>\nRewriteEngine On\n";
      $htaccess_contents_bans_end = "RewriteRule !^(banned.php|youarebanned.jpg|favicon.ico|css/site_futaba.css)$ " . kxEnv::Get('kx:paths:boards:folder') . "banned.php [L]\n</IfModule>";
    } else {
      $htaccess_contents_bans_start = '';
      $htaccess_contents_bans_end = '';
    }
    $htaccess_contents_new = $htaccess_contents_preserve . $htaccess_contents_bans_start . $htaccess_contents_bans_iplist . $htaccess_contents_bans_end;
    file_put_contents(KX_BOARD . '.htaccess', $htaccess_contents_new);
  }
}
